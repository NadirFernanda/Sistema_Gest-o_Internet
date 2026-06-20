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
        $sites = $siteId
            ? [MikroTikSite::findOrFail($siteId)]
            : MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('⚠️  Nenhum site MikroTik ativo encontrado.');
            return 0;
        }

        $totalChecked = 0;
        $totalUpdated = 0;

        foreach ($sites as $site) {
            try {
                $this->info("📡 Verificando site: {$site->nome} ({$site->host})");

                $service = MikroTikService::forSite($site);

                // Verifica ligação antes de actualizar — se falhar, não marca ninguém offline
                $test = $service->testConnection();
                if (! $test['ok']) {
                    $this->warn("  ⚠️  Router inacessível ({$site->nome}): " . ($test['error'] ?? 'sem resposta'));
                    Log::warning('MikroTik: check-online-status ignorado — router inacessível', [
                        'site_id' => $site->id, 'site' => $site->nome, 'error' => $test['error'] ?? '',
                    ]);
                    continue;
                }

                $activeSessions = $service->listActiveSessions();

                // Create a map of active usernames
                $activeUsernames = collect($activeSessions)
                    ->keyBy(fn($s) => $s['name'] ?? '')
                    ->keys()
                    ->toArray();

                // Get all synced planos for this site (usando relacionamento de clientes)
                $planos = Plano::whereNotNull('mikrotik_username')
                    ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                    ->where('estado', 'Ativo')
                    ->get();

                foreach ($planos as $plano) {
                    $isOnline = in_array($plano->mikrotik_username, $activeUsernames);
                    $this->updateStatus($plano, $site, $isOnline, $activeSessions);
                    $totalChecked++;
                    $totalUpdated++;
                }

                $this->line("  ✅ {$planos->count()} clientes verificados");
            } catch (\Throwable $e) {
                $this->error("❌ Erro ao verificar site {$site->nome}: {$e->getMessage()}");
                Log::error('MikroTik: check-online-status falhou', [
                    'site_id' => $site->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("✨ Verificação completa! {$totalUpdated}/{$totalChecked} clientes atualizados.");
        return 0;
    }

    private function updateStatus(
        Plano $plano,
        MikroTikSite $site,
        bool $isOnline,
        array $activeSessions,
    ): void {
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
            $downtime = now()->diffInSeconds($status->last_seen_offline_at ?? now());
            $status->total_downtime_seconds += $downtime;
            $status->is_online = true;
            $status->last_seen_online_at = now();
            $status->disconnect_reason = null;

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
