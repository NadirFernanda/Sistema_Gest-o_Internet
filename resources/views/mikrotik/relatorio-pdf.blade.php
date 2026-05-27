<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; background: #fff; }

    .header { padding: 16px 20px 12px; border-bottom: 3px solid #f5a623; margin-bottom: 16px; }
    .header h1 { font-size: 16px; font-weight: 700; color: #222; }
    .header .subtitle { font-size: 10px; color: #888; margin-top: 3px; }
    .header .meta { font-size: 9px; color: #aaa; margin-top: 6px; }

    .sites-row { display: flex; gap: 10px; margin: 0 20px 16px; }
    .site-box {
        flex: 1; border: 1px solid #e0e0e0; border-left: 4px solid #3bb273;
        border-radius: 6px; padding: 8px 12px; font-size: 9px;
    }
    .site-box strong { font-size: 10px; display: block; margin-bottom: 2px; }

    table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
    thead tr { background: #f5a623; color: #fff; }
    th { padding: 7px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; }
    td { padding: 6px 8px; font-size: 9px; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
    tbody tr:nth-child(even) { background: #fafafa; }

    .col-num { width: 28px; text-align: center; color: #aaa; }
    .col-name { min-width: 130px; font-weight: 600; }

    .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 8px; font-weight: 700; }
    .badge-ativo     { background: #e8f7ef; color: #2a8a55; }
    .badge-suspenso  { background: #fdecea; color: #c0392b; }
    .badge-emaviso   { background: #fef9e7; color: #b7770d; }
    .badge-outro     { background: #f0f0f0; color: #777; }

    .footer { margin-top: 16px; padding: 10px 20px 0; border-top: 1px solid #eee; font-size: 8px; color: #aaa; display: flex; justify-content: space-between; }

    .summary { margin: 0 20px 14px; display: flex; gap: 16px; }
    .summary-item { font-size: 9px; color: #555; }
    .summary-item strong { color: #222; font-size: 11px; display: block; }
</style>
</head>
<body>

<div class="header">
    <h1>Relatório MikroTik — Planos Sincronizados</h1>
    <div class="subtitle">Gestão de sites e utilizadores HotSpot</div>
    <div class="meta">Gerado em {{ now()->format('d/m/Y \à\s H:i') }}</div>
</div>

{{-- Sites --}}
<div class="sites-row">
    @foreach($sites as $site)
    <div class="site-box">
        <strong>{{ $site->nome }}</strong>
        {{ $site->localizacao ?? '' }} &bull; {{ $site->host }}:{{ $site->port }} &bull; {{ $site->clientes_count ?? '—' }} cliente(s)
    </div>
    @endforeach
</div>

{{-- Resumo --}}
@php
    $total    = $planos->count();
    $ativos   = $planos->where('estado', 'Ativo')->count();
    $suspensos= $planos->where('estado', 'Suspenso')->count();
    $outros   = $total - $ativos - $suspensos;
@endphp
<div class="summary">
    <div class="summary-item"><strong>{{ $total }}</strong> Total</div>
    <div class="summary-item"><strong style="color:#2a8a55;">{{ $ativos }}</strong> Ativos</div>
    <div class="summary-item"><strong style="color:#c0392b;">{{ $suspensos }}</strong> Suspensos</div>
    @if($outros > 0)
    <div class="summary-item"><strong>{{ $outros }}</strong> Outros</div>
    @endif
</div>

{{-- Tabela --}}
<table>
    <thead>
        <tr>
            <th class="col-num">#</th>
            <th class="col-name">Cliente</th>
            <th>Site</th>
            <th>Plano</th>
            <th>Username MikroTik</th>
            <th>Estado</th>
            <th>Renovação</th>
            <th>Última Sync</th>
        </tr>
    </thead>
    <tbody>
        @forelse($planos as $i => $plano)
        @php
            $badgeClass = match($plano->estado) {
                'Ativo'    => 'badge-ativo',
                'Suspenso' => 'badge-suspenso',
                'Em aviso' => 'badge-emaviso',
                default    => 'badge-outro',
            };
        @endphp
        <tr>
            <td class="col-num">{{ $i + 1 }}</td>
            <td class="col-name">{{ $plano->cliente?->nome ?? '—' }}</td>
            <td>{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
            <td>{{ $plano->nome }}</td>
            <td>{{ $plano->mikrotik_username }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ $plano->estado }}</span></td>
            <td>{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:#aaa;padding:20px;">Nenhum registo encontrado.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <span>LuandaWiFi — Sistema de Gestão</span>
    <span>Total: {{ $planos->count() }} planos</span>
</div>

</body>
</html>
