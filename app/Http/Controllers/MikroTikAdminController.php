<?php

namespace App\Http\Controllers;

use App\Exports\MikroTikExport;
use App\Models\Cliente;
use App\Models\MikroTikBandwidthSample;
use App\Models\MikroTikSite;
use App\Models\Plano;
use App\Services\MikroTikService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class MikroTikAdminController extends Controller
{
    /** Painel principal — lista todos os sites e planos sincronizados. */
    public function index(Request $request)
    {
        $sites = MikroTikSite::withCount('clientes')->orderBy('nome')->get();

        $selectedSiteId = $request->query('site_id');
        $selectedSite   = $selectedSiteId ? $sites->firstWhere('id', (int) $selectedSiteId) : null;
        $search         = trim($request->query('search', ''));
        $estadoFiltro   = $request->query('estado', '');

        // Query centrada no plano: 1 linha por plano (clientes com múltiplos planos aparecem várias vezes)
        $query = DB::table('planos')
            ->join('clientes', 'clientes.id', '=', 'planos.cliente_id')
            ->leftJoin('mikrotik_sites', 'mikrotik_sites.id', '=', 'clientes.mikrotik_site_id')
            ->leftJoin('mikrotik_online_statuses', 'mikrotik_online_statuses.plano_id', '=', 'planos.id')
            ->select(
                'clientes.id as cliente_id',
                'clientes.nome as cliente_nome',
                'mikrotik_sites.nome as site_nome',
                'planos.id as plano_id',
                'planos.nome as plano_nome',
                'planos.mikrotik_username',
                'planos.mikrotik_synced_at',
                'planos.estado as plano_estado',
                'planos.proxima_renovacao',
                'mikrotik_online_statuses.is_online',
                'mikrotik_online_statuses.last_seen_online_at',
                'mikrotik_online_statuses.last_seen_offline_at',
                'mikrotik_online_statuses.total_downtime_seconds'
            )
            ->whereNotNull('clientes.mikrotik_site_id')
            ->whereIn('planos.estado', ['Ativo', 'Em aviso', 'Suspenso', 'Cancelado'])
            ->orderByRaw("LOWER(clientes.nome), planos.id DESC");

        if ($selectedSiteId) {
            $query->where('clientes.mikrotik_site_id', (int) $selectedSiteId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('clientes.nome', 'like', "%{$search}%")
                  ->orWhere('planos.mikrotik_username', 'like', "%{$search}%");
            });
        }

        if ($estadoFiltro === 'nao_sincronizado') {
            $query->whereNotNull('planos.id')->whereNull('planos.mikrotik_username');
        } elseif ($estadoFiltro !== '') {
            $query->where('planos.estado', $estadoFiltro);
        }

        $clientes = $query->paginate(30)->withQueryString();

        // Processar resultados para adicionar objeto de status online
        $clientes->getCollection()->transform(function ($item) {
            // Criar objeto de status online a partir dos dados da query
            if ($item->is_online !== null) {
                $item->mikrotik_online_status = new MikroTikStatusDisplay(
                    (bool) $item->is_online,
                    $item->last_seen_online_at ? Carbon::parse($item->last_seen_online_at) : null,
                    $item->last_seen_offline_at ? Carbon::parse($item->last_seen_offline_at) : null,
                    (int) ($item->total_downtime_seconds ?? 0)
                );
            } else {
                $item->mikrotik_online_status = null;
            }
            
            return $item;
        });

        $planosPending = DB::table('planos')
            ->join('clientes', 'clientes.id', '=', 'planos.cliente_id')
            ->whereNotNull('clientes.mikrotik_site_id')
            ->whereNull('planos.mikrotik_username')
            ->whereIn('planos.estado', ['Ativo', 'Em aviso', 'Suspenso', 'Cancelado'])
            ->count();

        $siteRoutes = $sites->mapWithKeys(fn($s) => [
            $s->id => [
                'test'          => route('mikrotik.sites.test', $s),
                'edit'          => route('mikrotik.sites.edit', $s),
                'syncPendentes' => route('mikrotik.sites.sync-pendentes', $s),
                'profiles'      => route('mikrotik.sites.profiles', $s),
            ],
        ]);

        return view('mikrotik.index', compact('sites', 'clientes', 'planosPending', 'selectedSite', 'selectedSiteId', 'siteRoutes', 'search', 'estadoFiltro'));
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
        $result['site_nome'] = $site->nome;
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

        $mikrotik = MikroTikService::forSite($site);
        $ok       = $mikrotik->activateUser($plano);
        $fresh    = $plano->fresh();

        return response()->json([
            'ok'                 => $ok,
            'error'              => $ok ? null : $mikrotik->getLastError(),
            'mikrotik_username'  => $fresh->mikrotik_username,
            'mikrotik_synced_at' => $fresh->mikrotik_synced_at?->format('d/m/Y H:i'),
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

        // Se nunca foi sincronizado, apenas limpa o registo local sem tocar no router
        if (! $plano->mikrotik_username) {
            $plano->mikrotik_synced_at = now();
            $plano->saveQuietly();
            return response()->json(['ok' => true]);
        }

        $site = $plano->cliente?->mikrotikSite;
        if (! $site) {
            return response()->json(['ok' => false, 'error' => 'Cliente sem site MikroTik atribuído']);
        }

        $ok = MikroTikService::forSite($site)->removeUser($plano);
        return response()->json(['ok' => $ok, 'error' => $ok ? null : 'Falha ao ligar ao router']);
    }

    /** Exportar relatório PDF. */
    public function exportPdf(Request $request)
    {
        $planos = $this->planosParaExport($request);
        $sites  = MikroTikSite::withCount('clientes')->where('active', true)->get();

        $pdf = Pdf::loadView('mikrotik.relatorio-pdf', compact('planos', 'sites'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('mikrotik_relatorio_' . now()->format('Y-m-d') . '.pdf');
    }

    /** Exportar relatório Excel. */
    public function exportExcel(Request $request)
    {
        $planos = $this->planosParaExport($request);
        $sites  = MikroTikSite::withCount('clientes')->where('active', true)->get();

        return Excel::download(
            new MikroTikExport($planos, $sites),
            'mikrotik_relatorio_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    private function planosParaExport(Request $request)
    {
        $siteId       = $request->query('site_id');
        $search       = trim($request->query('search', ''));
        $estadoFiltro = $request->query('estado', '');

        $query = Plano::with('cliente.mikrotikSite', 'template')
            ->leftJoin('clientes', 'planos.cliente_id', '=', 'clientes.id')
            ->select('planos.*')
            ->whereHas('cliente', fn($q) => $q->whereNotNull('mikrotik_site_id'))
            ->orderByRaw("LOWER(COALESCE(clientes.nome, ''))");

        if ($siteId) {
            $query->whereHas('cliente', fn($q) => $q->where('mikrotik_site_id', (int) $siteId));
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('clientes.nome', 'like', "%{$search}%")
                  ->orWhere('planos.mikrotik_username', 'like', "%{$search}%");
            });
        }

        if ($estadoFiltro === 'nao_sincronizado') {
            $query->whereNotNull('planos.id')->whereNull('planos.mikrotik_username');
        } elseif ($estadoFiltro !== '') {
            $query->where('planos.estado', $estadoFiltro);
        }

        return $query->get();
    }

    /** Listar perfis HotSpot de um site (para diagnóstico). */
    public function listProfiles(MikroTikSite $site)
    {
        $profiles = MikroTikService::forSite($site)->listProfiles();
        $names = array_map(fn($p) => $p['=name'] ?? $p['name'] ?? '?', $profiles);
        return response()->json(['ok' => true, 'profiles' => $names]);
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
            ->whereIn('estado', ['Ativo', 'Em aviso', 'Suspenso', 'Cancelado'])
            ->get();

        if ($planos->isEmpty()) {
            return response()->json(['ok' => true, 'synced' => 0, 'failed' => 0, 'message' => 'Sem pendentes.']);
        }

        $mikrotik = MikroTikService::forSite($site);

        // Testar ligação uma vez antes de iterar — falha rápido se o router está inacessível
        $test = $mikrotik->testConnection();
        if (! $test['ok']) {
            return response()->json([
                'ok'      => false,
                'synced'  => 0,
                'failed'  => $planos->count(),
                'errors'  => ['Router inacessível: ' . ($test['error'] ?? 'sem resposta')],
                'message' => 'Router inacessível: ' . ($test['error'] ?? 'sem resposta'),
            ]);
        }

        $ok = 0;
        $fail = 0;
        $errors = [];

        foreach ($planos as $plano) {
            $plano->load(['cliente.mikrotikSite', 'template']);
            if ($mikrotik->activateUser($plano)) {
                $ok++;
            } else {
                $fail++;
                $errors[] = ($plano->cliente?->nome ?? "Plano #{$plano->id}") . ': ' . ($mikrotik->getLastError() ?? 'erro desconhecido');
            }
        }

        return response()->json([
            'ok'      => true,
            'synced'  => $ok,
            'failed'  => $fail,
            'errors'  => $errors,
            'message' => "Sincronizados: $ok | Falhas: $fail",
        ]);
    }

    /** Dados de largura de banda para o gráfico (AJAX) — suporta ?range=1h|6h|24h|7d. */
    public function trafficData(Request $request, Plano $plano)
    {
        $range = $request->query('range', '1h');

        $cfg = match ($range) {
            '6h'  => ['minutes' => 360,   'bucket' => 5,  'fmt' => 'H:i'],
            '24h' => ['minutes' => 1440,  'bucket' => 15, 'fmt' => 'H:i'],
            '7d'  => ['minutes' => 10080, 'bucket' => 60, 'fmt' => 'd/m'],
            default => ['minutes' => 60,  'bucket' => 1,  'fmt' => 'H:i'],
        };

        $since   = now()->subMinutes($cfg['minutes']);
        $samples = MikroTikBandwidthSample::where('plano_id', $plano->id)
            ->where('sampled_at', '>=', $since)
            ->orderBy('sampled_at')
            ->get();

        // Agregar em baldes para reduzir pontos no gráfico
        if ($cfg['bucket'] > 1 && $samples->count() > 0) {
            $buckets = collect();
            foreach ($samples->chunk($cfg['bucket']) as $chunk) {
                if ($chunk->isEmpty()) continue;
                $mid = $chunk->values()[(int)($chunk->count() / 2)];
                $buckets->push([
                    'label' => $mid->sampled_at->format($cfg['fmt']),
                    'rx'    => round($chunk->avg('rx_rate') / 1_000, 1),
                    'tx'    => round($chunk->avg('tx_rate') / 1_000, 1),
                ]);
            }
            $labels = $buckets->pluck('label')->values();
            $rx     = $buckets->pluck('rx')->values();
            $tx     = $buckets->pluck('tx')->values();
        } else {
            $labels = $samples->map(fn($s) => $s->sampled_at->format($cfg['fmt']))->values();
            $rx     = $samples->map(fn($s) => round($s->rx_rate / 1_000, 1))->values();
            $tx     = $samples->map(fn($s) => round($s->tx_rate / 1_000, 1))->values();
        }

        $latest = $samples->last();

        return response()->json([
            'labels'         => $labels,
            'rx'             => $rx,
            'tx'             => $tx,
            'current_rx'     => $latest ? MikroTikBandwidthSample::formatRate($latest->rx_rate) : null,
            'current_tx'     => $latest ? MikroTikBandwidthSample::formatRate($latest->tx_rate) : null,
            'ip'             => $latest?->ip_address,
            'caller_id'      => $latest?->caller_id,
            'uptime_seconds' => $latest?->uptime_seconds,
            'uptime_fmt'     => $latest ? MikroTikBandwidthSample::formatSeconds($latest->uptime_seconds ?? 0) : null,
            'max_rx_bps'     => $latest?->max_rx_bps,
            'max_tx_bps'     => $latest?->max_tx_bps,
            'max_rx_fmt'     => $latest?->max_rx_bps ? MikroTikBandwidthSample::formatRate($latest->max_rx_bps) : null,
            'max_tx_fmt'     => $latest?->max_tx_bps ? MikroTikBandwidthSample::formatRate($latest->max_tx_bps) : null,
            'sampled_at'     => $latest?->sampled_at?->diffForHumans(),
            'count'          => $samples->count(),
        ]);
    }

    /** Mostrar detalhes de um plano com histórico de status online/offline. */
    /**
     * Lista todos os PPP secrets de um site — usada pelo dropdown de associação manual.
     */
    public function listSiteSecrets(MikroTikSite $site)
    {
        $service = MikroTikService::forSite($site);

        try {
            $secrets = $service->listSecrets();
        } catch (\Throwable $e) {
            \Log::error('listSiteSecrets: falha ao listar secrets', [
                'site' => $site->nome, 'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Erro ao ligar ao router: ' . $e->getMessage()], 503);
        }

        if (empty($secrets)) {
            \Log::warning('listSiteSecrets: resposta vazia do router', ['site' => $site->nome]);
            return response()->json(['error' => 'Router não devolveu secrets — verifique logs do servidor.'], 503);
        }

        $result = [];
        foreach ($secrets as $s) {
            $name = $s['name'] ?? $s['=name'] ?? '';
            if ($name === '') continue;
            $result[] = [
                'name'     => $name,
                'disabled' => $s['disabled']  ?? $s['=disabled']  ?? 'no',
                'profile'  => $s['profile']   ?? $s['=profile']   ?? '',
                'comment'  => $s['comment']   ?? $s['=comment']   ?? '',
            ];
        }

        usort($result, fn($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json($result);
    }

    /**
     * Actualiza mikrotik_username de um plano e força re-sync.
     */
    public function updateUsername(Request $request, Plano $plano)
    {
        $validated = $request->validate([
            'mikrotik_username' => 'required|string|max:100',
        ]);

        $plano->mikrotik_username  = $validated['mikrotik_username'];
        $plano->mikrotik_synced_at = null; // forçar re-sync pelo sync-plans
        $plano->saveQuietly();

        \Log::info('MikroTik: username actualizado manualmente', [
            'plano_id' => $plano->id,
            'username' => $validated['mikrotik_username'],
            'by'       => auth()->user()?->name ?? 'admin',
        ]);

        // Activar imediatamente se o plano está Ativo/Em aviso
        if (in_array($plano->estado, ['Ativo', 'Em aviso', 'Suspenso'])) {
            $plano->load('cliente.mikrotikSite');
            $site = $plano->cliente?->mikrotikSite;
            if ($site) {
                MikroTikService::forSite($site)->activateUser($plano->fresh());
            }
        }

        return response()->json(['success' => true, 'mikrotik_username' => $validated['mikrotik_username']]);
    }

    public function showDetails(Request $request, Plano $plano)
    {
        $plano->load(['cliente.mikrotikSite', 'mikrotikOnlineStatus']);

        $cliente = $plano->cliente;
        if (!$cliente || !$cliente->mikrotik_site_id) {
            abort(404, 'Cliente ou site MikroTik não encontrado');
        }

        // Filtro de período
        $dateFrom = $request->get('date_from')
            ? \Carbon\Carbon::parse($request->get('date_from'))->startOfDay()
            : null;
        $dateTo   = $request->get('date_to')
            ? \Carbon\Carbon::parse($request->get('date_to'))->endOfDay()
            : null;

        $statusOnline = $plano->mikrotikOnlineStatus;

        $eventosQuery = $statusOnline
            ? $statusOnline->events()->orderBy('occurred_at', 'desc')
            : null;

        if ($eventosQuery && $dateFrom) {
            $eventosQuery->where('occurred_at', '>=', $dateFrom);
        }
        if ($eventosQuery && $dateTo) {
            $eventosQuery->where('occurred_at', '<=', $dateTo);
        }

        $eventos = $eventosQuery ? $eventosQuery->get() : collect();

        $totalOfflineEvents = $eventos->where('event_type', 'offline')->count();
        $totalOnlineEvents  = $eventos->where('event_type', 'online')->count();
        $totalDowntime      = (int) $eventos->where('event_type', 'offline')->sum('duration_seconds');
        $avgDowntime        = $totalOfflineEvents > 0 ? intval($totalDowntime / $totalOfflineEvents) : 0;

        $eventosUltimaSemana = $eventos->filter(function ($e) {
            return $e->occurred_at && $e->occurred_at->gte(now()->subDays(7));
        })->values();

        // ── Bandwidth: amostras iniciais (última hora para o gráfico) ──
        $bandwidthSamples = MikroTikBandwidthSample::where('plano_id', $plano->id)
            ->where('sampled_at', '>=', now()->subHour())
            ->orderBy('sampled_at')
            ->get();

        $latestSample = MikroTikBandwidthSample::where('plano_id', $plano->id)
            ->orderBy('sampled_at', 'desc')
            ->first();

        // ── Consumo de dados (PostgreSQL: ::bigint evita overflow de integer) ──
        $todayDownloadBytes = (int) DB::table('mikrotik_bandwidth_samples')
            ->where('plano_id', $plano->id)
            ->where('sampled_at', '>=', now()->startOfDay())
            ->sum(DB::raw('rx_rate::bigint * 60 / 8'));

        $todayUploadBytes = (int) DB::table('mikrotik_bandwidth_samples')
            ->where('plano_id', $plano->id)
            ->where('sampled_at', '>=', now()->startOfDay())
            ->sum(DB::raw('tx_rate::bigint * 60 / 8'));

        $monthDownloadBytes = (int) DB::table('mikrotik_bandwidth_samples')
            ->where('plano_id', $plano->id)
            ->where('sampled_at', '>=', now()->startOfMonth())
            ->sum(DB::raw('rx_rate::bigint * 60 / 8'));

        $monthUploadBytes = (int) DB::table('mikrotik_bandwidth_samples')
            ->where('plano_id', $plano->id)
            ->where('sampled_at', '>=', now()->startOfMonth())
            ->sum(DB::raw('tx_rate::bigint * 60 / 8'));

        // ── Velocidade de pico ──
        $peakRxRate = (int) (MikroTikBandwidthSample::where('plano_id', $plano->id)->max('rx_rate') ?? 0);
        $peakTxRate = (int) (MikroTikBandwidthSample::where('plano_id', $plano->id)->max('tx_rate') ?? 0);

        // ── Estabilidade: usa o período filtrado ou 30 dias por defeito ──
        $thirtyDaysAgo   = now()->subDays(30);
        $statsFrom       = $dateFrom ?? $thirtyDaysAgo;
        $statsTo         = $dateTo   ?? now();
        $statsDays       = max(1, (int) ceil($statsFrom->diffInSeconds($statsTo) / 86400));
        $downtimeThirtyDays = (int) $eventos
            ->where('event_type', 'offline')
            ->filter(fn($e) => $e->occurred_at->gte($statsFrom))
            ->sum('duration_seconds');
        $stabilityPct = max(0, min(100, round((1 - $downtimeThirtyDays / ($statsDays * 86400)) * 100, 1)));

        // ── Quedas por dia (período filtrado ou últimos 30 dias) ──
        $dropsChart = [];
        for ($i = $statsDays - 1; $i >= 0; $i--) {
            $dropsChart[$statsTo->copy()->subDays($i)->format('Y-m-d')] = 0;
        }
        $dropsByDate = $eventos
            ->where('event_type', 'offline')
            ->filter(fn($e) => $e->occurred_at->gte($statsFrom))
            ->groupBy(fn($e) => $e->occurred_at->format('Y-m-d'))
            ->map(fn($g) => $g->count())
            ->toArray();
        foreach ($dropsByDate as $date => $count) {
            if (array_key_exists($date, $dropsChart)) {
                $dropsChart[$date] = $count;
            }
        }

        // ── Consumo diário (últimos 7 dias) — PostgreSQL: usar ::date em vez de DATE() ──
        $sevenDaysAgo      = now()->subDays(6)->startOfDay();
        $dailyBandwidthRaw = DB::table('mikrotik_bandwidth_samples')
            ->where('plano_id', $plano->id)
            ->where('sampled_at', '>=', $sevenDaysAgo)
            ->selectRaw("sampled_at::date as date, SUM(rx_rate::bigint * 60 / 8) as dl, SUM(tx_rate::bigint * 60 / 8) as ul")
            ->groupByRaw("sampled_at::date")
            ->get()
            ->keyBy('date');

        $ptDays   = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $dailyUsage = [];
        for ($i = 6; $i >= 0; $i--) {
            $day  = now()->subDays($i);
            $date = $day->format('Y-m-d');
            $row  = $dailyBandwidthRaw[$date] ?? null;
            $dailyUsage[] = [
                'label'    => $day->format('d/m') . ' ' . $ptDays[$day->dayOfWeek],
                'download' => (int) ($row?->dl ?? 0),
                'upload'   => (int) ($row?->ul ?? 0),
                'drops'    => (int) ($dropsChart[$date] ?? 0),
            ];
        }

        $filterDateFrom = $dateFrom?->format('Y-m-d');
        $filterDateTo   = $dateTo?->format('Y-m-d');

        return view('mikrotik.detalhes-plano', compact(
            'plano', 'cliente', 'statusOnline',
            'eventos', 'totalOfflineEvents', 'totalOnlineEvents',
            'totalDowntime', 'avgDowntime', 'eventosUltimaSemana',
            'bandwidthSamples', 'latestSample',
            'todayDownloadBytes', 'todayUploadBytes',
            'monthDownloadBytes', 'monthUploadBytes',
            'peakRxRate', 'peakTxRate',
            'stabilityPct', 'dropsChart', 'dailyUsage',
            'filterDateFrom', 'filterDateTo'
        ));
    }
}
