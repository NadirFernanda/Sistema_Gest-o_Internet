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
                if ($sessions === null) {
                    // API falhou ou resposta incompleta — não actualizar status deste site
                    // para evitar marcar clientes como Offline por falha de ligação ao router
                    $this->warn("⚠️  {$site->nome}: listActiveSessions falhou (resposta incompleta) — status não actualizado.");
                    Log::warning('MikroTik: check-online-status ignorado para site — listActiveSessions null', [
                        'site_id' => $site->id, 'site' => $site->nome,
                    ]);
                    continue;
                }
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

        // Passo 2: verificar cada plano — primeiro no router do seu site,
        // depois nos outros routers (topologia real: Camama serve clientes de ambos os sites)
        $planos = Plano::whereNotNull('mikrotik_username')
            ->whereHas('cliente', fn($q) => $q->whereIn('mikrotik_site_id', $accessibleSiteIds))
            ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso'])
            ->with('cliente.mikrotikSite')
            ->get();

        // Pre-carregar modelos de sites para instanciar serviços de suspensão
        $siteModels = MikroTikSite::whereIn('id', $accessibleSiteIds)->get()->keyBy('id');

        // Índice de usernames com plano Ativo/Em aviso → impede suspender um secret
        // partilhado com outro plano que esteja activo (conflito de username duplicado)
        $usernamesAtivos = $planos
            ->whereIn('estado', ['Ativo', 'Em aviso'])
            ->pluck('mikrotik_username')
            ->filter()
            ->flip()
            ->toArray();

        $totalChecked  = 0;
        $forcedSuspend = 0;

        foreach ($planos as $plano) {
            $site     = $plano->cliente->mikrotikSite;
            $siteId   = $site->id;
            $username = $plano->mikrotik_username;

            // Verificar no router atribuído primeiro; depois nos outros
            $isOnline     = false;
            $activeSiteId = null;

            if (isset($activeUsersBySite[$siteId][$username])) {
                $isOnline     = true;
                $activeSiteId = $siteId;
            } else {
                foreach ($activeUsersBySite as $otherSiteId => $users) {
                    if ($otherSiteId !== $siteId && isset($users[$username])) {
                        $isOnline     = true;
                        $activeSiteId = $otherSiteId;
                        break;
                    }
                }
            }

            $totalChecked++;

            // Plano Suspenso mas com sessão activa → suspender em TODOS os routers
            // e só depois gravar o status (evita "Online flash" na BD)
            if ($plano->estado === 'Suspenso' && $isOnline) {
                // Segurança: proxima_renovacao no futuro indica cliente que pagou mas
                // cujo estado ficou Suspenso por inconsistência (ex: compensação sem reactivar).
                // Reactivar automaticamente em vez de suspender para não cortar cliente a pagar.
                if ($plano->proxima_renovacao && \Carbon\Carbon::parse($plano->proxima_renovacao)->isFuture()) {
                    Log::warning('MikroTik: plano Suspenso com renovação futura — reactivando automaticamente', [
                        'plano_id'          => $plano->id,
                        'username'          => $username,
                        'proxima_renovacao' => $plano->proxima_renovacao,
                    ]);
                    $plano->estado = 'Ativo';
                    $plano->save();
                    $this->updateStatus($plano, $site, true, null);
                    $this->warn("  ♻  Plano #{$plano->id} ({$username}) reactivado automaticamente — proxima_renovacao no futuro.");
                    continue;
                }

                // Segurança: se outro plano Ativo usa o MESMO username, não desactivar
                // o secret (bloquearia o cliente legítimo). Registar conflito e ignorar.
                if (isset($usernamesAtivos[$username])) {
                    Log::error('MikroTik: CONFLITO de username — plano Suspenso partilha username com plano Ativo, suspensão ignorada', [
                        'plano_id' => $plano->id,
                        'username' => $username,
                    ]);
                    $this->error("  ⚠ Plano #{$plano->id} ({$username}) CONFLITO: outro plano Ativo usa o mesmo username! Corrigir no Diagnóstico PPPoE.");
                    // Marcar como online (é a sessão do Ativo que está activa)
                    $this->updateStatus($plano, $site, true, null);
                    continue;
                }

                $suspended       = false;
                $routersUsados   = [];

                foreach ($accessibleSiteIds as $svSiteId) {
                    if (! isset($siteServices[$svSiteId])) {
                        $siteServices[$svSiteId] = \App\Services\MikroTikService::forSite(
                            $siteModels[$svSiteId]
                        );
                    }
                    if ($siteServices[$svSiteId]->suspendUser($plano)) {
                        $suspended      = true;
                        $routersUsados[] = $siteModels[$svSiteId]->nome;
                    }
                }

                if ($suspended) {
                    $forcedSuspend++;
                    // Gravar como Offline — o cliente foi desligado agora
                    $this->updateStatus($plano, $site, false, 'Suspenso — acesso bloqueado pelo sistema');
                    Log::warning('MikroTik: plano Suspenso ainda online — forçada suspensão', [
                        'plano_id' => $plano->id,
                        'username' => $username,
                        'routers'  => $routersUsados,
                    ]);
                    $label = implode(' + ', $routersUsados);
                    $this->warn("  🔒 Plano #{$plano->id} ({$username}) suspenso em: {$label}.");
                } else {
                    // Suspensão falhou em todos os routers — gravar como Online com aviso
                    $this->updateStatus($plano, $site, true, null);
                    Log::error('MikroTik: plano Suspenso online — suspensão FALHOU em todos os routers', [
                        'plano_id' => $plano->id,
                        'username' => $username,
                        'site'     => $site->nome,
                    ]);
                    $this->error("  ✗ Plano #{$plano->id} ({$username}) — FALHOU suspender em todos os routers. Ir ao Diagnóstico PPPoE.");
                }
                continue;
            }

            // Plano normal (Ativo/Em aviso) ou Suspenso já offline
            $siteLogs = $logsBySite[$siteId] ?? [];
            $reason   = $isOnline ? null : $this->findDisconnectReason($username, $siteLogs);
            $this->updateStatus($plano, $site, $isOnline, $reason);
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
            // Debounce: aguardar um segundo ciclo antes de confirmar queda.
            // PPPoE sessions podem desaparecer brevemente durante renovação de sessão
            // ou por falha momentânea da API do router — registar só se o cliente
            // estiver offline em dois ciclos consecutivos (≥ cron interval).
            $minsSinceOnline = $status->last_seen_online_at
                ? now()->diffInMinutes($status->last_seen_online_at)
                : 999;

            if ($minsSinceOnline < 7) {
                // Primeira detecção offline — aguardar confirmação no próximo ciclo.
                // Não alterar is_online nem criar evento ainda.
                Log::debug('MikroTik: cliente aparece offline mas aguarda confirmação (debounce)', [
                    'plano_id' => $plano->id,
                    'username' => $plano->mikrotik_username,
                    'mins_since_online' => $minsSinceOnline,
                ]);
                return;
            }

            // Transição confirmada: online → offline (dois ciclos consecutivos offline)
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

            Log::warning('MikroTik: cliente caiu offline (confirmado em 2 ciclos)', [
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
