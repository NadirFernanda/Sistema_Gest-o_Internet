// JS extracted from planos.blade.php — runs in browser when bundled by Vite

// Ensure we have a CSRF token available for any injected forms (fallbacks)
const __csrfMeta = (typeof document !== 'undefined') ? document.querySelector('meta[name="csrf-token"]') : null;
const csrfToken = __csrfMeta ? __csrfMeta.getAttribute('content') : (function(){
    try{
        const holder = document.getElementById('pageCsrfHolder');
        if(holder){ const inp = holder.querySelector('input[name="_token"]'); if(inp) return inp.value; }
    }catch(_){ }
    return '';
})();

            const form = document.getElementById('formPlano');
            if(form){
                form.addEventListener('submit', function(){
                    const raw = unformat(display.value);
                    const n = parseFloat(raw);
                    if(!isNaN(n)) hidden.value = n.toFixed(2);
                });
            }

    // Templates loader and modal (depends on window.planosConfig)
    (function(){
        if(typeof window.planosConfig === 'undefined') window.planosConfig = {};
        // correct default URL: route is '/plan-templates-list-json' (see routes/web.php)
        const planTemplatesListUrl = window.planosConfig.planTemplatesList || '/plan-templates-list-json';
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
                        <h3 style="margin:0">Planos</h3>
                    </div>
                    <style>
                      /* Modal-specific toolbar: three equal buttons inline */
                      #templatesModal .controls { display:flex !important; gap:12px !important; margin-bottom:12px !important; }
                      #templatesModal .controls .ctrl-btn { flex:1 !important; padding:10px 12px !important; height:44px !important; border-radius:8px !important; font-weight:700 !important; text-align:center !important; }
                      #templatesModal .controls .ctrl-btn.positive { background:#f7b500 !important; color:#fff !important; box-shadow:0 6px 18px rgba(247,181,0,0.18) !important; border:0 !important; }
                      #templatesModal .controls .ctrl-btn.ghost { background:transparent !important; border:1px solid #e6e6e6 !important; color:#222 !important; }
                      @media (max-width:640px){ #templatesModal .controls { flex-direction:column; } }
                    </style>
                    <div class="controls">
                        <button id="newTemplateBtn" class="ctrl-btn positive">Novo Plano</button>
                        <button id="reloadTemplatesBtn" class="ctrl-btn">Recarregar</button>
                        <button id="closeTemplatesModalBottom" class="ctrl-btn ghost">Fechar</button>
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

            function _parseJsonSafe(text){ try{ return JSON.parse(text); }catch(_){ return null; } }
            function _showApiFeedback(obj, successFallback){
                if(!obj) { if(successFallback) alert(successFallback); else showNoPermModal('Resposta inesperada.'); return; }
                if(typeof obj === 'object'){
                    if(obj.success === true || obj.success === 'true'){
                        alert(obj.message || successFallback || 'Operação realizada com sucesso.');
                        return;
                    }
                    if(obj.success === false || obj.error || obj.message){
                        alert(obj.message || obj.error || JSON.stringify(obj));
                        return;
                    }
                    // fallback: show message if present
                    if(obj.message) { alert(obj.message); return; }
                    alert(JSON.stringify(obj));
                    return;
                }
                if(typeof obj === 'string') { alert(obj); return; }
                showNoPermModal(successFallback || 'Erro desconhecido');
            }

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
                                        html += `<tr data-id="${t.id}">` +
                                                        `<td>${escapeHtml(t.name)}</td>` +
                                                        `<td>${t.preco?('Kz '+Number(t.preco).toLocaleString('pt-AO',{minimumFractionDigits:2})):''}</td>` +
                                                        `<td>${t.ciclo||''}</td>` +
                                                        `<td>${t.estado||''}</td>` +
                                                        `<td style="text-align:right">` +
                                                            `<div class="template-actions">` +
                                                                `<button class="editBtn btn-icon btn-warning" data-id="${t.id}" title="Editar" aria-label="Editar Plano">` +
                                                                    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>` +
                                                                `</button>` +
                                                                `<button class="delBtn btn-icon btn-danger" data-id="${t.id}" title="Apagar" aria-label="Apagar Plano">` +
                                                                    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>` +
                                                                `</button>` +
                                                            `</div>` +
                                                        `</td>` +
                                                    `</tr>`;
                                });
                html += '</tbody></table></div>';
                listContainer.innerHTML = html;
                listContainer.querySelectorAll('.editBtn').forEach(b => b.addEventListener('click', function(e){
                    const id = (e.currentTarget && e.currentTarget.getAttribute) ? e.currentTarget.getAttribute('data-id') : (e.target && e.target.getAttribute ? e.target.getAttribute('data-id') : null);
                    if(id) showEditForm(id);
                }));
                listContainer.querySelectorAll('.delBtn').forEach(b => b.addEventListener('click', function(e){
                    const id = (e.currentTarget && e.currentTarget.getAttribute) ? e.currentTarget.getAttribute('data-id') : (e.target && e.target.getAttribute ? e.target.getAttribute('data-id') : null);
                    if(id) deleteTemplate(id);
                }));
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

            function showNoPermModal(msg){
                try{
                    const modal = document.getElementById('noPermModal');
                    if(!modal) { alert(msg || 'Você não tem permissão.'); return; }
                    const body = document.getElementById('noPermBody');
                    if(body && msg) body.textContent = msg;
                    const mailLink = document.getElementById('noPermMailLink');
                    if(mailLink && window.planosConfig && window.planosConfig.adminContactEmail){ mailLink.href = 'mailto:' + window.planosConfig.adminContactEmail; mailLink.textContent = window.planosConfig.adminContactEmail; }
                    modal.style.display = 'flex';
                    const closeBtn = document.getElementById('noPermClose');
                    const okBtn = document.getElementById('noPermOk');
                    function hide(){ modal.style.display = 'none'; }
                    if(closeBtn) closeBtn.onclick = hide;
                    if(okBtn) okBtn.onclick = hide;
                    modal.addEventListener('click', function(ev){ if(ev.target === modal) hide(); });
                }catch(_){ try{ alert(msg || 'Você não tem permissão para executar esta ação. Contacte o administrador.'); }catch(_){} }
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
                        .then(r => r.text().then(txt => ({ ok: r.ok, status: r.status, text: txt, ct: (r.headers.get ? r.headers.get('content-type') : '') })))
                        .then(res => {
                            if (res.ok) {
                                const ct = (res.ct || '').toLowerCase();
                                const obj = (ct.indexOf('application/json') !== -1) ? _parseJsonSafe(res.text) : null;
                                _showApiFeedback(obj, 'Modelo salvo com sucesso.');
                                loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; formContainer.style.display='none';
                                return;
                            }
                            const ct = (res.ct || '').toLowerCase();
                            if (ct.indexOf('application/json') !== -1) {
                                const obj = _parseJsonSafe(res.text);
                                if(obj) _showApiFeedback(obj, 'Erro ao salvar. Contacte o administrador.');
                                else showNoPermModal('Erro ao salvar. Contacte o administrador.');
                            } else {
                                showNoPermModal('Erro ao salvar. Contacte o administrador.');
                            }
                        })
                        .catch((err)=> {
                            try{ console.error(err); }catch(_){ }
                        });
                });
            }

            function deleteTemplate(id){
                if(!confirm('Confirma apagar este modelo?')) return;
                fetch(`${planTemplatesBase}/${id}`, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override':'DELETE' } })
                    .then(res => r.text().then(txt => ({ ok: r.ok, text: txt, ct: (r.headers.get ? r.headers.get('content-type') : '') })))
                    .then(res => {
                        if (res.ok) {
                            const obj = (res.ct || '').toLowerCase().indexOf('application/json') !== -1 ? _parseJsonSafe(res.text) : null;
                            _showApiFeedback(obj, 'Modelo apagado com sucesso.');
                            loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; 
                            return;
                        }
                        const ct = (res.ct || '').toLowerCase();
                        if (ct.indexOf('application/json') !== -1) {
                            const obj = _parseJsonSafe(res.text);
                            if(obj) _showApiFeedback(obj, 'Erro ao apagar. Contacte o administrador.');
                            else showNoPermModal('Erro ao apagar. Contacte o administrador.');
                        } else { showNoPermModal('Erro ao apagar. Contacte o administrador.'); }
                    })
                    .catch((err)=> { try{ console.error(err); }catch(_){} });
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
                        .then(res => {
                            if (res.ok) return res.text();
                            return res.text().then(t => { throw new Error(t || 'Erro'); });
                        })
                        .then((txt) =>{
                            const obj = _parseJsonSafe(txt);
                            _showApiFeedback(obj, 'Modelo salvo com sucesso.');
                            loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; formContainer.style.display='none';
                        })
                        .catch((err)=> {
                            try{
                                console.error(err);
                                const msg = (err && err.message) ? String(err.message) : '';
                                if(msg.trim().startsWith('<') || msg.trim().toLowerCase().indexOf('<!doctype') === 0){
                                    showNoPermModal('Erro ao salvar. Contacte o administrador.');
                                } else {
                                    const parsed = _parseJsonSafe(msg);
                                    if(parsed) _showApiFeedback(parsed, 'Erro ao salvar.');
                                    else alert('Erro ao salvar: ' + (msg || 'Erro desconhecido'));
                                }
                            }catch(_){ }
                        });
                });
            }

            function deleteTemplate(id){
                if(!confirm('Confirma apagar este modelo?')) return;
                fetch(`${planTemplatesBase}/${id}`, { method:'POST', headers:{ 'X-CSRF-TOKEN': csrf, 'X-HTTP-Method-Override':'DELETE' } })
                    .then(r => {
                        if (r.ok) return r.text();
                        return r.text().then(t => { throw new Error(t || 'Erro'); });
                    })
                    .then((txt)=>{
                        const obj = _parseJsonSafe(txt);
                        _showApiFeedback(obj, 'Modelo apagado com sucesso.');
                        loadList(); if(typeof window.loadTemplates === 'function') try{ window.loadTemplates(); }catch(_){}; 
                    })
                    .catch((err)=> { try{ console.error(err); const msg = (err && err.message) ? String(err.message) : ''; if(msg.trim().startsWith('<') || msg.trim().toLowerCase().indexOf('<!doctype') === 0){ showNoPermModal('Erro ao apagar. Contacte o administrador.'); } else { const parsed = _parseJsonSafe(msg); if(parsed) _showApiFeedback(parsed, 'Erro ao apagar.'); else alert('Erro ao apagar: ' + (msg || 'Erro desconhecido')); } }catch(_){} });
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
                const closeBottom = modal.querySelector('#closeTemplatesModalBottom'); if(closeBottom) closeBottom.addEventListener('click', function(){ modal.style.display = 'none'; });
            }

            // backdrop / close handlers
            if(modal){
                modal.addEventListener('click', function(e){
                        if(e.target && e.target.id === 'templatesModal') return (modal.style.display = 'none');
                        if(e.target && (e.target.id === 'closeTemplatesModal' || e.target.id === 'closeTemplatesModalBottom')) return (modal.style.display = 'none');
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
                                <span class="plan-price">${esc(preco)}</span>
                                <span class="plan-cycle">${esc((p.ciclo !== undefined && p.ciclo !== null && p.ciclo !== '') ? p.ciclo : (p.template && (p.template.ciclo !== undefined && p.template.ciclo !== null && p.template.ciclo !== '') ? p.template.ciclo : ''))}</span>
                            </div>
                            <div class="muted" style="color:#444">${esc(p.description || p.descricao || '')}</div>
                            ${ (p.template_active_clients_count !== undefined && p.template_active_clients_count !== null) ? (`<div style="margin-top:8px;color:#666;font-weight:600;">${esc(p.template_active_clients_count)} ${p.template_active_clients_count === 1 ? 'Cliente cadastrado' : 'Clientes cadastrados'}</div>`) : '' }
                            <div class="plan-actions">
                                     <a href="${p.web_show || ('/planos/' + p.id)}" class="btn-icon btn-ghost" title="Ver" aria-label="Ver Plano">
                                         <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                     </a>
                                           <a href="${p.web_edit || ('/planos/' + p.id + '/edit')}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                               </a>
                               <form action="${p.web_delete || ('/planos/' + p.id)}" method="POST" style="display:inline-block; margin-left:6px;">
                                   <input type="hidden" name="_token" value="${csrfToken}">
                                   <input type="hidden" name="_method" value="DELETE">
                                   <button type="submit" class="btn-icon btn-danger" title="Apagar" aria-label="Apagar" onclick="return confirm('Apagar plano?')">
                                       <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                   </button>
                               </form>
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

        // Expose a debug helper to manually refresh planos from console and
        // perform an initial load so the list shows on page load.
        try{
            window.__refreshPlanos = function(){ try{ fetchAndUpdate(''); }catch(_){ } };
            // Initial fetch to populate the list when page loads
            fetchAndUpdate('');
        }catch(_){ }
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
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
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

// (end of planos.js)
