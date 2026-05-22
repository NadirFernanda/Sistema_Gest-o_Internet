<?php

namespace App\Console\Commands;

use App\Models\Plano;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MikroTikExpirePlans extends Command
{
    protected $signature   = 'mikrotik:expire-plans';
    protected $description = 'Suspende no MikroTik os planos cuja proxima_renovacao já passou';

    public function handle(MikroTikService $mikrotik): int
    {
        if (! $mikrotik->isConfigured()) {
            $this->warn('MikroTik não configurado. Defina MIKROTIK_HOST no .env');
            return self::SUCCESS;
        }

        // Plans that are past renewal date AND have a MikroTik username (were activated)
        $expired = Plano::with('cliente')
            ->whereNotNull('mikrotik_username')
            ->where('proxima_renovacao', '<', Carbon::today())
            ->whereNotIn('estado', ['Cancelado', 'Suspenso'])
            ->get();

        if ($expired->isEmpty()) {
            $this->info('Sem planos vencidos por suspender.');
            return self::SUCCESS;
        }

        $this->info("A suspender {$expired->count()} plano(s) vencido(s)…");

        $ok = $failed = 0;

        foreach ($expired as $plano) {
            if ($mikrotik->suspendUser($plano)) {
                $ok++;
            } else {
                $failed++;
                $this->warn("  ✗ Plano #{$plano->id} — {$plano->cliente?->nome}");
            }
        }

        $this->info("Concluído — suspensos: $ok | Falhas: $failed");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
