@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* ── Cards ────────────────────────────────────────────── */
        .pf-card{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);padding:22px 26px 24px;margin-bottom:14px;}
        .pf-card__header{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
        .pf-card__step{width:28px;height:28px;border-radius:50%;background:#f5a623;color:#fff;font-weight:800;font-size:.82rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .pf-card__title{font-weight:700;font-size:.97rem;color:#1a1a2e;}

        /* ── Fields ───────────────────────────────────────────── */
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

        /* ── Alert ────────────────────────────────────────────── */
        .pf-alert{padding:12px 16px;border-radius:10px;font-size:.88rem;margin-bottom:14px;line-height:1.5;}
        .pf-alert--success{background:#e8f7ef;border:1px solid #a8e6c0;color:#1a6b3d;}
        .pf-alert--error{background:#fdecea;border:1px solid #f5bab5;color:#922b21;}
        .pf-alert--warn{background:#fff8ec;border:1px solid #f5dba0;color:#7a5200;}
        .pf-alert ul{margin:4px 0 0;padding-left:18px;}

        /* ── Site info note ───────────────────────────────────── */
        .site-note{background:#fff8ec;border:1px solid #f5dba0;border-radius:10px;padding:11px 15px;font-size:.85rem;color:#7a5200;}
        .site-note a{color:#d4820a;font-weight:600;}

        /* ── Submit ───────────────────────────────────────────── */
        .pf-submit{width:100%;height:54px;background:#f5a623;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:.02em;box-shadow:0 6px 22px rgba(245,166,35,.32);transition:opacity .15s,transform .1s;margin-top:6px;}
        .pf-submit:hover{opacity:.9;}
        .pf-submit:active{transform:scale(.99);}
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Novo Cliente',
        'subtitle' => 'Registo de novo assinante',
        'heroCtAs' => '<a href="'.route('clientes').'" class="btn btn-ghost">← Clientes</a>',
    ])

    <div style="max-width:660px; margin:24px auto 56px; padding:0 16px;">

        @if(session('success'))
            <div class="pf-alert pf-alert--success">{{ session('success') }}</div>
        @endif

        @if(session('error_cliente_existente_id'))
            <div class="pf-alert pf-alert--warn" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
                <div style="flex:1;">
                    <strong>Cliente já registado:</strong> {{ session('error_cliente_existente_nome') }}<br>
                    <span style="font-size:.85rem;">Este BI já pertence a um cliente existente. Pode adicionar um novo plano directamente.</span>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('planos.create', ['cliente_id' => session('error_cliente_existente_id')]) }}"
                       style="background:#f5a623;color:#fff;padding:8px 18px;border-radius:8px;font-weight:700;text-decoration:none;white-space:nowrap;">
                        + Adicionar plano
                    </a>
                    <a href="{{ route('clientes.show', session('error_cliente_existente_id')) }}"
                       style="background:#eee;color:#333;padding:8px 18px;border-radius:8px;font-weight:600;text-decoration:none;white-space:nowrap;">
                        Ver cliente
                    </a>
                </div>
            </div>
        @elseif(session('error'))
            <div class="pf-alert pf-alert--error">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="pf-alert pf-alert--warn">
                <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="formClienteCreate" method="POST" action="{{ route('clientes.store') }}">
            @csrf

            {{-- ── Card 1: Identificação ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">1</div>
                    <div class="pf-card__title">Identificação</div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="bi_tipo">Tipo de documento <span style="color:#e05a4f">*</span></label>
                        <select id="bi_tipo" name="bi_tipo" class="pf-select" required>
                            <option value="BI"    {{ old('bi_tipo', 'BI') == 'BI'    ? 'selected' : '' }}>BI</option>
                            <option value="NIF"   {{ old('bi_tipo') == 'NIF'   ? 'selected' : '' }}>NIF</option>
                            <option value="Outro" {{ old('bi_tipo') == 'Outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        @error('bi_tipo') <div class="field-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="bi_numero" id="labelBiNumero">BI <span style="color:#e05a4f">*</span></label>
                        <input id="bi_numero" name="bi_numero" type="text"
                               value="{{ old('bi_numero') }}"
                               placeholder="Número do documento"
                               class="pf-input" required>
                        @error('bi_numero') <div class="field-err">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div id="bi_tipo_outro_wrap" class="pf-field" style="display:none;">
                    <label class="pf-label" for="bi_tipo_outro">Especificar documento <span style="color:#e05a4f">*</span></label>
                    <input id="bi_tipo_outro" name="bi_tipo_outro" type="text"
                           value="{{ old('bi_tipo_outro') }}"
                           placeholder="Ex: Passaporte, Cartão Estrangeiro"
                           class="pf-input">
                    @error('bi_tipo_outro') <div class="field-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="nome">Nome completo <span style="color:#e05a4f">*</span></label>
                    <input id="nome" name="nome" type="text"
                           value="{{ old('nome') }}"
                           placeholder="Nome completo do cliente"
                           class="pf-input" required>
                    @error('nome') <div class="field-err">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- ── Card 2: Contactos ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">2</div>
                    <div class="pf-card__title">Contactos</div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="contato">Contacto (WhatsApp) <span style="color:#e05a4f">*</span></label>
                        <input id="contato" name="contato" type="text"
                               value="{{ old('contato') }}"
                               placeholder="+244 9XX XXX XXX"
                               class="pf-input" required>
                        <div class="field-hint">Usado como username MikroTik</div>
                        @error('contato') <div class="field-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="email">Email <span style="color:#e05a4f">*</span></label>
                        <input id="email" name="email" type="email"
                               value="{{ old('email') }}"
                               placeholder="email@exemplo.com"
                               class="pf-input" required>
                        @error('email') <div class="field-err">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- ── Card 3: Site MikroTik ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">3</div>
                    <div class="pf-card__title">Site MikroTik</div>
                </div>

                @if($sites->isNotEmpty())
                    <div class="pf-field">
                        <label class="pf-label" for="mikrotik_site_id">Atribuir ao site</label>
                        <select id="mikrotik_site_id" name="mikrotik_site_id" class="pf-select">
                            <option value="">— Seleccionar site (opcional) —</option>
                            @foreach($sites as $siteId => $siteNome)
                                <option value="{{ $siteId }}" {{ old('mikrotik_site_id') == $siteId ? 'selected' : '' }}>
                                    {{ $siteNome }}
                                </option>
                            @endforeach
                        </select>
                        @error('mikrotik_site_id') <div class="field-err">{{ $message }}</div> @enderror
                    </div>
                @else
                    <div class="site-note">
                        Nenhum site configurado.
                        <a href="{{ route('mikrotik.index') }}" target="_blank">Criar site em /mikrotik</a>
                    </div>
                @endif
            </div>

            <button type="submit" class="pf-submit">Cadastrar Cliente</button>
        </form>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var biTipo    = document.getElementById('bi_tipo');
    var biLabel   = document.getElementById('labelBiNumero');
    var outroWrap = document.getElementById('bi_tipo_outro_wrap');

    function updateTipo() {
        if (biTipo.value === 'Outro') {
            biLabel.textContent = 'Nº do documento *';
            outroWrap.style.display = 'block';
        } else {
            biLabel.textContent = biTipo.value + ' *';
            outroWrap.style.display = 'none';
        }
    }

    biTipo.addEventListener('change', updateTipo);
    updateTipo();
});
</script>
@endpush
@endsection
