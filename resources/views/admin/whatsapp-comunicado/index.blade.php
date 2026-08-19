@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .comunicado-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 1100px; margin: 0 auto; }
        @media (max-width: 768px) { .comunicado-grid { grid-template-columns: 1fr; } }
        .card-comunicado { background: #fff; border-radius: 16px; box-shadow: 0 2px 8px #0001; padding: 24px; }
        .card-comunicado h3 { font-size: 1em; font-weight: 700; color: #333; margin: 0 0 14px; padding-bottom: 10px; border-bottom: 2px solid #F5A800; }
        .modo-radio { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
        .modo-radio label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.95em; padding: 10px 14px; border-radius: 10px; border: 1px solid #e0e0e0; transition: background 0.15s; }
        .modo-radio label:has(input:checked) { background: #fff8e6; border-color: #F5A800; }
        .cliente-lista { max-height: 320px; overflow-y: auto; border: 1px solid #e0e0e0; border-radius: 10px; padding: 6px; }
        .cliente-item { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 6px; cursor: pointer; font-size: 0.88em; }
        .cliente-item:hover { background: #f8f8f8; }
        .cliente-item input[type=checkbox] { accent-color: #F5A800; width: 15px; height: 15px; flex-shrink: 0; }
        .custo-bar { background: #fff8e6; border: 1px solid #F5A800; border-radius: 10px; padding: 12px 16px; font-size: 0.9em; color: #7a5000; display: flex; justify-content: space-between; align-items: center; }
        #filtro-clientes { width: 100%; margin-bottom: 8px; }
        .alerta-saldo { background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 10px 14px; font-size: 0.88em; color: #7a5000; }
        .resultado-box { max-width: 1100px; margin: 16px auto 0; }
        .resultado-box .ok  { background: #e6f9ec; border: 1px solid #34c759; border-radius: 10px; padding: 14px 18px; color: #136c34; }
        .resultado-box .err { background: #fff0f0; border: 1px solid #e74c3c; border-radius: 10px; padding: 14px 18px; color: #c0392b; margin-top: 8px; }
        .resultado-box ul { margin: 8px 0 0; padding-left: 18px; font-size: 0.88em; }
    </style>
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title'    => 'Comunicados WhatsApp',
        'subtitle' => 'Escreva uma mensagem livre e envie para todos os clientes ou para números específicos.',
        'stackLeft' => true,
    ])

    <div style="max-width:1100px;margin:18px auto;padding:0 8px;display:flex;gap:8px;">
        <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="width:auto;display:inline-block;">← Painel</a>
        <a href="{{ route('saldo-whatsapp.index') }}" class="btn btn-ghost" style="width:auto;display:inline-block;">Ver saldo WhatsApp</a>
    </div>

    {{-- Resultado do envio --}}
    @if (session('resultado'))
        @php $r = session('resultado'); @endphp
        <div class="resultado-box" style="padding:0 8px;">
            <div class="{{ ($r['falhados'] === 0 && !$r['sem_saldo']) ? 'ok' : 'err' }}">
                <strong>{{ $r['resumo'] }}</strong>
                @if (! empty($r['erros']))
                    <ul>
                        @foreach ($r['erros'] as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="resultado-box" style="padding:0 8px;">
            <div class="err">{{ $errors->first() }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('whatsapp-comunicado.enviar') }}" id="form-comunicado">
        @csrf

        <div class="comunicado-grid" style="margin-top:20px;padding:0 8px;">

            {{-- Coluna esquerda: mensagem --}}
            <div class="card-comunicado">
                <h3>Mensagem</h3>

                <label style="font-size:0.85em;color:#666;display:block;margin-bottom:4px;">Texto da mensagem</label>
                <textarea name="mensagem" id="mensagem" rows="12"
                          maxlength="4000" placeholder="Escreva a sua mensagem aqui…"
                          class="search-input" style="width:100%;resize:vertical;font-family:inherit;line-height:1.5;"
                          required>{{ old('mensagem') }}</textarea>
                <div style="text-align:right;font-size:0.78em;color:#999;margin-top:4px;">
                    <span id="char-count">0</span>/4000 caracteres
                </div>

                <div class="custo-bar" style="margin-top:16px;" id="custo-bar">
                    <span>Destinatários seleccionados: <strong id="total-dest">0</strong></span>
                    <span>Custo estimado: <strong id="custo-total">0,00 Kz</strong></span>
                </div>

                @php $saldoNum = (float) $saldo; $alertaSaldo = $saldoNum < 500; @endphp
                @if ($alertaSaldo)
                    <div class="alerta-saldo" style="margin-top:10px;">
                        ⚠️ Saldo disponível: <strong>{{ number_format($saldo, 2, ',', '.') }} Kz</strong>. Recarregue antes de enviar.
                    </div>
                @else
                    <div style="margin-top:10px;font-size:0.85em;color:#555;">
                        Saldo disponível: <strong>{{ number_format($saldo, 2, ',', '.') }} Kz</strong>
                    </div>
                @endif

                <button type="submit" class="btn btn-search" style="width:100%;margin-top:20px;" id="btn-enviar">
                    Enviar mensagem
                </button>
            </div>

            {{-- Coluna direita: destinatários --}}
            <div class="card-comunicado">
                <h3>Destinatários</h3>

                <div class="modo-radio">
                    <label>
                        <input type="radio" name="modo" value="todos" id="modo-todos"
                               {{ old('modo', 'todos') === 'todos' ? 'checked' : '' }}>
                        <span>
                            <strong>Todos os clientes activos</strong>
                            <span style="display:block;font-size:0.82em;color:#888;">{{ $clientes->count() }} cliente(s) com número de telefone</span>
                        </span>
                    </label>

                    <label>
                        <input type="radio" name="modo" value="seleccionados" id="modo-seleccionados"
                               {{ old('modo') === 'seleccionados' ? 'checked' : '' }}>
                        <span>
                            <strong>Seleccionar clientes específicos</strong>
                            <span style="display:block;font-size:0.82em;color:#888;">Escolha da lista de clientes</span>
                        </span>
                    </label>

                    <label>
                        <input type="radio" name="modo" value="manuais" id="modo-manuais"
                               {{ old('modo') === 'manuais' ? 'checked' : '' }}>
                        <span>
                            <strong>Números manuais</strong>
                            <span style="display:block;font-size:0.82em;color:#888;">Cole ou escreva números separados por vírgula ou linha</span>
                        </span>
                    </label>
                </div>

                {{-- Painel: seleccionar clientes --}}
                <div id="painel-seleccionados" style="display:none;">
                    <input type="text" id="filtro-clientes" class="search-input" placeholder="Filtrar clientes…" autocomplete="off">
                    <div style="display:flex;gap:10px;margin-bottom:8px;font-size:0.82em;">
                        <button type="button" onclick="selectAll(true)"  class="btn btn-ghost" style="padding:4px 12px;">Todos</button>
                        <button type="button" onclick="selectAll(false)" class="btn btn-ghost" style="padding:4px 12px;">Nenhum</button>
                    </div>
                    <div class="cliente-lista" id="lista-clientes">
                        @foreach ($clientes as $c)
                            <label class="cliente-item" data-nome="{{ strtolower($c->nome) }}">
                                <input type="checkbox" name="cliente_ids[]" value="{{ $c->id }}"
                                       class="cb-cliente"
                                       {{ is_array(old('cliente_ids')) && in_array($c->id, old('cliente_ids')) ? 'checked' : '' }}>
                                <span>
                                    <strong>{{ $c->nome }}</strong>
                                    <span style="color:#888;font-size:0.9em;margin-left:6px;">{{ $c->contato }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Painel: números manuais --}}
                <div id="painel-manuais" style="display:none;">
                    <label style="font-size:0.85em;color:#666;display:block;margin-bottom:6px;">
                        Um número por linha, ou separados por vírgula. <br>
                        Formato local (9XXXXXXXX) ou internacional (244XXXXXXXXX).
                    </label>
                    <textarea name="numeros" id="numeros" rows="10"
                              class="search-input" style="width:100%;resize:vertical;font-family:monospace;font-size:0.9em;"
                              placeholder="912345678&#10;923456789&#10;244912345678">{{ old('numeros') }}</textarea>
                </div>

                {{-- Modo todos: informativo --}}
                <div id="painel-todos" style="">
                    <div style="background:#f0fff4;border:1px solid #34c759;border-radius:10px;padding:14px 16px;font-size:0.9em;color:#136c34;">
                        A mensagem será enviada a <strong>{{ $clientes->count() }}</strong> cliente(s) que têm número de telefone registado.
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
const CUSTO = {{ $custoPorMensagem }};
const TOTAL_CLIENTES = {{ $clientes->count() }};

// Contagem de caracteres
const msgArea = document.getElementById('mensagem');
const charCount = document.getElementById('char-count');
msgArea.addEventListener('input', () => { charCount.textContent = msgArea.value.length; atualizarCusto(); });
charCount.textContent = msgArea.value.length;

// Alternar painéis de modo
const modos = document.querySelectorAll('input[name="modo"]');
modos.forEach(r => r.addEventListener('change', alternarPainel));

function alternarPainel() {
    const modo = document.querySelector('input[name="modo"]:checked')?.value;
    document.getElementById('painel-todos').style.display        = modo === 'todos'         ? '' : 'none';
    document.getElementById('painel-seleccionados').style.display = modo === 'seleccionados' ? '' : 'none';
    document.getElementById('painel-manuais').style.display      = modo === 'manuais'        ? '' : 'none';
    atualizarCusto();
}
alternarPainel();

// Filtro da lista de clientes
document.getElementById('filtro-clientes').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#lista-clientes .cliente-item').forEach(el => {
        el.style.display = el.dataset.nome.includes(q) ? '' : 'none';
    });
});

// Seleccionar tudo / nenhum
function selectAll(check) {
    document.querySelectorAll('.cb-cliente').forEach(cb => { cb.checked = check; });
    atualizarCusto();
}
document.querySelectorAll('.cb-cliente').forEach(cb => cb.addEventListener('change', atualizarCusto));

// Contar manuais
document.getElementById('numeros').addEventListener('input', atualizarCusto);

function contarManuais() {
    const txt = document.getElementById('numeros').value;
    if (!txt.trim()) return 0;
    return txt.split(/[\n,;]+/).filter(n => n.trim() !== '').length;
}

function atualizarCusto() {
    const modo = document.querySelector('input[name="modo"]:checked')?.value;
    let n = 0;
    if (modo === 'todos') n = TOTAL_CLIENTES;
    else if (modo === 'seleccionados') n = document.querySelectorAll('.cb-cliente:checked').length;
    else if (modo === 'manuais') n = contarManuais();

    document.getElementById('total-dest').textContent = n;
    const custo = (n * CUSTO).toFixed(2).replace('.', ',');
    document.getElementById('custo-total').textContent = custo + ' Kz';
}

// Confirmação antes de enviar
document.getElementById('form-comunicado').addEventListener('submit', function (e) {
    const modo = document.querySelector('input[name="modo"]:checked')?.value;
    let n = 0;
    if (modo === 'todos') n = TOTAL_CLIENTES;
    else if (modo === 'seleccionados') n = document.querySelectorAll('.cb-cliente:checked').length;
    else if (modo === 'manuais') n = contarManuais();

    if (n === 0) { e.preventDefault(); alert('Seleccione pelo menos um destinatário.'); return; }

    const custo = (n * CUSTO).toFixed(2).replace('.', ',');
    if (!confirm(`Confirma o envio para ${n} destinatário(s)?\nCusto estimado: ${custo} Kz`)) {
        e.preventDefault();
        return;
    }
    document.getElementById('btn-enviar').disabled = true;
    document.getElementById('btn-enviar').textContent = 'A enviar…';
});

atualizarCusto();
</script>
@endpush
@endsection
