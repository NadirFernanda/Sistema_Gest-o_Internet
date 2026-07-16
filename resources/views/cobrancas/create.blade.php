@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .pf-card{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);padding:22px 26px 24px;margin-bottom:14px;}
        .pf-card__header{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
        .pf-card__step{width:28px;height:28px;border-radius:50%;background:#f5a623;color:#fff;font-weight:800;font-size:.82rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .pf-card__title{font-weight:700;font-size:.97rem;color:#1a1a2e;}
        .pf-field{margin-bottom:15px;}
        .pf-field:last-child{margin-bottom:0;}
        .pf-label{display:block;font-size:.75rem;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:.05em;margin-bottom:7px;}
        .pf-input,.pf-select{width:100%;height:46px;padding:0 14px;border:1.5px solid #e8eaf0;border-radius:10px;font-size:.93rem;color:#222;background:#fff;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none;}
        .pf-input:focus,.pf-select:focus{outline:none;border-color:#f5a623;box-shadow:0 0 0 3px rgba(245,166,35,.13);}
        .pf-select{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aaa' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:38px;cursor:pointer;}
        .pf-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
        @media(max-width:540px){.pf-grid-2{grid-template-columns:1fr;}}
        .field-err{font-size:.8rem;color:#c0392b;margin-top:5px;}
        .field-hint{font-size:.78rem;color:#aaa;margin-top:5px;}
        .pf-alert{padding:12px 16px;border-radius:10px;font-size:.88rem;margin-bottom:14px;}
        .pf-alert--error{background:#fdecea;border:1px solid #f5bab5;color:#922b21;}
        .pf-alert--warn{background:#fff8ec;border:1px solid #f5dba0;color:#7a5200;}
        .pf-alert ul{margin:4px 0 0;padding-left:18px;}
        .pf-submit{width:100%;height:54px;background:#f5a623;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:.02em;box-shadow:0 6px 22px rgba(245,166,35,.32);transition:opacity .15s,transform .1s;margin-top:6px;}
        .pf-submit:hover{opacity:.9;}
        .pf-submit:active{transform:scale(.99);}

        /* Status pills */
        .status-pills{display:flex;gap:10px;flex-wrap:wrap;}
        .status-pill{cursor:pointer;border:2px solid #e6e9ef;border-radius:10px;padding:9px 20px;font-size:.88rem;font-weight:600;color:#666;transition:all .15s;user-select:none;flex:1;min-width:90px;text-align:center;}
        .status-pill input[type=radio]{display:none;}
        .status-pill:hover{border-color:#f5a623;color:#333;background:#fffbf2;}
        .status-pill.pill-pendente.selected{border-color:#f5a623;background:#fff8ec;color:#d4820a;}
        .status-pill.pill-pago.selected{border-color:#3bb273;background:#e8f7ef;color:#1a6b3d;}
        .status-pill.pill-atrasado.selected{border-color:#e05a4f;background:#fdecea;color:#922b21;}
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @php $isEdit = isset($cobranca); @endphp

    @include('layouts.partials.clientes-hero', [
        'title'    => $isEdit ? 'Editar Cobrança' : 'Nova Cobrança',
        'subtitle' => $isEdit ? 'Actualizar dados da cobrança' : 'Registar nova cobrança',
        'heroCtAs' => '<a href="'.route('cobrancas.index').'" class="btn btn-ghost">← Cobranças</a>',
    ])

    <div style="max-width:660px; margin:24px auto 56px; padding:0 16px;">

        @if($errors->any())
            <div class="pf-alert pf-alert--warn">
                <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form action="{{ $isEdit ? route('cobrancas.update', $cobranca->id) : route('cobrancas.store') }}"
              method="POST">
            @csrf
            @if($isEdit) @method('PUT') @endif

            {{-- ── Card 1: Cliente ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">1</div>
                    <div class="pf-card__title">Cliente</div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="cliente_id">Selecionar cliente <span style="color:#e05a4f">*</span></label>
                    <select name="cliente_id" id="cliente_id" class="pf-select" required>
                        <option value="">— Escolher cliente —</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}"
                                {{ (old('cliente_id') ?? ($cobranca->cliente_id ?? null)) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('cliente_id') <div class="field-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field" id="plano_id_wrap" style="display:none;">
                    <label class="pf-label" for="plano_id">Plano a renovar</label>
                    <select name="plano_id" id="plano_id" class="pf-select">
                        <option value="">— Selecionar plano (opcional) —</option>
                    </select>
                    <div class="field-hint">Se o cliente tiver mais de um plano, seleccione qual deve ser renovado com este pagamento.</div>
                    @error('plano_id') <div class="field-err">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ── Card 2: Cobrança ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">2</div>
                    <div class="pf-card__title">Detalhe da cobrança</div>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="descricao">Descrição <span style="color:#e05a4f">*</span></label>
                    <input type="text" name="descricao" id="descricao" class="pf-input"
                           placeholder="Ex: Mensalidade de Junho 2026"
                           value="{{ old('descricao', $cobranca->descricao ?? '') }}" required>
                    @error('descricao') <div class="field-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="valor">Valor (Kz) <span style="color:#e05a4f">*</span></label>
                    <input type="number" step="0.01" min="0" name="valor" id="valor" class="pf-input"
                           placeholder="0,00"
                           value="{{ old('valor', $cobranca->valor ?? '') }}" required>
                    @error('valor') <div class="field-err">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ── Card 3: Datas & Estado ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">3</div>
                    <div class="pf-card__title">Datas e estado</div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="data_vencimento">Vencimento <span style="color:#e05a4f">*</span></label>
                        <input type="date" name="data_vencimento" id="data_vencimento" class="pf-input"
                               value="{{ old('data_vencimento', isset($cobranca) ? $cobranca->data_vencimento : '') }}" required>
                        @error('data_vencimento') <div class="field-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="data_pagamento">Data de pagamento</label>
                        <input type="date" name="data_pagamento" id="data_pagamento" class="pf-input"
                               value="{{ old('data_pagamento', $cobranca->data_pagamento ?? '') }}">
                        <div class="field-hint">Preencher quando pago</div>
                        @error('data_pagamento') <div class="field-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="pf-field" style="margin-top:4px;">
                    <label class="pf-label">Estado <span style="color:#e05a4f">*</span></label>
                    @php $currentStatus = old('status', $cobranca->status ?? 'pendente'); @endphp
                    <div class="status-pills">
                        @foreach(['pendente' => 'Pendente', 'pago' => 'Pago', 'atrasado' => 'Atrasado'] as $val => $label)
                            <label class="status-pill pill-{{ $val }} {{ $currentStatus == $val ? 'selected' : '' }}">
                                <input type="radio" name="status" value="{{ $val }}"
                                       {{ $currentStatus == $val ? 'checked' : '' }} required>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @error('status') <div class="field-err">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="pf-submit">
                {{ $isEdit ? 'Guardar alterações' : 'Registar cobrança' }}
            </button>
        </form>

    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.status-pill').forEach(function (pill) {
    pill.addEventListener('click', function () {
        document.querySelectorAll('.status-pill').forEach(function (p) { p.classList.remove('selected'); });
        pill.classList.add('selected');
    });
});

// Carregar planos do cliente seleccionado
var clienteSel  = document.getElementById('cliente_id');
var planoSel    = document.getElementById('plano_id');
var planoWrap   = document.getElementById('plano_id_wrap');
var prePlanoId  = '{{ old('plano_id', $cobranca->plano_id ?? '') }}';

function carregarPlanos(clienteId) {
    if (!clienteId) { planoWrap.style.display = 'none'; return; }
    fetch('/clientes/' + clienteId + '/planos-json', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(r) { return r.json(); })
        .then(function(planos) {
            planoSel.innerHTML = '<option value="">— Selecionar plano (opcional) —</option>';
            planos.forEach(function(p) {
                var opt = document.createElement('option');
                opt.value = p.id;
                var label = p.localizacao ? p.nome + ' · ' + p.localizacao : p.nome;
                opt.textContent = label + ' — ' + p.estado + ' (vence ' + (p.proxima_renovacao || '?') + ')';
                if (String(p.id) === String(prePlanoId)) opt.selected = true;
                planoSel.appendChild(opt);
            });
            planoWrap.style.display = planos.length > 1 ? 'block' : 'none';
            if (planos.length === 1 && !prePlanoId) {
                planoSel.value = planos[0].id;
            }
        })
        .catch(function() { planoWrap.style.display = 'none'; });
}

clienteSel.addEventListener('change', function() { carregarPlanos(this.value); });
if (clienteSel.value) carregarPlanos(clienteSel.value);
</script>
@endpush
@endsection
