<?php

namespace App\Http\Controllers;

use App\Exports\MikroTikExport;
use App\Models\Cliente;
use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MikroTikAdminController extends Controller
{
    /** Painel principal — lista todos os sites e planos sincronizados. */
    public function index(Request $request)
    {
        $sites = MikroTikSite::withCount('clientes')->orderBy('nome')->get();

        $selectedSiteId = $request->query('site_id');
        $selectedSite   = $selectedSiteId ? $sites->firstWhere('id', (int) $selectedSiteId) : null;

        // Query centrada no cliente: 1 linha por cliente, com o plano mais recente via LEFT JOIN
        $bestPlanSub = DB::table('planos')
            ->selectRaw('MAX(id) as plano_id, cliente_id')
            ->groupBy('cliente_id');

        $query = DB::table('clientes')
            ->leftJoinSub($bestPlanSub, 'bp', 'bp.cliente_id', '=', 'clientes.id')
            ->leftJoin('planos', 'planos.id', '=', 'bp.plano_id')
            ->leftJoin('mikrotik_sites', 'mikrotik_sites.id', '=', 'clientes.mikrotik_site_id')
            ->select(
                'clientes.id as cliente_id',
                'clientes.nome as cliente_nome',
                'mikrotik_sites.nome as site_nome',
                'planos.id as plano_id',
                'planos.nome as plano_nome',
                'planos.mikrotik_username',
                'planos.mikrotik_synced_at',
                'planos.estado as plano_estado',
                'planos.proxima_renovacao'
            )
            ->whereNotNull('clientes.mikrotik_site_id')
            ->orderByRaw("LOWER(clientes.nome)");

        if ($selectedSiteId) {
            $query->where('clientes.mikrotik_site_id', (int) $selectedSiteId);
        }

        $clientes = $query->paginate(30)->withQueryString();

        $planosPending = DB::table('planos')
            ->join('clientes', 'clientes.id', '=', 'planos.cliente_id')
            ->whereNotNull('clientes.mikrotik_site_id')
            ->whereNull('planos.mikrotik_username')
            ->whereIn('planos.estado', ['Ativo', 'Em aviso'])
            ->count();

        $siteRoutes = $sites->mapWithKeys(fn($s) => [
            $s->id => [
                'test'          => route('mikrotik.sites.test', $s),
                'edit'          => route('mikrotik.sites.edit', $s),
                'syncPendentes' => route('mikrotik.sites.sync-pendentes', $s),
            ],
        ]);

        return view('mikrotik.index', compact('sites', 'clientes', 'planosPending', 'selectedSite', 'selectedSiteId', 'siteRoutes'));
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

    /** Exportar relatório PDF. */
    public function exportPdf()
    {
        $planos = $this->planosParaExport();
        $sites  = MikroTikSite::withCount('clientes')->where('active', true)->get();

        $pdf = Pdf::loadView('mikrotik.relatorio-pdf', compact('planos', 'sites'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('mikrotik_relatorio_' . now()->format('Y-m-d') . '.pdf');
    }

    /** Exportar relatório Excel. */
    public function exportExcel()
    {
        $planos = $this->planosParaExport();
        $sites  = MikroTikSite::withCount('clientes')->where('active', true)->get();

        return Excel::download(
            new MikroTikExport($planos, $sites),
            'mikrotik_relatorio_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    private function planosParaExport()
    {
        return Plano::with('cliente.mikrotikSite', 'template')
            ->leftJoin('clientes', 'planos.cliente_id', '=', 'clientes.id')
            ->select('planos.*')
            ->whereHas('cliente', fn($q) => $q->whereNotNull('mikrotik_site_id'))
            ->orderByRaw("LOWER(COALESCE(clientes.nome, ''))")
            ->get();
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

    /** Sincronizar todos os planos sem username de um site específico. */
    public function syncPendentesSite(MikroTikSite $site)
    {
        $planos = Plano::with('cliente', 'template')
            ->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', $site->id))
            ->whereNull('mikrotik_username')
            ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso'])
            ->get();

        if ($planos->isEmpty()) {
            return response()->json(['ok' => true, 'synced' => 0, 'failed' => 0, 'message' => 'Sem pendentes.']);
        }

        $mikrotik = MikroTikService::forSite($site);
        $ok = 0;
        $fail = 0;

        foreach ($planos as $plano) {
            $plano->load(['cliente.mikrotikSite', 'template']);
            $mikrotik->activateUser($plano) ? $ok++ : $fail++;
        }

        return response()->json([
            'ok'      => true,
            'synced'  => $ok,
            'failed'  => $fail,
            'message' => "Sincronizados: $ok | Falhas: $fail",
        ]);
    }
}
