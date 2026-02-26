@extends('layouts.app')

@section('content')
@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:18px; border-radius:8px; background:#eafaf1; color:#218c5b; padding:12px 18px; font-size:1.08rem;">
        <strong>Sucesso:</strong> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:18px; border-radius:8px; background:#faeaea; color:#c0392b; padding:12px 18px; font-size:1.08rem;">
        <strong>Erro:</strong> {{ session('error') }}
    </div>
@endif
    <div class="clientes-container">
        <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
        <header class="clientes-hero modern-hero">
            <div class="hero-inner">
                <div class="hero-left">
                    <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
                    <div class="hero-titles">
                        <h1>Gestão de Clientes</h1>
                        <p class="hero-sub">Lista, gestão e ações rápidas</p>
                    </div>
                </div>
                <div class="hero-right">
                    <!-- space reserved for header right (visual only) -->
                </div>
            </div>
        </header>

        {{-- Barra de ações e busca (padronizada com Planos: pesquisa à esquerda, CTAs à direita) --}}
        <div class="clientes-toolbar" style="max-width:1100px;margin:18px auto;display:flex;gap:10px;align-items:center;">
            <form method="GET" action="{{ url('/clientes') }}" id="formBuscaCliente" class="search-form-inline" style="flex:1;">
                <input type="search" name="busca" id="buscaClientes" placeholder="Pesquise por nome etc..." class="search-input" value="{{ request('busca') }}" style="height:40px; flex:1; min-width:320px; padding:8px 18px; border-radius:8px;" />
                <button type="submit" class="btn btn-search">Pesquisar</button>
            </form>
            <div style="display:flex;gap:8px;">
                @can('clientes.create')
                    @php
                        $user = auth()->user();
                    @endphp
                    @if(!$user || !$user->hasRole('colaborador'))
                        <a href="{{ url('/clientes/create') }}" class="btn btn-cta">Cadastrar</a>
                    @endif
                @endcan
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Painel</a>
            </div>
        </div>
        @if(isset($clientes))
            <div class="clientes-lista" id="clientesLista">
                @if(count($clientes) > 0)
                    <div class="clientes-lista-moderna">
                        @foreach($clientes as $c)
                        <div class="cliente-item-moderna">
                            <div class="cliente-info-moderna">
                                <span class="cliente-nome">{{ $c->nome }}</span>
                                <span class="cliente-bi">{{ $c->bi ?? '-' }}</span>
                                <span class="cliente-contato">({{ $c->contato }})</span>
                            </div>
                            <div class="cliente-botoes-moderna" style="white-space:nowrap;">
                                <a href="{{ route('clientes.show', $c->id) }}" class="btn-icon btn-warning" title="Ver Ficha" aria-label="Ver Ficha">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('clientes.ficha.pdf', $c->id) }}" class="btn-icon btn-warning" title="Ficha PDF" aria-label="Ficha PDF" style="margin-left:6px;" target="_blank" rel="noopener">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </a>
                                @can('clientes.edit')
                                <a href="{{ route('clientes.edit', $c->id) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar" style="margin-left:6px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                </a>
                                @endcan
                                @can('clientes.delete')
                                <form action="{{ route('clientes.destroy', $c->id) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-icon btn-danger" title="Excluir" aria-label="Excluir" onclick="return confirm('Excluir cliente?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        @endforeach
                    </div>
                    {{-- styles moved to public/css/clientes.css --}}
                @else
                    <p>Nenhum cliente cadastrado ainda.</p>
                @endif
            </div>
        @endif

        {{-- Se estiver na ficha de um cliente específico --}}
        @if(isset($cliente))

            {{-- Modernized ficha (card + actions) --}}
            <div class="ficha-cliente" style="margin-top:12px;">
                <div class="ficha-card" style="max-width:980px;margin:0 auto;padding:20px;background:linear-gradient(180deg,#fff9eb, #fffbe7);border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.06);">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                        <h2 style="margin:0;font-size:1.45rem;">Ficha do Cliente: {{ $cliente->nome }}</h2>
                        @php
                            $user = auth()->user();
                        @endphp
                        @if(isset($cliente) && $cliente->id && (!$user || (!$user->hasRole('colaborador') && !$user->hasRole('gerente'))))
                        {{-- Compensação controls moved to plano detail view to avoid duplication --}}
                        @endif
                    </div>

                    <div class="cliente-dados-moderna" style="background:transparent;border-radius:10px;padding:18px 8px 6px 8px;margin-top:16px;">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 18px;align-items:center">
                                <div style="text-align:center"><strong>BI/NIF:</strong><div style="margin-top:6px;font-size:1.05rem">{{ $cliente->bi ?? '-' }}</div></div>
                                <div style="text-align:center"><strong>Nome:</strong><div style="margin-top:6px;font-size:1.05rem">{{ $cliente->nome }}</div></div>
                            <div style="text-align:center"><strong>Email:</strong><div style="margin-top:6px;font-size:1.05rem">{{ $cliente->email ?? '-' }}</div></div>
                            <div style="text-align:center"><strong>Contacto (WhatsApp):</strong><div style="margin-top:6px;font-size:1.05rem">{{ $cliente->contato ?? '-' }}</div></div>
                        </div>
                    </div>
                    {{-- estilos movidos para resources/css/clientes.css (importados via resources/css/app.css) --}}
                    <!-- cliente-dados-moderna already contains the display block above; duplicate removed -->
                </div>

                    <form id="formEditarCliente" method="POST" action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : '#' }}" class="form-editar-cliente-moderna" style="display:none;"
                        data-id="{{ $cliente->id ?? '' }}"
                        data-bi="{{ $cliente->bi ?? '' }}"
                        data-nome="{{ $cliente->nome ?? '' }}"
                        data-email="{{ $cliente->email ?? '' }}"
                        data-contato="{{ $cliente->contato ?? '' }}">
                    @csrf
                    @method('PUT')
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
                    background: #f7b500;
                    color: #fff;
                    font-size: 1.15em;
                    font-weight: bold;
                    border: none;
                    border-radius: 6px;
                    padding: 10px 38px;
                    transition: background 0.2s;
                }
                .form-editar-botoes .btn-primary:hover {
                    background: #e0a800;
                }
                .form-editar-cliente-moderna.closing {
                    opacity: 0;
                    transform: translateY(6px);
                }
                </style>
                <h3 style="margin-top:24px;">Equipamentos Instalados</h3>
                <a href="{{ route('cliente_equipamento.create', $cliente->id) }}" class="btn btn-secondary">Vincular Equipamento do Estoque</a>
                @php
                    $hasEquip = (isset($cliente->equipamentos) && $cliente->equipamentos->count());
                    $hasVincs = (isset($cliente->clienteEquipamentos) && $cliente->clienteEquipamentos->count());
                @endphp
                @if($hasEquip || $hasVincs)
                    <div class="estoque-tabela-moderna" style="background:#fff; border-radius:16px; box-shadow:0 2px 8px #0001; padding:18px 18px 8px 18px; margin-top:18px; overflow-x:auto;">
                        <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate; background:#fff; border-radius:8px; overflow:hidden;">
                            <thead>
                                <tr>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Marca</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Descrição</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Modelo</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Forma de Ligação</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Nº Série</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Morada</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Ponto de Referência</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Quantidade</th>
                                    <th style="text-align:center;vertical-align:middle;background:#fffbe7;color:#f7b500;font-weight:bold;font-size:1.09em;border-bottom:2px solid #ffe6a0;padding:14px 12px;">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($hasEquip)
                                    @foreach($cliente->equipamentos as $equipamento)
                                        <tr>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->marca ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->descricao ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->modelo ?? '-' }}</td>
                                                <td style="text-align:center;vertical-align:middle;">-</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->numero_serie ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->morada ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $equipamento->ponto_referencia ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">1</td>
                                            <td></td>
                                        </tr>
                                    @endforeach
                                @endif

                                @if($hasVincs)
                                    @foreach($cliente->clienteEquipamentos as $v)
                                        @php $est = $v->equipamento; @endphp
                                        <tr>
                                            <td style="text-align:center;vertical-align:middle;">{{ $est->marca ?? $est->nome ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $est->descricao ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $est->modelo ?? '-' }}</td>
                                                <td style="text-align:center;vertical-align:middle;">{{ $v->forma_ligacao ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $est->numero_serie ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $v->morada ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $v->ponto_referencia ?? '-' }}</td>
                                            <td style="text-align:center;vertical-align:middle;">{{ $v->quantidade ?? 1 }}</td>
                                            <td style="white-space:nowrap;text-align:center;vertical-align:middle;">
                                                <a href="{{ route('cliente_equipamento.edit', [$cliente->id, $v->id]) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
                                                </a>
                                                <form action="{{ route('cliente_equipamento.destroy', [$cliente->id, $v->id]) }}" method="POST" style="display:inline-block; margin-left:6px;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-icon btn-danger" title="Eliminar" aria-label="Eliminar" onclick="return confirm('Tem certeza que deseja remover este equipamento?')">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>Nenhum equipamento cadastrado para este cliente.</p>
                @endif
    </div>

    {{-- Hidden CSRF holder for JS-driven forms/modal submissions -- ensure a fresh token is always present in the DOM --}}
    <form id="pageCsrfHolder" style="display:none;">
        @csrf
    </form>

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
        // (Formulário de cadastro de cliente agora abre em página separada)
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
                        // redireciona para a lista de clientes onde a mensagem de sucesso será exibida
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.href = "{{ url('/clientes') }}";
                        }
                        return;
                    }
                    if (data.errors) {
                        let msg = 'Erro ao cadastrar cliente:\n';
                        for (const [campo, mensagens] of Object.entries(data.errors)) {
                            msg += `${campo}: ${Array.isArray(mensagens) ? mensagens.join(', ') : mensagens}\n`;
                        }
                        alert(msg);
                        return;
                    }
                    if (data.message) {
                        alert(data.message);
                        return;
                    }
                    alert('Erro desconhecido ao cadastrar cliente.');
                })
                .catch(() => alert('Erro ao cadastrar cliente.'));
            });
        }

        // Exclusão de cliente (botões antigos) — abrir modal para confirmação com motivo
        document.querySelectorAll('.btn-excluir-cliente').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                if (!id) return;
                openDeleteModal(id);
            });
        });

        // Actions dropdown: toggle and delete handlers
        document.querySelectorAll('.actions-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                const id = this.getAttribute('aria-controls');
                const menu = document.getElementById(id);
                const expanded = this.getAttribute('aria-expanded') === 'true';
                // close any other open menus
                document.querySelectorAll('.actions-menu').forEach(m => { if (m !== menu) m.hidden = true; });
                document.querySelectorAll('.actions-toggle').forEach(t => { if (t !== this) t.setAttribute('aria-expanded','false'); });
                if (menu) {
                    menu.hidden = expanded; // toggle
                    this.setAttribute('aria-expanded', String(!expanded));
                }
                e.stopPropagation();
            });
        });

        // close menus on outside click
        document.addEventListener('click', function() {
            document.querySelectorAll('.actions-menu').forEach(m => m.hidden = true);
            document.querySelectorAll('.actions-toggle').forEach(t => t.setAttribute('aria-expanded','false'));
        });

        // Note: deletion from actions menu now uses modal-based flow below (openDeleteModal)
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
                    if (editBIEl) editBIEl.value = formEditarClienteEl.dataset.bi || '';
                    if (editNomeEl) editNomeEl.value = formEditarClienteEl.dataset.nome || '';
                    if (editEmailEl) editEmailEl.value = formEditarClienteEl.dataset.email || '';
                    if (editContatoEl) editContatoEl.value = formEditarClienteEl.dataset.contato || '';
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
        // Robust auto-open when arriving with #formEditarCliente (retries + hashchange)
        (function(){
            const tryOpenEditFromHash = (attemptsLeft = 6) => {
                if (location.hash !== '#formEditarCliente') return;
                const formEditarClienteEl = document.getElementById('formEditarCliente');
                if (!formEditarClienteEl) {
                    if (attemptsLeft > 0) {
                        setTimeout(() => tryOpenEditFromHash(attemptsLeft - 1), 120);
                    }
                    return;
                }
                // preencher campos antes de mostrar
                const editBIEl = document.getElementById('editBI');
                const editNomeEl = document.getElementById('editNome');
                const editEmailEl = document.getElementById('editEmail');
                const editContatoEl = document.getElementById('editContato');
                if (editBIEl) editBIEl.value = formEditarClienteEl.dataset.bi || '';
                if (editNomeEl) editNomeEl.value = formEditarClienteEl.dataset.nome || '';
                if (editEmailEl) editEmailEl.value = formEditarClienteEl.dataset.email || '';
                if (editContatoEl) editContatoEl.value = formEditarClienteEl.dataset.contato || '';
                formEditarClienteEl.style.display = 'block';
                try { if (typeof clienteDados !== 'undefined' && clienteDados) clienteDados.style.display = 'none'; } catch(e) {}
                if (editNomeEl) editNomeEl.focus();
                // ensure the element is visible to the user
                try { formEditarClienteEl.scrollIntoView({behavior:'smooth', block:'center'}); } catch(e) {}
            };

            // initial attempt on load
            tryOpenEditFromHash();
            // respond to hash changes
            window.addEventListener('hashchange', function(){ tryOpenEditFromHash(); });
        })();
        const formEditarCliente = document.getElementById('formEditarCliente');
        if (formEditarCliente) {
            formEditarCliente.addEventListener('submit', function(e) {
                e.preventDefault();
                const bi = document.getElementById('editBI').value;
                const nome = document.getElementById('editNome').value;
                const email = document.getElementById('editEmail').value;
                const contato = document.getElementById('editContato').value;
                const token = document.querySelector('input[name="_token"]').value;
                const clienteId = document.getElementById('formEditarCliente').dataset.id || null;
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
                                    <div><div style="margin-top:6px">${escapeHtml(data.cliente.bi || '-')}</div></div>
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

        /* --- Modal for delete confirmation (with reason) --- */
        // create modal HTML and append to body
        (function(){
            const modalHtml = `
            <div id="confirmDeleteModal" class="modal-overlay" aria-hidden="true">
              <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirmDeleteTitle">
                <h3 id="confirmDeleteTitle">Confirmar exclusão</h3>
                <p>Por favor, informe o motivo da exclusão (opcional) e confirme. Esta ação não pode ser desfeita.</p>
                <textarea id="deleteReason" placeholder="Motivo da exclusão (opcional)"></textarea>
                <div class="modal-actions">
                  <button id="btnCancelDelete" class="btn btn-cancel">Cancelar</button>
                  <button id="btnConfirmDelete" class="btn btn-confirm">Excluir</button>
                </div>
              </div>
            </div>`;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        })();

        const confirmModal = document.getElementById('confirmDeleteModal');
        const btnCancelDelete = document.getElementById('btnCancelDelete');
        const btnConfirmDelete = document.getElementById('btnConfirmDelete');
        const deleteReasonEl = document.getElementById('deleteReason');
        let deleteTargetId = null;

        function openDeleteModal(id){
            deleteTargetId = id;
            deleteReasonEl.value = '';
            confirmModal.classList.add('active');
            confirmModal.setAttribute('aria-hidden','false');
            deleteReasonEl.focus();
        }
        function closeDeleteModal(){
            confirmModal.classList.remove('active');
            confirmModal.setAttribute('aria-hidden','true');
            deleteTargetId = null;
        }

        if (btnCancelDelete) btnCancelDelete.addEventListener('click', function(e){ e.preventDefault(); closeDeleteModal(); });

        if (btnConfirmDelete) btnConfirmDelete.addEventListener('click', function(e){
            e.preventDefault();
            if (!deleteTargetId) { closeDeleteModal(); return; }
            const reason = deleteReasonEl.value || '';
            // create a form to submit as POST with _method=DELETE for maximum compatibility
            // Try to get CSRF token from a hidden input first, then from the meta tag (layout)
            const metaTokenEl = document.querySelector('meta[name="csrf-token"]');
            const hiddenTokenInput = document.querySelector('input[name="_token"]');
            const token = (metaTokenEl && metaTokenEl.getAttribute('content')) || (hiddenTokenInput && hiddenTokenInput.value) || null;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/clientes/${deleteTargetId}`;
            form.style.display = 'none';

            if (token) {
                const t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = token; form.appendChild(t);
            }
            const m = document.createElement('input'); m.type = 'hidden'; m.name = '_method'; m.value = 'DELETE'; form.appendChild(m);
            const r = document.createElement('input'); r.type = 'hidden'; r.name = 'reason'; r.value = reason; form.appendChild(r);
            document.body.appendChild(form);
            form.submit();
        });

        // wire modal to old and new delete buttons
        document.querySelectorAll('.btn-excluir-cliente, .actions-delete').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.stopPropagation();
                const id = this.getAttribute('data-id');
                if (!id) return;
                openDeleteModal(id);
            });
        });

        // Expose a robust global function to open/close the client edit form.
        // This is called by the inline onclick on the Edit button to avoid cases
        // where other JS listeners fail to attach.
        window.showClientEditForm = function(e) {
            if (e && e.preventDefault) e.preventDefault();
            const formEditarClienteEl = document.getElementById('formEditarCliente');
            const clienteDadosBlock = document.querySelector('.cliente-dados-moderna');
            if (!formEditarClienteEl) return false;
            // toggle visibility
            if (formEditarClienteEl.style.display === 'none' || formEditarClienteEl.style.display === '') {
                // populate from data-* attributes
                const editBIEl = document.getElementById('editBI');
                const editNomeEl = document.getElementById('editNome');
                const editEmailEl = document.getElementById('editEmail');
                const editContatoEl = document.getElementById('editContato');
                if (editBIEl) editBIEl.value = formEditarClienteEl.dataset.bi || '';
                if (editNomeEl) editNomeEl.value = formEditarClienteEl.dataset.nome || '';
                if (editEmailEl) editEmailEl.value = formEditarClienteEl.dataset.email || '';
                if (editContatoEl) editContatoEl.value = formEditarClienteEl.dataset.contato || '';
                formEditarClienteEl.style.display = 'block';
                if (clienteDadosBlock) clienteDadosBlock.style.display = 'none';
                if (editNomeEl) editNomeEl.focus();
                try { location.hash = 'formEditarCliente'; } catch (err) {}
                return true;
            }
            // otherwise close
            formEditarClienteEl.classList.add('closing');
            setTimeout(() => {
                formEditarClienteEl.style.display = 'none';
                formEditarClienteEl.classList.remove('closing');
                if (clienteDadosBlock) clienteDadosBlock.style.display = 'block';
                if (history && history.replaceState) history.replaceState(null, null, window.location.pathname + window.location.search);
            }, 220);
            return true;
        };
</script>

@endpush
@endif
@endsection
