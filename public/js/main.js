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
            fetch(`/api/alertas?dias=${DIAS_ALERTA}`)
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
                    let html = '<table class="tabela-alertas"><thead><tr><th>Cliente</th><th>Plano</th><th>Contacto</th><th>Termina em</th><th>Data de Término</th></tr></thead><tbody>';
                    alertas.forEach(a => {
                        let destaque = a.diasRestantes <= 2 ? ' style="background:#ffeaea;color:#c0392b;"' : '';
                        html += `<tr data-plano-id="${a.id}"${destaque}><td>${a.nome}</td><td>${a.plano}</td><td>${a.contato}</td><td><b>${a.diasRestantes} dias</b></td><td>${a.dataTermino}</td></tr>`;
                    });
                    html += '</tbody></table>';
                    lista.innerHTML = html;
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
    // --- CLIENTES ---
    // (Removido código que sobrescrevia a lista de clientes do backend)
                fetch('http://127.0.0.1:8000/api/clientes')
                    .then(async res => {
                        let clientes = [];
                        try {
                            clientes = await res.json();
                        } catch (err) {
                            clientes = [];
                        }
                        const cliente = clientes[i];
                        if (!cliente || !cliente.id) {
                            alert('Não foi possível identificar o cliente.');
                            return;
                        }
                        fetch(`http://127.0.0.1:8000/api/clientes/${cliente.id}`, {
                            method: 'DELETE'
                        })
                        .then(res => {
                            if (res.ok) {
                                alert('Cliente removido com sucesso!');
                                renderClientes();
                            } else {
                                alert('Erro ao remover cliente.');
                            }
                        })
                        .catch(() => {
                            alert('Erro de conexão ao remover cliente.');
                        });
                    });
            }
            if (e.target.classList.contains('btn-editar')) {
                const i = e.target.getAttribute('data-i');
                fetch('http://127.0.0.1:8000/api/clientes')
                    .then(async res => {
                        let clientes = [];
                        try {
                            clientes = await res.json();
                        } catch (err) {
                            clientes = [];
                        }
                        const c = clientes[i];
                        if (!c) return;
                        const setValue = (id, value) => {
                            const el = document.getElementById(id);
                            if (el) el.value = value;
                        };
                        setValue('nomeCliente', c.nome);
                        setValue('emailCliente', c.email);
                        setValue('contatoCliente', c.contato);
                        setValue('dataAtivacaoCliente', c.data_ativacao || '');
                        setValue('estadoCliente', c.estado);
                        // Salva o id do cliente em edição
                        document.getElementById('formCliente').setAttribute('data-edit-id', c.id);
                    });
            }
        });
        // Inicializar
        renderClientes();
        // (removido: preencherSelectPlanos e listeners relacionados)
    }

    // --- PLANOS ---
    if (document.getElementById('formPlano')) {
        // Preencher select de clientes
        function preencherSelectClientesPlano() {
            const select = document.getElementById('clientePlano');
            if (!select) return;
            const valorAtual = select.value;
            fetch('http://127.0.0.1:8000/api/clientes')
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

        function renderPlanos() {
            const lista = document.getElementById('planosLista');
            fetch('http://127.0.0.1:8000/api/planos')
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
                    let html = '<table class="tabela-planos"><thead><tr><th>Cliente</th><th>Nome</th><th>Descrição</th><th>Preço (Kz)</th><th>Ciclo (dias)</th><th>Ativação</th><th>Vencimento</th><th>Status</th><th>Ações</th></tr></thead><tbody>';
                    planos.forEach((p, i) => {
                        let clienteNome = (p.cliente && p.cliente.nome) ? p.cliente.nome : '-';
                        let ativacao = p.data_ativacao ? new Date(p.data_ativacao) : null;
                        let vencimento = '-';
                        if (ativacao && p.ciclo) {
                            let v = new Date(ativacao);
                            v.setDate(v.getDate() + parseInt(p.ciclo));
                            vencimento = v.toLocaleDateString();
                        }
                        html += `<tr><td>${clienteNome}</td><td>${p.nome}</td><td>${p.descricao}</td><td>${p.preco}</td><td>${p.ciclo}</td><td>${p.data_ativacao || '-'}</td><td>${vencimento}</td><td>${p.estado || '-'}</td><td><button class=\"btn-editar-plano\" data-i=\"${i}\" data-id=\"${p.id}\">Editar</button> <button class=\"btn-remover-plano\" data-i=\"${i}\" data-id=\"${p.id}\">Remover</button></td></tr>`;
                    });
                    html += '</tbody></table>';
                    lista.innerHTML = html;
                })
                .catch(() => {
                    lista.innerHTML = '<p>Erro ao carregar planos.</p>';
                });
        }
        // Cadastro
        document.getElementById('formPlano').addEventListener('submit', function(e) {
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
            let url = 'http://127.0.0.1:8000/api/planos';
            let method = 'POST';
            const editId = this.getAttribute('data-edit-id');
            if (editId && editId !== '') {
                url += `/${editId}`;
                method = 'PUT';
            }
            fetch(url, {
                method,
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
        document.getElementById('planosLista').addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remover-plano') || e.target.classList.contains('btn-editar-plano')) {
                const i = e.target.getAttribute('data-i');
                const linhas = document.querySelectorAll('#planosLista tbody tr');
                const linha = linhas[i];
                if (!linha) return;
                const id = linha.querySelector('.btn-editar-plano, .btn-remover-plano').getAttribute('data-id') || null;
                if (!id) return;
                if (e.target.classList.contains('btn-remover-plano')) {
                    if (confirm('Tem certeza que deseja remover este plano?')) {
                        fetch(`http://127.0.0.1:8000/api/planos/${id}`, { method: 'DELETE' })
                            .then(res => {
                                if (res.ok) {
                                    alert('Plano removido com sucesso!');
                                    renderPlanos();
                                } else if (res.status === 404) {
                                    alert('Plano já foi removido ou não existe. Atualizando lista.');
                                    renderPlanos();
                                } else {
                                    alert('Erro ao remover plano.');
                                }
                            })
                            .catch(() => {
                                alert('Erro de conexão ao remover plano.');
                                renderPlanos();
                            });
                    }
                }
                if (e.target.classList.contains('btn-editar-plano')) {
                    fetch(`http://127.0.0.1:8000/api/planos/${id}`)
                        .then(async res => {
                            let p = null;
                            try { p = await res.json(); } catch (err) { p = null; }
                            if (!p || !p.id) {
                                alert('Não foi possível identificar o plano.');
                                return;
                            }
                            document.getElementById('nomePlano').value = p.nome;
                            document.getElementById('descricaoPlano').value = p.descricao;
                            document.getElementById('precoPlano').value = p.preco;
                            document.getElementById('cicloPlano').value = p.ciclo;
                            document.getElementById('dataAtivacaoPlano').value = p.data_ativacao || '';
                            document.getElementById('estadoPlano').value = p.estado || '';
                            document.getElementById('formPlano').setAttribute('data-edit-id', p.id);
                        });
                }
            }
        });
        // Inicializar
        preencherSelectClientesPlano();
        renderPlanos();
        window.addEventListener('focus', preencherSelectClientesPlano);
    }
});
