<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\MikroTikSite;
use App\Models\Plano;
use Illuminate\Support\Facades\Log;

/**
 * High-level MikroTik HotSpot management service.
 *
 * Each instance is bound to one site (RouterBoard).
 * Use MikroTikService::forSite($site) to get a configured instance.
 *
 * Username strategy: phone digits with optional prefix from site config.
 * Profile mapping:   PlanTemplate.name (matches MikroTik profile name directly).
 */
class MikroTikService
{
    private MikroTikApiClient $api;

    private string $host;
    private int    $port;
    private string $username;
    private string $password;
    private string $userPrefix;
    private string $defaultProfile;

    public function __construct(?MikroTikSite $site = null)
    {
        if ($site) {
            $this->host           = $site->host;
            $this->port           = $site->port;
            $this->username       = $site->username;
            $this->password       = $site->password;
            $this->userPrefix     = $site->user_prefix;
            $this->defaultProfile = $site->default_profile;
        } else {
            // fallback para .env (sem site configurado na BD)
            $this->host           = (string) config('services.mikrotik.host',            '');
            $this->port           = (int)    config('services.mikrotik.port',            8728);
            $this->username       = (string) config('services.mikrotik.username',        'admin');
            $this->password       = (string) config('services.mikrotik.password',        '');
            $this->userPrefix     = (string) config('services.mikrotik.user_prefix',     '');
            $this->defaultProfile = (string) config('services.mikrotik.default_profile', 'default');
        }

        $this->api = new MikroTikApiClient($this->host, $this->port);
    }

    public static function forSite(MikroTikSite $site): self
    {
        return new self($site);
    }

    public function isConfigured(): bool
    {
        return $this->host !== '';
    }

    // ─── Public operations ───────────────────────────────────────────────────

    /**
     * Activate or renew a client's HotSpot account based on their Plano.
     * Creates the user if absent; updates profile and enables if suspended.
     */
    public function activateUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) {
            Log::debug('MikroTik: não configurado, a ignorar activação', ['plano_id' => $plano->id]);
            return false;
        }

        try {
            $this->connect();

            $cliente  = $plano->cliente;
            $username = $this->buildUsername($cliente);
            $password = $this->buildPassword($cliente);
            $profile  = $this->resolveProfile($plano);
            $comment  = "SGA#{$plano->id}|{$cliente->nome}";

            $existing = $this->findUser($username);

            $disabled = $plano->estado === 'Suspenso' ? 'yes' : 'no';

            if ($existing) {
                $this->api->command('/ip/hotspot/user/set', [
                    '.id'      => $existing['.id'],
                    'profile'  => $profile,
                    'disabled' => $disabled,
                    'comment'  => $comment,
                ]);
                Log::info('MikroTik: utilizador actualizado', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host, 'disabled' => $disabled,
                ]);
            } else {
                $this->api->command('/ip/hotspot/user/add', [
                    'name'     => $username,
                    'password' => $password,
                    'profile'  => $profile,
                    'disabled' => $disabled,
                    'comment'  => $comment,
                ]);
                Log::info('MikroTik: utilizador criado', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host, 'disabled' => $disabled,
                ]);
            }

            $plano->mikrotik_username  = $username;
            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('MikroTik: activação falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Suspend a user when their plan expires (disabled=yes, not deleted).
     */
    public function suspendUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) return false;

        try {
            $this->connect();
            $username = $plano->mikrotik_username ?? $this->buildUsername($plano->cliente);
            $existing = $this->findUser($username);

            if ($existing) {
                $this->api->command('/ip/hotspot/user/set', [
                    '.id'      => $existing['.id'],
                    'disabled' => 'yes',
                    'comment'  => ($existing['comment'] ?? '') . '|SUSPENSO',
                ]);
                Log::info('MikroTik: utilizador suspenso', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host,
                ]);
            }

            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('MikroTik: suspensão falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Permanently remove a HotSpot user (cancelled plan).
     */
    public function removeUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) return false;

        try {
            $this->connect();
            $username = $plano->mikrotik_username ?? $this->buildUsername($plano->cliente);
            $existing = $this->findUser($username);

            if ($existing) {
                $this->api->command('/ip/hotspot/user/remove', ['.id' => $existing['.id']]);
                Log::info('MikroTik: utilizador removido', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host,
                ]);
            }

            $plano->mikrotik_username  = null;
            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('MikroTik: remoção falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Return available HotSpot profiles (to map plan templates).
     */
    public function listProfiles(): array
    {
        if (! $this->isConfigured()) return [];

        try {
            $this->connect();
            $result = $this->api->command('/ip/hotspot/user/profile/print');
            return array_values(array_filter($result, fn($r) => ($r['type'] ?? '') === '!re'));
        } catch (\Throwable $e) {
            Log::error('MikroTik: listProfiles falhado', ['host' => $this->host, 'error' => $e->getMessage()]);
            return [];
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Ping the router — used by the admin panel status widget.
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => false, 'error' => 'Host não definido'];
        }

        try {
            $this->connect();
            $result   = $this->api->command('/system/identity/print');
            $identity = '';
            foreach ($result as $r) {
                if (isset($r['name'])) {
                    $identity = $r['name'];
                }
            }
            return ['ok' => true, 'identity' => $identity, 'host' => $this->host];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage(), 'host' => $this->host];
        } finally {
            $this->safeDisconnect();
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function connect(): void
    {
        $this->api->connect($this->username, $this->password);
    }

    private function safeDisconnect(): void
    {
        try { $this->api->disconnect(); } catch (\Throwable) {}
    }

    private function findUser(string $username): ?array
    {
        $result = $this->api->command('/ip/hotspot/user/print', [], ['name' => $username]);
        foreach ($result as $r) {
            if (($r['type'] ?? '') === '!re') return $r;
        }
        return null;
    }

    private function buildUsername(Cliente $cliente): string
    {
        $phone = preg_replace('/\D/', '', $cliente->contato ?? '');
        $base  = $phone !== ''
            ? $phone
            : preg_replace('/[^a-z0-9]/', '', strtolower($cliente->nome ?? 'cliente'));

        return $this->userPrefix . $base;
    }

    private function buildPassword(Cliente $cliente): string
    {
        $phone = preg_replace('/\D/', '', $cliente->contato ?? '');
        return $phone !== '' ? $phone : substr(md5($cliente->id . $cliente->nome), 0, 8);
    }

    private function resolveProfile(Plano $plano): string
    {
        // Profile name = PlanTemplate name (must match exactly in MikroTik)
        $templateName = $plano->template?->name ?? '';
        return $templateName !== '' ? $templateName : $this->defaultProfile;
    }
}
