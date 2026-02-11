@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Clientes</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>

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
                .busca-clientes-box-alinhada {
                    width: 100%;
                    display: flex;
                    justify-content: flex-end;
                    margin-top: 32px;
                    margin-bottom: 18px;
                }
                .busca-clientes-form-alinhada {
                    display: grid;
                    grid-template-columns: minmax(0, 1fr) auto;
                    align-items: stretch;
                    column-gap: 16px;
                    width: 100%;
                    max-width: 700px;
                }
                .busca-input-wrapper {
                    position: relative;
                    min-width: 0;
                }
                .busca-icone {
                    position: absolute;
                    left: 0px;
                    top: 50%;
                    transform: translateY(-50%);
                    pointer-events: none;
                    width: 36px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .busca-input-wrapper input[type="text"] {
                    width: 100%;
                    padding: 8px 12px 8px 52px;
                    border-radius: 8px;
                    border: 1.5px solid #e09b00;
                    font-size: 1.08em;
                    background: #fffbe7;
                    transition: border 0.2s;
                    box-sizing: border-box;
                }
                .busca-input-wrapper input[type="text"]:focus {
                    outline: none;
                    border: 1.5px solid #b87d00;
                    background: #fffde9;
                }
                .btn.btn-primary {
                    background: #e09b00;
                    color: #fff;
                    border: none;
                    border-radius: 8px;
                    padding: 8px 32px;
                    font-weight: 500;
                    font-size: 1.08em;
                    box-shadow: 0 2px 8px #0001;
                    transition: background 0.2s;
                    white-space: nowrap;
                }
                .btn.btn-primary:hover {
                    background: #b87d00;
                }
                @media (max-width: 768px) {
                    .busca-clientes-form-alinhada {
                        grid-template-columns: 1fr;
                        row-gap: 10px;
                        max-width: 100%;
                    }
                    .btn.btn-primary {
                        width: 100%;
                        text-align: center;
                    }
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
            <div class="ficha-cliente" style="margin-top:32px;">
                <h2>Ficha do Cliente: {{ $cliente->nome }}</h2>

                <div class="cliente-dados-moderna" style="background:#fffbe7;border-radius:10px;padding:18px 24px;margin-bottom:18px;max-width:900px;margin-left:auto;margin-right:auto;display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 18px;align-items:center">
                        <div><strong>BI/NIF:</strong><div style="margin-top:6px">{{ $cliente->bi ?? '-' }}</div></div>
                        <div><strong>Nome:</strong><div style="margin-top:6px">{{ $cliente->nome }}</div></div>
                        <div><strong>Email:</strong><div style="margin-top:6px">{{ $cliente->email ?? '-' }}</div></div>
                        <div><strong>Contacto (WhatsApp):</strong><div style="margin-top:6px">{{ $cliente->contato ?? '-' }}</div></div>
                    </div>
                </div>

                <form id="formEditarCliente" method="POST" class="form-editar-cliente-moderna">
                    @csrf
                    <div class="form-editar-grid">
                        <div class="form-editar-campo">
                            <label for="editBI"><strong>BI/NIF:</strong></label>
                            <input type="text" id="editBI" name="bi" value="" placeholder="{{ $cliente->bi ?? 'BI / NIF' }}" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editNome"><strong>Nome:</strong></label>
                            <input type="text" id="editNome" name="nome" value="" placeholder="{{ $cliente->nome }}" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editEmail"><strong>Email:</strong></label>
                            <input type="email" id="editEmail" name="email" value="" placeholder="{{ $cliente->email ?? 'Email' }}" required>
                        </div>
                        <div class="form-editar-campo">
                            <label for="editContato"><strong>Contacto (WhatsApp):</strong></label>
                            <input type="text" id="editContato" name="contato" value="" placeholder="{{ $cliente->contato ?? 'Contacto (WhatsApp)' }}" required>
                        </div>
                        <div class="form-editar-botoes">
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
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
                </style>
                <a href="{{ route('clientes') }}" class="btn btn-secondary">Voltar à Lista</a>
                <a href="#" id="btnMostrarEditar" class="btn btn-warning" style="margin-left:8px;">Editar Cliente</a>
                <button class="btn btn-danger btn-excluir-cliente" data-id="{{ $cliente->id }}" style="margin-left:8px;">Excluir Cliente</button>
                <h3 style="margin-top:24px;">Equipamentos Instalados</h3>
                <a href="{{ route('cliente_equipamento.create', $cliente->id) }}" class="btn btn-secondary">Vincular Equipamento do Estoque</a>
                <ul>
                    @forelse($cliente->equipamentos as $equipamento)
                        <li>
                            <strong>{{ $equipamento->nome }}</strong> - Morada: {{ $equipamento->morada }}
                            @if($equipamento->ponto_referencia)
                                (Ponto de referência: {{ $equipamento->ponto_referencia }})
                            @endif
                        </li>
                    @empty
                        <li>Nenhum equipamento cadastrado para este cliente.</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        if (btnMostrarEditar) {
            btnMostrarEditar.addEventListener('click', function(e) {
                e.preventDefault();
                const formEditarClienteEl = document.getElementById('formEditarCliente');
                if (formEditarClienteEl && (formEditarClienteEl.style.display === 'none' || formEditarClienteEl.style.display === '')) {
                    formEditarClienteEl.style.display = 'block';
                    if (clienteDados) clienteDados.style.display = 'none';
                    const editNomeEl = document.getElementById('editNome');
                    if (editNomeEl) editNomeEl.focus();
                    location.hash = 'formEditarCliente';
                } else if (formEditarClienteEl) {
                    formEditarClienteEl.style.display = 'none';
                    if (clienteDados) clienteDados.style.display = 'block';
                }
            });
        }
        // If page opened with hash, show the edit form automatically
        if (location.hash === '#formEditarCliente') {
            const formEditarClienteEl = document.getElementById('formEditarCliente');
            if (formEditarClienteEl) {
                formEditarClienteEl.style.display = 'block';
                if (clienteDados) clienteDados.style.display = 'none';
                const editNomeEl = document.getElementById('editNome');
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
                        document.getElementById('editBI').value = data.cliente.bi;
                        document.getElementById('editNome').value = data.cliente.nome;
                        document.getElementById('editEmail').value = data.cliente.email;
                        document.getElementById('editContato').value = data.cliente.contato;
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
