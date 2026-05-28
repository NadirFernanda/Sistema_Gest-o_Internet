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
        .site-note{background:#fff8ec;border:1px solid #f5dba0;border-radius:10px;padding:11px 15px;font-size:.85rem;color:#7a5200;}
        .site-note a{color:#d4820a;font-weight:600;}
        .pf-submit{width:100%;height:54px;background:#f5a623;color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:.02em;box-shadow:0 6px 22px rgba(245,166,35,.32);transition:opacity .15s,transform .1s;margin-top:6px;}
        .pf-submit:hover{opacity:.9;}
        .pf-submit:active{transform:scale(.99);}
        .client-chip{display:inline-flex;align-items:center;gap:10px;background:#f4f6f9;border-radius:10px;padding:10px 16px;margin-bottom:20px;}
        .client-chip__avatar{width:36px;height:36px;border-radius:50%;background:#f5a623;color:#fff;font-weight:800;font-size:.9rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .client-chip__name{font-weight:700;font-size:.92rem;color:#222;}
        .client-chip__bi{font-size:.78rem;color:#999;margin-top:1px;}
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'Editar Cliente',
        'subtitle' => 'Actualizar dados do assinante',
        'heroCtAs' => '<a href="'.route('clientes.show', $cliente->id).'" class="btn btn-ghost">← Ficha do cliente</a>',
    ])

    <div style="max-width:660px; margin:24px auto 56px; padding:0 16px;">

        {{-- Client context chip --}}
        <div class="client-chip">
            <div class="client-chip__avatar">{{ mb_strtoupper(mb_substr($cliente->nome, 0, 1)) }}</div>
            <div>
                <div class="client-chip__name">{{ $cliente->nome }}</div>
                <div class="client-chip__bi">{{ $cliente->bi ?? 'Sem documento' }}</div>
            </div>
        </div>

        @if(session('error'))
            <div class="pf-alert pf-alert--error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="pf-alert pf-alert--warn">
                <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('clientes.update', $cliente->id) }}">
            @csrf
            @method('PUT')

            {{-- ── Card 1: Identificação ── --}}
            <div class="pf-card">
                <div class="pf-card__header">
                    <div class="pf-card__step">1</div>
                    <div class="pf-card__title">Identificação</div>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="editNome">Nome completo <span style="color:#e05a4f">*</span></label>
                    <input id="editNome" name="nome" type="text"
                           value="{{ old('nome', $cliente->nome) }}"
                           placeholder="Nome completo do cliente"
                           class="pf-input" required>
                    @error('nome') <div class="field-err">{{ $message }}</div> @enderror
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="editBI">BI / NIF <span style="color:#e05a4f">*</span></label>
                    <input id="editBI" name="bi" type="text"
                           value="{{ old('bi', $cliente->bi) }}"
                           placeholder="Número do documento"
                           class="pf-input" required>
                    @error('bi') <div class="field-err">{{ $message }}</div> @enderror
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
                        <label class="pf-label" for="editContato">Contacto (WhatsApp) <span style="color:#e05a4f">*</span></label>
                        <input id="editContato" name="contato" type="text"
                               value="{{ old('contato', $cliente->contato) }}"
                               placeholder="+244 9XX XXX XXX"
                               class="pf-input">
                        <div class="field-hint">Usado como username MikroTik</div>
                        @error('contato') <div class="field-err">{{ $message }}</div> @enderror
                    </div>

                    <div class="pf-field">
                        <label class="pf-label" for="editEmail">Email</label>
                        <input id="editEmail" name="email" type="email"
                               value="{{ old('email', $cliente->email) }}"
                               placeholder="email@exemplo.com"
                               class="pf-input">
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
                        <label class="pf-label" for="editSite">Site atribuído</label>
                        <select id="editSite" name="mikrotik_site_id" class="pf-select">
                            <option value="">— Sem site atribuído —</option>
                            @foreach($sites as $siteId => $siteNome)
                                <option value="{{ $siteId }}" {{ old('mikrotik_site_id', $cliente->mikrotik_site_id) == $siteId ? 'selected' : '' }}>
                                    {{ $siteNome }}
                                </option>
                            @endforeach
                        </select>
                        @error('mikrotik_site_id') <div class="field-err">{{ $message }}</div> @enderror
                    </div>
                @else
                    <div class="site-note">
                        Nenhum site MikroTik configurado.
                        <a href="{{ route('mikrotik.index') }}" target="_blank">Criar site em /mikrotik</a>
                    </div>
                @endif
            </div>

            <button type="submit" class="pf-submit">Guardar alterações</button>
        </form>

    </div>
</div>
@endsection
