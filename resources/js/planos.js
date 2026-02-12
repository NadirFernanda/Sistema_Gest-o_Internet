// JS extracted from planos.blade.php — runs in browser when bundled by Vite
(function(){
    // Price display handling
    (function(){
        const display = document.getElementById('precoPlanoDisplay');
        const hidden = document.getElementById('precoPlano');
        if (display) {
            function unformat(value){
                if(!value) return '';
                let v = value.replace(/[^0-9,\.]/g, '');
                v = v.replace(/,/g, '.');
                return v;
            }
            function formatNumber(num){
                return 'Kz ' + Number(num).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            display.addEventListener('input', function(){
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
                const raw = hidden.value;
                if(raw) this.value = raw.replace('.', ',');
                else this.value = '';
            });
            const form = document.getElementById('formPlano');
            if(form){
                form.addEventListener('submit', function(){
                    const raw = unformat(display.value);
                    const n = parseFloat(raw);
                    if(!isNaN(n)) hidden.value = n.toFixed(2);
                });
            }
        }
    })();

    // Templates loader and modal (depends on window.planosConfig)
    (function(){
        if(typeof window.planosConfig === 'undefined') window.planosConfig = {};
        const planTemplatesListUrl = window.planosConfig.planTemplatesList || '/plan-templates/list.json';
        const planTemplatesBase = window.planosConfig.planTemplatesBase || '/plan-templates';

        const tplSelect = document.getElementById('templateSelector');
        const refreshBtn = document.getElementById('refreshTemplatesBtn');
        if(!tplSelect) return;

        window.loadTemplates = function(){
            fetch(planTemplatesListUrl)
                .then(r => r.json())
                .then(list => {
                    if(window._choicesMap && window._choicesMap['templateSelector']){
                        const choices = list.map(t => ({ value: t.id, label: t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2}) : '') }));
                        window._choicesMap['templateSelector'].setChoices(choices, 'value', 'label', true);
                    } else {
                        tplSelect.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
                        list.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO',{minimumFractionDigits:2, maximumFractionDigits:2}) : '');
                            tplSelect.appendChild(opt);
                        });
                    }
                }).catch(()=>{});
        };

        tplSelect.addEventListener('change', function(){
            const id = this.value;
            if(!id) return;
            fetch(`${planTemplatesBase}/${id}/json`)
                .then(r => r.json())
                .then(t => {
                    if(t.name) document.getElementById('nomePlano').value = t.name;
                    if(t.description) document.getElementById('descricaoPlano').value = t.description;
                    if(t.preco){
                        document.getElementById('precoPlano').value = Number(t.preco).toFixed(2);
                        const dp = document.getElementById('precoPlanoDisplay');
                        if(dp) dp.value = 'Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2});
                    }
                    if(t.ciclo) document.getElementById('cicloPlano').value = t.ciclo;
                    if(t.estado) document.getElementById('estadoPlano').value = t.estado;
                }).catch(()=>{});
        });

        function callLoadTemplates(){ if(typeof window.loadTemplates === 'function'){ try{ window.loadTemplates(); }catch(_){} } }
        if(refreshBtn) refreshBtn.addEventListener('click', callLoadTemplates);
        callLoadTemplates();

        // Modal management
        (function(){
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
            const meta = document.querySelector('meta[name="csrf-token"]');
            const csrf = meta ? meta.getAttribute('content') : '';

            function fetchJson(url, opts){
                opts = opts || {};
                opts.headers = Object.assign({ 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }, opts.headers || {});
                return fetch(url, opts).then(r => r.json());
            }

            function loadList(){
                listContainer.innerHTML = '<em>Carregando...</em>';
                fetch(planTemplatesListUrl)
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
                listContainer.querySelectorAll('.editBtn').forEach(b => b.addEventListener('click', e => showEditForm(e.target.getAttribute('data-id'))));
                listContainer.querySelectorAll('.delBtn').forEach(b => b.addEventListener('click', e => deleteTemplate(e.target.getAttribute('data-id'))));
            }

            function showCreateForm(){
                formContainer.style.display = 'block';
                formContainer.innerHTML = formHtml();
                bindForm();
                setTimeout(() => {
                    const first = formContainer.querySelector('input[name="name"]');
                    try{ if(first){ first.focus({preventScroll:false}); first.scrollIntoView({behavior:'smooth', block:'center'}); } if(modal){ modal.querySelector('.modal').scrollTop = Math.max(0, formContainer.offsetTop - 40); } }catch(_){ }
                }, 120);
            }

            function showEditForm(id){
                formContainer.style.display = 'block';
                formContainer.innerHTML = '<div>Carregando...</div>';
                fetch(`${planTemplatesBase}/${id}/json`).then(r => r.json()).then(t => {
                    formContainer.innerHTML = formHtml(t);
                    bindForm(id);
                    setTimeout(() => { const first = formContainer.querySelector('input[name="name"]'); try{ if(first){ first.focus({preventScroll:false}); first.scrollIntoView({behavior:'smooth', block:'center'}); } if(modal){ modal.querySelector('.modal').scrollTop = Math.max(0, formContainer.offsetTop - 40); } }catch(_){ } }, 120);
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
                    const url = id?`${planTemplatesBase}/${id}`:`${planTemplatesBase}`;
                    const method = id?'PUT':'POST';
                    fetch(url, { method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override': method }, body: fd })
                        .then(r => { if(r.ok) return r.text(); throw new Error('Erro'); })
                        .then(()=>{ loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; formContainer.style.display='none'; })
                        .catch(()=> alert('Erro ao salvar.'));
                });
            }

            function deleteTemplate(id){
                if(!confirm('Confirma apagar este modelo?')) return;
                fetch(`${planTemplatesBase}/${id}`, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override':'DELETE' } })
                    .then(r => { if(r.ok) return r.text(); throw new Error('Erro'); })
                    .then(()=>{ loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; })
                    .catch(()=> alert('Erro ao apagar.'));
            }

            function escapeHtml(s){ if(!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
            function escapeAttr(s){ if(!s) return ''; return String(s).replace(/"/g,'&quot;'); }

            const manageBtn = document.getElementById('manageTemplatesBtn');
            if(manageBtn){ manageBtn.addEventListener('click', function(e){ e.preventDefault(); if(modal) modal.style.display = 'flex'; loadList(); }); }
            const refreshBtnEl = document.getElementById('refreshTemplatesBtn');
            if(refreshBtnEl) refreshBtnEl.addEventListener('click', loadTemplates);
            if(modal){
                const reloadBtn = modal.querySelector('#reloadTemplatesBtn'); if(reloadBtn) reloadBtn.addEventListener('click', loadList);
                const newBtn = modal.querySelector('#newTemplateBtn'); if(newBtn) newBtn.addEventListener('click', showCreateForm);
            }

            // backdrop / close handlers
            if(modal){
                modal.addEventListener('click', function(e){
                    if(e.target && e.target.id === 'templatesModal') return (modal.style.display = 'none');
                    if(e.target && e.target.id === 'closeTemplatesModal') return (modal.style.display = 'none');
                    const newBtn = e.target.closest && e.target.closest('#newTemplateBtn'); if(newBtn) return showCreateForm();
                    const reloadBtn = e.target.closest && e.target.closest('#reloadTemplatesBtn'); if(reloadBtn) return loadList();
                });
                document.addEventListener('keydown', function(e){ if(e.key === 'Escape' && modal && modal.style.display === 'flex') modal.style.display = 'none'; });
            }
        })();
    })();

    // AJAX search and rendering
    (function(){
        const btn = document.getElementById('btnBuscarPlanos');
        const input = document.getElementById('buscaPlanos');
        const clear = document.getElementById('clearSearch');
        const lista = document.getElementById('planosLista');
        if(!input || !lista) return;

        const headers = { 'X-Requested-With':'XMLHttpRequest' };
        function updateHistory(q){ try{ const url = new URL(window.location.href); if(q) url.searchParams.set('q', q); else url.searchParams.delete('q'); window.history.replaceState({}, '', url.toString()); }catch(_){ } }

        function renderPlans(plans){
            if(!Array.isArray(plans) || !plans.length){
                lista.innerHTML = `<div style="padding:24px;text-align:center;color:#666"><p style="margin:0 0 8px 0">Nenhum plano encontrado.</p><a href="${window.planosConfig.planosCreateRoute||'/planos/create'}" class="btn btn-cta">Cadastrar Primeiro Plano</a></div>`;
                return;
            }
            function esc(s){ if(s === null || s === undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
            let html = '<div class="plan-grid">';
            plans.forEach(p => {
                const cliente = p.cliente && (p.cliente.nome || p.cliente.name) ? (p.cliente.nome || p.cliente.name) : '-';
                const preco = p.preco ? ('Kz ' + Number(p.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2})) : '';
                const estadoClass = (p.estado && p.estado.toLowerCase && p.estado.toLowerCase().includes('ativo')) ? 'ativo' : 'inativo';
                html += `
                    <article class="plan-card" data-id="${p.id}">
                        <div class="plan-title">${esc(p.nome||p.name||'')}</div>
                        <div class="plan-meta">
                            <div class="plan-badge plan-price">${esc(preco)}</div>
                            <div class="plan-badge plan-cycle">${esc(p.ciclo||'')}</div>
                            <div style="margin-left:auto"><small class="status-badge ${estadoClass}">${esc(p.estado||'')}</small></div>
                        </div>
                        <div class="muted" style="color:#444">${esc(p.description || p.descricao || '')}</div>
                        <div class="plan-actions">
                            <a href="/planos/${p.id}" class="btn btn-sm">Ver</a>
                            <a href="/planos/${p.id}/edit" class="btn btn-sm">Editar</a>
                            <button class="btn btn-sm btn-remove" data-id="${p.id}">Apagar</button>
                        </div>
                    </article>`;
            });
            html += '</div>';
            lista.innerHTML = html;
            // attach delete handlers if needed later (delegation could be used)
            lista.querySelectorAll('.btn-remove').forEach(b => b.addEventListener('click', function(){
                const id = this.getAttribute('data-id');
                if(!id) return;
                if(!confirm('Confirma apagar este plano?')) return;
                // create a form and submit to ensure CSRF token is included for non-AJAX fallback
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                let token = tokenMeta ? tokenMeta.getAttribute('content') : '';
                if(!token){
                    const holder = document.getElementById('pageCsrfHolder');
                    const holderInput = holder ? holder.querySelector('input[name="_token"]') : null;
                    if(holderInput) token = holderInput.value;
                }
                if(!token){
                    const anyInput = document.querySelector('input[name="_token"]');
                    if(anyInput) token = anyInput.value;
                }
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/planos/${id}`;
                form.style.display = 'none';
                const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = '_token'; inp.value = token || ''; form.appendChild(inp);
                const method = document.createElement('input'); method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE'; form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }));
        }

        function fetchAndUpdate(q){
            const apiUrl = (window.planosConfig && window.planosConfig.planosApi ? window.planosConfig.planosApi : '/api/planos') + (q ? '?busca=' + encodeURIComponent(q) : '');
            const prev = lista.innerHTML;
            lista.innerHTML = '<div style="padding:12px 0">Carregando resultados...</div>';
            fetch(apiUrl, { headers: Object.assign({}, headers), credentials: 'same-origin' })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(json => { renderPlans(json); updateHistory(q); })
                .catch(()=>{ lista.innerHTML = prev; });
        }

        function debounce(fn, wait){ let t; return function(){ const args = arguments; clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); }; }
        const debouncedFetch = debounce(function(){ fetchAndUpdate(input.value.trim()); }, 300);
        input.addEventListener('input', debouncedFetch);
        input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); fetchAndUpdate(input.value.trim()); } });
        if(clear){ clear.addEventListener('click', function(){ input.value = ''; input.focus(); fetchAndUpdate(''); }); }
        if(btn){ btn.addEventListener('click', function(e){ e.preventDefault(); fetchAndUpdate(input.value.trim()); }); }
    })();

    // Auto-open create form when returning with success flash
    (function(){
        try{
            const container = document.getElementById('planCreateContainer');
            const btn = document.getElementById('openCreatePlano');
            const successAlert = document.querySelector('.alert.alert-success');
            if(container && btn && successAlert){
                container.style.display = 'block';
                btn.textContent = 'Fechar formulário';
                try{ if(typeof window.loadTemplates === 'function') window.loadTemplates(); }catch(_){ }
                setTimeout(() => {
                    const first = container.querySelector('input, select, textarea');
                    if(first) try{ first.focus(); first.scrollIntoView({behavior:'smooth', block:'center'}); }catch(_){ }
                }, 60);
            }
        }catch(_){ }
    })();

    // Toggle inline create form
    (function(){
        const btn = document.getElementById('openCreatePlano');
        const container = document.getElementById('planCreateContainer');
        if(!btn || !container) return;
        btn.addEventListener('click', function(e){
            e.preventDefault();
            const isHidden = window.getComputedStyle(container).display === 'none';
            if(isHidden){
                container.style.display = 'block';
                try{ if(typeof window.loadTemplates === 'function') window.loadTemplates(); }catch(_){ }
                setTimeout(() => { const first = container.querySelector('input, select, textarea'); if(first) try{ first.focus(); first.scrollIntoView({behavior:'smooth', block:'center'}); }catch(_){ } }, 60);
                btn.textContent = 'Fechar formulário';
            } else {
                container.style.display = 'none';
                btn.textContent = 'Cadastrar Plano';
            }
        });
    })();

    // Populate clients into #clientePlano
    (function(){
        document.addEventListener('DOMContentLoaded', function(){
            const clienteSelect = document.getElementById('clientePlano');
            if(!clienteSelect) return;
            const url = (window.planosConfig && window.planosConfig.clientesJson) ? window.planosConfig.clientesJson : '/clientes';
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(list => {
                    const items = Array.isArray(list) ? list : (list.data || []);
                    items.forEach(c => {
                        try {
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = (c.nome || c.name) + (c.bi ? ' — ' + c.bi : '');
                            clienteSelect.appendChild(opt);
                        } catch(_) { }
                    });
                })
                .catch(() => { });
        });
    })();

})();
