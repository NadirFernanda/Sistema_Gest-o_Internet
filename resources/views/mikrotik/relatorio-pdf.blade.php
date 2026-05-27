<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size:9.5px; color:#1a1a2e; background:#fff; }

/* ── Header ── */
.header {
    background: linear-gradient(135deg, #f5a623 0%, #e8920f 100%);
    padding: 18px 24px 14px;
    color: #fff;
    margin-bottom: 0;
}
.header-top { display:flex; justify-content:space-between; align-items:flex-start; }
.header h1  { font-size:18px; font-weight:800; letter-spacing:-.3px; }
.header .sub { font-size:10px; opacity:.85; margin-top:3px; }
.header .date-badge {
    background:rgba(255,255,255,.22); padding:4px 12px;
    border-radius:20px; font-size:9px; white-space:nowrap;
}

/* ── Summary bar ── */
.summary-bar {
    background:#1a1a2e; color:#fff;
    display:table; width:100%; padding:0;
}
.summary-bar td {
    padding:10px 20px; text-align:center;
    border-right:1px solid rgba(255,255,255,.1);
    width:25%;
}
.summary-bar td:last-child { border-right:none; }
.summary-bar .num { font-size:18px; font-weight:800; display:block; }
.summary-bar .lbl { font-size:8px; opacity:.65; text-transform:uppercase; letter-spacing:.06em; }
.num-green  { color:#4ade80; }
.num-red    { color:#f87171; }
.num-yellow { color:#fbbf24; }

/* ── Sites grid ── */
.sites-wrap { padding:14px 20px 0; display:table; width:100%; }
.site-box {
    display:table-cell; width:50%;
    padding:10px 14px; background:#f8f9fb;
    border:1px solid #e8ecf2; border-radius:7px;
}
.site-box + .site-box { margin-left:10px; }
.site-box .s-name { font-weight:700; font-size:10px; color:#1a1a2e; }
.site-box .s-loc  { font-size:8.5px; color:#999; margin-top:2px; }
.site-box .s-meta { margin-top:5px; font-size:8.5px; color:#666; }
.site-box .s-ip   { background:#e8ecf2; padding:1px 6px; border-radius:4px; font-family:monospace; }
.site-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:#4ade80; margin-right:4px; vertical-align:middle; }

/* ── Table ── */
.tbl-wrap { padding:16px 20px 0; }
.section-label {
    font-size:9px; font-weight:700; text-transform:uppercase;
    letter-spacing:.08em; color:#999; margin-bottom:8px;
    padding-bottom:4px; border-bottom:2px solid #f5a623; display:inline-block;
}
table.data { width:100%; border-collapse:collapse; }
table.data thead tr { background:#1a1a2e; }
table.data th {
    padding:8px 10px; color:#fff; font-size:8.5px; font-weight:700;
    text-transform:uppercase; letter-spacing:.05em; text-align:left;
}
table.data td { padding:7px 10px; font-size:9px; border-bottom:1px solid #f0f2f5; }
table.data tbody tr:nth-child(even) td { background:#f8f9fb; }
table.data tbody tr:last-child td { border-bottom:none; }

.col-n   { width:30px; text-align:center; color:#bbb; }
.col-nm  { min-width:110px; font-weight:600; color:#1a1a2e; }
.col-si  { color:#777; }
.col-pl  { color:#555; }
.col-us  { font-family:monospace; background:#f4f6f9; padding:1px 5px; border-radius:4px; }

.badge   { display:inline-block; padding:2px 8px; border-radius:12px; font-size:8px; font-weight:700; }
.b-ativo    { background:#dcfce7; color:#166534; }
.b-suspenso { background:#fee2e2; color:#991b1b; }
.b-aviso    { background:#fef9c3; color:#854d0e; }
.b-outro    { background:#f3f4f6; color:#6b7280; }

/* ── Footer ── */
.footer {
    margin-top:18px; padding:10px 20px;
    border-top:1px solid #e8ecf2;
    display:table; width:100%;
}
.footer td { font-size:8px; color:#aaa; }
.footer td:last-child { text-align:right; }
</style>
</head>
<body>

{{-- Header --}}
<div class="header">
    <div class="header-top">
        <div>
            <div class="header h1" style="font-size:18px;font-weight:800;">LuandaWiFi</div>
            <div class="sub">Relatório MikroTik — Planos Sincronizados</div>
        </div>
        <div class="date-badge">{{ now()->format('d/m/Y H:i') }}</div>
    </div>
</div>

{{-- Summary bar --}}
@php
    $total     = $planos->count();
    $ativos    = $planos->where('estado', 'Ativo')->count();
    $suspensos = $planos->where('estado', 'Suspenso')->count();
    $aviso     = $planos->where('estado', 'Em aviso')->count();
@endphp
<table class="summary-bar">
    <tr>
        <td><span class="num">{{ $total }}</span><span class="lbl">Total</span></td>
        <td><span class="num num-green">{{ $ativos }}</span><span class="lbl">Ativos</span></td>
        <td><span class="num num-red">{{ $suspensos }}</span><span class="lbl">Suspensos</span></td>
        <td><span class="num num-yellow">{{ $aviso }}</span><span class="lbl">Em aviso</span></td>
    </tr>
</table>

{{-- Sites --}}
<div class="sites-wrap">
    <table style="width:100%;border-collapse:separate;border-spacing:8px 0;">
        <tr>
            @foreach($sites as $site)
            <td style="background:#f8f9fb;border:1px solid #e8ecf2;border-radius:7px;padding:10px 14px;vertical-align:top;">
                <div style="display:flex;align-items:center;gap:6px;">
                    <span class="site-dot"></span>
                    <span style="font-weight:700;font-size:10px;color:#1a1a2e;">{{ $site->nome }}</span>
                </div>
                @if($site->localizacao)
                <div style="font-size:8.5px;color:#999;margin-top:2px;">{{ $site->localizacao }}</div>
                @endif
                <div style="margin-top:5px;font-size:8.5px;color:#666;">
                    <span style="background:#e8ecf2;padding:1px 6px;border-radius:4px;font-family:monospace;">{{ $site->host }}:{{ $site->port }}</span>
                    &nbsp;·&nbsp; {{ $site->clientes_count ?? '—' }} cliente(s)
                </div>
            </td>
            @endforeach
        </tr>
    </table>
</div>

{{-- Table --}}
<div class="tbl-wrap">
    <div style="margin-bottom:8px;">
        <span class="section-label">Listagem de Planos</span>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th class="col-n">#</th>
                <th class="col-nm">Cliente</th>
                <th class="col-si">Site</th>
                <th class="col-pl">Plano</th>
                <th>Username MikroTik</th>
                <th>Estado</th>
                <th>Renovação</th>
                <th>Última Sync</th>
            </tr>
        </thead>
        <tbody>
            @forelse($planos as $i => $plano)
            @php
                $bc = match($plano->estado) {
                    'Ativo'    => 'b-ativo',
                    'Suspenso' => 'b-suspenso',
                    'Em aviso' => 'b-aviso',
                    default    => 'b-outro',
                };
            @endphp
            <tr>
                <td class="col-n">{{ $i + 1 }}</td>
                <td class="col-nm">{{ $plano->cliente?->nome ?? '—' }}</td>
                <td class="col-si">{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
                <td class="col-pl">{{ $plano->nome }}</td>
                <td><span class="col-us">{{ $plano->mikrotik_username }}</span></td>
                <td><span class="badge {{ $bc }}">{{ $plano->estado }}</span></td>
                <td>{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
                <td>{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#bbb;padding:24px;">Nenhum registo.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Footer --}}
<table class="footer">
    <tr>
        <td>LuandaWiFi — Sistema de Gestão &copy; {{ now()->year }}</td>
        <td>Total de registos: {{ $planos->count() }}</td>
    </tr>
</table>

</body>
</html>
