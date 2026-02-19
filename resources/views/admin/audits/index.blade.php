@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Auditoria',
        'subtitle' => '',
        'stackLeft' => true,
    ])

        {{-- Toolbar padrão (pesquisar à esquerda, CTAs à direita) --}}
        <style>
        .clientes-toolbar, .clientes-toolbar form.search-form-inline {
            max-width:1100px;
            margin:18px auto;
            display:flex;
            gap:10px;
            align-items:center;
        }
        .clientes-toolbar form.search-form-inline {
            flex:1;
            display:flex;
            gap:8px;
            align-items:center;
        }
        .clientes-toolbar .search-input {
            height:40px !important;
            flex:1 !important;
            min-width:320px !important;
            max-width:100%;
            padding:0 12px !important;
            border-radius:8px !important;
            border:2px solid #e6a248 !important;
            box-sizing:border-box;
            font-size:1rem;
            display:inline-flex;
            align-items:center;
        }
        .clientes-toolbar .btn,
        .clientes-toolbar .btn-search,
        .clientes-toolbar .btn-cta,
        .clientes-toolbar .btn-ghost {
            height:40px !important;
            min-width:140px !important;
            max-width:140px !important;
            width:140px !important;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            border-radius:8px;
            text-align:center;
            white-space:nowrap;
            box-sizing:border-box;
        }
        </style>
        <div class="clientes-toolbar">
            <form method="GET" action="{{ url()->current() }}" class="search-form-inline">
                <input type="search" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar auditoria por usuário, ação, módulo ou resumo..." class="search-input" />

                {{-- Dynamic selects: if server provided lists are small, render them; otherwise we'll fetch via AJAX --}}
                @php
                    $renderModules = !empty($modules) && count($modules) <= 100;
                    $renderActions = !empty($actions) && count($actions) <= 100;
                @endphp

                @if($renderModules)
                    <select name="module" class="search-input" style="max-width:220px;padding:0 8px;">
                        <option value="">Todos os módulos</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                @else
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input id="module-search" type="search" placeholder="Pesquisar módulos..." class="search-input" style="max-width:160px;padding:0 8px;" />
                        <select id="module-select" name="module" class="search-input" style="max-width:220px;padding:0 8px;" data-selected="{{ request('module') }}">
                            <option value="">Todos os módulos</option>
                        </select>
                    </div>
                @endif

                @if($renderActions)
                    <select name="action" class="search-input" style="max-width:160px;padding:0 8px;">
                        <option value="">Todas as ações</option>
                        @foreach($actions as $act)
                            <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ \App\Services\AuditService::translateAction($act) }}</option>
                        @endforeach
                    </select>
                @else
                    <div style="display:flex;gap:6px;align-items:center;">
                        <input id="action-search" type="search" placeholder="Pesquisar ações..." class="search-input" style="max-width:120px;padding:0 8px;" />
                        <select id="action-select" name="action" class="search-input" style="max-width:160px;padding:0 8px;" data-selected="{{ request('action') }}">
                            <option value="">Todas as ações</option>
                        </select>
                    </div>
                @endif

                <button type="submit" class="btn btn-search">Pesquisar</button>
                @if(request()->query())
                    <a href="{{ url()->current() }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
                @endif
            </form>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
            </div>
        </div>

    {{-- filtro removido conforme solicitado: permanecer apenas header e tabela --}}

    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
            <thead>
                <tr>
                    <th style="text-align:center;vertical-align:middle;">ID</th>
                    <th style="text-align:center;vertical-align:middle;">Quando</th>
                    <th style="text-align:center;vertical-align:middle;">Usuário</th>
                    <th style="text-align:center;vertical-align:middle;">Ação</th>
                    <th style="text-align:center;vertical-align:middle;">Módulo</th>
                    <th style="text-align:center;vertical-align:middle;">Recurso</th>
                    <th style="text-align:center;vertical-align:middle;">Resumo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $a)
                    <tr>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->id }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->created_at }}</td>
                        <td style="text-align:center;vertical-align:middle;">
                            {{ $a->actor_name ?? (\App\Models\User::find($a->user_id)?->name ?? $a->user_id) }}
                            ({{ \App\Services\AuditService::translateRole($a->actor_role ?? $a->role) }})
                        </td>
                        <td style="text-align:center;vertical-align:middle;">{{ \App\Services\AuditService::translateAction($a->action) }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ $a->module ?? class_basename($a->resource_type ?? $a->auditable_type) }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ class_basename($a->resource_type ?? $a->auditable_type) }}#{{ $a->resource_id ?? $a->auditable_id }}</td>
                        <td style="text-align:center;vertical-align:middle;">{{ \App\Services\AuditService::formatHumanReadable($a) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $audits->links() }}
