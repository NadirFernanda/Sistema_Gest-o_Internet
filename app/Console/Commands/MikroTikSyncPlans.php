<?php

namespace App\Console\Commands;

use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class MikroTikSyncPlans extends Command
{
    protected $signature   = 'mikrotik:sync-plans {--limit=200}';
    protected $description = 'Sincroniza planos activos com o MikroTik (cria/actualiza utilizadores)';

    public function handle(MikroTikService $mikrotik): int
    {
        if (! $mikrotik->isConfigured()) {
            $this->warn('MikroTik não configurado. Defina MIKROTIK_HOST no .env');
            return self::SUCCESS;
        }

        $limit = (int) $this->option('limit');

        // Active plans not yet synced, or updated after last sync
        $planos = Plano::with('cliente', 'template')
            ->whereIn('estado', ['Ativo', 'Em aviso'])
            ->where(function ($q) {
                $q->whereNull('mikrotik_synced_at')
                  ->orWhereColumn('mikrotik_synced_at', '<', 'updated_at');
            })
            ->limit($limit)
            ->get();

        if ($planos->isEmpty()) {
            $this->info('Sem planos por sincronizar.');
            return self::SUCCESS;
        }

        $this->info("A sincronizar {$planos->count()} plano(s) com o MikroTik…");

        $ok = $failed = 0;

        foreach ($planos as $plano) {
            if ($mikrotik->activateUser($plano)) {
                $ok++;
            } else {
                $failed++;
                $this->warn("  ✗ Plano #{$plano->id} — {$plano->cliente?->nome}");
            }
        }

        $this->info("Concluído — activados/actualizados: $ok | Falhas: $failed");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
