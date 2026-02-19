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
        /* Toolbar layout: single-row, proportional spacing */
        .clientes-toolbar {
            max-width:1100px;
            margin:18px auto;
            display:flex;
            gap:12px;
            align-items:center;
            justify-content:space-between;
            box-sizing:border-box;
            padding:0 8px;
        }
        .clientes-toolbar .right-actions { margin-left:8px; display:flex; gap:8px; align-items:center; flex:0 0 auto; }
        .clientes-toolbar form.search-form-inline {
            flex:1 1 auto; /* take remaining space */
            display:flex;
            gap:10px;
            align-items:center;
            flex-wrap:nowrap; /* keep everything on one line */
        }
        /* primary search expands, others have proportional fixed sizes */
        .clientes-toolbar .search-input { height:42px !important; padding:0 12px !important; border-radius:8px !important; border:2px solid #e6a248 !important; box-sizing:border-box; font-size:1rem; display:inline-flex; align-items:center; }
        /* main free-text search should expand */
        .clientes-toolbar input[name="busca"].search-input { flex:1 1 420px; min-width:240px; }
        /* module and action controls keep fixed but proportional widths */
        .clientes-toolbar #module-select.search-input, .clientes-toolbar #module-autocomplete.search-input { flex:0 0 220px; min-width:160px; max-width:260px; }
        .clientes-toolbar #action-select.search-input, .clientes-toolbar #action-autocomplete.search-input { flex:0 0 220px; min-width:160px; max-width:280px; }
        /* Force neutral select appearance (no blue accent) */
        .clientes-toolbar select.search-input { -webkit-appearance: none; appearance: none; background: #fff !important; color: #222 !important; border: 2px solid #e6a248 !important; box-shadow: none !important; outline: none !important; }
        .clientes-toolbar .btn, .clientes-toolbar .btn-cta, .clientes-toolbar .btn-ghost { height:42px !important; min-width:96px !important; padding:0 14px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; border-radius:8px; text-align:center; white-space:nowrap; box-sizing:border-box; }
        /* make search smaller to match Painel button */
        .clientes-toolbar .btn-search { height:42px !important; min-width:96px !important; padding:0 12px; display:inline-flex; align-items:center; justify-content:center; font-weight:700; border-radius:8px; box-sizing:border-box; }

        /* Responsive: allow collapse into two rows on very small screens */
        @media (max-width: 720px) {
            .clientes-toolbar { padding:6px 8px; }
            .clientes-toolbar form.search-form-inline { flex-wrap:wrap; gap:8px; }
            .clientes-toolbar input[name="busca"].search-input { flex:1 1 100%; }
            .clientes-toolbar #module-select.search-input, .clientes-toolbar #module-autocomplete.search-input, .clientes-toolbar #action-select.search-input, .clientes-toolbar #action-autocomplete.search-input { flex:1 1 45%; }
            .clientes-toolbar .right-actions { width:100%; justify-content:flex-end; margin-top:6px; }
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

                {{-- Module control: render native select when server provided small list, otherwise use autocompleter --}}
                <div style="display:flex;gap:6px;align-items:center;position:relative;">
                    @if($renderModules)
                        <select id="module-select" name="module" class="search-input" data-loaded="1">
                            <option value="" {{ empty(request('module')) ? 'selected' : '' }}>Todos os módulos</option>
                            @foreach($modules as $m)
                                <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="module-autocomplete" name="module" type="search" value="{{ request('module') }}" placeholder="Todos os módulos / pesquisar..." class="search-input" autocomplete="off" data-initial='@json([])' />
                        <div id="module-dropdown" class="module-dropdown" style="display:none;position:absolute;left:0;top:44px;z-index:60;max-height:240px;overflow:auto;background:#fff;border:1px solid #eee;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:180px;"></div>
                    @endif
                </div>

                {{-- Action control: native select when small list, otherwise autocompleter --}}
                <div style="display:flex;gap:6px;align-items:center;position:relative;">
                    @if($renderActions)
                        <select id="action-select" name="action" class="search-input" data-loaded="1">
                            <option value="" {{ empty(request('action')) ? 'selected' : '' }}>Todas as ações</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ \App\Services\AuditService::translateAction($act) }}</option>
                            @endforeach
                        </select>
                    @else
                        <input id="action-autocomplete" name="action" type="search" value="{{ request('action') }}" placeholder="Todas as ações / pesquisar..." class="search-input" autocomplete="off" data-initial='@json([])' />
                        <div id="action-dropdown" class="action-dropdown" style="display:none;position:absolute;left:0;top:44px;z-index:60;max-height:200px;overflow:auto;background:#fff;border:1px solid #eee;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.08);min-width:140px;"></div>
                    @endif
                </div>

                <button type="submit" class="btn btn-search">Pesquisar</button>
                @if(request()->query())
                    <a href="{{ url()->current() }}" class="btn btn-ghost" style="margin-left:6px;">Limpar</a>
                @endif
            </form>
            <div class="right-actions">
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
    min-width: 320px;
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
    min-width: 520px;
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
/* module dropdown items */
.module-item { font-size: 0.95em; }
.module-item:hover { background:#f5f0d8; }
.action-item { font-size: 0.95em; }
.action-item:hover { background:#f5f0d8; }
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
                    if (selectId === 'action-select'){
                        var actionTranslations = { 'create':'Criar','created':'Criado','update':'Atualizar','updated':'Atualizado','delete':'Remover','deleted':'Removido','restore':'Restaurar','restored':'Restaurado','login':'Login','logout':'Logout','attach':'Associar','detach':'Desassociar' };
                        opt.text = actionTranslations[item] || item;
                    } else {
                        opt.text = item;
                    }
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
    // If server did not pre-render options or select has only placeholder, populate it via AJAX
    if (modSel && (!modSel.dataset || !modSel.dataset.loaded || modSel.options.length <= 1)) {
        fetchAndPopulate('{{ url('/admin/audit-logs/modules') }}', 'module-select');
    }
    var actSel = document.getElementById('action-select');
    if (actSel && (!actSel.dataset || !actSel.dataset.loaded || actSel.options.length <= 1)) {
        fetchAndPopulate('{{ url('/admin/audit-logs/actions') }}', 'action-select');
    }
    // client-side translations for common actions (fallback when actions are loaded via AJAX)
    var actionTranslations = {
        'create': 'Criar',
        'created': 'Criado',
        'update': 'Atualizar',
        'updated': 'Atualizado',
        'delete': 'Remover',
        'deleted': 'Removido',
        'restore': 'Restaurar',
        'restored': 'Restaurado',
        'login': 'Login',
        'logout': 'Logout',
        'attach': 'Associar',
        'detach': 'Desassociar'
    };
    // New: unified autocomplete using datalist
    var modInput = document.getElementById('module-autocomplete');
    var moduleList = document.getElementById('module-list');
    if (modInput){
        var dropdown = document.getElementById('module-dropdown');
        var activeIndex = -1;
        var currentItems = [];

        function renderDropdown(list){
            currentItems = list || [];
            activeIndex = -1;
            if (!dropdown) return;
            dropdown.innerHTML = '';
            if (!currentItems.length) { dropdown.style.display = 'none'; return; }
            currentItems.forEach(function(item, idx){
                var el = document.createElement('div');
                el.className = 'module-item';
                el.dataset.index = idx;
                el.dataset.value = item;
                el.style.padding = '8px 10px';
                el.style.cursor = 'pointer';
                el.style.borderBottom = '1px solid #f3f3f3';
                el.textContent = item;
                el.addEventListener('mousedown', function(e){ // use mousedown to set before blur
                    e.preventDefault();
                    modInput.value = item;
                    dropdown.style.display = 'none';
                });
                dropdown.appendChild(el);
            });
            dropdown.style.display = 'block';
        }

        function fetchModules(q){
            var url = '{{ url('/admin/audit-logs/modules') }}' + (q ? ('?q=' + encodeURIComponent(q)) : '');
            fetch(url, {credentials: 'same-origin'})
                .then(function(r){ return r.json(); })
                .then(function(list){ renderDropdown(list); })
                .catch(function(){ /* ignore */ });
        }

        // if server provided initial list, render it
        try{
            var initial = JSON.parse(modInput.dataset.initial || '[]');
            if (initial && initial.length) renderDropdown(initial.slice(0,100));
        } catch(e) { /* ignore */ }

        modInput.addEventListener('input', debounce(function(e){
            var q = e.target.value || '';
            if (!q) {
                // show initial list if any
                try{ var init = JSON.parse(modInput.dataset.initial || '[]'); if (init && init.length) return renderDropdown(init.slice(0,100)); }catch(_){}
                dropdown.style.display = 'none';
                return;
            }
            fetchModules(q);
        }, 200));

        modInput.addEventListener('keydown', function(e){
            if (!dropdown || dropdown.style.display === 'none') return;
            var items = dropdown.querySelectorAll('.module-item');
            if (!items.length) return;
            if (e.key === 'ArrowDown'){
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach(function(it,i){ it.style.background = i === activeIndex ? '#f5f0d8' : ''; });
                items[activeIndex].scrollIntoView({block:'nearest'});
            } else if (e.key === 'ArrowUp'){
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach(function(it,i){ it.style.background = i === activeIndex ? '#f5f0d8' : ''; });
                items[activeIndex].scrollIntoView({block:'nearest'});
            } else if (e.key === 'Enter'){
                if (activeIndex >=0 && items[activeIndex]){
                    e.preventDefault();
                    modInput.value = items[activeIndex].dataset.value;
                    dropdown.style.display = 'none';
                }
            } else if (e.key === 'Escape'){
                dropdown.style.display = 'none';
            }
        });

        // close when clicking outside
        document.addEventListener('click', function(ev){
            if (!dropdown) return;
            if (ev.target === modInput) return;
            if (!dropdown.contains(ev.target)) dropdown.style.display = 'none';
        });
    }

    // Action autocompleter
    var actInput = document.getElementById('action-autocomplete');
    if (actInput){
        var actDropdown = document.getElementById('action-dropdown');
        var actActive = -1;
        var actItems = [];

        function renderActionDropdown(list){
            actItems = list || [];
            actActive = -1;
            if (!actDropdown) return;
            actDropdown.innerHTML = '';
            if (!actItems.length) { actDropdown.style.display = 'none'; return; }
            actItems.forEach(function(item, idx){
                var el = document.createElement('div');
                el.className = 'action-item';
                el.dataset.index = idx;
                el.dataset.value = item;
                el.style.padding = '8px 10px';
                el.style.cursor = 'pointer';
                el.style.borderBottom = '1px solid #f3f3f3';
                el.textContent = (item && item.length) ? (item) : item;
                el.addEventListener('mousedown', function(e){ e.preventDefault(); actInput.value = item; actDropdown.style.display = 'none'; });
                actDropdown.appendChild(el);
            });
            actDropdown.style.display = 'block';
        }

        function fetchActions(q){
            var url = '{{ url('/admin/audit-logs/actions') }}' + (q ? ('?q=' + encodeURIComponent(q)) : '');
            fetch(url, {credentials: 'same-origin'})
                .then(function(r){ return r.json(); })
                .then(function(list){ renderActionDropdown(list); })
                .catch(function(){ /* ignore */ });
        }

        try{ var initialActs = JSON.parse(actInput.dataset.initial || '[]'); if (initialActs && initialActs.length) renderActionDropdown(initialActs.slice(0,100)); } catch(e){}

        actInput.addEventListener('input', debounce(function(e){ var q = e.target.value || ''; if (!q){ try{ var ia = JSON.parse(actInput.dataset.initial || '[]'); if (ia && ia.length) return renderActionDropdown(ia.slice(0,100)); }catch(_){} actDropdown.style.display='none'; return; } fetchActions(q); }, 200));

        actInput.addEventListener('keydown', function(e){ if (!actDropdown || actDropdown.style.display === 'none') return; var items = actDropdown.querySelectorAll('.action-item'); if (!items.length) return; if (e.key === 'ArrowDown'){ e.preventDefault(); actActive = Math.min(actActive + 1, items.length - 1); items.forEach(function(it,i){ it.style.background = i === actActive ? '#f5f0d8' : ''; }); items[actActive].scrollIntoView({block:'nearest'}); } else if (e.key === 'ArrowUp'){ e.preventDefault(); actActive = Math.max(actActive - 1, 0); items.forEach(function(it,i){ it.style.background = i === actActive ? '#f5f0d8' : ''; }); items[actActive].scrollIntoView({block:'nearest'}); } else if (e.key === 'Enter'){ if (actActive >=0 && items[actActive]){ e.preventDefault(); actInput.value = items[actActive].dataset.value; actDropdown.style.display='none'; } } else if (e.key === 'Escape'){ actDropdown.style.display='none'; } });

        document.addEventListener('click', function(ev){ if (!actDropdown) return; if (ev.target === actInput) return; if (!actDropdown.contains(ev.target)) actDropdown.style.display = 'none'; });
    }
});
</script>
@endpush
