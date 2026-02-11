@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Planos</h1>
        <div style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
            <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
            <a href="{{ route('plan-templates.index') }}" id="manageTemplatesBtn" class="btn" style="background:#2a6fda;">Gerir Modelos</a>
            <button type="button" id="refreshTemplatesBtn" class="btn" style="background:#6c757d;">Atualizar Modelos</button>
        </div>
        <form id="formPlano" class="form-cadastro">
            <select id="templateSelector" class="select">
                <option value="">Usar modelo (opcional)</option>
            </select>
            
            <select id="clientePlano" class="select" required>
                <option value="">Selecione o cliente</option>
            </select>
            <input type="text" id="nomePlano" placeholder="Nome do plano" required>
            <input type="text" id="descricaoPlano" placeholder="Descrição" required>
            <input type="hidden" name="preco" id="precoPlano">
            <input type="text" id="precoPlanoDisplay" placeholder="Preço (Kz)" required>
            <input type="number" id="cicloPlano" placeholder="Ciclo de serviço (dias)" min="1" required>
            <input type="date" id="dataAtivacaoPlano" placeholder="Data de ativação" required>
            <select id="estadoPlano" required>
                <option value="">Estado do plano</option>
                <option value="Ativo">Ativo</option>
                <option value="Em aviso">Em aviso</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Cancelado">Cancelado</option>
            </select>
            <button type="submit">Cadastrar Plano</button>
        </form>
        <h2 style="margin-top:32px;">Lista de Planos</h2>
        <div class="busca-planos-form" style="margin:12px 0 4px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input
                type="text"
                id="buscaPlanos"
                placeholder="Pesquisar por plano ou cliente..."
                style="flex:1; min-width:220px; padding:8px 10px; border-radius:8px; border:1px solid #ccc;"
            >
            <button
                type="button"
                id="btnBuscarPlanos"
                style="padding:8px 16px; border-radius:8px; border:none; background:#f7b500; color:#fff; cursor:pointer; white-space:nowrap;"
            >
                Pesquisar
            </button>
        </div>
        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* simple modal styles */
        #templatesModal { position:fixed; inset:0; display:none; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1200; }
        #templatesModal .modal { background:#fff; width:90%; max-width:900px; border-radius:8px; padding:16px; box-shadow:0 6px 24px rgba(0,0,0,0.2); }
        #templatesModal table { width:100%; border-collapse:collapse; }
        #templatesModal th, #templatesModal td { padding:8px 6px; border-bottom:1px solid #eee; text-align:left; }
        #templatesModal .controls { display:flex; gap:8px; margin-bottom:8px; }
        #templatesModal .small-btn { padding:6px 10px; border-radius:6px; border:none; cursor:pointer; }
    </style>
    <script>
        (function(){
            const display = document.getElementById('precoPlanoDisplay');
            const hidden = document.getElementById('precoPlano');
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
                // load templates and hook selector
            (function(){
                const tplSelect = document.getElementById('templateSelector');
                const refreshBtn = document.getElementById('refreshTemplatesBtn');
                if(!tplSelect) return;

                function loadTemplates(){
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
                }

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

                if(refreshBtn) refreshBtn.addEventListener('click', loadTemplates);

                // initial load
                loadTemplates();
            })();
                        // Modal for managing templates
                        (function(){
                                // create modal markup
                                const modalHtml = `
                                <div id="templatesModal">
                                    <div class="modal">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                            <h3 style="margin:0">Modelos de Plano</h3>
                                            <div>
                                                <button id="closeTemplatesModal" class="small-btn" style="background:#eee">Fechar</button>
                                            </div>
                                        </div>
                                        <div class="controls">
                                            <button id="newTemplateBtn" class="small-btn" style="background:#2a6fda; color:#fff">Novo Modelo</button>
                                            <button id="reloadTemplatesBtn" class="small-btn" style="background:#6c757d; color:#fff">Recarregar</button>
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
                                        if(!list.length){ listContainer.innerHTML = '<div>Nenhum modelo cadastrado.</div>'; return; }
                                        let html = '<table><thead><tr><th>Nome</th><th>Preço</th><th>Ciclo</th><th>Estado</th><th></th></tr></thead><tbody>';
                                        list.forEach(t => {
                                                html += `<tr data-id="${t.id}"><td>${escapeHtml(t.name)}</td><td>${t.preco?('Kz '+Number(t.preco).toLocaleString('pt-AO',{minimumFractionDigits:2})):''}</td><td>${t.ciclo||''}</td><td>${t.estado||''}</td><td style="text-align:right;"><button class="editBtn small-btn" data-id="${t.id}" style="background:#ffc107">Editar</button> <button class="delBtn small-btn" data-id="${t.id}" style="background:#dc3545;color:#fff">Apagar</button></td></tr>`;
                                        });
                                        html += '</tbody></table>';
                                        listContainer.innerHTML = html;
                                        // attach handlers
                                        listContainer.querySelectorAll('.editBtn').forEach(b => b.addEventListener('click', e => showEditForm(e.target.getAttribute('data-id'))));
                                        listContainer.querySelectorAll('.delBtn').forEach(b => b.addEventListener('click', e => deleteTemplate(e.target.getAttribute('data-id'))));
                                }

                                function showCreateForm(){
                                        formContainer.style.display = 'block';
                                        formContainer.innerHTML = formHtml();
                                        bindForm();
                                }

                                function showEditForm(id){
                                        formContainer.style.display = 'block';
                                        formContainer.innerHTML = '<div>Carregando...</div>';
                                        fetch(`/plan-templates/${id}/json`).then(r => r.json()).then(t => {
                                                formContainer.innerHTML = formHtml(t);
                                                bindForm(id);
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
                                                    <button type="button" id="cancelTemplateForm" class="small-btn" style="background:#eee">Cancelar</button>
                                                    <button type="submit" class="small-btn" style="background:#28a745;color:#fff">Salvar</button>
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
                                                        .then(()=>{ loadList(); loadTemplates(); formContainer.style.display='none'; })
                                                        .catch(()=> alert('Erro ao salvar.'));
                                        });
                                }

                                function deleteTemplate(id){
                                        if(!confirm('Confirma apagar este modelo?')) return;
                                        fetch(`/plan-templates/${id}`, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override':'DELETE' } })
                                                .then(r => { if(r.ok) return r.text(); throw new Error('Erro'); })
                                                .then(()=>{ loadList(); loadTemplates(); })
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
                                    // backdrop click
                                    if(e.target && e.target.id === 'templatesModal') return closeModal();
                                    // close button
                                    if(e.target && e.target.id === 'closeTemplatesModal') return closeModal();
                                    // delegated handlers for buttons that may be re-rendered
                                    const newBtn = e.target.closest && e.target.closest('#newTemplateBtn');
                                    if(newBtn) return showCreateForm();
                                    const reloadBtn = e.target.closest && e.target.closest('#reloadTemplatesBtn');
                                    if(reloadBtn) return loadList();
                                });
                                document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal && modal.style.display === 'flex') closeModal(); });
                        })();
        })();
    </script>
@endsection
