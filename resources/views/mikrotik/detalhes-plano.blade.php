@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .det-container { max-width:1100px; margin:0 auto; padding:0 16px 40px; }

        /* ── Breadcrumb ── */
        .det-back { display:inline-flex; align-items:center; gap:6px; color:#888; font-size:0.88rem; text-decoration:none; margin-bottom:18px; }
        .det-back:hover { color:#f5a623; }

        /* ── Header card ── */
        .det-header { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:22px 26px; margin-bottom:20px; display:flex; align-items:flex-start; justify-content:space-between; gap:20px; flex-wrap:wrap; }
        .det-header__info h2 { font-size:1.4rem; font-weight:800; color:#1a1a2e; margin:0 0 6px; }
        .det-header__meta { font-size:0.88rem; color:#777; display:flex; flex-wrap:wrap; gap:10px 20px; margin-top:8px; }
        .det-header__meta span { display:inline-flex; align-items:center; gap:4px; }
        .det-header__meta code { background:#f4f6f9; padding:1px 7px; border-radius:5px; font-size:0.82rem; color:#444; }

        /* ── Status badge grande ── */
        .det-status { display:flex; align-items:center; gap:10px; padding:14px 20px; border-radius:12px; font-weight:700; font-size:1rem; white-space:nowrap; }
        .det-status--online  { background:#d4edda; color:#155724; border:1.5px solid #c3e6cb; }
        .det-status--offline { background:#f8d7da; color:#721c24; border:1.5px solid #f5c6cb; }
        .det-status--unknown { background:#e2e3e5; color:#383d41; border:1.5px solid #d6d8db; }
        .det-status__dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
        .det-status__dot--on  { background:#28a745; }
        .det-status__dot--off { background:#dc3545; }
        .det-status__dot--unk { background:#999; }
        .det-status__sub { font-size:0.8rem; font-weight:400; opacity:.8; margin-top:3px; }

        /* ── Stats cards ── */
        .det-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:20px; }
        @media(max-width:700px){ .det-stats { grid-template-columns:repeat(2,1fr); } }
        .det-stat { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:18px 20px; }
        .det-stat__val { font-size:1.6rem; font-weight:800; margin-bottom:4px; }
        .det-stat__lbl { font-size:0.78rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em; }
        .det-stat--orange .det-stat__val { color:#e07a20; }
        .det-stat--green  .det-stat__val { color:#2a8a55; }
        .det-stat--red    .det-stat__val { color:#c0392b; }
        .det-stat--blue   .det-stat__val { color:#2563eb; }

        /* ── History card ── */
        .det-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:22px 26px; }
        .det-card h3 { font-size:1rem; font-weight:700; color:#222; margin:0 0 16px; }

        /* ── Filter buttons ── */
        .det-filters { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
        .det-filter { padding:5px 16px; border-radius:20px; border:1.5px solid #e0e6f0; background:#fff; font-size:0.82rem; font-weight:600; cursor:pointer; color:#666; transition:all .15s; }
        .det-filter:hover { border-color:#f5a623; color:#f5a623; }
        .det-filter.active { background:#f5a623; border-color:#f5a623; color:#fff; }

        /* ── Events table ── */
        .det-table { width:100%; border-collapse:collapse; font-size:0.86rem; }
        .det-table thead { background:#f7f9fb; }
        .det-table th { padding:9px 12px; text-align:left; font-size:0.73rem; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; border-bottom:2px solid #edf0f4; }
        .det-table td { padding:10px 12px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
        .det-table tbody tr:last-child td { border-bottom:none; }
        .det-table tbody tr:hover { background:#fafbfd; }

        .ev-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:0.76rem; font-weight:700; }
        .ev-online  { background:#d4edda; color:#155724; }
        .ev-offline { background:#f8d7da; color:#721c24; }

        /* ── Timeline ── */
        .det-timeline { margin-top:24px; padding-top:20px; border-top:1px solid #f0f2f5; }
        .det-timeline h4 { font-size:0.92rem; font-weight:700; color:#333; margin:0 0 14px; }
        .tl-item { display:flex; gap:14px; align-items:flex-start; margin-bottom:10px; }
        .tl-time { min-width:90px; font-size:0.77rem; font-family:monospace; color:#888; padding-top:4px; }
        .tl-body { flex:1; }
        .tl-event { padding:8px 14px; border-radius:8px; font-size:0.83rem; font-weight:600; }
        .tl-event--online  { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .tl-event--offline { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }

        .empty-state { text-align:center; padding:40px; color:#bbb; font-size:0.9rem; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Detalhes do Cliente',
        'subtitle' => 'Histórico de ligação e eventos online/offline',
    ])

    <div class="det-container">

        <a href="{{ route('mikrotik.index') }}" class="det-back">← Voltar à lista MikroTik</a>

        {{-- ── Header ── --}}
        <div class="det-header">
            <div class="det-header__info">
                <h2>{{ $cliente->nome }}</h2>
                <div class="det-header__meta">
                    <span>🏢 {{ $cliente->mikrotikSite?->nome ?? '—' }}</span>
                    <span>🖧 Username: <code>{{ $plano->mikrotik_username ?? 'Não sincronizado' }}</code></span>
                    <span>📋 Plano: {{ $plano->nome ?? '—' }}</span>
                    @if($plano->proxima_renovacao)
                    <span>📅 Renova em: {{ \Carbon\Carbon::parse($plano->proxima_renovacao)->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>

            <div>
                @if($statusOnline)
                    @if($statusOnline->is_online)
                        <div class="det-status det-status--online">
                            <span class="det-status__dot det-status__dot--on"></span>
                            <div>
                                <div>ONLINE</div>
                                <div class="det-status__sub">Desde {{ $statusOnline->last_seen_online_at?->diffForHumans() ?? '—' }}</div>
                            </div>
                        </div>
                    @else
                        <div class="det-status det-status--offline">
                            <span class="det-status__dot det-status__dot--off"></span>
                            <div>
                                <div>OFFLINE</div>
                                <div class="det-status__sub">Desde {{ $statusOnline->last_seen_offline_at?->diffForHumans() ?? '—' }}</div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="det-status det-status--unknown">
                        <span class="det-status__dot det-status__dot--unk"></span>
                        <div>
                            <div>Aguardando…</div>
                            <div class="det-status__sub">Primeira verificação pendente</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Estatísticas ── --}}
        @if($statusOnline)
        <div class="det-stats">
            <div class="det-stat det-stat--red">
                <div class="det-stat__val">{{ $totalOfflineEvents }}</div>
                <div class="det-stat__lbl">Quedas detectadas</div>
            </div>
            <div class="det-stat det-stat--green">
                <div class="det-stat__val">{{ $totalOnlineEvents }}</div>
                <div class="det-stat__lbl">Reconexões</div>
            </div>
            <div class="det-stat det-stat--orange">
                @php
                    $td = (int) $totalDowntime;
                    $tdDays  = intdiv($td, 86400);
                    $tdHours = intdiv($td % 86400, 3600);
                    $tdMins  = intdiv($td % 3600, 60);
                @endphp
                <div class="det-stat__val">
                    @if($tdDays > 0) {{ $tdDays }}d {{ $tdHours }}h
                    @elseif($tdHours > 0) {{ $tdHours }}h {{ $tdMins }}m
                    @else {{ $tdMins }}m
                    @endif
                </div>
                <div class="det-stat__lbl">Downtime total</div>
            </div>
            <div class="det-stat det-stat--blue">
                @php
                    $ad = (int) $avgDowntime;
                    $adHours = intdiv($ad, 3600);
                    $adMins  = intdiv($ad % 3600, 60);
                @endphp
                <div class="det-stat__val">
                    @if($adHours > 0) {{ $adHours }}h {{ $adMins }}m
                    @else {{ $adMins }}m
                    @endif
                </div>
                <div class="det-stat__lbl">Média por queda</div>
            </div>
        </div>
        @endif

        {{-- ── Gráfico de largura de banda ── --}}
        <div class="det-card" style="margin-bottom:20px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
                <h3 style="margin:0;">Largura de Banda</h3>
                <div style="display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                    <div style="text-align:center;">
                        <div id="bw-rx" style="font-size:1.3rem; font-weight:800; color:#2563eb;">—</div>
                        <div style="font-size:0.72rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em;">▼ Download</div>
                    </div>
                    <div style="text-align:center;">
                        <div id="bw-tx" style="font-size:1.3rem; font-weight:800; color:#16a34a;">—</div>
                        <div style="font-size:0.72rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em;">▲ Upload</div>
                    </div>
                    <div style="text-align:center;">
                        <div id="bw-ip" style="font-size:0.9rem; font-weight:700; color:#555; font-family:monospace;">—</div>
                        <div style="font-size:0.72rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em;">IP da sessão</div>
                    </div>
                    <div style="text-align:center;">
                        <div id="bw-updated" style="font-size:0.78rem; color:#bbb;">—</div>
                        <div style="font-size:0.72rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em;">Actualizado</div>
                    </div>
                </div>
            </div>
            <div style="position:relative; height:180px;">
                <canvas id="bandwidthChart"></canvas>
            </div>
            <div style="margin-top:8px; font-size:0.75rem; color:#bbb; text-align:right;">
                Últimas 2 horas — actualiza automaticamente a cada minuto
            </div>
        </div>

        {{-- ── Histórico de eventos ── --}}
        <div class="det-card">
            <h3>Histórico de Eventos</h3>

            @if($eventos->isEmpty())
                <div class="empty-state">Sem eventos registados. A monitorização começará na próxima verificação (cada 5 minutos).</div>
            @else
                <div class="det-filters">
                    <button class="det-filter active" onclick="filtrar('todos', this)">Todos ({{ $eventos->count() }})</button>
                    <button class="det-filter" onclick="filtrar('offline', this)">Quedas ({{ $eventos->where('event_type','offline')->count() }})</button>
                    <button class="det-filter" onclick="filtrar('online', this)">Reconexões ({{ $eventos->where('event_type','online')->count() }})</button>
                </div>

                <table class="det-table">
                    <thead>
                        <tr>
                            <th>Data / Hora</th>
                            <th>Evento</th>
                            <th>Duração</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($eventos as $evento)
                        <tr class="ev-row" data-type="{{ $evento->event_type }}">
                            <td>
                                <div style="font-family:monospace; font-size:0.83rem;">{{ $evento->occurred_at->format('d/m/Y H:i:s') }}</div>
                                <div style="font-size:0.76rem; color:#aaa;">{{ $evento->occurred_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                @if($evento->event_type === 'online')
                                    <span class="ev-badge ev-online">✓ Online</span>
                                @else
                                    <span class="ev-badge ev-offline">✕ Offline</span>
                                @endif
                            </td>
                            <td>
                                @if($evento->event_type === 'offline' && !$evento->duration_seconds)
                                    <span style="color:#e05a4f; font-size:0.79rem; font-weight:700;">⏳ Em andamento</span>
                                @elseif($evento->duration_seconds)
                                    <span style="font-weight:600; color:{{ $evento->event_type === 'offline' ? '#c0392b' : '#2563eb' }};">
                                        {{ $evento->getReadableDuration() }}
                                    </span>
                                @else
                                    <span style="color:#bbb;">—</span>
                                @endif
                            </td>
                            <td style="color:#777; font-size:0.82rem;">{{ $evento->disconnect_reason ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Últimos 7 dias --}}
                @if($eventosUltimaSemana->isNotEmpty())
                <div class="det-timeline">
                    <h4>Últimos 7 dias</h4>
                    @foreach($eventosUltimaSemana as $ev)
                    <div class="tl-item">
                        <div class="tl-time">{{ $ev->occurred_at->format('d/m H:i') }}</div>
                        <div class="tl-body">
                            @if($ev->event_type === 'online')
                                <div class="tl-event tl-event--online">
                                    ✓ Voltou Online
                                    @if($ev->duration_seconds)
                                        <span style="font-weight:400; font-size:0.8rem;"> — Após {{ $ev->getReadableDuration() }} offline</span>
                                    @endif
                                </div>
                            @else
                                <div class="tl-event tl-event--offline">
                                    ✕ Saiu Offline
                                    @if($ev->duration_seconds)
                                        <span style="font-weight:400; font-size:0.8rem;"> — Durou {{ $ev->getReadableDuration() }}</span>
                                    @elseif(!$ev->duration_seconds)
                                        <span style="font-weight:400; font-size:0.8rem; opacity:.75;"> — ⏳ Em andamento</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Gráfico de largura de banda ──────────────────────────────────────────────
const initialData = @json([
    'labels' => $bandwidthSamples->map(fn($s) => $s->sampled_at->format('H:i'))->values(),
    'rx'     => $bandwidthSamples->map(fn($s) => round($s->rx_rate / 1_000_000, 3))->values(),
    'tx'     => $bandwidthSamples->map(fn($s) => round($s->tx_rate / 1_000_000, 3))->values(),
    'current_rx' => $bandwidthSamples->last()?->getFormattedRxRate(),
    'current_tx' => $bandwidthSamples->last()?->getFormattedTxRate(),
    'ip'         => $bandwidthSamples->last()?->ip_address,
    'sampled_at' => $bandwidthSamples->last()?->sampled_at?->diffForHumans(),
]);

const ctx = document.getElementById('bandwidthChart').getContext('2d');
const bwChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: initialData.labels,
        datasets: [
            {
                label: 'Download (Mbps)',
                data: initialData.rx,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2,
                pointRadius: 0,
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Upload (Mbps)',
                data: initialData.tx,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.08)',
                borderWidth: 2,
                pointRadius: 0,
                fill: true,
                tension: 0.3,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.dataset.label.split(' ')[0]}: ${ctx.raw} Mbps`,
                }
            },
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 }, maxTicksLimit: 12, color: '#aaa' },
            },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.05)' },
                ticks: {
                    font: { size: 10 }, color: '#aaa',
                    callback: v => v + ' M',
                },
            },
        },
    },
});

function applyBwData(data) {
    bwChart.data.labels                 = data.labels;
    bwChart.data.datasets[0].data       = data.rx;
    bwChart.data.datasets[1].data       = data.tx;
    bwChart.update('none');

    if (data.current_rx) document.getElementById('bw-rx').textContent = data.current_rx;
    if (data.current_tx) document.getElementById('bw-tx').textContent = data.current_tx;
    if (data.ip)         document.getElementById('bw-ip').textContent = data.ip;
    if (data.sampled_at) document.getElementById('bw-updated').textContent = data.sampled_at;
}

// Aplicar dados iniciais
applyBwData(initialData);

// Actualizar a cada 60 segundos
setInterval(() => {
    fetch('{{ route('mikrotik.planos.traffic-data', $plano) }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(applyBwData)
    .catch(() => {});
}, 60_000);

// ── Filtro de eventos ────────────────────────────────────────────────────────
function filtrar(tipo, btn) {
    document.querySelectorAll('.det-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.ev-row').forEach(row => {
        row.style.display = (tipo === 'todos' || row.dataset.type === tipo) ? '' : 'none';
    });
}
</script>
@endpush
