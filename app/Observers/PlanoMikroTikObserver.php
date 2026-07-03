<?php

namespace App\Observers;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Support\Facades\Log;

class PlanoMikroTikObserver
{
    /**
     * Sincroniza automaticamente com o MikroTik quando o estado do plano muda.
     * Garante que o secret PPPoE é activado/suspenso/removido sem intervenção manual.
     */
    public function updated(Plano $plano): void
    {
        if (! $plano->wasChanged('estado')) {
            return;
        }

        $username = $plano->mikrotik_username;
        if (! $username) {
            return;
        }

        $plano->loadMissing('cliente.mikrotikSite');
        $site = $plano->cliente?->mikrotikSite;
        if (! $site) {
            return;
        }

        $novo     = $plano->estado;
        $anterior = $plano->getOriginal('estado');

        Log::info('MikroTik: PlanoObserver — estado mudou, sincronizar', [
            'plano_id' => $plano->id,
            'username' => $username,
            'anterior' => $anterior,
            'novo'     => $novo,
        ]);

        try {
            if (in_array($novo, ['Ativo', 'Em aviso'])) {
                // Activar em TODOS os routers (Camama serve clientes de ambos os sites)
                $sites = MikroTikSite::where('active', true)->get();
                foreach ($sites as $routerSite) {
                    $ok = MikroTikService::forSite($routerSite)->activateUser($plano->fresh());
                    Log::info('MikroTik: PlanoObserver — activateUser', [
                        'plano_id' => $plano->id,
                        'username' => $username,
                        'router'   => $routerSite->nome,
                        'ok'       => $ok,
                    ]);
                }
            } elseif ($novo === 'Suspenso') {
                // Suspender em TODOS os routers
                $sites = MikroTikSite::where('active', true)->get();
                foreach ($sites as $routerSite) {
                    $ok = MikroTikService::forSite($routerSite)->suspendUser($plano->fresh());
                    Log::info('MikroTik: PlanoObserver — suspendUser', [
                        'plano_id' => $plano->id,
                        'username' => $username,
                        'router'   => $routerSite->nome,
                        'ok'       => $ok,
                    ]);
                }
            } elseif ($novo === 'Cancelado') {
                // Remover do router atribuído
                MikroTikService::forSite($site)->removeUser($plano->fresh());
                Log::info('MikroTik: PlanoObserver — removeUser (Cancelado)', [
                    'plano_id' => $plano->id,
                    'username' => $username,
                    'router'   => $site->nome,
                ]);
            }
        } catch (\Throwable $e) {
            // Nunca falhar o save por causa do MikroTik — apenas registar
            Log::error('MikroTik: PlanoObserver — falha ao sincronizar', [
                'plano_id' => $plano->id,
                'username' => $username,
                'novo'     => $novo,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
