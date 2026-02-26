// Lógica simples para simular login e navegação

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Simulação de autenticação
            window.location.href = '/dashboard';
        });
    }
    // --- ALERTAS ---
    if (document.getElementById('alertasLista')) {
        function renderAlertas() {
            const lista = document.getElementById('alertasLista');
            const diasAlertaInput = document.getElementById('diasAlerta');
            const DIAS_ALERTA = diasAlertaInput ? parseInt(diasAlertaInput.value) : 5;
            fetch(`/api/alertas?dias=${DIAS_ALERTA}`, { credentials: 'include' })
                .then(async res => {
                    let alertas = [];
                    try {
                        alertas = await res.json();
                    } catch (err) {
                        alertas = [];
                    }
                    if (!alertas.length) {
                        lista.innerHTML = '<p>Nenhum alerta ativo no momento.</p>';
                        return;
                    }

                    // Use the same styled table wrapper and classes as Estoque de Equipamentos
                    let html = `<div class="estoque-tabela-moderna">` +
                        `<table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;min-width:640px;font-size:1.07em;">` +
                        `<thead>` +
                        `<tr>` +
                        `<th style="text-align:center;vertical-align:middle;"><input type="checkbox" id="selecionarTodosAlertas"></th>` +
                        `<th style="text-align:center;vertical-align:middle;">Cliente</th>` +
                        `<th style="text-align:center;vertical-align:middle;">Plano</th>` +
                        `<th style="text-align:center;vertical-align:middle;">Contacto</th>` +
                        `<th style="text-align:center;vertical-align:middle;">Termina em</th>` +
                        `<th style="text-align:center;vertical-align:middle;">Data de Término</th>` +
                        /* Removida a coluna de ações para alertas (não necessária) */
                        `</tr>` +
                        `</thead><tbody>`;

                    alertas.forEach(a => {
                        let destaque = a.diasRestantes <= 2 ? ' style="background:#ffeaea;color:#c0392b;"' : '';
                        // No image column for alerts; keep row minimal
                        html += `<tr data-plano-id="${a.id}"${destaque}>` +
                            `<td style="text-align:center;vertical-align:middle;"><input type="checkbox" class="chk-alerta" data-plano-id="${a.id}" data-nome="${a.nome}" data-contato="${a.contato}" data-email="${a.email}"></td>` +
                            `<td style="text-align:center;vertical-align:middle;"><span style="font-weight:500;">${a.nome}</span></td>` +
                            
                            `<td style="text-align:center;vertical-align:middle;">${a.plano}</td>` +
                            `<td style="text-align:center;vertical-align:middle;">${a.contato}</td>` +
                            `<td style="text-align:center;vertical-align:middle;"><b>${a.diasRestantes} dias</b></td>` +
                            `<td style="text-align:center;vertical-align:middle;">${a.dataTermino}</td>` +
                            `</tr>`;
                    });

                    html += '</tbody></table></div>';
                    lista.innerHTML = html;

                    const chkTodos = document.getElementById('selecionarTodosAlertas');
                    if (chkTodos) {
                        chkTodos.addEventListener('change', function() {
                            const itens = document.querySelectorAll('#alertasLista .chk-alerta');
                            itens.forEach(cb => { cb.checked = chkTodos.checked; });
                        });
                    }
                })
                .catch(() => {
                    lista.innerHTML = '<p>Erro ao carregar alertas.</p>';
                });
        }
        // Atualiza ao mudar o campo de dias
        const diasAlertaInput = document.getElementById('diasAlerta');
        if (diasAlertaInput) {
            diasAlertaInput.addEventListener('input', renderAlertas);
        }
        renderAlertas();
    }

    // Navegação dos botões do dashboard
    const btnClientes = document.querySelector('.dashboard-actions .btn:nth-child(1)');
    const btnPlanos = document.querySelector('.dashboard-actions .btn:nth-child(2)');
    const btnAlertas = document.querySelector('.dashboard-actions .btn:nth-child(3)');
    if (btnClientes) {
        btnClientes.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/clientes';
        });
    }
    if (btnPlanos) {
        btnPlanos.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/planos';
        });
    }
    if (btnAlertas) {
        btnAlertas.addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = '/alertas';
        });
    }


    // --- PLANOS (legacy) ---
    // If the Vite bundle is present it registers `window.__refreshPlanos`.
    // Skip legacy Planos logic when the modern bundle is running to avoid
    // duplicate rendering (cards vs table).
    if (typeof window.__refreshPlanos === 'undefined' && document.getElementById('formPlano')) {
        // Preencher select de clientes
        function preencherSelectClientesPlano() {
            const select = document.getElementById('clientePlano');
            if (!select) return;
            const valorAtual = select.value;
            fetch('/api/clientes', { credentials: 'include' })
                .then(async res => {
                    let clientes = [];
                    try {
                        clientes = await res.json();
                    } catch (err) {
                        clientes = [];
                    }
                    select.innerHTML = '<option value="">Selecione o cliente</option>';
                    clientes.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nome + (c.email ? ' (' + c.email + ')' : '');
                        select.appendChild(opt);
                    });
                    if (valorAtual) select.value = valorAtual;
                })
                .catch(() => {
                    select.innerHTML = '<option value="">Erro ao carregar clientes</option>';
                    select.disabled = true;
                });
        }

        function getFiltroPlanos() {
            const input = document.getElementById('buscaPlanos');
            return input ? input.value.trim() : '';
        }

        function renderPlanos() {
            const lista = document.getElementById('planosLista');
            if (!lista) return;
            const filtro = encodeURIComponent(getFiltroPlanos());
            const url = filtro ? `/api/planos?busca=${filtro}` : '/api/planos';
            fetch(url, { credentials: 'include' })
                .then(async res => {
                    let planos = [];
                    try {
                        planos = await res.json();
                    } catch (err) {
                        planos = [];
                    }
                    if (!planos.length) {
                        lista.innerHTML = '<p>Nenhum plano cadastrado ainda.</p>';
                        return;
                    }
                    let html = `<div class="tabela-cobrancas-moderna" style="background:#fff;border-radius:16px;box-shadow:0 2px 8px #0001;padding:18px 18px 8px 18px;margin-top:18px;overflow-x:auto;">
                        <table class="tabela-planos" style="width:100%;min-width:700px;font-size:1.01em;background:#fff;border-radius:8px;overflow:hidden;">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Preço (Kz)</th>
                                <th>Ciclo (dias)</th>
                                <th>Ativação</th>
                                <th>Vencimento</th>
                                <th>Status</th>
                                <th style="text-align:center;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>`;
                    planos.forEach((p, i) => {
                        let clienteNome = (p.cliente && p.cliente.nome) ? p.cliente.nome : '-';
                        let ativacao = p.data_ativacao ? new Date(p.data_ativacao) : null;
                        let vencimento = '-';
                        let diasLeft = null;
                        if (ativacao && p.ciclo) {
                            let v = new Date(ativacao);
                            v.setDate(v.getDate() + parseInt(p.ciclo));
                            vencimento = v.toLocaleDateString();
                            // calcular dias restantes (inteiro, arredondando para cima)
                            const now = new Date();
                            const diffMs = v.setHours(0,0,0,0) - new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
                            diasLeft = Math.ceil(diffMs / (1000 * 60 * 60 * 24));
                        }
                        // Status badge
                        let estado = (p.estado || '-');
                        let badge = '';
                        if (estado === 'Ativo') badge = '<span class="badge bg-success">Ativo</span>';
                        else if (estado === 'Em aviso') badge = '<span class="badge bg-warning text-dark">Em aviso</span>';
                        else if (estado === 'Suspenso') badge = '<span class="badge bg-danger">Suspenso</span>';
                        else if (estado === 'Cancelado') badge = '<span class="badge bg-danger">Cancelado</span>';
                        else badge = `<span class="badge bg-secondary">${estado}</span>`;
                        // action buttons: show placeholders that trigger a "contact admin" modal when user lacks permission
                        const editBtn = (p.can_edit ? `<button class="btn-editar-plano" data-i="${i}" data-id="${p.id}" style="margin-bottom:4px;">Editar</button>` : `<button class="btn-editar-plano btn-no-perm" data-action="edit" data-id="${p.id}">Editar</button>`);
                        const delBtn = (p.can_delete ? `<button class="btn-remover-plano" data-i="${i}" data-id="${p.id}">Remover</button>` : `<button class="btn-remover-plano btn-no-perm" data-action="delete" data-id="${p.id}">Remover</button>`);

                        // decide a classe de destaque com as faixas pedidas:
                        // dias < 0 => expirado (cinza)
                        // 0-5 => vermelho
                        // 6-10 => amarelo
                        // 11-30 => verde
                        // >30 => sem destaque
                        let rowClass = '';
                        if (diasLeft !== null) {
                            if (diasLeft < 0) {
                                rowClass = 'plan-status-row status-expired';
                            } else if (diasLeft <= 5) {
                                rowClass = 'plan-status-row status-red';
                            } else if (diasLeft <= 10) {
                                rowClass = 'plan-status-row status-yellow';
                            } else if (diasLeft <= 30) {
                                rowClass = 'plan-status-row status-green';
                            } else {
                                rowClass = '';
                            }
                        }

                        html += `<tr class="${rowClass}">
                            <td><span style="font-weight:500;">${clienteNome}</span></td>
                            <td>${p.nome}</td>
                            <td>${p.descricao}</td>
                            <td>Kz ${Number(p.preco).toLocaleString('pt-AO', {minimumFractionDigits:2})}</td>
                            <td>${p.ciclo}</td>
                            <td>${p.data_ativacao || '-'}</td>
                            <td>${vencimento}</td>
                            <td>${badge}</td>
                            <td style="text-align:center;">
                                ${ editBtn + delBtn }
                            </td>
                        </tr>`;
                    });
                    html += '</tbody></table></div>';
                    lista.innerHTML = html;
                })
                .catch(() => {
                    lista.innerHTML = '<p>Erro ao carregar planos.</p>';
                });
        }
        // Cadastro
        document.getElementById('formPlano').addEventListener('submit', function(e) {
            // If the form requests non-AJAX submission, allow normal POST
            try { if (this.dataset && this.dataset.noAjax) return; } catch(_) {}
            e.preventDefault();
            const btnSubmit = this.querySelector('button[type="submit"]');
            if (btnSubmit) btnSubmit.disabled = true;
            const cliente_id = document.getElementById('clientePlano').value;
            const nome = document.getElementById('nomePlano').value.trim();
            const descricao = document.getElementById('descricaoPlano').value.trim();
            const preco = document.getElementById('precoPlano').value;
            const ciclo = document.getElementById('cicloPlano').value;
            const data_ativacao = document.getElementById('dataAtivacaoPlano').value;
            const estado = document.getElementById('estadoPlano').value;
            // Validação dos campos com mensagens em português
            let camposFaltando = [];
            if (!cliente_id) camposFaltando.push('cliente');
            if (!nome) camposFaltando.push('nome do plano');
            if (!descricao) camposFaltando.push('descrição do plano');
            if (!preco) camposFaltando.push('preço do plano');
            if (!ciclo) camposFaltando.push('ciclo do plano');
            if (!data_ativacao) camposFaltando.push('data de ativação');
            if (!estado) camposFaltando.push('estado do plano');
            if (camposFaltando.length > 0) {
                alert('Por favor, preencha os seguintes campos: ' + camposFaltando.join(', ') + '.');
                return;
            }
            let url = '/api/planos';
            let method = 'POST';
            const editId = this.getAttribute('data-edit-id');
            if (editId && editId !== '') {
                url += `/${editId}`;
                method = 'PUT';
            }
            fetch(url, {
                method,
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ cliente_id, nome, descricao, preco, ciclo, data_ativacao, estado })
            })
                .then(async res => {
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (err) {
                        data = null;
                    }
                    console.log('Resposta do backend:', res.status, data);
                    if (res.ok && data && data.success) {
                        alert('Plano cadastrado com sucesso!');
                        this.reset();
                        renderPlanos();
                    } else if (data && data.error) {
                        alert('Erro: ' + data.error);
                    } else if (data && data.message) {
                        alert('Erro: ' + data.message);
                    } else {
                        alert('Erro ao cadastrar plano.');
                    }
                    if (btnSubmit) btnSubmit.disabled = false;
                })
                .catch((err) => {
                    console.error('Erro de conexão ou requisição:', err);
                    alert('Erro de conexão com o servidor.');
                });
        });
        // Remover e editar
        const planosListaEl = document.getElementById('planosLista');
        if (planosListaEl) {
            planosListaEl.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remover-plano') || e.target.classList.contains('btn-editar-plano')) {
                const i = e.target.getAttribute('data-i');
                const linhas = document.querySelectorAll('#planosLista tbody tr');
                const linha = linhas[i];
                if (!linha) return;
                const id = linha.querySelector('.btn-editar-plano, .btn-remover-plano').getAttribute('data-id') || null;
                if (!id) return;
                if (e.target.classList.contains('btn-remover-plano')) {
                    if (confirm('⚠️ Esta ação é irreversível!\n\nDeseja realmente remover este plano?')) {
                        const btn = e.target;
                        btn.disabled = true;
                        btn.textContent = 'Removendo...';
                        fetch(`/api/planos/${id}`, { method: 'DELETE', credentials: 'include' })
                            .then(res => {
                                if (res.ok) {
                                    // Remover a linha da tabela sem recarregar tudo
                                    linha.style.background = '#ffeaea';
                                    linha.style.transition = 'opacity 0.4s';
                                    linha.style.opacity = '0.4';
                                    setTimeout(() => {
                                        linha.remove();
                                        // Se não houver mais linhas, mostrar mensagem
                                        if (document.querySelectorAll('#planosLista tbody tr').length === 0) {
                                            document.getElementById('planosLista').innerHTML = '<p>Nenhum plano cadastrado ainda.</p>';
                                        }
                                    }, 400);
                                    alert('Plano removido com sucesso!');
                                } else if (res.status === 404) {
                                    alert('Plano já foi removido ou não existe. Atualizando lista.');
                                    renderPlanos();
                                } else {
                                    alert('Erro ao remover plano.');
                                    btn.disabled = false;
                                    btn.textContent = 'Remover';
                                }
                            })
                            .catch(() => {
                                alert('Erro de conexão ao remover plano.');
                                btn.disabled = false;
                                btn.textContent = 'Remover';
                            });
                    }
                }
                if (e.target.classList.contains('btn-editar-plano')) {
                    fetch(`/api/planos/${id}`, { credentials: 'include' })
                        .then(async res => {
                            let p = null;
                            try { p = await res.json(); } catch (err) { p = null; }
                            if (!p || !p.id) {
                                alert('Não foi possível identificar o plano.');
                                return;
                            }
                            document.getElementById('nomePlano').value = p.nome;
                            document.getElementById('descricaoPlano').value = p.descricao;
                            const hiddenPreco = document.getElementById('precoPlano');
                            const displayPreco = document.getElementById('precoPlanoDisplay');
                            if (hiddenPreco) hiddenPreco.value = p.preco;
                            if (displayPreco) {
                                const n = parseFloat(p.preco);
                                if (!isNaN(n)) displayPreco.value = 'Kz ' + Number(n).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                else displayPreco.value = '';
                            }
                            document.getElementById('cicloPlano').value = p.ciclo;
                            document.getElementById('dataAtivacaoPlano').value = p.data_ativacao || '';
                            document.getElementById('estadoPlano').value = p.estado || '';
                            document.getElementById('formPlano').setAttribute('data-edit-id', p.id);
                        });
                }
            }
            });
        }
        // Busca de planos
        const btnBuscarPlanos = document.getElementById('btnBuscarPlanos');
        if (btnBuscarPlanos) {
            btnBuscarPlanos.addEventListener('click', function(e) {
                e.preventDefault();
                renderPlanos();
            });
        }
        const inputBuscaPlanos = document.getElementById('buscaPlanos');
        if (inputBuscaPlanos) {
            inputBuscaPlanos.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    renderPlanos();
                }
            });
        }
        // Inicializar
        preencherSelectClientesPlano();
        renderPlanos();
        window.addEventListener('focus', preencherSelectClientesPlano);

        // Show "no permission" modal when user tries forbidden action
        function showNoPermModal() {
            const modal = document.getElementById('noPermModal');
            if (!modal) {
                alert('Você não tem permissão para executar esta ação. Contacte o administrador.');
                return;
            }
            // Update mail link if available
            try {
                const mailLink = document.getElementById('noPermMailLink');
                if (mailLink && window.planosConfig && window.planosConfig.adminContactEmail) {
                    mailLink.href = 'mailto:' + window.planosConfig.adminContactEmail;
                    mailLink.textContent = window.planosConfig.adminContactEmail;
                }
            } catch (_) {}
            modal.style.display = 'flex';
            const closeBtn = document.getElementById('noPermClose');
            const okBtn = document.getElementById('noPermOk');
            function hide() { modal.style.display = 'none'; }
            if (closeBtn) { closeBtn.onclick = hide; }
            if (okBtn) { okBtn.onclick = hide; }
            // click outside
            modal.addEventListener('click', function(ev) { if (ev.target === modal) hide(); });
        }

        document.body.addEventListener('click', function(e) {
            const t = e.target;
            if (t && t.classList && t.classList.contains('btn-no-perm')) {
                e.preventDefault();
                showNoPermModal();
            }
        });
    }
});
