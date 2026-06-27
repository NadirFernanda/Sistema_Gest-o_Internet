<?php

namespace App\Console\Commands;

use App\Models\MikroTikOnlineStatus;
use App\Models\MikroTikOnlineStatusEvent;
use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MikroTikCheckOnlineStatus extends Command
{
    protected $signature = 'mikrotik:check-online-status {--site= : Check only a specific site}';

    protected $description = 'Check online status of all MikroTik PPPoE users and update tracking';

    public function handle(): int
    {
        $this->info('🔍 Verificando status online dos clientes MikroTik...');

        $siteId = $this->option('site');
        $sites  = $siteId
            ? MikroTikSite::where('id', $siteId)->get()
            : MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('⚠️  Nenhum site MikroTik ativo encontrado.');
            return 0;
        }

        // Passo 1: recolher sessões activas e logs PPP POR SITE (não juntar tudo)
        // para evitar colisões de username entre routers diferentes
        $activeUsersBySite = []; // [siteId => [username => true]]
        $logsBySite        = []; // [siteId => [...entries]]
        $accessibleSiteIds = [];
        $siteServices      = []; // reutilizar instâncias para force-suspend

        foreach ($sites as $site) {
            try {
                $service = MikroTikService::forSite($site);
                $test    = $service->testConnection();

                if (! $test['ok']) {
                    $this->warn("⚠️  Router inacessível ({$site->nome}): " . ($test['error'] ?? 'sem resposta'));
                    Log::warning('MikroTik: check-online-status ignorado — router inacessível', [
                        'site_id' => $site->id, 'site' => $site->nome, 'error' => $test['error'] ?? '',
                    ]);
                    continue;
                }

                $sessions = $service->listActiveSessions();
                $activeUsersBySite[$site->id] = [];
                $count = 0;
                foreach ($sessions as $s) {
                    $name = $s['name'] ?? '';
                    if ($name !== '') {
                        $activeUsersBySite[$site->id][$name] = true;
                        $count++;
                    }
                }

                $logs = $service->getRecentPppLogs();
                $logsBySite[$site->id] = $logs;

                $accessibleSiteIds[]      = $site->id;
                $siteServices[$site->id]  = $service;

                $this->line("📡 {$site->nome}: {$count} sessões activas, " . count($logs) . " entradas de log PPP");
                Log::info('MikroTik: sessões activas recolhidas', [
                    'site' => $site->nome, 'count' => $count,
                ]);
            } catch (\Throwable $e) {
                $this->error("❌ Erro ao ligar a {$site->nome}: {$e->getMessage()}");
                Log::error('MikroTik: falha ao recolher sessões', [
                    'site_id' => $site->id, 'error' => $e->getMessage(),
                ]);
            }
        }

        if (empty($accessibleSiteIds)) {
            $this->warn('⚠️  Nenhum router acessível. Status não actualizado.');
            return 0;
        }

        $totalActive = array_sum(array_map('count', $activeUsersBySite));
        $this->line("📊 Total de usernames activos (todos os routers): {$totalActive}");

        // Passo 2: verificar cada plano apenas contra o router do seu próprio site
        $planos = Plano::whereNotNull('mikrotik_username')
            ->whereHas('cliente', fn($q) => $q->whereIn('mikrotik_site_id', $accessibleSiteIds))
            ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso'])
            ->with('cliente.mikrotikSite')
            ->get();

        $totalChecked  = 0;
        $forcedSuspend = 0;

        foreach ($planos as $plano) {
            $site   = $plano->cliente->mikrotikSite;
            $siteId = $site->id;

            // Verificar apenas contra sessões do router deste plano
            $siteActive = $activeUsersBySite[$siteId] ?? [];
            $isOnline   = isset($siteActive[$plano->mikrotik_username]);

            $siteLogs = $logsBySite[$siteId] ?? [];
            $reason   = $isOnline ? null : $this->findDisconnectReason($plano->mikrotik_username, $siteLogs);

            $this->updateStatus($plano, $site, $isOnline, $reason);
            $totalChecked++;

            if ($plano->estado === 'Suspenso' && $isOnline) {
                if (! isset($siteServices[$siteId])) {
                    $siteServices[$siteId] = \App\Services\MikroTikService::forSite($site);
                }
                if ($siteServices[$siteId]->suspendUser($plano)) {
                    $forcedSuspend++;
                    Log::warning('MikroTik: plano Suspenso ainda online — forçada suspensão', [
                        'plano_id' => $plano->id,
                        'username' => $plano->mikrotik_username,
                        'site'     => $site->nome,
                    ]);
                    $this->warn("  🔒 Plano #{$plano->id} ({$plano->mikrotik_username}) suspenso forçado — estava online indevidamente.");
                } else {
                    Log::error('MikroTik: plano Suspenso online — suspensão FALHOU (secret não existe no router?)', [
                        'plano_id' => $plano->id,
                        'username' => $plano->mikrotik_username,
                        'site'     => $site->nome,
                    ]);
                    $this->error("  ✗ Plano #{$plano->id} ({$plano->mikrotik_username}) — FALHOU suspender. Secret não existe no router. Ir ao Diagnóstico PPPoE.");
                }
            }
        }

        if ($forcedSuspend > 0) {
            $this->warn("🔒 {$forcedSuspend} plano(s) suspenso(s) à força (estavam online indevidamente).");
        }
        $this->info("✨ Verificação completa! {$totalChecked} clientes verificados.");
        return 0;
    }

    private function findDisconnectReason(string $username, array $logs): string
    {
        foreach ($logs as $entry) {
            $message = $entry['message'] ?? '';

            if (! str_contains($message, $username)) continue;

            // Padrões mais específicos primeiro
            if (str_contains($message, 'lcp-echo-timeout'))       return 'lcp-echo-timeout (sinal fraco ou cabo)';
            if (str_contains($message, 'authentication failed'))   return 'Falha de autenticação (senha errada)';
            if (str_contains($message, 'user request'))            return 'Desligado pelo utilizador';
            if (str_contains($message, 'link failure'))            return 'Falha de ligação física';
            if (str_contains($message, 'session timeout'))         return 'Timeout de sessão';
            if (str_contains($message, 'idle timeout'))            return 'Timeout por inactividade';
            if (str_contains($message, 'admin'))                   return 'Desconectado pelo administrador';
            if (str_contains($message, 'terminated'))              return 'Sessão terminada';
            if (str_contains($message, 'auth'))                    return 'Falha de autenticação';

            // Tentar extrair razão do padrão "logged out: <razão>"
            if (preg_match('/logged\s+out[:\s]+(.+)/i', $message, $m)) {
                return trim($m[1]);
            }
            // Qualquer entrada que mencione o username já é relevante
            if (str_contains($message, 'disconnected') || str_contains($message, 'logged')) {
                return 'Desconectado';
            }
        }

        return 'Queda detectada';
    }

    private function updateStatus(Plano $plano, MikroTikSite $site, bool $isOnline, ?string $disconnectReason = null): void
    {
        $status = MikroTikOnlineStatus::firstOrCreate(
            [
                'plano_id' => $plano->id,
                'mikrotik_site_id' => $site->id,
            ],
            [
                'is_online' => $isOnline,
                'last_seen_online_at' => $isOnline ? now() : null,
                'last_seen_offline_at' => !$isOnline ? now() : null,
            ]
        );

        $wasOnline = $status->is_online;

        if ($isOnline && !$wasOnline) {
            // Transição: offline → online
            $offlineAt = $status->last_seen_offline_at ?? now();
            $downtime  = max(0, now()->timestamp - $offlineAt->timestamp);
            $status->total_downtime_seconds = ((int) $status->total_downtime_seconds) + $downtime;
            $status->is_online = true;
            $status->last_seen_online_at = now();
            $status->disconnect_reason = null;

            // Actualizar o evento "offline" mais recente com a duração real da queda
            MikroTikOnlineStatusEvent::where('mikrotik_online_status_id', $status->id)
                ->where('event_type', 'offline')
                ->whereNull('duration_seconds')
                ->orderBy('occurred_at', 'desc')
                ->first()
                ?->update(['duration_seconds' => $downtime]);

            // Registar evento: voltou online
            MikroTikOnlineStatusEvent::create([
                'plano_id' => $plano->id,
                'mikrotik_online_status_id' => $status->id,
                'event_type' => 'online',
                'occurred_at' => now(),
                'duration_seconds' => $downtime,
            ]);

            Log::info('MikroTik: cliente voltou online', [
                'plano_id' => $plano->id,
                'username' => $plano->mikrotik_username,
                'downtime_seconds' => $downtime,
                'site' => $site->nome,
            ]);
        } elseif (!$isOnline && $wasOnline) {
            // Transição: online → offline
            $reason = $disconnectReason ?? 'Queda detectada';
            $status->is_online = false;
            $status->last_seen_offline_at = now();
            $status->disconnect_reason = $reason;

            // Registar evento: caiu offline
            MikroTikOnlineStatusEvent::create([
                'plano_id' => $plano->id,
                'mikrotik_online_status_id' => $status->id,
                'event_type' => 'offline',
                'occurred_at' => now(),
                'disconnect_reason' => $reason,
            ]);

            Log::warning('MikroTik: cliente caiu offline', [
                'plano_id' => $plano->id,
                'username' => $plano->mikrotik_username,
                'site' => $site->nome,
            ]);
        } elseif ($isOnline) {
            // Mantém-se online
            $status->last_seen_online_at = now();
        } else {
            // Mantém-se offline — tentar actualizar razão se ainda genérica
            $status->last_seen_offline_at = now();
            if ($disconnectReason && $disconnectReason !== 'Queda detectada'
                && (! $status->disconnect_reason || $status->disconnect_reason === 'Queda detectada')) {
                $status->disconnect_reason = $disconnectReason;
                MikroTikOnlineStatusEvent::where('mikrotik_online_status_id', $status->id)
                    ->where('event_type', 'offline')
                    ->where(fn($q) => $q->whereNull('disconnect_reason')
                        ->orWhere('disconnect_reason', 'Queda detectada'))
                    ->orderBy('occurred_at', 'desc')
                    ->first()
                    ?->update(['disconnect_reason' => $disconnectReason]);
            }
        }

        $status->save();
    }
}
