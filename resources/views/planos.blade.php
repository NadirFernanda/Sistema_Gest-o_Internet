@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <div class="planos-header">
            <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
            <h1>Gestão de Planos</h1>
        </div>
        <div class="hero-ctas">
            <a href="{{ route('dashboard') }}" class="btn btn-cta">Voltar ao Dashboard</a>
            <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn btn-cta">Gerir Modelos</a>
            <button type="button" id="refreshTemplatesBtn" class="btn btn-cta">Atualizar Modelos</button>
            <a href="{{ route('planos.create') }}" class="btn btn-cta" id="openCreatePlano">Cadastrar Plano</a>
        </div>
        <!-- Hidden inline form container (loaded from partial). Shown only when user clicks 'Cadastrar Plano' -->
        <div id="planCreateContainer" style="display:none; margin-top:16px;">
            @includeWhen(true, 'planos._form')
        </div>
        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert alert-success" style="margin-top:16px;padding:12px;border-radius:6px;background:#e6f7d9;color:#155724;">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger" style="margin-top:16px;padding:12px;border-radius:6px;background:#f8d7da;color:#721c24;">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="margin-top:16px;padding:12px;border-radius:6px;background:#f8d7da;color:#721c24;">
                <ul style="margin:0 0 0 18px;padding:0;">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="search-row" style="margin-top:32px;">
            <h2 style="margin:0 0 8px 0;">Lista de Planos</h2>
            <div class="search-bar" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                <div class="search-input-wrap" style="position:relative;flex:1;min-width:220px;max-width:760px;">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.7;">
                        <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm8.707 2.293-4.387-4.387" stroke="#6b6b6b" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input type="text" id="buscaPlanos" class="search-input" placeholder="Pesquisar por plano ou cliente..." aria-label="Pesquisar planos" style="width:100%;padding:12px 44px 12px 42px;border-radius:10px;border:1px solid #e9e9e9;box-shadow:0 6px 18px rgba(0,0,0,0.04);" />
                    <button id="clearSearch" class="search-clear" title="Limpar pesquisa" aria-label="Limpar pesquisa" style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:transparent;border:none;font-size:18px;color:#9b9b9b;cursor:pointer;">×</button>
                </div>
                <button type="button" id="btnBuscarPlanos" class="btn-cta search-btn" style="padding:10px 16px;">Pesquisar</button>
            </div>
        </div>
        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* simple modal styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.48); align-items:center; justify-content:center; z-index:1200; }
        /* make modal wider but limit height so content scrolls internally */
        #templatesModal .modal { background:#fff; width:94%; max-width:1200px; border-radius:10px; padding:18px; box-shadow:0 10px 40px rgba(0,0,0,0.16); max-height:80vh; overflow:auto; }
        /* keep modal content scrollable when tall */
        #templatesModal .modal .modal-body { max-height:72vh; overflow:auto; padding-right:6px; }
        #templatesModal table { width:100%; border-collapse:collapse; }
        /* make the table area scrollable so modal controls remain visible */
        #templatesModal .templates-table-wrapper{ max-height:50vh; overflow:auto; }
        #templatesModal .templates-table-wrapper table{ width:100%; border-collapse:collapse; }
            #templatesModal th, #templatesModal td { padding:10px 10px; border-bottom:1px solid #f1f1f1; text-align:left; vertical-align:middle; }
        #templatesModal thead th { background:transparent; font-weight:700; }
        #templatesModal .controls { display:flex; gap:12px; margin-bottom:12px; flex-wrap:wrap; }
        /* Ensure modal primary buttons use the project yellow even if Bootstrap is present */
        #templatesModal .btn-primary--fixed { background: #f7b500 !important; color: #fff !important; box-shadow: 0 6px 18px rgba(247,181,0,0.12) !important; }
        #templatesModal .small-btn { padding:10px 14px; border-radius:8px; border:none; cursor:pointer; font-weight:600; }
        /* Actions column - modern stacked buttons with gap */
        #templatesModal .template-actions{ display:flex; flex-direction:column; gap:10px; align-items:flex-end; }
        #templatesModal .editBtn, #templatesModal .delBtn{ min-width:140px; padding:10px 14px; border-radius:8px; border:none; cursor:pointer; font-weight:600; }
            /* Toned-down primary color and smaller, modern buttons */
            #templatesModal .btn-primary--fixed { background: #e0a200 !important; color: #fff !important; box-shadow: 0 4px 12px rgba(224,162,2,0.08) !important; }
            #templatesModal .small-btn { padding:8px 10px; border-radius:10px; border:none; cursor:pointer; font-weight:600; font-size:0.95rem; }
            /* Actions column - compact stacked buttons with subtle shadow */
            #templatesModal .editBtn{ background:#e0a200; color:#fff; box-shadow:0 6px 18px rgba(224,162,2,0.08); }
            #templatesModal .delBtn{ background:#f3f3f3; color:#222; box-shadow:none; }
        @media (max-width:900px){ #templatesModal .template-actions{ flex-direction:row; gap:8px; align-items:center; } #templatesModal .editBtn, #templatesModal .delBtn{ min-width:96px; } }
        /* Top hero CTAs: softened color, smaller size, modern spacing */
        .hero-ctas{ display:flex; gap:14px; justify-content:center; flex-wrap:wrap; margin:18px 0; }
        .hero-ctas .btn-cta{ background:#e0a200; color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:700; box-shadow:0 6px 18px rgba(224,162,2,0.08); display:inline-flex; align-items:center; justify-content:center; font-size:1rem; transition:transform .12s ease, box-shadow .12s ease, filter .12s ease; }
        .hero-ctas .btn-cta:hover{ transform:translateY(-2px); box-shadow:0 10px 26px rgba(224,162,2,0.10); filter:brightness(.99); }
        .hero-ctas .btn-cta:active{ transform:translateY(0); }
        @media (max-width:720px){ .hero-ctas .btn-cta{ padding:9px 14px; font-size:0.95rem; } }
        /* Modern search bar styles */
        .search-bar .search-input { width:100%; padding:12px 44px 12px 42px; border-radius:10px; border:1px solid #e9e9e9; box-shadow:0 6px 18px rgba(0,0,0,0.04); }
        .search-bar .search-btn{ background:#e0a200; color:#fff; border-radius:10px; font-weight:700; box-shadow:0 6px 18px rgba(224,162,2,0.08); border:none; cursor:pointer; }
        .search-bar .search-btn:hover{ filter:brightness(.98); }
        .search-input-wrap .search-icon{ left:12px; }
        .search-input-wrap .search-clear{ right:8px; }
        /* Standardize table status badges and action buttons in main list */
        #planosLista table { width:100%; }
        #planosLista td, #planosLista th { vertical-align:middle; }
        #planosLista .status-badge{ display:inline-block; min-width:84px; padding:6px 10px; border-radius:8px; text-align:center; font-weight:700; font-size:0.95rem; color:#fff; }
        #planosLista .status-badge.ativo{ background:#38a169; }
        #planosLista .status-badge.inativo{ background:#e53e3e; }
        /* action buttons column: stacked on desktop, row on mobile */
        #planosLista .action-buttons{ display:flex; flex-direction:column; gap:8px; align-items:flex-end; }
        #planosLista .action-buttons .btn{ min-width:120px; padding:8px 12px; border-radius:10px; font-weight:700; font-size:0.95rem; }
        @media (max-width:900px){ #planosLista .action-buttons{ flex-direction:row; } #planosLista .action-buttons .btn{ min-width:96px; } }
        /* Sticky header inside modal so controls remain visible while scrolling content */
        #templatesModal .modal-header { position: sticky; top: 0; z-index: 22; background: #fff; padding-bottom:8px; border-bottom:1px solid #f6f6f6; }
    </style>
    <script>
        (function(){
            const display = document.getElementById('precoPlanoDisplay');
            const hidden = document.getElementById('precoPlano');
            if (display) {
                function unformat(value){
                    if(!value) return '';
                    // remove non numeric except comma and dot
                    let v = value.replace(/[^0-9,\.]/g, '');
                    v = v.replace(/,/g, '.');
                    return v;
                }
                function formatNumber(num){
                    return 'Kz ' + Number(num).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                display.addEventListener('input', function(e){
                    const raw = unformat(this.value);
                    const n = parseFloat(raw);
                    if(!isNaN(n)) {
                        hidden.value = n.toFixed(2);
                    } else {
                        hidden.value = '';
                    }
                });
                display.addEventListener('blur', function(){
                    const raw = unformat(this.value);
                    const n = parseFloat(raw);
                    if(!isNaN(n)) this.value = formatNumber(n);
                });
                display.addEventListener('focus', function(){
                    // show editable raw number when focusing
                    const raw = hidden.value;
                    if(raw) this.value = raw.replace('.', ',');
                    else this.value = '';
                });
                // ensure hidden has value before any submit triggered by other scripts
                const form = document.getElementById('formPlano');
                if(form){
                    form.addEventListener('submit', function(){
                        // make sure hidden has plain dot-decimal string
                        const raw = unformat(display.value);
                        const n = parseFloat(raw);
                        if(!isNaN(n)) hidden.value = n.toFixed(2);
                    });
                }
            }
                // load templates and hook selector
            (function(){
                const tplSelect = document.getElementById('templateSelector');
                const refreshBtn = document.getElementById('refreshTemplatesBtn');
                if(!tplSelect) return;

                // expose a global loader so other modules (modal) can refresh templates
                window.loadTemplates = function(){
                    fetch('{{ route('plan-templates.list.json') }}')
                        .then(r => r.json())
                        .then(list => {
                            // if Choices instance exists, use setChoices to update
                            if(window._choicesMap && window._choicesMap['templateSelector']){
                                const choices = list.map(t => ({ value: t.id, label: t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2}) : '') }));
                                window._choicesMap['templateSelector'].setChoices(choices, 'value', 'label', true);
                            } else {
                                // fallback: rebuild native options
                                tplSelect.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
                                list.forEach(t => {
                                    const opt = document.createElement('option');
                                    opt.value = t.id;
                                    opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2}) : '');
                                    tplSelect.appendChild(opt);
                                });
                            }
                        }).catch(()=>{});
                };

                tplSelect.addEventListener('change', function(){
                    const id = this.value;
                    if(!id) return;
                    fetch(`/plan-templates/${id}/json`)
                        .then(r => r.json())
                        .then(t => {
                            if(t.name) document.getElementById('nomePlano').value = t.name;
                            if(t.description) document.getElementById('descricaoPlano').value = t.description;
                            if(t.preco){
                                document.getElementById('precoPlano').value = Number(t.preco).toFixed(2);
                                document.getElementById('precoPlanoDisplay').value = 'Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2});
                            }
                            if(t.ciclo) document.getElementById('cicloPlano').value = t.ciclo;
                            if(t.estado) document.getElementById('estadoPlano').value = t.estado;
                        }).catch(()=>{});
                });

                // safe caller wrapper in case the loader isn't available (prevents ReferenceError)
                function callLoadTemplates(){ if(typeof window.loadTemplates === 'function'){ try{ window.loadTemplates(); }catch(_){} } }

                if(refreshBtn) refreshBtn.addEventListener('click', callLoadTemplates);

                // initial load
                callLoadTemplates();
            })();
                        // Modal for managing templates
                        (function(){
                                // create modal markup
                                const modalHtml = `
                                <div id="templatesModal">
                                    <div class="modal">
                                        <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                                    <h3 style="margin:0">Modelos de Plano</h3>
                                                    <div>
                                                        <button id="closeTemplatesModal" class="small-btn btn btn-secondary">Fechar</button>
                                                    </div>
                                                </div>
                                        <div class="controls">
                                                <button id="newTemplateBtn" class="small-btn btn" style="background:#f7b500;color:#fff;box-shadow:0 6px 18px rgba(247,181,0,0.18);">Novo Modelo</button>
                                            <button id="reloadTemplatesBtn" class="small-btn btn btn-secondary">Recarregar</button>
                                        </div>
                                        <div id="templatesListContainer"><em>Carregando...</em></div>
                                        <div id="templateFormContainer" style="margin-top:12px; display:none;"></div>
                                    </div>
                                </div>`;

                                document.body.insertAdjacentHTML('beforeend', modalHtml);
                                const modal = document.getElementById('templatesModal');
                                const listContainer = document.getElementById('templatesListContainer');
                                const formContainer = document.getElementById('templateFormContainer');
                                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                                function openModal(){ modal.style.display = 'flex'; loadList(); }
                                function closeModal(){ modal.style.display = 'none'; formContainer.style.display = 'none'; }

                                function fetchJson(url, opts){
                                        opts = opts || {};
                                        opts.headers = Object.assign({ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }, opts.headers || {});
                                        return fetch(url, opts).then(r => r.json());
                                }

                                function loadList(){
                                        listContainer.innerHTML = '<em>Carregando...</em>';
                                        fetch('{{ route('plan-templates.list.json') }}')
                                                .then(r => r.json())
                                                .then(list => renderList(list))
                                                .catch(()=>{ listContainer.innerHTML = '<div>Erro ao carregar.</div>'; });
                                }

                                function renderList(list){
                                    if(!list.length){ listContainer.innerHTML = '<div class="muted" style="padding:8px 0">Nenhum modelo cadastrado.</div>'; return; }
                                    let html = '<div class="templates-table-wrapper"><table><thead><tr><th>Nome</th><th>Preço</th><th> Clico</th><th>Estado</th><th style="width:170px"></th></tr></thead><tbody>';
                                    list.forEach(t => {
                                        html += `<tr data-id="${t.id}"><td>${escapeHtml(t.name)}</td><td>${t.preco?('Kz '+Number(t.preco).toLocaleString('pt-AO',{minimumFractionDigits:2})):''}</td><td>${t.ciclo||''}</td><td>${t.estado||''}</td><td><div class="template-actions"><button class="editBtn" data-id="${t.id}">Editar</button><button class="delBtn" data-id="${t.id}">Apagar</button></div></td></tr>`;
                                    });
                                    html += '</tbody></table></div>';
                                    listContainer.innerHTML = html;
                                    // attach handlers
                                    listContainer.querySelectorAll('.editBtn').forEach(b => b.addEventListener('click', e => showEditForm(e.target.getAttribute('data-id'))));
                                    listContainer.querySelectorAll('.delBtn').forEach(b => b.addEventListener('click', e => deleteTemplate(e.target.getAttribute('data-id'))));
                                }

                                function showCreateForm(){
                                    formContainer.style.display = 'block';
                                    formContainer.innerHTML = formHtml();
                                    bindForm();
                                    // focus and bring the first input into view and ensure modal scrolls to it
                                    setTimeout(() => {
                                        const first = formContainer.querySelector('input[name="name"]');
                                        try{
                                            if(first){ first.focus({preventScroll:false}); first.scrollIntoView({behavior:'smooth', block:'center'}); }
                                            if(modal){ modal.querySelector('.modal').scrollTop = Math.max(0, formContainer.offsetTop - 40); }
                                        }catch(_){}
                                    }, 120);
                                }

                                function showEditForm(id){
                                    formContainer.style.display = 'block';
                                    formContainer.innerHTML = '<div>Carregando...</div>';
                                    fetch(`/plan-templates/${id}/json`).then(r => r.json()).then(t => {
                                        formContainer.innerHTML = formHtml(t);
                                        bindForm(id);
                                        // focus name input when editing and ensure visible and scroll modal
                                        setTimeout(() => {
                                            const first = formContainer.querySelector('input[name="name"]');
                                            try{
                                                if(first){ first.focus({preventScroll:false}); first.scrollIntoView({behavior:'smooth', block:'center'}); }
                                                if(modal){ modal.querySelector('.modal').scrollTop = Math.max(0, formContainer.offsetTop - 40); }
                                            }catch(_){}
                                        }, 120);
                                    }).catch(()=>{ formContainer.innerHTML = '<div>Erro ao carregar modelo.</div>'; });
                                }

                                function formHtml(data){
                                        data = data || {name:'', description:'', preco:'', ciclo:'', estado:''};
                                        return `
                                            <form id="templateAjaxForm">
                                                <div style="display:flex; gap:8px;">
                                                    <input name="name" placeholder="Nome" required value="${escapeAttr(data.name)}" style="flex:1;padding:8px;border:1px solid #ccc;border-radius:6px;" />
                                                    <input name="preco" placeholder="Preço" value="${escapeAttr(data.preco||'')}" style="width:140px;padding:8px;border:1px solid #ccc;border-radius:6px;" />
                                                </div>
                                                <div style="margin-top:8px;"><input name="ciclo" placeholder="Ciclo (dias)" value="${escapeAttr(data.ciclo||'')}" style="width:160px;padding:8px;border:1px solid #ccc;border-radius:6px;" /></div>
                                                <div style="margin-top:8px;"><input name="estado" placeholder="Estado" value="${escapeAttr(data.estado||'')}" style="padding:8px;border:1px solid #ccc;border-radius:6px;" /></div>
                                                <div style="margin-top:8px;"><textarea name="description" placeholder="Descrição" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:6px;">${escapeAttr(data.description||'')}</textarea></div>
                                                <div style="margin-top:8px; display:flex; gap:8px; justify-content:flex-end;">
                                                    <button type="button" id="cancelTemplateForm" class="small-btn btn btn-secondary">Cancelar</button>
                                                    <button type="submit" class="small-btn btn btn-primary">Salvar</button>
                                                </div>
                                            </form>`;
                                }

                                function bindForm(id){
                                        const form = document.getElementById('templateAjaxForm');
                                        document.getElementById('cancelTemplateForm').addEventListener('click', ()=>{ formContainer.style.display='none'; });
                                        form.addEventListener('submit', function(e){
                                                e.preventDefault();
                                                const fd = new FormData(form);
                                                const url = id?`/plan-templates/${id}`:'/plan-templates';
                                                const method = id?'PUT':'POST';
                                                fetch(url, { method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': method }, body: fd })
                                                        .then(r => { if(r.ok) return r.text(); throw new Error('Erro'); })
                                                .then(()=>{ loadList(); callLoadTemplates(); formContainer.style.display='none'; })
                                                        .catch(()=> alert('Erro ao salvar.'));
                                        });
                                }

                                function deleteTemplate(id){
                                        if(!confirm('Confirma apagar este modelo?')) return;
                                        fetch(`/plan-templates/${id}`, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override':'DELETE' } })
                                            .then(r => { if(r.ok) return r.text(); throw new Error('Erro'); })
                                            .then(()=>{ loadList(); callLoadTemplates(); })
                                                .catch(()=> alert('Erro ao apagar.'));
                                }

                                function escapeHtml(s){ if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
                                function escapeAttr(s){ if(!s) return ''; return String(s).replace(/"/g,'&quot;'); }

                                // open modal via Manage Models button (use stable id)
                                const manageBtn = document.getElementById('manageTemplatesBtn');
                                if(manageBtn){ manageBtn.addEventListener('click', function(e){ e.preventDefault(); openModal(); }); }
                                const refreshBtnEl = document.getElementById('refreshTemplatesBtn');
                                if(refreshBtnEl) refreshBtnEl.addEventListener('click', loadTemplates);
                                if(modal){
                                    const reloadBtn = modal.querySelector('#reloadTemplatesBtn');
                                    if(reloadBtn) reloadBtn.addEventListener('click', loadList);
                                    const newBtn = modal.querySelector('#newTemplateBtn');
                                    if(newBtn) newBtn.addEventListener('click', showCreateForm);
                                }

                                // robust handlers: support clicking backdrop, the close button, Escape key
                                modal.addEventListener('click', function(e){
                                    // debug: log clicks inside modal
                                    try { console.debug('[templatesModal] click on', e.target && (e.target.id || e.target.className || e.target.tagName)); } catch(_){}
                                    // backdrop click
                                    if(e.target && e.target.id === 'templatesModal') return closeModal();
                                    // close button
                                    if(e.target && e.target.id === 'closeTemplatesModal') return closeModal();
                                    // delegated handlers for buttons that may be re-rendered
                                    const newBtn = e.target.closest && e.target.closest('#newTemplateBtn');
                                    if(newBtn) { try{ console.debug('delegated: newTemplateBtn'); }catch(_){}; return showCreateForm(); }
                                    const reloadBtn = e.target.closest && e.target.closest('#reloadTemplatesBtn');
                                    if(reloadBtn) { try{ console.debug('delegated: reloadTemplatesBtn'); }catch(_){}; return loadList(); }
                                });
                                document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal && modal.style.display === 'flex') closeModal(); });
                        })();
        })();
    </script>
    <script>
        // AJAX search (debounced) — updates #planosLista without full reload
        (function(){
            const btn = document.getElementById('btnBuscarPlanos');
            const input = document.getElementById('buscaPlanos');
            const clear = document.getElementById('clearSearch');
            const lista = document.getElementById('planosLista');
            if(!input || !lista) return;

            const headers = { 'X-Requested-With':'XMLHttpRequest' };

            function updateHistory(q){
                try{
                    const url = new URL(window.location.href);
                    if(q) url.searchParams.set('q', q); else url.searchParams.delete('q');
                    window.history.replaceState({}, '', url.toString());
                }catch(_){ }
            }

            function renderPlans(plans){
                if(!Array.isArray(plans) || !plans.length){ lista.innerHTML = '<div style="padding:12px 0">Nenhum plano encontrado.</div>'; return; }
                let html = '<table class="table"><thead><tr><th>Cliente</th><th>Nome</th><th>Preço</th><th>Ciclo</th><th>Estado</th><th style="width:160px"></th></tr></thead><tbody>';
                plans.forEach(p => {
                    const cliente = p.cliente && (p.cliente.nome || p.cliente.name) ? (p.cliente.nome || p.cliente.name) : '-';
                    const preco = p.preco ? ('Kz ' + Number(p.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2})) : '';
                    const estadoClass = (p.estado && p.estado.toLowerCase && p.estado.toLowerCase().includes('ativo')) ? 'ativo' : 'inativo';
                    html += `<tr data-id="${p.id}"><td>${escapeHtml(cliente)}</td><td>${escapeHtml(p.nome||p.name||'')}</td><td>${preco}</td><td>${escapeHtml(p.ciclo||'')}</td><td><span class="status-badge ${estadoClass}">${escapeHtml(p.estado||'')}</span></td><td><div class="action-buttons"><button class="btn btn-sm" data-id="${p.id}">Editar</button><button class="btn btn-sm" data-id="${p.id}">Apagar</button></div></td></tr>`;
                });
                html += '</tbody></table>';
                lista.innerHTML = html;
            }

            function fetchAndUpdate(q){
                const apiUrl = '/api/planos' + (q ? '?busca=' + encodeURIComponent(q) : '');
                const prev = lista.innerHTML;
                lista.innerHTML = '<div style="padding:12px 0">Carregando resultados...</div>';
                fetch(apiUrl, { headers: Object.assign({}, headers), credentials: 'same-origin' })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(json => { renderPlans(json); updateHistory(q); })
                    .catch(()=>{ lista.innerHTML = prev; });
            }

            // debounce helper
            function debounce(fn, wait){ let t; return function(){ const args = arguments; clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); }; }

            const debouncedFetch = debounce(function(){ fetchAndUpdate(input.value.trim()); }, 300);

            // live input
            input.addEventListener('input', debouncedFetch);

            // enter key triggers immediate fetch
            input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); fetchAndUpdate(input.value.trim()); } });

            // clear button resets and fetches
            if(clear){ clear.addEventListener('click', function(){ input.value = ''; input.focus(); fetchAndUpdate(''); }); }

            // optional explicit search button
            if(btn){ btn.addEventListener('click', function(e){ e.preventDefault(); fetchAndUpdate(input.value.trim()); }); }
        })();
    </script>
        <script>
            // If we returned with a success flash, automatically open the inline create form
            (function(){
                try{
                    const container = document.getElementById('planCreateContainer');
                    const btn = document.getElementById('openCreatePlano');
                    const successAlert = document.querySelector('.alert.alert-success');
                    if(container && btn && successAlert){
                        container.style.display = 'block';
                        btn.textContent = 'Fechar formulário';
                        try{ if(typeof window.loadTemplates === 'function') window.loadTemplates(); }catch(_){}
                        setTimeout(() => {
                            const first = container.querySelector('input, select, textarea');
                            if(first) try{ first.focus(); first.scrollIntoView({behavior:'smooth', block:'center'}); }catch(_){}
                        }, 60);
                    }
                }catch(_){/* ignore */}
            })();
        </script>
    <script>
        // Toggle inline create form visibility when clicking 'Cadastrar Plano'
        (function(){
            const btn = document.getElementById('openCreatePlano');
            const container = document.getElementById('planCreateContainer');
            if(!btn || !container) return;
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const isHidden = window.getComputedStyle(container).display === 'none';
                if(isHidden){
                    container.style.display = 'block';
                    try{ if(typeof window.loadTemplates === 'function') window.loadTemplates(); }catch(_){}
                    setTimeout(() => {
                        const first = container.querySelector('input, select, textarea');
                        if(first) try{ first.focus(); first.scrollIntoView({behavior:'smooth', block:'center'}); }catch(_){}
                    }, 60);
                    btn.textContent = 'Fechar formulário';
                } else {
                    container.style.display = 'none';
                    btn.textContent = 'Cadastrar Plano';
                }
            });
        })();
    </script>
    <script>
        // Populate `#clientePlano` with clients from the server (returns JSON when Accept: application/json)
        document.addEventListener('DOMContentLoaded', function(){
            const clienteSelect = document.getElementById('clientePlano');
            if(!clienteSelect) return;
            fetch('/clientes', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(list => {
                    const items = Array.isArray(list) ? list : (list.data || []);
                    items.forEach(c => {
                        try {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = (c.nome || c.name) + (c.bi ? ' — ' + c.bi : '');
                            clienteSelect.appendChild(opt);
                        } catch(_) { /* ignore malformed entries */ }
                    });
                })
                .catch(() => {
                    // silently ignore - leaving the default option if request fails
                });
        });
    </script>
@endsection