</div>
<style>
.estoque-container-moderna {
    max-width: 1100px;
    margin: 48px auto 0 auto;
    background: #fafafa;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    padding: 38px 38px 38px 38px;
    min-width: 350px;
}
.estoque-cabecalho-moderna {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
}
.estoque-cabecalho-moderna h1 {
    color: #111;
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 0;
}
.estoque-cabecalho-botoes {
    max-width: 1100px;
    margin: 18px auto 0;
    margin-bottom: 12px;
    padding: 0 12px;
}
.estoque-cabecalho-botoes-inner {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}
.estoque-cabecalho-botoes-inner .btn-block {
    display: block;
    width: 100%;
    padding: 12px 18px;
    border-radius: 12px;
    background: #f7b500;
    color: #fff;
    text-align: center;
    font-weight: 700;
    box-shadow: 0 8px 20px rgba(247,181,0,0.18);
    text-decoration: none;
}
.estoque-cabecalho-botoes-inner .btn-block:hover { opacity: 0.95; }
.estoque-busca-form {
    margin: 0 0 8px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
}
.estoque-busca-form input[type="text"] {
    flex: 1;
    min-width: 220px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.estoque-busca-form button[type="submit"] {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #f7b500;
    color: #fff;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-busca-form .btn-limpar-busca {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #aaa;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-tabela-moderna {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px #0001;
    padding: 18px 18px 8px 18px;
    margin-top: 18px;
    overflow-x: auto;
}
.tabela-estoque-moderna {
    width: 100%;
    min-width: 640px;
    font-size: 1.07em;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.tabela-estoque-moderna td .btn,
.tabela-estoque-moderna td form .btn {
    padding: 6px 8px;
    font-size: 0.95em;
}
    .tabela-estoque-moderna th {
        background: #fffbe7;
        color: #e0a800;
}

/* Responsive: hide Nº Série (4th column) on narrower viewports and tighten table */
@media (max-width: 900px) {
    .tabela-estoque-moderna {
        min-width: 520px;
        font-size: 0.98em;
    }
    .tabela-estoque-moderna th:nth-child(4),
    .tabela-estoque-moderna td:nth-child(4) {
        display: none;
    }
    /* Reduce padding on very small screens */
    @media (max-width: 640px) {
        .tabela-estoque-moderna th,
        .tabela-estoque-moderna td {
            padding: 6px 6px;
            font-size: 0.95em;
        }
        .tabela-estoque-moderna { min-width: 480px; }
    }
}
.tabela-estoque-moderna th {
    background: #fffbe7;
    color: #e0a800;
    font-weight: bold;
    font-size: 1.09em;
    border-bottom: 2px solid #ffe6a0;
    padding: 14px 12px;
}
.tabela-estoque-moderna td {
    background: #fff;
    color: #222;
    font-size: 1em;
    padding: 13px 12px;
}
.tabela-estoque-moderna tr {
    border-bottom: 1px solid #f3e6b0;
}
/* Icon buttons for compact actions */
.btn-icon {
    padding: 6px;
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid #e6e6e6;
    background: #fff;
    color: #222;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease, border-color 0.12s ease;
}
.btn-icon svg { width: 16px; height: 16px; }
.btn-icon:hover { background: #f7b500; color: #fff; border-color: #f7b500; }
.btn-icon.btn-danger:hover { background: #e74c3c; border-color: #e74c3c; color: #fff; }
</style>
@endsection
@push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function(){
    function fetchAndPopulate(url, selectId, q){
        var sel = document.getElementById(selectId);
        if (!sel) return;
        var selected = sel.dataset.selected || '';
        var fullUrl = url + (q ? ('?q=' + encodeURIComponent(q)) : '');
        fetch(fullUrl, {credentials: 'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(list){
                // clear existing options except first (placeholder)
                while (sel.options.length > 1) sel.remove(1);
                list.forEach(function(item){
                    var opt = document.createElement('option');
                    opt.value = item;
                    opt.text = item;
                    sel.appendChild(opt);
                });
                if (selected) sel.value = selected;
                sel.dataset.loaded = '1';
            })
            .catch(function(){ /* ignore */ });
    }

    // simple debounce
    function debounce(fn, delay){
        var t;
        return function(){
            var args = arguments;
            clearTimeout(t);
            t = setTimeout(function(){ fn.apply(null, args); }, delay);
        };
    }

    var modSel = document.getElementById('module-select');
    var modSearch = document.getElementById('module-search');
    if (modSel && modSearch){
        var loadModules = function(q){ fetchAndPopulate('{{ url('/admin/audit-logs/modules') }}', 'module-select', q); };
        modSearch.addEventListener('input', debounce(function(e){ loadModules(e.target.value); }, 300));
        // initial load if page had selected value
        if (modSel.dataset.selected) loadModules('');
    } else if (modSel){
        // legacy: load on focus once
        modSel.addEventListener('focus', function(){ fetchAndPopulate('{{ url('/admin/audit-logs/modules') }}', 'module-select', ''); }, {once:true});
    }

    var actSel = document.getElementById('action-select');
    var actSearch = document.getElementById('action-search');
    if (actSel && actSearch){
        var loadActions = function(q){ fetchAndPopulate('{{ url('/admin/audit-logs/actions') }}', 'action-select', q); };
        actSearch.addEventListener('input', debounce(function(e){ loadActions(e.target.value); }, 300));
        if (actSel.dataset.selected) loadActions('');
    } else if (actSel){
        actSel.addEventListener('focus', function(){ fetchAndPopulate('{{ url('/admin/audit-logs/actions') }}', 'action-select', ''); }, {once:true});
    }
});
</script>
@endpush
