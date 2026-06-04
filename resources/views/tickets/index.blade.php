@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .tk-counter { display:flex; gap:12px; flex-wrap:wrap; max-width:1100px; margin:16px auto 0; }
        .tk-card { flex:1; min-width:160px; background:#fff; border-radius:10px; padding:14px 18px;
                   box-shadow:0 2px 10px rgba(0,0,0,.06); border-left:4px solid; cursor:pointer; text-decoration:none; }
        .tk-card:hover { opacity:.85; }
        .tk-card .tk-num { font-size:1.7rem; font-weight:800; line-height:1; }
        .tk-card .tk-label { font-size:0.82rem; color:#888; margin-top:4px; }

        .tk-table { width:100%; border-collapse:collapse; font-size:0.87rem; }
        .tk-table thead { background:#f7f9fb; }
        .tk-table th { padding:9px 12px; text-align:left; font-size:0.73rem; font-weight:700; color:#999;
                       text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid #edf0f4; white-space:nowrap; }
        .tk-table td { padding:10px 12px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
        .tk-table tbody tr:last-child td { border-bottom:none; }
        .tk-table tbody tr:hover { background:#fafbfd; }

        .tk-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; color:#fff; }
        .tk-pri   { display:inline-block; padding:2px 8px; border-radius:4px; font-size:0.73rem; font-weight:700; color:#fff; }
        .tk-id    { font-weight:700; color:#555; font-size:0.82rem; }
        .tk-assunto a { color:#1a1a2e; font-weight:600; text-decoration:none; }
        .tk-assunto a:hover { color:#f5a623; }
        .tk-cliente { font-size:0.83rem; color:#666; }
        .tk-data  { font-size:0.8rem; color:#aaa; white-space:nowrap; }
        .empty-row td { text-align:center; color:#bbb; padding:40px; font-size:0.9rem; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Tickets de Suporte',
        'subtitle' => 'Gestão de pedidos e reclamações de clientes',
    ])

    {{-- Contadores --}}
    <div class="tk-counter">
        <a href="{{ route('tickets.index', ['estado' => 'Aberto']) }}" class="tk-card" style="border-color:#3b82f6;">
            <div class="tk-num" style="color:#3b82f6;">{{ $totais['aberto'] }}</div>
            <div class="tk-label">Abertos</div>
        </a>
        <a href="{{ route('tickets.index', ['estado' => 'Em Andamento']) }}" class="tk-card" style="border-color:#f59e0b;">
            <div class="tk-num" style="color:#f59e0b;">{{ $totais['em_andamento'] }}</div>
            <div class="tk-label">Em Andamento</div>
        </a>
        <a href="{{ route('tickets.index', ['estado' => 'Resolvido']) }}" class="tk-card" style="border-color:#22c55e;">
            <div class="tk-num" style="color:#22c55e;">{{ $totais['resolvido'] }}</div>
            <div class="tk-label">Resolvidos</div>
        </a>
        <a href="{{ route('tickets.index', ['estado' => 'Fechado']) }}" class="tk-card" style="border-color:#9ca3af;">
            <div class="tk-num" style="color:#9ca3af;">{{ $totais['fechado'] }}</div>
            <div class="tk-label">Fechados</div>
        </a>
    </div>

    {{-- Toolbar --}}
    <div class="alertas-toolbar" style="max-width:1100px; margin-top:14px; flex-wrap:wrap; gap:8px;">
        <div class="alertas-toolbar-left" style="flex:1; display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
            <div style="position:relative; flex:1; min-width:200px;">
                <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); pointer-events:none;"
                     xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="#aaa" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" id="tkSearch" placeholder="Pesquisar assunto ou cliente…"
                       value="{{ $search }}" autocomplete="off"
                       style="width:100%; height:38px; padding:0 12px 0 34px; border:1px solid #dde3ec; border-radius:8px; font-size:0.88rem; box-sizing:border-box;">
            </div>
            <select id="tkEstado" onchange="aplicarFiltro()" style="height:38px; padding:0 10px; border:1px solid #dde3ec; border-radius:8px; font-size:0.88rem; background:#fff; cursor:pointer;">
                <option value="" {{ $estadoFiltro === '' ? 'selected' : '' }}>Todos os estados</option>
                <option value="Aberto"       {{ $estadoFiltro === 'Aberto'       ? 'selected' : '' }}>Aberto</option>
                <option value="Em Andamento" {{ $estadoFiltro === 'Em Andamento' ? 'selected' : '' }}>Em Andamento</option>
                <option value="Resolvido"    {{ $estadoFiltro === 'Resolvido'    ? 'selected' : '' }}>Resolvido</option>
                <option value="Fechado"      {{ $estadoFiltro === 'Fechado'      ? 'selected' : '' }}>Fechado</option>
            </select>
            <select id="tkCategoria" onchange="aplicarFiltro()" style="height:38px; padding:0 10px; border:1px solid #dde3ec; border-radius:8px; font-size:0.88rem; background:#fff; cursor:pointer;">
                <option value="" {{ $categoriaFiltro === '' ? 'selected' : '' }}>Todas as categorias</option>
                <option value="Técnico"     {{ $categoriaFiltro === 'Técnico'     ? 'selected' : '' }}>Técnico</option>
                <option value="Cobrança"    {{ $categoriaFiltro === 'Cobrança'    ? 'selected' : '' }}>Cobrança</option>
                <option value="Equipamento" {{ $categoriaFiltro === 'Equipamento' ? 'selected' : '' }}>Equipamento</option>
                <option value="Plano"       {{ $categoriaFiltro === 'Plano'       ? 'selected' : '' }}>Plano</option>
                <option value="Outro"       {{ $categoriaFiltro === 'Outro'       ? 'selected' : '' }}>Outro</option>
            </select>
            <select id="tkPrioridade" onchange="aplicarFiltro()" style="height:38px; padding:0 10px; border:1px solid #dde3ec; border-radius:8px; font-size:0.88rem; background:#fff; cursor:pointer;">
                <option value="" {{ $prioridadeFiltro === '' ? 'selected' : '' }}>Todas as prioridades</option>
                <option value="Baixa"   {{ $prioridadeFiltro === 'Baixa'   ? 'selected' : '' }}>Baixa</option>
                <option value="Normal"  {{ $prioridadeFiltro === 'Normal'  ? 'selected' : '' }}>Normal</option>
                <option value="Alta"    {{ $prioridadeFiltro === 'Alta'    ? 'selected' : '' }}>Alta</option>
                <option value="Urgente" {{ $prioridadeFiltro === 'Urgente' ? 'selected' : '' }}>Urgente</option>
            </select>
        </div>
        <div class="alertas-toolbar-actions">
            <a href="{{ route('tickets.create') }}" class="btn btn-cta" style="white-space:nowrap;">+ Novo Ticket</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="white-space:nowrap;">Painel</a>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="mkt-section">
        <div class="mkt-section-head">
            <h3>Tickets <span style="font-weight:400; color:#999; font-size:0.9rem;">— {{ $tickets->total() }} total</span></h3>
        </div>
        <div style="background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); overflow:hidden;">
            <table class="tk-table">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Assunto</th>
                        <th style="width:120px;">Categoria</th>
                        <th style="width:90px;">Prioridade</th>
                        <th style="width:120px;">Estado</th>
                        <th style="width:130px;">Cliente</th>
                        <th style="width:120px;">Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td class="tk-id">#{{ $ticket->id }}</td>
                        <td class="tk-assunto">
                            <a href="{{ route('tickets.show', $ticket) }}">{{ $ticket->assunto }}</a>
                            @if($ticket->mensagens_count ?? 0 > 0)
                                <span style="font-size:0.75rem; color:#aaa; margin-left:4px;">
                                    {{ $ticket->ultimaMensagem?->created_at?->diffForHumans() }}
                                </span>
                            @endif
                        </td>
                        <td style="font-size:0.82rem; color:#666;">{{ $ticket->categoria }}</td>
                        <td>
                            <span class="tk-pri" style="background:{{ \App\Models\Ticket::prioridadeCor($ticket->prioridade) }};">
                                {{ $ticket->prioridade }}
                            </span>
                        </td>
                        <td>
                            <span class="tk-badge" style="background:{{ \App\Models\Ticket::estadoCor($ticket->estado) }};">
                                {{ $ticket->estado }}
                            </span>
                        </td>
                        <td class="tk-cliente">
                            @if($ticket->cliente)
                                <a href="{{ route('clientes.show', $ticket->cliente) }}" style="color:#555; text-decoration:none;">
                                    {{ Str::limit($ticket->cliente->nome, 20) }}
                                </a>
                            @else
                                <span style="color:#ccc;">—</span>
                            @endif
                        </td>
                        <td class="tk-data">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="7">Nenhum ticket encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;">{{ $tickets->links() }}</div>
    </div>

</div>
@endsection

@push('scripts')
<script>
let searchTimer;
document.getElementById('tkSearch').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => aplicarFiltro(), 500);
});
function aplicarFiltro() {
    const url = new URL(window.location.href);
    const search    = document.getElementById('tkSearch').value.trim();
    const estado    = document.getElementById('tkEstado').value;
    const categoria = document.getElementById('tkCategoria').value;
    const prioridade= document.getElementById('tkPrioridade').value;
    if (search)     url.searchParams.set('search', search);    else url.searchParams.delete('search');
    if (estado)     url.searchParams.set('estado', estado);    else url.searchParams.delete('estado');
    if (categoria)  url.searchParams.set('categoria', categoria); else url.searchParams.delete('categoria');
    if (prioridade) url.searchParams.set('prioridade', prioridade); else url.searchParams.delete('prioridade');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
@endpush
