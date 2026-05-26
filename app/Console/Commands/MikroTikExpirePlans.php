<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MikroTikExpirePlans extends Command
{
    protected $signature   = 'mikrotik:expire-plans';
    protected $description = 'Suspende no MikroTik os planos cuja proxima_renovacao já passou';

    public function handle(): int
    {
        $sites = MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('Sem sites MikroTik activos configurados.');
            return self::SUCCESS;
        }

        $totalOk   = 0;
        $totalFail = 0;

        foreach ($sites as $site) {
            $mikrotik = MikroTikService::forSite($site);

            // Planos vencidos que ainda não foram suspensos
            $expired = Plano::with('cliente')
                ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                ->whereNotNull('mikrotik_username')
                ->where('proxima_renovacao', '<', Carbon::today())
                ->whereNotIn('estado', ['Cancelado', 'Suspenso'])
                ->get();

            // Planos já marcados como Suspenso na BD mas ainda não sincronizados ao MikroTik
            $pendingSuspend = Plano::with('cliente')
                ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                ->whereNotNull('mikrotik_username')
                ->where('estado', 'Suspenso')
                ->where(function ($q) {
                    $q->whereNull('mikrotik_synced_at')
                      ->orWhereColumn('mikrotik_synced_at', '<', 'updated_at');
                })
                ->get();

            $toSuspend = $expired->merge($pendingSuspend)->unique('id');

            if ($toSuspend->isEmpty()) {
                $this->line("  [{$site->nome}] Sem planos por suspender.");
                continue;
            }

            $this->info("  [{$site->nome}] A suspender {$toSuspend->count()} plano(s)…");

            foreach ($toSuspend as $plano) {
                if ($mikrotik->suspendUser($plano)) {
                    $plano->estado = 'Suspenso';
                    $plano->saveQuietly();
                    $totalOk++;
                } else {
                    $totalFail++;
                    $this->warn("    ✗ Plano #{$plano->id} — {$plano->cliente?->nome}");
                }
            }
        }

        $this->info("Concluído — suspensos: $totalOk | Falhas: $totalFail");

        return $totalFail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
