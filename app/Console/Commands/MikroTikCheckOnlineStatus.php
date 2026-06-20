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

        // Passo 1: recolher TODAS as sessões activas de todos os routers acessíveis
        // (clientes podem estar conectados num router diferente do atribuído no BD)
        $allActiveUsernames = [];
        $accessibleSiteIds  = [];

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
                $count    = 0;
                foreach ($sessions as $s) {
                    $name = $s['name'] ?? '';
                    if ($name !== '') {
                        $allActiveUsernames[$name] = true;
                        $count++;
                    }
                }

                $accessibleSiteIds[] = $site->id;
                $this->line("📡 {$site->nome}: {$count} sessões activas");
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

        $this->line('📊 Total de usernames activos (todos os routers): ' . count($allActiveUsernames));

        // Passo 2: verificar todos os clientes Ativo de todos os sites acessíveis
        // contra a lista unificada de sessões (independente de qual router)
        $planos = Plano::whereNotNull('mikrotik_username')
            ->whereHas('cliente', fn($q) => $q->whereIn('mikrotik_site_id', $accessibleSiteIds))
            ->where('estado', 'Ativo')
            ->with('cliente.mikrotikSite')
            ->get();

        $totalChecked = 0;
        foreach ($planos as $plano) {
            $isOnline = isset($allActiveUsernames[$plano->mikrotik_username]);
            $site     = $plano->cliente->mikrotikSite;
            $this->updateStatus($plano, $site, $isOnline);
            $totalChecked++;
        }

        $this->info("✨ Verificação completa! {$totalChecked} clientes verificados.");
        return 0;
    }

    private function updateStatus(Plano $plano, MikroTikSite $site, bool $isOnline): void
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
            $status->is_online = false;
            $status->last_seen_offline_at = now();
            $status->disconnect_reason = 'Queda detectada';

            // Registar evento: caiu offline
            MikroTikOnlineStatusEvent::create([
                'plano_id' => $plano->id,
                'mikrotik_online_status_id' => $status->id,
                'event_type' => 'offline',
                'occurred_at' => now(),
                'disconnect_reason' => 'Queda detectada',
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
            // Mantém-se offline
            $status->last_seen_offline_at = now();
        }

        $status->save();
    }
}
