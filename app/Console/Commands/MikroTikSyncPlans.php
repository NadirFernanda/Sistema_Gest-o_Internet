<?php

namespace App\Console\Commands;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Console\Command;

class MikroTikSyncPlans extends Command
{
    protected $signature   = 'mikrotik:sync-plans {--limit=200}';
    protected $description = 'Sincroniza planos activos com cada site MikroTik (cria/actualiza utilizadores)';

    public function handle(): int
    {
        $sites = MikroTikSite::where('active', true)->get();

        if ($sites->isEmpty()) {
            $this->warn('Sem sites MikroTik activos configurados.');
            return self::SUCCESS;
        }

        $limit    = (int) $this->option('limit');
        $totalOk  = 0;
        $totalFail = 0;

        foreach ($sites as $site) {
            $mikrotik = MikroTikService::forSite($site);

            // Planos deste site que precisam de sync:
            // - Ativo/Em aviso: sync normal (cria/actualiza/activa)
            // - Suspenso sem username: registar pela primeira vez como disabled
            $planos = Plano::with('cliente', 'template')
                ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
                ->where(function ($q) {
                    $q->where(function ($active) {
                        $active->whereIn('estado', ['Ativo', 'Em aviso'])
                               ->where(function ($stale) {
                                   $stale->whereNull('mikrotik_synced_at')
                                         ->orWhereColumn('mikrotik_synced_at', '<', 'updated_at');
                               });
                    })->orWhere(function ($unregistered) {
                        $unregistered->where('estado', 'Suspenso')
                                     ->whereNull('mikrotik_username');
                    });
                })
                ->limit($limit)
                ->get();

            if ($planos->isEmpty()) {
                $this->line("  [{$site->nome}] Sem planos por sincronizar.");
                continue;
            }

            $this->info("  [{$site->nome}] A sincronizar {$planos->count()} plano(s)…");

            foreach ($planos as $plano) {
                if ($mikrotik->activateUser($plano)) {
                    $totalOk++;
                } else {
                    $totalFail++;
                    $this->warn("    ✗ Plano #{$plano->id} — {$plano->cliente?->nome}");
                }
            }
        }

        $this->info("Concluído — activados/actualizados: $totalOk | Falhas: $totalFail");

        return $totalFail > 0 ? self::FAILURE : self::SUCCESS;
    }
}
