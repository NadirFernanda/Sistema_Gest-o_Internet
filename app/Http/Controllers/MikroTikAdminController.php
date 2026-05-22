<?php

namespace App\Http\Controllers;

use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Http\Request;

class MikroTikAdminController extends Controller
{
    public function __construct(private readonly MikroTikService $mikrotik) {}

    /** Painel de estado e listagem de planos. */
    public function index()
    {
        $connection = $this->mikrotik->testConnection();
        $profiles   = $connection['ok'] ? $this->mikrotik->listProfiles() : [];

        // Planos com username MikroTik atribuído
        $planosSync = Plano::with('cliente', 'template')
            ->whereNotNull('mikrotik_username')
            ->orderByDesc('mikrotik_synced_at')
            ->paginate(30);

        // Planos activos sem username (nunca sincronizados)
        $planosPending = Plano::with('cliente')
            ->whereNull('mikrotik_username')
            ->whereIn('estado', ['Ativo', 'Em aviso'])
            ->count();

        return view('mikrotik.index', compact('connection', 'profiles', 'planosSync', 'planosPending'));
    }

    /** Testar ligação ao MikroTik (AJAX). */
    public function testConnection()
    {
        return response()->json($this->mikrotik->testConnection());
    }

    /** Sincronizar manualmente um plano. */
    public function syncPlano(Request $request, Plano $plano)
    {
        $plano->load(['cliente', 'template']);
        $ok = $this->mikrotik->activateUser($plano);

        return response()->json([
            'ok'                 => $ok,
            'mikrotik_username'  => $plano->fresh()->mikrotik_username,
            'mikrotik_synced_at' => $plano->fresh()->mikrotik_synced_at?->format('d/m/Y H:i'),
        ]);
    }

    /** Suspender manualmente um plano no MikroTik. */
    public function suspendPlano(Plano $plano)
    {
        $plano->load('cliente');
        $ok = $this->mikrotik->suspendUser($plano);

        return response()->json(['ok' => $ok]);
    }

    /** Remover utilizador do MikroTik. */
    public function removePlano(Plano $plano)
    {
        $plano->load('cliente');
        $ok = $this->mikrotik->removeUser($plano);

        return response()->json(['ok' => $ok]);
    }

    /** Disparar sync completo (chama o artisan command em background). */
    public function runSync()
    {
        \Artisan::queue('mikrotik:sync-plans');
        return response()->json(['ok' => true, 'message' => 'Sync iniciado em background.']);
    }
}
