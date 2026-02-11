@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Clientes</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>

        <style>
            @media print {
                .no-print { display: none !important; }
            }
        </style>

        {{-- Se estiver na listagem de clientes --}}
        @if(isset($clientes))
            <form id="formCliente" class="form-cadastro" method="POST" action="{{ url('/clientes') }}">
                @csrf
                <input type="text" id="nomeCliente" name="nome" placeholder="Nome completo" required>
                <input type="text" id="biCliente" name="bi" placeholder="BI / NIF" required>
                <input type="email" id="emailCliente" name="email" placeholder="Email" required>
                <input type="text" id="contatoCliente" name="contato" placeholder="Contacto (WhatsApp)" required>
                <button type="submit">Cadastrar Cliente</button>
            </form>
            <div class="busca-clientes-box-alinhada">
                <form method="GET" action="{{ url('/clientes') }}" id="formBuscaCliente" class="busca-clientes-form-alinhada">
                    <div class="busca-input-wrapper">
                        <span class="busca-icone">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" stroke="#e09b00" stroke-width="2"/><path d="M20 20L17 17" stroke="#e09b00" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Pesquisar cliente por nome, BI, email ou contato" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary">Pesquisar</button>
                </form>
            </div>
            <style>
                /* Search box: large rounded orange bar with icon and full-width button */
                .busca-clientes-box-alinhada {
                    width: 100%;
                    display: flex;
                    justify-content: center;
                    margin-top: 12px;
                }
                .busca-clientes-form-alinhada {
                    width: 100%;
                    max-width: 980px;
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 10px;
                    align-items: center;
                }
                .busca-input-wrapper { position: relative; }
                .busca-input-wrapper .busca-icone { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); pointer-events: none; }
                .busca-input-wrapper input {
                    width: 100%;
                    padding: 16px 18px 16px 52px;
                    border-radius: 999px;
                    border: none;
                    background: #e09b00;
                    color: #fff;
                    font-size: 1.05rem;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                }
                .busca-input-wrapper input::placeholder { color: rgba(255,255,255,0.9); }
                .btn.btn-primary {
                    background: #e09b00;
                    color: #fff;
                    border: none;
                    border-radius: 10px;
                    padding: 12px 18px;
                    font-weight: 700;
                    font-size: 1.05rem;
                    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
                }
                .btn.btn-primary:hover { background: #b87d00; }
                @media (max-width: 768px) {
                    .busca-clientes-form-alinhada { max-width: 100%; }
                    .btn.btn-primary { width: 100%; }
                }
            </style>
            <h2>Lista de Clientes</h2>
            <div class="clientes-lista" id="clientesLista">
                @if(count($clientes) > 0)
                    <div class="clientes-lista-moderna">
                        @foreach($clientes as $c)
                        <div class="cliente-item-moderna">
                            <div class="cliente-info-moderna">
                                <span class="cliente-nome">{{ $c->nome }}</span>
                                <span class="cliente-bi"><strong>BI/NIF:</strong> {{ $c->bi ?? '-' }}</span>
                                <span class="cliente-contato">({{ $c->contato }})</span>
                            </div>
                            <div class="cliente-botoes-moderna">
                                <a href="{{ route('clientes.show', $c->id) }}" class="btn btn-sm btn-info">Ver Ficha</a>
                                <a href="{{ route('clientes.ficha', $c->id) }}" class="btn btn-sm btn-secondary">Ficha (PDF)</a>
                                <a href="{{ route('clientes.show', $c->id) }}#formEditarCliente" class="btn btn-sm btn-warning">Editar</a>
                                <button class="btn btn-sm btn-danger btn-excluir-cliente" data-id="{{ $c->id }}">Excluir</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <style>
                        .clientes-lista-moderna {
                            display: flex;
                            flex-direction: column;
                            gap: 18px;
                            margin-top: 18px;
                        }
                        .cliente-item-moderna {
                            display: flex;
                            align-items: center;
                            justify-content: space-between;
                            background: #fffbe7;
                            border-radius: 8px;
                            padding: 16px 24px;
                            box-shadow: 0 2px 8px #0001;
                        }
                        .cliente-info-moderna {
                            display: flex;
                            gap: 18px;
                            font-size: 1.1em;
                            align-items: center;
                        }
                        .cliente-bi {
                            color: #e09b00;
                            font-weight: bold;
                        }
                        .cliente-nome {
                            font-weight: 500;
                        }
                        .cliente-botoes-moderna {
                            display: flex;
                            gap: 10px;
                        }
                        .btn.btn-sm {
                            min-width: 90px;
                            font-size: 1em;
                        }
                    </style>
                @else
                    <p>Nenhum cliente cadastrado ainda.</p>
                @endif
            </div>
        @endif

        {{-- Se estiver na ficha de um cliente específico --}}
        @if(isset($cliente))

            {{-- Toolbar com ações (fica acima do cartão da ficha, fora do cartão impresso) --}}
            <div class="ficha-toolbar no-print">
                <a href="{{ route('clientes.ficha.pdf', $cliente->id) }}" class="btn btn-sm btn-secondary">Download PDF</a>
                <form action="{{ route('clientes.ficha.send', $cliente->id) }}" method="post" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm btn-primary">Enviar por e-mail</button>
                </form>
            </div>

            {{-- ficha-toolbar styles moved to resources/css/app.css (Vite) --}}

            <div class="ficha-cliente" style="margin-top:12px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;max-width:900px;margin-left:auto;margin-right:auto;">
                    <h2 style="margin:0;">Ficha do Cliente: {{ $cliente->nome }}</h2>
                </div>

                <div class="cliente-dados-moderna" style="background:#fffbe7;border-radius:10px;padding:18px 24px;margin-bottom:18px;max-width:900px;margin-left:auto;margin-right:auto;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;align-items:center">
                        <div><strong>BI/NIF:</strong><div style="margin-top:6px">{{ $cliente->bi ?? '-' }}</div></div>
                        <div><strong>Nome:</strong><div style="margin-top:6px">{{ $cliente->nome }}</div></div>
                        <div><strong>Email:</strong><div style="margin-top:6px">{{ $cliente->email ?? '-' }}</div></div>
                        <div><strong>Contacto (WhatsApp):</strong><div style="margin-top:6px">{{ $cliente->contato ?? '-' }}</div></div>
                    </div>
                </div>

                <form id="formEditarCliente" method="POST" class="form-editar-cliente-moderna" style="display:none;">
                    @csrf
                    <div class="form-editar-grid">
                        <div class="form-editar-campo">
                            <label for="editBI"><strong>BI/NIF:</strong></label>
                            <input type="text" id="editBI" name="bi" value="" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editNome"><strong>Nome:</strong></label>
                            <input type="text" id="editNome" name="nome" value="" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editEmail"><strong>Email:</strong></label>
                            <input type="email" id="editEmail" name="email" value="" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editContato"><strong>Contacto (WhatsApp):</strong></label>
                            <input type="text" id="editContato" name="contato" value="" required>
                        </div>
                        <div class="form-editar-botoes">
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            <button type="button" id="btnCancelarEditar" class="btn btn-secondary" style="margin-left:12px;">Cancelar</button>
                            <span id="msgAtualizaCliente" style="margin-left:16px;font-weight:bold;"></span>
                        </div>
                    </div>
                </form>
                <style>
                .form-editar-cliente-moderna {
                    background: #fffbe7;
                    border-radius: 10px;
                    box-shadow: 0 2px 8px #0001;
                    padding: 24px 32px 18px 32px;
                    margin-bottom: 24px;
                    max-width: 900px;
                    margin-left: auto;
                    margin-right: auto;
                    transition: opacity 240ms ease, transform 240ms ease;
                    opacity: 1;
                    transform: translateY(0);
                }
                .form-editar-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr 1fr 1fr;
                    gap: 18px 24px;
                    align-items: end;
                }
                .form-editar-campo label {
                    display: block;
                    margin-bottom: 4px;
                    font-size: 1.08em;
                }
                .form-editar-campo input {
                    width: 100%;
                    padding: 7px 10px;
                    border-radius: 5px;
                    border: 1px solid #e0c36a;
                    font-size: 1.08em;
                }
                .form-editar-botoes {
                    grid-column: 1 / span 4;
                    display: flex;
                    align-items: center;
                    margin-top: 8px;
                }
                .form-editar-botoes .btn-primary {
                    background: #ffb800;
                    color: #fff;
                    font-size: 1.15em;
                    font-weight: bold;
                    border: none;
                    border-radius: 6px;
                    padding: 10px 38px;
                    transition: background 0.2s;
                }
                .form-editar-botoes .btn-primary:hover {
                    background: #e09b00;
                }
                .form-editar-cliente-moderna.closing {
                    opacity: 0;
                    transform: translateY(6px);
                }
                </style>
                <a href="{{ route('clientes') }}" class="btn btn-secondary">Voltar à Lista</a>
                <a href="#" id="btnMostrarEditar" class="btn btn-warning" style="margin-left:8px;">Editar Cliente</a>
                <button class="btn btn-danger btn-excluir-cliente" data-id="{{ $cliente->id }}" style="margin-left:8px;">Excluir Cliente</button>
                <h3 style="margin-top:24px;">Equipamentos Instalados</h3>
                <a href="{{ route('cliente_equipamento.create', $cliente->id) }}" class="btn btn-secondary">Vincular Equipamento do Estoque</a>
                @php
                    $hasEquip = (isset($cliente->equipamentos) && $cliente->equipamentos->count());
                    $hasVincs = (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count());
                @endphp
                <style>
                    /* Ficha: tabela de equipamentos */
                    .ficha-equip-table .table {
                        width: 100%;
                        border-collapse: separate;
                    }
                    .ficha-equip-table th, .ficha-equip-table td {
                        vertical-align: middle;
                        padding: 12px 10px;
                        border: 1px solid #eee;
                    }
                    .ficha-equip-table thead th {
                        background: #fff9e6;
                        color: #222;
                        font-weight: 600;
                        text-align: left;
                    }
                    .ficha-equip-table .table-responsive { overflow-x: auto; }
                    .ficha-equip-table .table .btn { min-width: 100px; margin-right:8px; }
                    @media (max-width: 768px) {
                        .ficha-equip-table th:nth-child(2), .ficha-equip-table td:nth-child(2) { display:none; }
                        .ficha-equip-table th:nth-child(3), .ficha-equip-table td:nth-child(3) { display:none; }
                        .ficha-equip-table .table .btn { display:block; margin:8px 0; }
                    }
                </style>

                @if($hasEquip || $hasVincs)
                    <div class="ficha-equip-table">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Modelo</th>
                                <th>Morada</th>
                                <th>Ponto de Referência</th>
                                <th>Quantidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($hasEquip)
                                @foreach($cliente->equipamentos as $equipamento)
                                    <tr>
                                        <td>{{ $equipamento->nome }}</td>
                                        <td>{{ $equipamento->modelo ?? '-' }}</td>
                                        <td>{{ $equipamento->morada ?? '-' }}</td>
                                        <td>{{ $equipamento->ponto_referencia ?? '-' }}</td>
                                        <td>1</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endif

                            @if($hasVincs)
                                @foreach($cliente->clienteEquipamentos as $v)
                                    @php $est = $v->equipamento; @endphp
                                    <tr>
                                        <td>{{ $est->nome ?? $est->modelo ?? 'Equipamento do estoque' }}</td>
                                        <td>{{ $est->modelo ?? '-' }}</td>
                                        <td>{{ $v->morada ?? '-' }}</td>
                                        <td>{{ $v->ponto_referencia ?? '-' }}</td>
                                        <td>{{ $v->quantidade ?? 1 }}</td>
                                        <td>
                                            <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $v->id]) }}" class="btn btn-sm btn-warning">Editar</a>
                                            <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $v->id]) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja remover este equipamento?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                @else
                    <p>Nenhum equipamento cadastrado para este cliente.</p>
                @endif
            </div>
        @endif
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // small helper to avoid injecting HTML from server data
        function escapeHtml(unsafe) {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
        const form = document.getElementById('formCliente');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const nome = document.getElementById('nomeCliente').value;
                const email = document.getElementById('emailCliente').value;
                const contato = document.getElementById('contatoCliente').value;
                const bi = document.getElementById('biCliente').value;
                const token = document.querySelector('input[name="_token"]').value;

                fetch("{{ url('/clientes') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nome, email, contato, bi })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else if (data.errors) {
                        let msg = 'Erro ao cadastrar cliente:\n';
                        for (const [campo, mensagens] of Object.entries(data.errors)) {
                            msg += `${campo}: ${Array.isArray(mensagens) ? mensagens.join(', ') : mensagens}\n`;
                        }
                        alert(msg);
                    } else {
                        alert('Erro desconhecido ao cadastrar cliente.');
                    }
                })
                .catch(() => alert('Erro ao cadastrar cliente.'));
            });
        }

        // Exclusão de cliente
        document.querySelectorAll('.btn-excluir-cliente').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (confirm('Tem certeza que deseja excluir este cliente? Esta ação não pode ser desfeita.')) {
                    const id = this.getAttribute('data-id');
                    fetch(`/clientes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "{{ url('/clientes') }}";
                        } else {
                            alert('Erro ao excluir cliente.');
                        }
                    })
                    .catch(() => alert('Erro ao excluir cliente.'));
                }
            });
        });
        // Edição completa do cliente
        @if(isset($cliente))
        const btnMostrarEditar = document.getElementById('btnMostrarEditar');
        const clienteDados = document.querySelector('.cliente-dados-moderna');
        function closeEditForm({removeHash = false} = {}) {
            const formEditarClienteEl = document.getElementById('formEditarCliente');
            const clienteDadosBlock = document.querySelector('.cliente-dados-moderna');
            if (!formEditarClienteEl) return;
            // apply closing class for fade
            formEditarClienteEl.classList.add('closing');
            setTimeout(() => {
                formEditarClienteEl.style.display = 'none';
                formEditarClienteEl.classList.remove('closing');
                // clear fields
                const editBIEl2 = document.getElementById('editBI');
                const editNomeEl2 = document.getElementById('editNome');
                const editEmailEl2 = document.getElementById('editEmail');
                const editContatoEl2 = document.getElementById('editContato');
                if (editBIEl2) editBIEl2.value = '';
                if (editNomeEl2) editNomeEl2.value = '';
                if (editEmailEl2) editEmailEl2.value = '';
                if (editContatoEl2) editContatoEl2.value = '';
                if (clienteDadosBlock) clienteDadosBlock.style.display = 'block';
                if (removeHash && history && history.replaceState) {
                    history.replaceState(null, null, window.location.pathname + window.location.search);
                }
            }, 240);
        }

        if (btnMostrarEditar) {
            btnMostrarEditar.addEventListener('click', function(e) {
                e.preventDefault();
                const formEditarClienteEl = document.getElementById('formEditarCliente');
                if (formEditarClienteEl && (formEditarClienteEl.style.display === 'none' || formEditarClienteEl.style.display === '')) {
                    // preenche os inputs com os valores atuais do cliente
                    const editBIEl = document.getElementById('editBI');
                    const editNomeEl = document.getElementById('editNome');
                    const editEmailEl = document.getElementById('editEmail');
                    const editContatoEl = document.getElementById('editContato');
                    if (editBIEl) editBIEl.value = {!! json_encode($cliente->bi ?? '') !!};
                    if (editNomeEl) editNomeEl.value = {!! json_encode($cliente->nome ?? '') !!};
                    if (editEmailEl) editEmailEl.value = {!! json_encode($cliente->email ?? '') !!};
                    if (editContatoEl) editContatoEl.value = {!! json_encode($cliente->contato ?? '') !!};
                    formEditarClienteEl.style.display = 'block';
                    if (clienteDados) clienteDados.style.display = 'none';
                    if (editNomeEl) editNomeEl.focus();
                    location.hash = 'formEditarCliente';
                } else if (formEditarClienteEl) {
                    closeEditForm({removeHash:false});
                }
            });
        }
        // Cancel button: fecha o form e limpa campos
                const btnCancelar = document.getElementById('btnCancelarEditar');
                if (btnCancelar) {
                    btnCancelar.addEventListener('click', function(e) {
                        e.preventDefault();
                        closeEditForm({removeHash:true});
                    });
                }
        }
        // If page opened with hash, show the edit form automatically
        if (location.hash === '#formEditarCliente') {
            const formEditarClienteEl = document.getElementById('formEditarCliente');
            if (formEditarClienteEl) {
                // preencher campos antes de mostrar
                const editBIEl = document.getElementById('editBI');
                const editNomeEl = document.getElementById('editNome');
                const editEmailEl = document.getElementById('editEmail');
                const editContatoEl = document.getElementById('editContato');
                if (editBIEl) editBIEl.value = {!! json_encode($cliente->bi ?? '') !!};
                if (editNomeEl) editNomeEl.value = {!! json_encode($cliente->nome ?? '') !!};
                if (editEmailEl) editEmailEl.value = {!! json_encode($cliente->email ?? '') !!};
                if (editContatoEl) editContatoEl.value = {!! json_encode($cliente->contato ?? '') !!};
                formEditarClienteEl.style.display = 'block';
                if (clienteDados) clienteDados.style.display = 'none';
                if (editNomeEl) editNomeEl.focus();
            }
        }
        const formEditarCliente = document.getElementById('formEditarCliente');
        if (formEditarCliente) {
            formEditarCliente.addEventListener('submit', function(e) {
                e.preventDefault();
                const bi = document.getElementById('editBI').value;
                const nome = document.getElementById('editNome').value;
                const email = document.getElementById('editEmail').value;
                const contato = document.getElementById('editContato').value;
                const token = document.querySelector('input[name="_token"]').value;
                const clienteId = {{ $cliente->id }};
                const msgSpan = document.getElementById('msgAtualizaCliente');
                msgSpan.textContent = '';
                fetch(`/clientes/${clienteId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ bi, nome, email, contato })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        msgSpan.textContent = 'Dados atualizados com sucesso!';
                        msgSpan.style.color = 'green';
                        // update ficha (read-only block) with new data
                        const clienteDadosBlock = document.querySelector('.cliente-dados-moderna');
                        if (clienteDadosBlock) {
                            clienteDadosBlock.innerHTML = `
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;align-items:center">
                                    <div><strong>BI/NIF:</strong><div style="margin-top:6px">${escapeHtml(data.cliente.bi || '-')}</div></div>
                                    <div><strong>Nome:</strong><div style="margin-top:6px">${escapeHtml(data.cliente.nome || '-')}</div></div>
                                    <div><strong>Email:</strong><div style="margin-top:6px">${escapeHtml(data.cliente.email || '-')}</div></div>
                                    <div><strong>Contacto (WhatsApp):</strong><div style="margin-top:6px">${escapeHtml(data.cliente.contato || '-')}</div></div>
                                </div>
                            `;
                        }
                        // hide edit form, clear fields and remove hash
                        const formEditar = document.getElementById('formEditarCliente');
                        if (formEditar) {
                            formEditar.style.display = 'none';
                            const fbi = document.getElementById('editBI');
                            const fnome = document.getElementById('editNome');
                            const femail = document.getElementById('editEmail');
                            const fcont = document.getElementById('editContato');
                            if (fbi) fbi.value = '';
                            if (fnome) fnome.value = '';
                            if (femail) femail.value = '';
                            if (fcont) fcont.value = '';
                        }
                        if (history && history.replaceState) {
                            history.replaceState(null, null, window.location.pathname + window.location.search);
                        }
                    } else if (data.errors) {
                        let msg = 'Erro ao atualizar: ';
                        for (const [campo, mensagens] of Object.entries(data.errors)) {
                            msg += `${campo}: ${Array.isArray(mensagens) ? mensagens.join(', ') : mensagens} `;
                        }
                        msgSpan.textContent = msg;
                        msgSpan.style.color = 'red';
                    } else {
                        msgSpan.textContent = 'Erro desconhecido ao atualizar.';
                        msgSpan.style.color = 'red';
                    }
                })
                .catch(() => {
                    msgSpan.textContent = 'Erro ao atualizar.';
                    msgSpan.style.color = 'red';
                });
            });
        }
        @endif
    });
</script>
@endpush
@endsection
