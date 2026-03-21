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
                let html = '<div class="templates-table-wrapper"><table><thead><tr><th>Nome</th><th>Preço</th><th> Clico</th><th>Tipo</th><th>Estado</th><th style="text-align:center; width:120px">Clientes</th><th style="width:170px"></th></tr></thead><tbody>';
                                list.forEach(t => {
                                        html += `<tr data-id="${t.id}">` +
                                                        `<td>${escapeHtml(t.name)}</td>` +
                                                        `<td>${t.preco?('Kz '+Number(t.preco).toLocaleString('pt-AO',{minimumFractionDigits:2})):''}</td>` +
                                                        `<td>${t.ciclo||''}</td>` +
                                                        `<td>${t.tipo||''}</td>` +
                                                        `<td>${t.estado||''}</td>` +
                                                        `<td style="text-align:center">${(t.template_active_clients_count !== undefined && t.template_active_clients_count !== null) ? (Number(t.template_active_clients_count) === 1 ? '1 Cliente' : (escapeHtml(String(t.template_active_clients_count)) + ' Clientes')) : ''}</td>` +
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
                data = data || {name:'', description:'', preco:'', ciclo:'', estado:'', tipo:''};
                return `
                    <form id="templateAjaxForm">
                        <div style="display:flex; gap:8px;">
                            <input name="name" placeholder="Nome" required value="${escapeAttr(data.name)}" style="flex:1;padding:8px;border:1px solid #ccc;border-radius:6px;" />
                            <input name="preco" placeholder="Preço" value="${escapeAttr(data.preco||'')}" style="width:140px;padding:8px;border:1px solid #ccc;border-radius:6px;" />
                        </div>
                        <div style="margin-top:8px;"><input name="ciclo" placeholder="Ciclo (dias)" value="${escapeAttr(data.ciclo||'')}" style="width:160px;padding:8px;border:1px solid #ccc;border-radius:6px;" /></div>
                        <div style="margin-top:8px;">
                            <select name="tipo" style="padding:8px;border:1px solid #ccc;border-radius:6px;width:100%;">
                                <option value="">Escolha o tipo</option>
                                <option value="familiar" ${data.tipo == 'familiar' ? 'selected' : ''}>Familiar</option>
                                <option value="institucional" ${data.tipo == 'institucional' ? 'selected' : ''}>Institucional</option>
                                <option value="empresarial" ${data.tipo == 'empresarial' ? 'selected' : ''}>Empresarial</option>
                                <option value="site" ${data.tipo == 'site' ? 'selected' : ''}>Site</option>
                            </select>
                        </div>
                        <div style="margin-top:8px;">
                            <select name="estado" style="padding:8px;border:1px solid #ccc;border-radius:6px;width:100%;">
                                <option value="">Escolha o estado</option>
                                <option value="Ativo" ${data.estado == 'Ativo' ? 'selected' : ''}>Ativo</option>
                                <option value="Em aviso" ${data.estado == 'Em aviso' ? 'selected' : ''}>Em aviso</option>
                                <option value="Suspenso" ${data.estado == 'Suspenso' ? 'selected' : ''}>Suspenso</option>
                                <option value="Cancelado" ${data.estado == 'Cancelado' ? 'selected' : ''}>Cancelado</option>
                                <option value="Site" ${data.estado == 'Site' ? 'selected' : ''}>Site</option>
                                <option value="Agente Autorizado" ${data.estado == 'Agente Autorizado' ? 'selected' : ''}>Agente Autorizado</option>
                            </select>
                        </div>
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

        const __apiTokenMeta = document.querySelector('meta[name="api-token"]');
        const __apiToken = __apiTokenMeta ? __apiTokenMeta.getAttribute('content') : '';
        const headers = { 'X-Requested-With':'XMLHttpRequest', 'X-API-TOKEN': __apiToken };
        function updateHistory(q){ try{ const url = new URL(window.location.href); if(q) url.searchParams.set('q', q); else url.searchParams.delete('q'); window.history.replaceState({}, '', url.toString()); }catch(_){ } }

        function renderPlans(plans){
            if(!Array.isArray(plans) || !plans.length){
                lista.innerHTML = `<div style="padding:24px;text-align:center;color:#666"><p style="margin:0 0 8px 0">Nenhum plano encontrado.</p><a href="${window.planosConfig.planosCreateRoute||'/planos/create'}" class="btn btn-cta">Cadastrar Primeiro Plano</a></div>`;
                return;
            }
            // Sorting is handled by applyClientFilters() before renderPlans is called
            function esc(s){ if(s === null || s === undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
            let html = '<div class="plan-grid">';
            plans.forEach(p => {
                const cliente = p.cliente && (p.cliente.nome || p.cliente.name) ? (p.cliente.nome || p.cliente.name) : '-';
                const preco = p.preco ? ('Kz ' + Number(p.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2})) : '';
                const estadoClass = (p.estado && p.estado.toLowerCase && p.estado.toLowerCase().includes('ativo')) ? 'ativo' : 'inativo';

                // days remaining (diasRestantes) provided by server; render a small badge
                const dias = (p.diasRestantes !== undefined && p.diasRestantes !== null) ? Number(p.diasRestantes) : null;
                let vencHtml = '';
                try {
                    if (dias !== null) {
                        if (dias > 1) vencHtml = `<div class="plan-remaining"><span class="badge">${esc(dias)} dias para vencer</span></div>`;
                        else if (dias === 1) vencHtml = `<div class="plan-remaining"><span class="badge">1 dia para vencer</span></div>`;
                        else if (dias === 0) vencHtml = `<div class="plan-remaining"><span class="badge">Vence hoje</span></div>`;
                        else vencHtml = `<div class="plan-remaining"><span class="badge badge-danger">Vencido ${esc(Math.abs(dias))} dia(s)</span></div>`;
                    }
                } catch (_){ vencHtml = ''; }

                // decide a classe de status para o card com base em dias restantes
                // NOTE: preferimos usar `diasRestantes` como fonte da verdade — alguns planos
                // não têm `estado` consistente na API, o que fazia alguns cards não receberem
                // a cor correta mesmo tendo X dias restantes.
                let statusClass = '';
                try {
                    if (dias !== null) {
                        // apply stripes so that plans with 10 or more days show green
                        // dias < 0 => expirado (cinza)
                        // 0-5 => vermelho
                        // 6-9 => amarelo
                        // >=10 => verde
                        if (dias < 0) {
                            statusClass = 'status-expired';
                        } else if (dias <= 5) {
                            statusClass = 'status-red';
                        } else if (dias <= 9) {
                            statusClass = 'status-yellow';
                        } else {
                            statusClass = 'status-green';
                        }
                    }
                } catch (_) { statusClass = ''; }

                html += `
                        <article class="plan-card ${statusClass}" data-id="${p.id}">
                            <div class="plan-title">${esc(p.nome||p.name||'')}</div>
                            <div class="plan-client" style="font-size:0.95rem;color:#333;margin-top:6px;font-weight:600">${esc(cliente)}</div>
                            <div class="plan-meta">
                                <span class="plan-price">${esc(preco)}</span>
                                <span class="plan-cycle">${esc((p.ciclo !== undefined && p.ciclo !== null && p.ciclo !== '') ? p.ciclo : (p.template && (p.template.ciclo !== undefined && p.template.ciclo !== null && p.template.ciclo !== '') ? p.template.ciclo : ''))}</span>
                            </div>
                            ${vencHtml}
                            <div class="muted" style="color:#444">${esc(p.description || p.descricao || '')}</div>

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

        function fetchAndUpdate(){
            const base = (window.planosConfig && window.planosConfig.planosApi ? window.planosConfig.planosApi : '/api/planos');
            const params = [];
            if (_filters.q)          params.push('busca='       + encodeURIComponent(_filters.q));
            if (_filters.templateId) params.push('template_id=' + encodeURIComponent(_filters.templateId));
            if (_filters.estado)     params.push('estado='      + encodeURIComponent(_filters.estado));
            if (_filters.tipo)       params.push('tipo='        + encodeURIComponent(_filters.tipo));
            const apiUrl = base + (params.length ? '?' + params.join('&') : '');
            const prev = lista.innerHTML;
            lista.innerHTML = '<div style="padding:12px 0">Carregando resultados...</div>';
            fetch(apiUrl, { headers: Object.assign({}, headers), credentials: 'same-origin' })
                .then(function(r){ return r.ok ? r.json() : Promise.reject(); })
                .then(function(json){
                    const processed = applyClientFilters(json);
                    renderPlans(processed);
                    updateHistory(_filters.q);
                    updateFiltrosAtivos();
                })
                .catch(function(){ lista.innerHTML = prev; });
        }

        // ──── State object for all active filters ────
        const _filters = {
            q:          '',
            templateId: null,
            estado:     '',
            tipo:       '',
            vencimento: '',
            dias:       5,
            preco:      '',
            ordenar:    'cliente_asc'
        };

        // ──── Client-side filter + sort (applied after API response) ────
        function applyClientFilters(plans) {
            let result = Array.isArray(plans) ? plans : [];

            // Vencimento filter
            if (_filters.vencimento) {
                const maxDias = parseInt(_filters.dias) || 5;
                result = result.filter(function(p) {
                    const dr = (p.diasRestantes !== undefined && p.diasRestantes !== null) ? Number(p.diasRestantes) : null;
                    if (_filters.vencimento === 'vencido')  return dr !== null && dr < 0;
                    if (_filters.vencimento === 'hoje')     return dr !== null && dr === 0;
                    if (_filters.vencimento === 'avencer')  return dr !== null && dr >= 0 && dr <= maxDias;
                    if (_filters.vencimento === 'vigente')  return dr !== null && dr > 0;
                    return true;
                });
            }

            // Price range filter
            if (_filters.preco) {
                const parts = _filters.preco.split('-').map(Number);
                const pMin = parts[0] || 0;
                const pMax = parts[1] || 9999999;
                result = result.filter(function(p) {
                    const pr = p.preco ? Number(p.preco) : 0;
                    return pr >= pMin && pr <= pMax;
                });
            }

            // Sort
            try {
                const ord = _filters.ordenar || 'cliente_asc';
                result = result.slice();
                if (ord === 'nome_asc') {
                    result.sort(function(a, b){ return (a.nome||'').localeCompare(b.nome||'', 'pt', {sensitivity:'base'}); });
                } else if (ord === 'nome_desc') {
                    result.sort(function(a, b){ return (b.nome||'').localeCompare(a.nome||'', 'pt', {sensitivity:'base'}); });
                } else if (ord === 'preco_asc') {
                    result.sort(function(a, b){ return Number(a.preco||0) - Number(b.preco||0); });
                } else if (ord === 'preco_desc') {
                    result.sort(function(a, b){ return Number(b.preco||0) - Number(a.preco||0); });
                } else if (ord === 'venc_asc') {
                    result.sort(function(a, b){
                        const da = (a.diasRestantes !== null && a.diasRestantes !== undefined) ? Number(a.diasRestantes) : 99999;
                        const db = (b.diasRestantes !== null && b.diasRestantes !== undefined) ? Number(b.diasRestantes) : 99999;
                        return da - db;
                    });
                } else if (ord === 'venc_desc') {
                    result.sort(function(a, b){
                        const da = (a.diasRestantes !== null && a.diasRestantes !== undefined) ? Number(a.diasRestantes) : -99999;
                        const db = (b.diasRestantes !== null && b.diasRestantes !== undefined) ? Number(b.diasRestantes) : -99999;
                        return db - da;
                    });
                } else if (ord === 'data_asc') {
                    result.sort(function(a, b){ return (a.data_ativacao||'') < (b.data_ativacao||'') ? -1 : 1; });
                } else if (ord === 'data_desc') {
                    result.sort(function(a, b){ return (b.data_ativacao||'') < (a.data_ativacao||'') ? -1 : 1; });
                } else {
                    // Default: by client name
                    result.sort(function(a, b) {
                        const na = (a.cliente && (a.cliente.nome || a.cliente.name) ? String(a.cliente.nome || a.cliente.name) : '').normalize('NFD').toLowerCase();
                        const nb = (b.cliente && (b.cliente.nome || b.cliente.name) ? String(b.cliente.nome || b.cliente.name) : '').normalize('NFD').toLowerCase();
                        return na.localeCompare(nb, 'pt', {sensitivity:'base'});
                    });
                }
            } catch(_) { /* keep original order on sort failure */ }

            return result;
        }

        // ──── Show active filter summary bar ────
        function updateFiltrosAtivos() {
            const bar = document.getElementById('planosFiltrosAtivos');
            const txt = document.getElementById('planosFiltrosAtivosTexto');
            if (!bar || !txt) return;
            const tags = [];
            if (_filters.templateId) {
                const el = document.querySelector('.tpl-item[data-template-id="' + _filters.templateId + '"]');
                tags.push('Plano: ' + (el ? el.getAttribute('data-template-name') : _filters.templateId));
            }
            if (_filters.estado)     tags.push('Estado: ' + _filters.estado);
            if (_filters.tipo)       tags.push('Tipo: ' + _filters.tipo);
            if (_filters.vencimento) {
                const vLabels = {vencido:'Vencidos', hoje:'Vence hoje', avencer:'A vencer em ' + (_filters.dias||5) + ' dia(s)', vigente:'Vigentes'};
                tags.push('Vencimento: ' + (vLabels[_filters.vencimento] || _filters.vencimento));
            }
            if (_filters.preco) {
                const pLabels = {'0-5000':'até Kz 5.000', '5001-15000':'Kz 5.001–15.000', '15001-30000':'Kz 15.001–30.000', '30001-9999999':'acima de Kz 30.000'};
                tags.push('Preço: ' + (pLabels[_filters.preco] || _filters.preco));
            }
            if (_filters.ordenar && _filters.ordenar !== 'cliente_asc') {
                const oLabels = {nome_asc:'Nome A–Z', nome_desc:'Nome Z–A', preco_asc:'Preço ↑', preco_desc:'Preço ↓', venc_asc:'Venc. próximo', venc_desc:'Venc. longe', data_asc:'Activação ↑', data_desc:'Activação ↓'};
                tags.push('Ordenar: ' + (oLabels[_filters.ordenar] || _filters.ordenar));
            }
            if (tags.length) {
                txt.innerHTML = tags.map(function(t){ return '<span style="background:#f7b500;color:#fff;border-radius:4px;padding:2px 8px;font-size:0.87rem;">' + t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</span>'; }).join('');
                bar.style.display = 'flex';
            } else {
                bar.style.display = 'none';
            }
        }

        // ──── Template card filter helpers ────
        function setTemplateFilter(id, name){
            _filters.templateId = id;
            document.querySelectorAll('.tpl-item').forEach(function(el) {
                const isActive = el.getAttribute('data-template-id') == id;
                el.style.borderColor = isActive ? '#f7b500' : 'rgba(231,214,137,0.4)';
                el.style.boxShadow   = isActive ? '0 0 0 2px rgba(247,181,0,0.35)' : '';
                el.style.background  = isActive ? '#fffbe7' : '#fff';
            });
            const filterBar  = document.getElementById('activePlanFilter');
            const filterName = document.getElementById('activePlanFilterName');
            if (filterBar)  filterBar.style.display = 'flex';
            if (filterName) filterName.textContent  = name;
            fetchAndUpdate();
        }

        function clearTemplateFilter(){
            _filters.templateId = null;
            document.querySelectorAll('.tpl-item').forEach(function(el){
                el.style.borderColor = 'rgba(231,214,137,0.4)';
                el.style.boxShadow   = '';
                el.style.background  = '#fff';
            });
            const filterBar = document.getElementById('activePlanFilter');
            if (filterBar) filterBar.style.display = 'none';
            fetchAndUpdate();
        }

        // ──── Clear ALL filters ────
        function clearAllFilters() {
            _filters.q          = '';
            _filters.templateId = null;
            _filters.estado     = '';
            _filters.tipo       = '';
            _filters.vencimento = '';
            _filters.dias       = 5;
            _filters.preco      = '';
            _filters.ordenar    = 'cliente_asc';
            if (input) input.value = '';
            const fEstado     = document.getElementById('filtroEstado');
            const fTipo       = document.getElementById('filtroTipo');
            const fVenc       = document.getElementById('filtroVencimento');
            const fDias       = document.getElementById('filtroDias');
            const fPreco      = document.getElementById('filtroPreco');
            const fOrdenar    = document.getElementById('filtroOrdenar');
            const diasWrapper = document.getElementById('diasFiltroWrapper');
            if (fEstado)     fEstado.value     = '';
            if (fTipo)       fTipo.value       = '';
            if (fVenc)       fVenc.value       = '';
            if (fDias)       fDias.value       = 5;
            if (fPreco)      fPreco.value      = '';
            if (fOrdenar)    fOrdenar.value    = 'cliente_asc';
            if (diasWrapper) diasWrapper.style.display = 'none';
            // also reset template card highlights
            document.querySelectorAll('.tpl-item').forEach(function(el){
                el.style.borderColor = 'rgba(231,214,137,0.4)';
                el.style.boxShadow   = '';
                el.style.background  = '#fff';
            });
            const filterBar = document.getElementById('activePlanFilter');
            if (filterBar) filterBar.style.display = 'none';
            fetchAndUpdate();
        }

        // ──── Template summary card click handlers ────
        document.querySelectorAll('.tpl-item').forEach(function(el) {
            el.addEventListener('click', function(){
                const tid   = this.getAttribute('data-template-id');
                const tname = this.getAttribute('data-template-name');
                if (!tid) return;
                if (_filters.templateId == tid) {
                    clearTemplateFilter();
                } else {
                    setTemplateFilter(tid, tname);
                }
            });
        });

        const clearFilterBtn = document.getElementById('clearPlanFilter');
        if (clearFilterBtn) clearFilterBtn.addEventListener('click', clearTemplateFilter);

        // ──── New filter control event listeners ────
        const fEstadoEl  = document.getElementById('filtroEstado');
        const fTipoEl    = document.getElementById('filtroTipo');
        const fVencEl    = document.getElementById('filtroVencimento');
        const fDiasEl    = document.getElementById('filtroDias');
        const fPrecoEl   = document.getElementById('filtroPreco');
        const fOrdenarEl = document.getElementById('filtroOrdenar');
        const diasWrpEl  = document.getElementById('diasFiltroWrapper');
        const btnLimpar1 = document.getElementById('btnLimparFiltros');
        const btnLimpar2 = document.getElementById('btnLimparFiltros2');

        function showHideDiasWrapper() {
            if (diasWrpEl) diasWrpEl.style.display = (_filters.vencimento === 'avencer') ? 'inline-flex' : 'none';
        }

        if (fEstadoEl)  fEstadoEl.addEventListener('change',  function(){ _filters.estado = this.value; fetchAndUpdate(); });
        if (fTipoEl)    fTipoEl.addEventListener('change',    function(){ _filters.tipo   = this.value; fetchAndUpdate(); });
        if (fVencEl)    fVencEl.addEventListener('change',    function(){ _filters.vencimento = this.value; showHideDiasWrapper(); fetchAndUpdate(); });
        if (fPrecoEl)   fPrecoEl.addEventListener('change',   function(){ _filters.preco  = this.value; fetchAndUpdate(); });
        if (fOrdenarEl) fOrdenarEl.addEventListener('change', function(){ _filters.ordenar = this.value; fetchAndUpdate(); });
        if (btnLimpar1) btnLimpar1.addEventListener('click',  clearAllFilters);
        if (btnLimpar2) btnLimpar2.addEventListener('click',  clearAllFilters);

        function debounce(fn, wait){ let t; return function(){ var args = arguments; clearTimeout(t); t = setTimeout(function(){ fn.apply(this, args); }, wait); }; }

        if (fDiasEl) {
            const debouncedDias = debounce(function(){
                _filters.dias = parseInt(fDiasEl.value) || 5;
                if (_filters.vencimento === 'avencer') fetchAndUpdate();
            }, 350);
            fDiasEl.addEventListener('input', debouncedDias);
        }

        // ──── Search input listeners ────
        const debouncedSearch = debounce(function(){ _filters.q = input.value.trim(); fetchAndUpdate(); }, 300);
        input.addEventListener('input', debouncedSearch);
        input.addEventListener('keydown', function(e){ if(e.key === 'Enter'){ e.preventDefault(); _filters.q = input.value.trim(); fetchAndUpdate(); } });
        if(clear){ clear.addEventListener('click', function(){ input.value = ''; _filters.q = ''; input.focus(); fetchAndUpdate(); }); }
        if(btn){   btn.addEventListener('click',  function(e){ e.preventDefault(); _filters.q = input.value.trim(); fetchAndUpdate(); }); }

        // ──── Debug helper + initial load ────
        try{
            window.__refreshPlanos = function(){ try{ fetchAndUpdate(); }catch(_){ } };
            fetchAndUpdate();
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
