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
    private ?string $lastError = null;

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

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        return $this->host !== '';
    }

    // ─── Public operations ───────────────────────────────────────────────────

    /**
     * Activate or renew a client's PPPoE secret based on their Plano.
     * Creates the secret if absent; updates profile and enables if suspended.
     */
    public function activateUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) {
            Log::debug('MikroTik: não configurado, a ignorar activação', ['plano_id' => $plano->id]);
            return false;
        }

        $this->lastError = null;

        try {
            $this->connect();

            $cliente  = $plano->cliente;
            $username = $plano->mikrotik_username ?? $this->buildUsername($cliente);
            $password = $this->buildPassword($cliente);
            $profile  = $this->resolveProfile($plano);
            $comment  = "SGA#{$plano->id}|{$cliente->nome}";

            $existing = $this->findUser($username);

            $disabled = in_array($plano->estado, ['Suspenso', 'Cancelado']) ? 'yes' : 'no';

            if ($existing) {
                $resp = $this->api->command('/ppp/secret/set', [
                    '.id'      => $existing['.id'],
                    'profile'  => $profile,
                    'disabled' => $disabled,
                    'comment'  => $comment,
                ]);
                if ($this->isProfileError($resp) && $profile !== $this->defaultProfile && $this->defaultProfile !== '') {
                    Log::warning('MikroTik PPPoE: perfil não existe no set, usando default_profile', [
                        'profile' => $profile, 'fallback' => $this->defaultProfile, 'host' => $this->host,
                    ]);
                    $resp = $this->api->command('/ppp/secret/set', [
                        '.id'      => $existing['.id'],
                        'profile'  => $this->defaultProfile,
                        'disabled' => $disabled,
                        'comment'  => $comment,
                    ]);
                }
                if ($this->isProfileError($resp)) {
                    Log::warning('MikroTik PPPoE: default_profile também inválido, a ignorar perfil', [
                        'host' => $this->host, 'username' => $username,
                    ]);
                    $resp = $this->api->command('/ppp/secret/set', [
                        '.id'      => $existing['.id'],
                        'disabled' => $disabled,
                        'comment'  => $comment,
                    ]);
                }
                $this->throwIfTrap($resp, 'set');

                // Só desconectar para forçar reconexão quando o secret estava suspenso
                // e está agora a ser reactivado. Se já estava activo, não interromper a sessão.
                $wasDisabled = ($existing['disabled'] ?? $existing['=disabled'] ?? 'no') === 'yes';
                if ($disabled === 'no' && $wasDisabled) {
                    $this->disconnectActivePpp($username);
                }

                Log::info('MikroTik PPPoE: secret actualizado', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host, 'disabled' => $disabled,
                ]);
            } else {
                $resp = $this->api->command('/ppp/secret/add', [
                    'name'     => $username,
                    'password' => $password,
                    'profile'  => $profile,
                    'disabled' => $disabled,
                    'comment'  => $comment,
                    'service'  => 'pppoe',
                ]);
                if ($this->isProfileError($resp) && $profile !== $this->defaultProfile && $this->defaultProfile !== '') {
                    Log::warning('MikroTik PPPoE: perfil não existe no add, usando default_profile', [
                        'profile' => $profile, 'fallback' => $this->defaultProfile, 'host' => $this->host,
                    ]);
                    $resp = $this->api->command('/ppp/secret/add', [
                        'name'     => $username,
                        'password' => $password,
                        'profile'  => $this->defaultProfile,
                        'disabled' => $disabled,
                        'comment'  => $comment,
                        'service'  => 'pppoe',
                    ]);
                }
                if ($this->isProfileError($resp)) {
                    Log::warning('MikroTik PPPoE: default_profile também inválido, a criar sem perfil', [
                        'host' => $this->host, 'username' => $username,
                    ]);
                    $resp = $this->api->command('/ppp/secret/add', [
                        'name'     => $username,
                        'password' => $password,
                        'disabled' => $disabled,
                        'comment'  => $comment,
                        'service'  => 'pppoe',
                    ]);
                }
                $this->throwIfTrap($resp, 'add');
                Log::info('MikroTik PPPoE: secret criado', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host, 'disabled' => $disabled,
                ]);
            }

            $plano->mikrotik_username  = $username;
            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            Log::error('MikroTik PPPoE: activação falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Suspend a PPPoE user: disable secret + disconnect active session immediately.
     */
    public function suspendUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) return false;

        try {
            $this->connect();
            $username = $plano->mikrotik_username ?? $this->buildUsername($plano->cliente);
            $existing = $this->findUser($username);

            if ($existing) {
                $this->api->command('/ppp/secret/set', [
                    '.id'      => $existing['.id'],
                    'disabled' => 'yes',
                    'comment'  => ($existing['comment'] ?? '') . '|SUSPENSO',
                ]);

                // Desconectar sessão PPPoE activa imediatamente
                $this->disconnectActivePpp($username);

                Log::info('MikroTik PPPoE: secret desactivado e sessão terminada', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host,
                ]);
            }

            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('MikroTik PPPoE: suspensão falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Permanently remove a PPPoE secret (cancelled plan).
     */
    public function removeUser(Plano $plano): bool
    {
        if (! $this->isConfigured()) return false;

        try {
            $this->connect();
            $username = $plano->mikrotik_username ?? $this->buildUsername($plano->cliente);
            $existing = $this->findUser($username);

            if ($existing) {
                $this->disconnectActivePpp($username);
                $this->api->command('/ppp/secret/remove', ['.id' => $existing['.id']]);
                Log::info('MikroTik PPPoE: secret removido', [
                    'username' => $username, 'plano_id' => $plano->id, 'host' => $this->host,
                ]);
            }

            $plano->mikrotik_username  = null;
            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('MikroTik PPPoE: remoção falhada', [
                'plano_id' => $plano->id, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Return available PPPoE profiles.
     */
    public function listProfiles(): array
    {
        if (! $this->isConfigured()) return [];

        try {
            $this->connect();
            $result = $this->api->command('/ppp/profile/print');
            return array_values(array_filter($result, fn($r) => ($r['type'] ?? '') === '!re'));
        } catch (\Throwable $e) {
            Log::error('MikroTik PPPoE: listProfiles falhado', ['host' => $this->host, 'error' => $e->getMessage()]);
            return [];
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * List all active PPPoE sessions on this router.
     * 
     * @return array Array of active sessions, each with keys: name, address, uptime, etc.
     */
    public function listActiveSessions(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        try {
            $this->connect();
            $result = $this->api->command('/ppp/active/print');
            return array_values(array_filter($result, fn($r) => ($r['type'] ?? '') === '!re'));
        } catch (\Throwable $e) {
            Log::error('MikroTik PPPoE: listActiveSessions falhado', [
                'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return [];
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Get all simple queue stats (includes dynamic PPPoE queues <pppoe-username>).
     * Used to sample bandwidth usage per client.
     */
    public function getAllQueueStats(): array
    {
        if (! $this->isConfigured()) return [];

        try {
            $this->connect();
            $result = $this->api->command('/queue/simple/print');
            return array_values(array_filter($result, fn($r) => ($r['type'] ?? '') === '!re'));
        } catch (\Throwable $e) {
            Log::debug('MikroTik: falha ao obter queue stats', [
                'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return [];
        } finally {
            $this->safeDisconnect();
        }
    }

    /**
     * Fetch recent PPP-related log entries from this router.
     * Used to determine disconnect reasons for offline clients.
     */
    public function getRecentPppLogs(): array
    {
        if (! $this->isConfigured()) return [];

        try {
            $this->connect();
            $result = $this->api->command('/log/print');

            $logs = array_filter(
                array_filter($result, fn($r) => ($r['type'] ?? '') === '!re'),
                fn($r) => str_contains($r['topics'] ?? '', 'ppp')
            );

            // Most recent first
            return array_reverse(array_values($logs));
        } catch (\Throwable $e) {
            Log::debug('MikroTik: falha ao obter logs PPP', [
                'host' => $this->host, 'error' => $e->getMessage(),
            ]);
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

    /**
     * Apply a rate-limit on the PPP PROFILE of a given username and reconnect their session.
     * RouterOS only creates dynamic queues (<pppoe-username>) when the profile has rate-limit.
     * Affects all clients on the same profile (correct: same plan = same speed).
     *
     * @return array{ok: bool, profile: string, message: string}
     */
    public function applyRateLimitViaProfile(string $username, string $rateLimit): array
    {
        try {
            $this->connect();

            // Find the user's secret to get their profile name
            $secret = $this->findUser($username);
            if (! $secret) {
                return ['ok' => false, 'profile' => '', 'message' => "Secret '{$username}' não encontrado"];
            }

            $profileName = $secret['profile'] ?? $secret['=profile'] ?? '';
            if (! $profileName) {
                return ['ok' => false, 'profile' => '', 'message' => 'Profile não definido no secret'];
            }

            // Find the profile on the router
            $profiles = $this->api->command('/ppp/profile/print', [], ['name' => $profileName]);
            $profile  = null;
            foreach ($profiles as $p) {
                if (($p['type'] ?? '') === '!re') { $profile = $p; break; }
            }

            if (! $profile) {
                return ['ok' => false, 'profile' => $profileName, 'message' => "Profile '{$profileName}' não encontrado no router"];
            }

            // Set rate-limit on the profile
            $this->api->command('/ppp/profile/set', [
                '.id'        => $profile['.id'],
                'rate-limit' => $rateLimit,
            ]);

            // Disconnect active session → client reconnects with new profile → dynamic queue created
            $this->disconnectActivePpp($username);

            Log::info('MikroTik: rate-limit aplicado no perfil e sessão reconectada', [
                'username' => $username, 'profile' => $profileName,
                'rate-limit' => $rateLimit, 'host' => $this->host,
            ]);

            return ['ok' => true, 'profile' => $profileName, 'message' => 'OK'];
        } catch (\Throwable $e) {
            Log::error('MikroTik: falha ao aplicar rate-limit no perfil', [
                'username' => $username, 'host' => $this->host, 'error' => $e->getMessage(),
            ]);
            return ['ok' => false, 'profile' => '', 'message' => $e->getMessage()];
        } finally {
            $this->safeDisconnect();
        }
    }

    private function findUser(string $username): ?array
    {
        $result = $this->api->command('/ppp/secret/print', [], ['name' => $username]);
        foreach ($result as $r) {
            if (($r['type'] ?? '') === '!re') return $r;
        }
        return null;
    }

    private function disconnectActivePpp(string $username): void
    {
        $sessions = $this->api->command('/ppp/active/print', [], ['name' => $username]);
        foreach ($sessions as $s) {
            if (($s['type'] ?? '') === '!re' && isset($s['.id'])) {
                $this->api->command('/ppp/active/remove', ['.id' => $s['.id']]);
            }
        }
    }

    private function isProfileError(array $response): bool
    {
        foreach ($response as $r) {
            if (($r['type'] ?? '') === '!trap') {
                $msg = $r['=message'] ?? $r['message'] ?? '';
                return stripos($msg, 'profile') !== false;
            }
        }
        return false;
    }

    private function throwIfTrap(array $response, string $op): void
    {
        foreach ($response as $r) {
            if (($r['type'] ?? '') === '!trap') {
                $msg = $r['=message'] ?? $r['message'] ?? 'erro desconhecido';
                throw new \RuntimeException("MikroTik: router rejeitou comando '$op' — $msg");
            }
        }
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
        // 1. Template name (authoritative)
        $templateName = $plano->template?->name ?? '';
        if ($templateName !== '') return $templateName;

        // 2. Plan's stored nome — for plans created before templates existed (template_id null)
        $planNome = trim($plano->nome ?? '');
        if ($planNome !== '') return $planNome;

        // 3. Site default profile as last resort
        return $this->defaultProfile;
    }
}
