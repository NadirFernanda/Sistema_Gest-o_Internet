<?php

namespace App\Http\Controllers;

use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class MikroTikAdminController extends Controller
{
    /** Painel principal — lista todos os sites e planos sincronizados. */
    public function index()
    {
        $sites = MikroTikSite::withCount('clientes')->orderBy('nome')->get();

        $planosSync = Plano::with('cliente', 'template')
            ->whereNotNull('mikrotik_username')
            ->orderByDesc('mikrotik_synced_at')
            ->paginate(30);

        $planosPending = Plano::whereHas('cliente', fn($q) => $q->whereNotNull('mikrotik_site_id'))
            ->whereNull('mikrotik_username')
            ->whereIn('estado', ['Ativo', 'Em aviso'])
            ->count();

        return view('mikrotik.index', compact('sites', 'planosSync', 'planosPending'));
    }

    /** Formulário de criação de site. */
    public function createSite()
    {
        return view('mikrotik.site-form', ['site' => new MikroTikSite()]);
    }

    /** Guardar novo site. */
    public function storeSite(Request $request)
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:100',
            'localizacao'     => 'nullable|string|max:255',
            'host'            => 'required|string|max:100',
            'port'            => 'required|integer|min:1|max:65535',
            'username'        => 'required|string|max:100',
            'password'        => 'required|string|max:255',
            'user_prefix'     => 'nullable|string|max:20',
            'default_profile' => 'required|string|max:100',
            'active'          => 'boolean',
        ]);

        $data['active']      = $request->boolean('active', true);
        $data['user_prefix'] = $data['user_prefix'] ?? '';

        MikroTikSite::create($data);

        return redirect()->route('mikrotik.sites.create')->with('success', "Site \"{$data['nome']}\" criado. Pode criar o próximo.");
    }

    /** Formulário de edição de site. */
    public function editSite(MikroTikSite $site)
    {
        return view('mikrotik.site-form', compact('site'));
    }

    /** Actualizar site. */
    public function updateSite(Request $request, MikroTikSite $site)
    {
        $data = $request->validate([
            'nome'            => 'required|string|max:100',
            'localizacao'     => 'nullable|string|max:255',
            'host'            => 'required|string|max:100',
            'port'            => 'required|integer|min:1|max:65535',
            'username'        => 'required|string|max:100',
            'password'        => 'nullable|string|max:255',
            'user_prefix'     => 'nullable|string|max:20',
            'default_profile' => 'required|string|max:100',
            'active'          => 'boolean',
        ]);

        $data['active']      = $request->boolean('active', true);
        $data['user_prefix'] = $data['user_prefix'] ?? '';

        // Só actualiza password se foi preenchida
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $site->update($data);

        return redirect()->route('mikrotik.sites.edit', $site)->with('success', 'Alterações guardadas.');
    }

    /** Testar ligação a um site (AJAX). */
    public function testSite(MikroTikSite $site)
    {
        $result = MikroTikService::forSite($site)->testConnection();
        return response()->json($result);
    }

    /** Sincronizar manualmente um plano. */
    public function syncPlano(Request $request, Plano $plano)
    {
        $plano->load(['cliente.mikrotikSite', 'template']);

        $site = $plano->cliente?->mikrotikSite;
        if (! $site) {
            return response()->json(['ok' => false, 'error' => 'Cliente sem site MikroTik atribuído']);
        }

        $ok = MikroTikService::forSite($site)->activateUser($plano);

        return response()->json([
            'ok'                 => $ok,
            'mikrotik_username'  => $plano->fresh()->mikrotik_username,
            'mikrotik_synced_at' => $plano->fresh()->mikrotik_synced_at?->format('d/m/Y H:i'),
        ]);
    }

    /** Suspender manualmente um plano no MikroTik. */
    public function suspendPlano(Plano $plano)
    {
        $plano->load(['cliente.mikrotikSite']);
        $site = $plano->cliente?->mikrotikSite;

        if (! $site) {
            return response()->json(['ok' => false, 'error' => 'Cliente sem site MikroTik atribuído']);
        }

        $ok = MikroTikService::forSite($site)->suspendUser($plano);

        if ($ok) {
            $plano->estado = 'Suspenso';
            $plano->saveQuietly();
        }

        return response()->json(['ok' => $ok]);
    }

    /** Remover utilizador do MikroTik. */
    public function removePlano(Plano $plano)
    {
        $plano->load(['cliente.mikrotikSite']);
        $site = $plano->cliente?->mikrotikSite;

        if (! $site) {
            return response()->json(['ok' => false, 'error' => 'Cliente sem site MikroTik atribuído']);
        }

        $ok = MikroTikService::forSite($site)->removeUser($plano);
        return response()->json(['ok' => $ok]);
    }

    /** Disparar sync completo. */
    public function runSync()
    {
        try {
            Artisan::call('mikrotik:sync-plans');
            $output = trim(Artisan::output());
            return response()->json(['ok' => true, 'message' => $output ?: 'Sync concluído.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
