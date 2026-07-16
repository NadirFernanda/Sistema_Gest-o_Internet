@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .det-container { max-width:1100px; margin:0 auto; padding:0 16px 48px; }

        /* Back */
        .det-back { display:inline-flex; align-items:center; gap:6px; color:#888; font-size:0.88rem; text-decoration:none; margin-bottom:18px; }
        .det-back:hover { color:#f5a623; }

        /* Header */
        .det-header { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:22px 26px; margin-bottom:20px; display:flex; align-items:flex-start; justify-content:space-between; gap:20px; flex-wrap:wrap; }
        .det-header__info h2 { font-size:1.4rem; font-weight:800; color:#1a1a2e; margin:0 0 6px; }
        .det-header__meta { font-size:0.88rem; color:#777; display:flex; flex-wrap:wrap; gap:10px 20px; margin-top:8px; }
        .det-header__meta span { display:inline-flex; align-items:center; gap:4px; }
        .det-header__meta code { background:#f4f6f9; padding:1px 7px; border-radius:5px; font-size:0.82rem; color:#444; }

        /* Status badge */
        .det-status { display:flex; align-items:center; gap:10px; padding:14px 20px; border-radius:12px; font-weight:700; font-size:1rem; white-space:nowrap; }
        .det-status--online  { background:#d4edda; color:#155724; border:1.5px solid #c3e6cb; }
        .det-status--offline { background:#f8d7da; color:#721c24; border:1.5px solid #f5c6cb; }
        .det-status--unknown { background:#e2e3e5; color:#383d41; border:1.5px solid #d6d8db; }
        .det-status__dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
        .det-status__dot--on  { background:#28a745; box-shadow:0 0 0 3px rgba(40,167,69,.25); }
        .det-status__dot--off { background:#dc3545; }
        .det-status__dot--unk { background:#999; }
        .det-status__sub { font-size:0.8rem; font-weight:400; opacity:.8; margin-top:3px; }

        /* Session info card */
        .session-card { background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%); border-radius:14px; padding:20px 26px; margin-bottom:20px; color:#fff; display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
        @media(max-width:700px){ .session-card { grid-template-columns:repeat(2,1fr); } }
        .session-item__lbl { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; opacity:.65; margin-bottom:5px; }
        .session-item__val { font-size:1.05rem; font-weight:800; font-family:monospace; word-break:break-all; }
        .session-item__val--speed { font-size:0.9rem; font-family:inherit; display:flex; gap:8px; flex-wrap:wrap; }
        .speed-pill { display:inline-flex; align-items:center; gap:4px; background:rgba(255,255,255,.15); padding:3px 10px; border-radius:20px; font-size:0.82rem; font-weight:700; }

        /* Stats grid */
        .det-stats { display:grid; grid-template-columns:repeat(6,1fr); gap:12px; margin-bottom:20px; }
        @media(max-width:900px){ .det-stats { grid-template-columns:repeat(3,1fr); } }
        @media(max-width:540px){ .det-stats { grid-template-columns:repeat(2,1fr); } }
        .det-stat { background:#fff; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.06); padding:16px 18px; }
        .det-stat__val { font-size:1.3rem; font-weight:800; margin-bottom:4px; line-height:1.1; }
        .det-stat__lbl { font-size:0.73rem; color:#999; font-weight:600; text-transform:uppercase; letter-spacing:.03em; }
        .det-stat--red    .det-stat__val { color:#c0392b; }
        .det-stat--green  .det-stat__val { color:#2a8a55; }
        .det-stat--orange .det-stat__val { color:#e07a20; }
        .det-stat--blue   .det-stat__val { color:#2563eb; }
        .det-stat--purple .det-stat__val { color:#7c3aed; }
        .det-stat--teal   .det-stat__val { color:#0891b2; }
        .stability-bar { margin-top:6px; height:4px; background:#eee; border-radius:3px; overflow:hidden; }
        .stability-bar__fill { height:100%; border-radius:3px; background:linear-gradient(90deg,#ef4444,#f59e0b,#22c55e); }

        /* Card base */
        .det-card { background:#fff; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07); padding:22px 26px; margin-bottom:20px; }
        .det-card__title { font-size:1rem; font-weight:700; color:#222; margin:0 0 16px; display:flex; align-items:center; gap:8px; }
        .det-card__title span { font-size:0.78rem; font-weight:500; color:#bbb; }

        /* Chart controls */
        .chart-controls { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
        .range-btn { padding:4px 14px; border:1.5px solid #e0e6f0; border-radius:20px; background:#fff; font-size:0.8rem; font-weight:600; color:#777; cursor:pointer; transition:all .15s; }
        .range-btn:hover  { border-color:#2563eb; color:#2563eb; }
        .range-btn.active { background:#2563eb; border-color:#2563eb; color:#fff; }

        /* Live stats under bandwidth chart */
        .bw-live { display:flex; gap:24px; flex-wrap:wrap; margin-bottom:14px; align-items:flex-end; }
        .bw-live__item { text-align:center; }
        .bw-live__val { font-size:1.5rem; font-weight:800; line-height:1; }
        .bw-live__lbl { font-size:0.7rem; color:#aaa; font-weight:600; text-transform:uppercase; letter-spacing:.04em; margin-top:3px; }
        .bw-live__val--dl { color:#2563eb; }
        .bw-live__val--ul { color:#16a34a; }
        .bw-live__val--sm { font-size:0.9rem; font-weight:700; color:#555; font-family:monospace; }

        /* Daily usage table */
        .day-table { width:100%; border-collapse:collapse; font-size:0.84rem; }
        .day-table th { padding:7px 12px; text-align:left; font-size:0.71rem; font-weight:700; color:#aaa; text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid #f0f2f5; }
        .day-table td { padding:9px 12px; border-bottom:1px solid #f5f6f8; vertical-align:middle; }
        .day-table tbody tr:last-child td { border-bottom:none; }
        .day-table tbody tr:hover { background:#fafbfd; }
        .bar-mini { display:inline-block; height:6px; border-radius:3px; vertical-align:middle; margin-right:6px; }

        /* Events table */
        .det-table { width:100%; border-collapse:collapse; font-size:0.86rem; }
        .det-table thead { background:#f7f9fb; }
        .det-table th { padding:9px 12px; text-align:left; font-size:0.73rem; font-weight:700; color:#999; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap; border-bottom:2px solid #edf0f4; }
        .det-table td { padding:10px 12px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
        .det-table tbody tr:last-child td { border-bottom:none; }
        .det-table tbody tr:hover { background:#fafbfd; }

        .ev-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:0.76rem; font-weight:700; }
        .ev-online  { background:#d4edda; color:#155724; }
        .ev-offline { background:#f8d7da; color:#721c24; }

        /* Filter buttons */
        .det-filters { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
        .det-filter { padding:5px 16px; border-radius:20px; border:1.5px solid #e0e6f0; background:#fff; font-size:0.82rem; font-weight:600; cursor:pointer; color:#666; transition:all .15s; }
        .det-filter:hover { border-color:#f5a623; color:#f5a623; }
        .det-filter.active { background:#f5a623; border-color:#f5a623; color:#fff; }

        /* Timeline */
        .det-timeline { margin-top:24px; padding-top:20px; border-top:1px solid #f0f2f5; }
        .det-timeline h4 { font-size:0.92rem; font-weight:700; color:#333; margin:0 0 14px; }
        .tl-item { display:flex; gap:14px; align-items:flex-start; margin-bottom:10px; }
        .tl-time { min-width:90px; font-size:0.77rem; font-family:monospace; color:#888; padding-top:4px; }
        .tl-body { flex:1; }
        .tl-event { padding:8px 14px; border-radius:8px; font-size:0.83rem; font-weight:600; }
        .tl-event--online  { background:#d4edda; color:#155724; border-left:4px solid #28a745; }
        .tl-event--offline { background:#f8d7da; color:#721c24; border-left:4px solid #dc3545; }

        .empty-state { text-align:center; padding:40px; color:#bbb; font-size:0.9rem; }
        .no-data-msg  { text-align:center; padding:30px; color:#ccc; font-size:0.85rem; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Detalhes do Cliente',
        'subtitle' => 'Monitorização em tempo real — largura de banda, estabilidade e histórico',
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
                        <div><div>Aguardando…</div><div class="det-status__sub">Primeira verificação pendente</div></div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Configuração PPPoE Username ── --}}
        <div class="det-card" @if(!$plano->mikrotik_username) style="border:2px solid #f5a62355;" @endif>
            <div class="det-card__title">⚙ Configuração PPPoE Username</div>
            @if(!$plano->mikrotik_username)
            <div style="background:#fff8ec;border:1.5px solid #f5a623;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:0.85rem;color:#7a5200;">
                ⚠ Este plano <strong>não tem username PPPoE associado</strong>. O sistema não consegue suspender/activar este cliente no router. Selecione o username correcto abaixo.
            </div>
            @endif
            <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
                <div style="flex:1;min-width:220px;">
                    <label style="font-size:0.72rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:6px;">Username PPPoE no router</label>
                    <input type="text" id="inputMikrotikUsername"
                           value="{{ $plano->mikrotik_username ?? '' }}"
                           placeholder="Ex: 923456789"
                           style="width:100%;height:42px;padding:0 12px;border:1.5px solid #e0e6f0;border-radius:10px;font-size:0.93rem;box-sizing:border-box;">
                </div>
                <button onclick="guardarUsername()" style="height:42px;padding:0 20px;background:#f5a623;color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;white-space:nowrap;">
                    Guardar e Sincronizar
                </button>
                <button onclick="carregarSecrets()" style="height:42px;padding:0 16px;background:#fff;color:#555;border:1.5px solid #e0e6f0;border-radius:10px;font-weight:600;cursor:pointer;white-space:nowrap;">
                    Ver secrets do router
                </button>
            </div>
            <div id="secretsPanel" style="display:none;margin-top:14px;">
                <div style="font-size:0.78rem;color:#aaa;margin-bottom:8px;">Clique num username para seleccionar:</div>
                <div id="secretsList" style="display:flex;flex-wrap:wrap;gap:8px;max-height:200px;overflow-y:auto;"></div>
            </div>
            <div id="usernameMsg" style="margin-top:8px;font-size:0.82rem;"></div>
        </div>

        {{-- ── Sessão Actual ── --}}
        @if($latestSample && $latestSample->sampled_at && $latestSample->sampled_at->gte(now()->subMinutes(10)))
        <div class="session-card">
            <div class="session-item">
                <div class="session-item__lbl">IP da Sessão</div>
                <div class="session-item__val">{{ $latestSample->ip_address ?? '—' }}</div>
            </div>
            <div class="session-item">
                <div class="session-item__lbl">MAC Address</div>
                <div class="session-item__val" style="font-size:0.9rem;">{{ $latestSample->caller_id ?? '—' }}</div>
            </div>
            <div class="session-item">
                <div class="session-item__lbl">Uptime da Sessão</div>
                <div class="session-item__val">{{ $latestSample->getFormattedUptime() }}</div>
            </div>
            <div class="session-item">
                <div class="session-item__lbl">Velocidade do Plano</div>
                <div class="session-item__val--speed">
                    @if($latestSample->max_rx_bps || $latestSample->max_tx_bps)
                        <span class="speed-pill">▼ {{ \App\Models\MikroTikBandwidthSample::formatRate($latestSample->max_rx_bps ?? 0) }}</span>
                        <span class="speed-pill">▲ {{ \App\Models\MikroTikBandwidthSample::formatRate($latestSample->max_tx_bps ?? 0) }}</span>
                    @else
                        <span style="opacity:.6;">Não disponível</span>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- ── Stats grid (6 cartões) ── --}}
        <div class="det-stats">
            {{-- Quedas (30 dias) --}}
            <div class="det-stat det-stat--red">
                @php
                    $dropsMonth = array_sum($dropsChart);
                @endphp
                <div class="det-stat__val">{{ $dropsMonth }}</div>
                <div class="det-stat__lbl">Quedas (30 dias)</div>
            </div>

            {{-- Estabilidade --}}
            <div class="det-stat det-stat--green">
                <div class="det-stat__val">{{ $stabilityPct }}%</div>
                <div class="det-stat__lbl">Estabilidade</div>
                <div class="stability-bar">
                    <div class="stability-bar__fill" style="width:{{ $stabilityPct }}%;"></div>
                </div>
            </div>

            {{-- Downtime total --}}
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
                    @else {{ $tdMins }}m @endif
                </div>
                <div class="det-stat__lbl">Downtime total</div>
            </div>

            {{-- Hoje download --}}
            <div class="det-stat det-stat--blue">
                <div class="det-stat__val">{{ \App\Models\MikroTikBandwidthSample::formatBytes($todayDownloadBytes) }}</div>
                <div class="det-stat__lbl">▼ Hoje (download)</div>
            </div>

            {{-- Hoje upload --}}
            <div class="det-stat det-stat--teal">
                <div class="det-stat__val">{{ \App\Models\MikroTikBandwidthSample::formatBytes($todayUploadBytes) }}</div>
                <div class="det-stat__lbl">▲ Hoje (upload)</div>
            </div>

            {{-- Pico de velocidade --}}
            <div class="det-stat det-stat--purple">
                <div class="det-stat__val" style="font-size:1.1rem;">{{ \App\Models\MikroTikBandwidthSample::formatRate($peakRxRate) }}</div>
                <div class="det-stat__lbl">Pico download</div>
            </div>
        </div>

        {{-- ── Gráfico de Largura de Banda ── --}}
        <div class="det-card">
            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                <div class="det-card__title" style="margin:0;">
                    Largura de Banda
                    <span id="bw-updated" style="font-size:0.75rem; color:#ccc;"></span>
                </div>
                <div class="chart-controls">
                    <button class="range-btn active" onclick="loadRange('1h', this)">1 hora</button>
                    <button class="range-btn" onclick="loadRange('6h', this)">6 horas</button>
                    <button class="range-btn" onclick="loadRange('24h', this)">24 horas</button>
                    <button class="range-btn" onclick="loadRange('7d', this)">7 dias</button>
                </div>
            </div>

            <div class="bw-live">
                <div class="bw-live__item">
                    <div id="bw-rx" class="bw-live__val bw-live__val--dl">—</div>
                    <div class="bw-live__lbl">▼ Download actual</div>
                </div>
                <div class="bw-live__item">
                    <div id="bw-tx" class="bw-live__val bw-live__val--ul">—</div>
                    <div class="bw-live__lbl">▲ Upload actual</div>
                </div>
                <div class="bw-live__item">
                    <div id="bw-ip" class="bw-live__val bw-live__val--sm">—</div>
                    <div class="bw-live__lbl">IP</div>
                </div>
                <div class="bw-live__item">
                    <div id="bw-mac" class="bw-live__val bw-live__val--sm">—</div>
                    <div class="bw-live__lbl">MAC</div>
                </div>
                <div class="bw-live__item">
                    <div id="bw-uptime" class="bw-live__val" style="font-size:1rem; font-weight:800; color:#888;">—</div>
                    <div class="bw-live__lbl">Uptime sessão</div>
                </div>
            </div>

            <div style="position:relative; height:200px;">
                <canvas id="bandwidthChart"></canvas>
            </div>
        </div>

        {{-- ── Aviso de Scheduler ── --}}
        @php $siteId = $cliente->mikrotikSite?->id; @endphp
        <div id="schedulerBanner" style="margin-bottom:14px;padding:13px 16px;background:#fff8ec;border:1.5px solid #f5a623;border-radius:12px;display:flex;gap:12px;align-items:flex-start;">
            <span style="font-size:1.3rem;flex-shrink:0;">🕐</span>
            <div style="flex:1;font-size:.9rem;color:#7a5200;">
                @if(!empty($scheduledDrops))
                    <strong style="color:#b36b00;">Padrão de queda em horário fixo detectado</strong><br>
                    O sistema detectou quedas recorrentes sempre no mesmo intervalo nos últimos 30 dias.
                    Causa provável: regra no <em>System → Scheduler</em> do router MikroTik.<br>
                    <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;align-items:center;">
                        @foreach($scheduledDrops as $drop)
                            <span style="background:#fff;border:1px solid #f5a623;border-radius:6px;padding:3px 10px;font-weight:600;font-size:.85rem;">
                                {{ $drop['horario'] }}h — {{ $drop['dias'] }} dias ({{ $drop['percentagem'] }}%)
                            </span>
                        @endforeach
                        @if($siteId)
                        <button onclick="verScheduler({{ $siteId }}, '{{ implode(',', array_column($scheduledDrops, 'horario')) }}')"
                            style="background:#f5a623;color:#fff;border:none;border-radius:6px;padding:4px 14px;font-size:.85rem;font-weight:700;cursor:pointer;">
                            Gerir Scheduler
                        </button>
                        @endif
                    </div>
                @else
                    <strong style="color:#b36b00;">Scheduler do Router</strong>
                    <span style="color:#aaa;margin-left:6px;font-size:.85rem;">Nenhum padrão de queda agendada detectado nos últimos 30 dias.</span>
                    @if($siteId)
                    <button onclick="verScheduler({{ $siteId }}, '')"
                        style="background:#e8eaf0;color:#555;border:none;border-radius:6px;padding:4px 14px;font-size:.83rem;font-weight:600;cursor:pointer;margin-left:8px;">
                        Ver regras do Scheduler
                    </button>
                    @endif
                @endif
            </div>
        </div>

        {{-- Painel de regras do Scheduler (AJAX) --}}
        <div id="schedulerPanel" style="display:none;margin-bottom:14px;background:#fffbe7;border:1px solid #ffe6a0;border-radius:10px;padding:14px 16px;">
            <div id="schedulerLoading" style="color:#b36b00;font-size:.88rem;">A carregar regras do Scheduler...</div>
            <div id="schedulerRules"></div>
        </div>

        {{-- ── Oscilações de sinal — Quedas por dia ── --}}
        <div class="det-card">
            <div class="det-card__title">
                Oscilações de Sinal
                <span>quedas por dia — últimos 30 dias</span>
            </div>
            @if(array_sum($dropsChart) === 0)
                <div class="no-data-msg">Sem quedas registadas nos últimos 30 dias</div>
            @else
                <div style="position:relative; height:130px;">
                    <canvas id="dropsChart"></canvas>
                </div>
            @endif
        </div>

        {{-- ── Consumo diário — últimos 7 dias ── --}}
        <div class="det-card">
            <div class="det-card__title">
                Consumo Diário
                <span>últimos 7 dias (estimativa)</span>
            </div>
            @php
                $maxDayBytes = max(1, max(array_map(fn($d) => $d['download'] + $d['upload'], $dailyUsage)));
            @endphp
            <table class="day-table">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th>Download</th>
                        <th>Upload</th>
                        <th>Total</th>
                        <th>Quedas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailyUsage as $day)
                    <tr>
                        <td style="font-weight:600; color:#555;">{{ $day['label'] }}</td>
                        <td>
                            <span class="bar-mini" style="width:{{ min(80, ($day['download'] / $maxDayBytes) * 80) }}px; background:#2563eb;"></span>
                            {{ \App\Models\MikroTikBandwidthSample::formatBytes($day['download']) }}
                        </td>
                        <td>
                            <span class="bar-mini" style="width:{{ min(80, ($day['upload'] / $maxDayBytes) * 80) }}px; background:#16a34a;"></span>
                            {{ \App\Models\MikroTikBandwidthSample::formatBytes($day['upload']) }}
                        </td>
                        <td style="font-weight:700;">
                            {{ \App\Models\MikroTikBandwidthSample::formatBytes($day['download'] + $day['upload']) }}
                        </td>
                        <td>
                            @if($day['drops'] > 0)
                                <span style="color:#dc3545; font-weight:700;">{{ $day['drops'] }} ✕</span>
                            @else
                                <span style="color:#28a745;">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ── Histórico de Eventos ── --}}
        <div class="det-card">
            <div class="det-card__title">Histórico de Eventos</div>

            {{-- Filtro por período --}}
            <form method="GET" action="{{ request()->url() }}"
                  style="display:flex;gap:.6rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:1rem;padding-bottom:1rem;border-bottom:1px solid #eef0f5;">
                <div>
                    <label style="font-size:.75rem;font-weight:600;color:#888;display:block;margin-bottom:.25rem;">Data início</label>
                    <input type="date" name="date_from" value="{{ $filterDateFrom ?? '' }}"
                           style="border:1.5px solid #e0e6f0;border-radius:8px;padding:.3rem .65rem;font-size:.85rem;color:#333;background:#fff;">
                </div>
                <div>
                    <label style="font-size:.75rem;font-weight:600;color:#888;display:block;margin-bottom:.25rem;">Data fim</label>
                    <input type="date" name="date_to" value="{{ $filterDateTo ?? '' }}"
                           style="border:1.5px solid #e0e6f0;border-radius:8px;padding:.3rem .65rem;font-size:.85rem;color:#333;background:#fff;">
                </div>
                <button type="submit"
                        style="background:#f5a623;color:#fff;border:none;border-radius:8px;padding:.35rem 1rem;font-size:.85rem;font-weight:600;cursor:pointer;">
                    Filtrar
                </button>
                @if($filterDateFrom || $filterDateTo)
                <a href="{{ request()->url() }}"
                   style="background:#f5f5f5;color:#666;border-radius:8px;padding:.35rem .9rem;font-size:.85rem;font-weight:600;text-decoration:none;line-height:2;">
                    Limpar
                </a>
                <span style="font-size:.8rem;color:#888;align-self:center;">
                    A mostrar {{ $eventos->count() }} evento(s)
                    {{ $filterDateFrom ? 'de '.\Carbon\Carbon::parse($filterDateFrom)->format('d/m/Y') : '' }}
                    {{ $filterDateTo ? 'até '.\Carbon\Carbon::parse($filterDateTo)->format('d/m/Y') : '' }}
                </span>
                @endif
            </form>

            @if($eventos->isEmpty())
                <div class="empty-state">Sem eventos no período seleccionado.</div>
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
                                    ✕ Saiu Offline{{ $ev->disconnect_reason ? ' — ' . $ev->disconnect_reason : '' }}
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

@php
    $jsBwData = [
        'labels'     => $bandwidthSamples->map(fn($s) => $s->sampled_at->format('H:i'))->values()->toArray(),
        'rx'         => $bandwidthSamples->map(fn($s) => round($s->rx_rate / 1000, 1))->values()->toArray(),
        'tx'         => $bandwidthSamples->map(fn($s) => round($s->tx_rate / 1000, 1))->values()->toArray(),
        'current_rx' => $latestSample ? $latestSample->getFormattedRxRate() : null,
        'current_tx' => $latestSample ? $latestSample->getFormattedTxRate() : null,
        'ip'         => $latestSample ? $latestSample->ip_address : null,
        'caller_id'  => $latestSample ? $latestSample->caller_id : null,
        'uptime_fmt' => $latestSample ? $latestSample->getFormattedUptime() : null,
        'sampled_at' => ($latestSample && $latestSample->sampled_at) ? $latestSample->sampled_at->diffForHumans() : null,
    ];
    $jsDropsData = [
        'labels' => array_map(fn($d) => substr($d, 8, 2) . '/' . substr($d, 5, 2), array_keys($dropsChart)),
        'values' => array_values($dropsChart),
    ];
@endphp
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const initialBw = {!! json_encode($jsBwData) !!};
const dropsData = {!! json_encode($jsDropsData) !!};

const trafficUrl = '{{ route('mikrotik.planos.traffic-data', $plano) }}';

// ── Gráfico de Largura de Banda ───────────────────────────────────────────────
const bwCtx = document.getElementById('bandwidthChart').getContext('2d');
const bwChart = new Chart(bwCtx, {
    type: 'line',
    data: {
        labels: initialBw.labels,
        datasets: [
            {
                label: 'Download (Kbps)',
                data: initialBw.rx,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.07)',
                borderWidth: 2, pointRadius: 0, fill: true, tension: 0.35,
            },
            {
                label: 'Upload (Kbps)',
                data: initialBw.tx,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,0.07)',
                borderWidth: 2, pointRadius: 0, fill: true, tension: 0.35,
            },
        ],
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        animation: { duration: 300 },
        plugins: {
            legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: {
                callbacks: {
                    label: c => ` ${c.dataset.label.split(' ')[0]}: ${c.raw} Kbps`,
                },
            },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 12, color: '#bbb' } },
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(0,0,0,0.04)' },
                ticks: { font: { size: 10 }, color: '#bbb', callback: v => v + ' K' },
            },
        },
    },
});

function applyBwData(data) {
    bwChart.data.labels           = data.labels;
    bwChart.data.datasets[0].data = data.rx;
    bwChart.data.datasets[1].data = data.tx;
    bwChart.update('none');

    if (data.current_rx) document.getElementById('bw-rx').textContent = data.current_rx;
    if (data.current_tx) document.getElementById('bw-tx').textContent = data.current_tx;
    if (data.ip)         document.getElementById('bw-ip').textContent = data.ip;
    if (data.caller_id)  document.getElementById('bw-mac').textContent = data.caller_id;
    if (data.uptime_fmt) document.getElementById('bw-uptime').textContent = data.uptime_fmt;
    if (data.sampled_at) document.getElementById('bw-updated').textContent = '— ' + data.sampled_at;
}

applyBwData(initialBw);

let currentRange = '1h';
function loadRange(range, btn) {
    currentRange = range;
    document.querySelectorAll('.range-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    fetchBw(range);
}

function fetchBw(range) {
    fetch(`${trafficUrl}?range=${range}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json()).then(applyBwData).catch(() => {});
}

// Auto-refresh a cada 60 segundos
setInterval(() => fetchBw(currentRange), 60_000);

// ── Gráfico de Quedas por dia ─────────────────────────────────────────────────
const dropsEl = document.getElementById('dropsChart');
if (dropsEl && dropsData.values.some(v => v > 0)) {
    const dropsCtx = dropsEl.getContext('2d');
    new Chart(dropsCtx, {
        type: 'bar',
        data: {
            labels: dropsData.labels,
            datasets: [{
                label: 'Quedas',
                data: dropsData.values,
                backgroundColor: dropsData.values.map(v =>
                    v === 0 ? 'rgba(34,197,94,0.2)' :
                    v <= 2  ? 'rgba(245,158,11,0.6)' :
                               'rgba(220,53,69,0.7)'
                ),
                borderColor: dropsData.values.map(v =>
                    v === 0 ? '#22c55e' :
                    v <= 2  ? '#f59e0b' :
                               '#dc3545'
                ),
                borderWidth: 1,
                borderRadius: 3,
            }],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: c => ` ${c.raw} queda(s)` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 9 }, maxTicksLimit: 15, color: '#bbb' } },
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 }, color: '#bbb' }, grid: { color: 'rgba(0,0,0,0.04)' } },
            },
        },
    });
}

// ── Filtro de eventos ─────────────────────────────────────────────────────────
function filtrar(tipo, btn) {
    document.querySelectorAll('.det-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.ev-row').forEach(row => {
        row.style.display = (tipo === 'todos' || row.dataset.type === tipo) ? '' : 'none';
    });
}

// ── PPPoE Username — associação manual ───────────────────────────────────────
const secretsUrl  = '{{ route('mikrotik.sites.secrets', $cliente->mikrotik_site_id) }}';
const usernameUrl = '{{ route('mikrotik.planos.update-username', $plano->id) }}';
const csrfToken   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function guardarUsername() {
    const val = document.getElementById('inputMikrotikUsername').value.trim();
    const msg = document.getElementById('usernameMsg');
    if (!val) { msg.style.color='#c0392b'; msg.textContent='Introduza o username.'; return; }

    msg.style.color='#888'; msg.textContent='A guardar…';
    fetch(usernameUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ mikrotik_username: val }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            msg.style.color = '#2a8a55';
            msg.textContent = '✓ Guardado e sincronizado! Username: ' + data.mikrotik_username;
            setTimeout(() => location.reload(), 1500);
        } else {
            msg.style.color = '#c0392b';
            msg.textContent = data.error ?? 'Erro ao guardar.';
        }
    })
    .catch(() => { msg.style.color='#c0392b'; msg.textContent='Erro de rede.'; });
}

function carregarSecrets() {
    const panel = document.getElementById('secretsPanel');
    const list  = document.getElementById('secretsList');
    if (panel.style.display === 'block') { panel.style.display='none'; return; }

    list.innerHTML = '<span style="color:#aaa;font-size:0.82rem;">A carregar secrets do router…</span>';
    panel.style.display = 'block';

    fetch(secretsUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data: secrets }) => {
        if (!ok || !Array.isArray(secrets)) {
            const msg = (typeof secrets === 'object' && secrets.error) ? secrets.error : 'Resposta inválida do servidor.';
            list.innerHTML = `<span style="color:#c0392b;font-size:0.82rem;">⚠ ${msg}</span>`;
            return;
        }
        if (secrets.length === 0) {
            list.innerHTML = '<span style="color:#aaa;font-size:0.82rem;">Nenhum secret encontrado.</span>';
            return;
        }
        list.innerHTML = '';
        secrets.forEach(s => {
            const btn = document.createElement('button');
            const isDisabled = s.disabled === 'true' || s.disabled === 'yes';
            btn.style.cssText = `padding:5px 12px;border-radius:20px;border:1.5px solid ${isDisabled ? '#f5c6cb' : '#c3e6cb'};
                background:${isDisabled ? '#fff5f5' : '#f0fff4'};color:${isDisabled ? '#721c24' : '#155724'};
                font-size:0.8rem;font-weight:600;cursor:pointer;`;
            btn.title = `Profile: ${s.profile || '—'} | ${s.comment || ''}`;
            btn.textContent = s.name + (isDisabled ? ' 🔒' : ' ✓');
            btn.onclick = () => {
                document.getElementById('inputMikrotikUsername').value = s.name;
                document.querySelectorAll('#secretsList button').forEach(b => b.style.outline='');
                btn.style.outline = '2px solid #f5a623';
            };
            list.appendChild(btn);
        });
    })
    .catch(() => { list.innerHTML = '<span style="color:#c0392b;font-size:0.82rem;">Erro ao ligar ao router.</span>'; });
}

// ── Scheduler ─────────────────────────────────────────────────────────────────
var _schedulerOpen = false;
function verScheduler(siteId, horarios) {
    var panel   = document.getElementById('schedulerPanel');
    var loading = document.getElementById('schedulerLoading');
    var rulesDiv= document.getElementById('schedulerRules');
    if (_schedulerOpen) { panel.style.display = 'none'; _schedulerOpen = false; return; }
    _schedulerOpen = true;
    panel.style.display = 'block';
    loading.style.display = 'block';
    rulesDiv.innerHTML = '';

    fetch('/mikrotik/sites/' + siteId + '/scheduler', {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        loading.style.display = 'none';
        if (!data.ok || !data.rules || data.rules.length === 0) {
            rulesDiv.innerHTML = '<em style="color:#aaa;font-size:.88rem;">Nenhuma regra de Scheduler no router.</em>';
            return;
        }
        var janelas = horarios ? horarios.split(',').map(h => h.trim()) : [];
        var relevantes = janelas.length > 0 ? data.rules.filter(r => {
            if (!r.start_time) return false;
            var parts = r.start_time.split(':');
            var h = parts[0] || '00';
            var m = parseInt(parts[1] || '0', 10);
            return janelas.indexOf(h + ':' + (m < 30 ? '00' : '30')) !== -1;
        }) : [];
        var toShow = relevantes.length > 0 ? relevantes : data.rules;
        if (relevantes.length === 0 && janelas.length > 0) {
            rulesDiv.innerHTML = '<div style="color:#aaa;font-size:.83rem;margin-bottom:8px;">Nenhuma regra exactamente neste horário. A mostrar todas:</div>';
        }
        var html = '<table style="width:100%;border-collapse:collapse;font-size:.85rem;">'
            + '<thead><tr style="background:#fff8ec;">'
            + '<th style="padding:6px 10px;text-align:left;border-bottom:1px solid #ffe6a0;">Nome</th>'
            + '<th style="padding:6px 10px;text-align:left;border-bottom:1px solid #ffe6a0;">Horário</th>'
            + '<th style="padding:6px 10px;text-align:left;border-bottom:1px solid #ffe6a0;">Intervalo</th>'
            + '<th style="padding:6px 10px;text-align:left;border-bottom:1px solid #ffe6a0;">Script</th>'
            + '<th style="padding:6px 10px;border-bottom:1px solid #ffe6a0;"></th>'
            + '</tr></thead><tbody>';
        toShow.forEach(r => {
            var hl = relevantes.length > 0 && relevantes.includes(r) ? 'background:#fff3d4;' : '';
            html += `<tr style="${hl}">
                <td style="padding:6px 10px;">${esc(r.name)}</td>
                <td style="padding:6px 10px;font-weight:600;">${esc(r.start_time)}</td>
                <td style="padding:6px 10px;">${esc(r.interval || '—')}</td>
                <td style="padding:6px 10px;font-family:monospace;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${esc(r.on_event)}">${esc(r.on_event)}</td>
                <td style="padding:6px 10px;">
                    <button onclick="removerRegraScheduler(${siteId}, '${esc(r.id)}', this)"
                        style="background:#e05a4f;color:#fff;border:none;border-radius:5px;padding:3px 10px;font-size:.8rem;font-weight:700;cursor:pointer;">Remover</button>
                </td></tr>`;
        });
        html += '</tbody></table>';
        rulesDiv.innerHTML = (rulesDiv.innerHTML || '') + html;
    })
    .catch(() => {
        loading.style.display = 'none';
        rulesDiv.innerHTML = '<em style="color:#c0392b;font-size:.88rem;">Erro ao conectar ao router.</em>';
    });
}

function removerRegraScheduler(siteId, ruleId, btn) {
    if (!confirm('Remover esta regra do Scheduler do router?')) return;
    btn.disabled = true; btn.textContent = '...';
    fetch('/mikrotik/sites/' + siteId + '/scheduler/remove', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ rule_id: ruleId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) { btn.closest('tr').remove(); }
        else { btn.disabled = false; btn.textContent = 'Remover'; alert('Falha ao remover. Ver logs.'); }
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Remover'; alert('Erro de rede.'); });
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
