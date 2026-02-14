@extends('layouts.app')

@section('content')

<div class="container">
    <a href="{{ route('clientes') }}" class="btn-back-circle btn-ghost mb-3" title="Voltar" aria-label="Voltar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <div class="client-card">
        <header class="client-card-header">
            <h2>Cadastrar Cliente</h2>
            <p class="muted">Preencha os dados do cliente. Campos obrigatórios marcados com *</p>
        </header>

        <form id="formClienteCreate" class="form-cadastro form-grid" method="POST" action="{{ route('clientes.store') }}">
            @csrf

            <div class="field full">
                <label for="nome">Nome completo *</label>
                <input type="text" id="nome" name="nome" class="input" placeholder="Nome completo" value="{{ old('nome') }}">
                    @if($errors->has('nome'))
                        <div class="text-danger">
                            @if($errors->first('nome') == 'O campo nome é obrigatório.')
                                Por favor preencha o nome do cliente.
                            @else
                                {{ $errors->first('nome') }}
                            @endif
                        </div>
                    @endif
            </div>

            <div class="field">
                <label for="bi_tipo">Tipo de documento *</label>
                <select id="bi_tipo" name="bi_tipo" class="select">
                    <option value="BI" @if(old('bi_tipo')=='BI') selected @endif>BI</option>
                    <option value="NIF" @if(old('bi_tipo')=='NIF') selected @endif>NIF</option>
                    <option value="Outro" @if(old('bi_tipo')=='Outro') selected @endif>Outro</option>
                </select>
                @if($errors->has('bi_tipo'))
                    <span style="color:#c0392b; margin-top:6px; font-size:0.95rem; display:block;">{{ $errors->first('bi_tipo') }}</span>
                @endif
            </div>

            <div class="field">
                <label for="bi_numero" id="labelBiNumero">BI / NIF *</label>
                <input type="text" id="bi_numero" name="bi_numero" class="input" placeholder="BI / NIF" value="{{ old('bi_numero') }}">
                    @if($errors->has('bi_numero'))
                        <div class="text-danger">
                            @if($errors->first('bi_numero') == 'O campo bi numero é obrigatório.')
                                Por favor preencha o número do BI.
                            @else
                                {{ $errors->first('bi_numero') }}
                            @endif
                        </div>
                    @endif
            </div>

            <div class="field full" id="bi_tipo_outro_wrap" style="display:none;">
                <label for="bi_tipo_outro">Especificar (Outro) *</label>
                <input type="text" id="bi_tipo_outro" name="bi_tipo_outro" class="input" placeholder="Ex: Passaporte, Cartão Estrangeiro" value="{{ old('bi_tipo_outro') }}">
                @if($errors->has('bi_tipo_outro'))
                    <span style="color:#c0392b; margin-top:6px; font-size:0.95rem; display:block;">{{ $errors->first('bi_tipo_outro') }}</span>
                @endif
            </div>

            <div class="field">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" class="input" placeholder="email@exemplo.com" value="">
                    @if($errors->has('email'))
                        <div class="text-danger">
                            @if($errors->first('email') == 'O campo email é obrigatório.')
                                Por favor preencha o e-mail do cliente.
                            @else
                                {{ $errors->first('email') }}
                            @endif
                        </div>
                    @endif
            </div>

            <div class="field">
                <label for="contato">Contacto (WhatsApp) *</label>
                <input type="text" id="contato" name="contato" class="input" placeholder="+244 9XX XXX XXX" value="">
                    @if($errors->has('contato'))
                        <div class="text-danger">
                            @if($errors->first('contato') == 'O campo contato é obrigatório.')
                                Por favor preencha o contato do cliente.
                            @else
                                {{ $errors->first('contato') }}
                            @endif
                        </div>
                    @endif
            </div>

            <div class="actions full">
                <button type="submit" class="btn-primary btn-cta">
                    <!-- simple SVG icon -->
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;margin-right:8px;">
                        <path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Cadastrar Cliente
                </button>
                <a href="{{ route('clientes') }}" class="btn-secondary btn-ghost">Cancelar</a>
            </div>
        </form>
    </div>

    <style>
    /* Modern form styles (sem card para erro) */
    .client-card { max-width:880px; margin:20px auto; background:#fff; border-radius:12px; padding:20px 22px; box-shadow:0 8px 30px rgba(2,6,23,0.08); }
    .client-card-header h2 { margin:0 0 6px; font-size:1.65rem; text-align:center; }
    .client-card-header .muted { color:#666; text-align:center; margin-bottom:12px; }

    .form-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:14px 18px; align-items:start; }
    .form-grid .full { grid-column: 1 / -1; }
    .field label { display:block; margin-bottom:6px; font-weight:600; color:#333; }
    .input, .select { width:100%; padding:12px 14px; border-radius:10px; border:1px solid #e6e6e6; box-shadow:inset 0 1px 0 rgba(255,255,255,0.6); font-size:1rem; transition:box-shadow .12s, border-color .12s; }
    .input:focus, .select:focus { outline:none; border-color:#f7b500; box-shadow:0 6px 24px rgba(247,181,0,0.12); }

    /* Mensagem de erro personalizada: texto simples, sem card */
    .field span[style*="color:#c0392b"] { margin-top:6px; font-size:0.95rem; display:block; font-weight:400; background:none; border:none; padding:0; }

    .actions { display:flex; gap:12px; justify-content:flex-end; align-items:center; }
    .btn-cta { background:#f7b500; color:#fff; border:none; padding:12px 22px; border-radius:10px; font-weight:700; display:inline-flex; align-items:center; box-shadow:0 6px 18px rgba(247,181,0,0.18); cursor:pointer; }
    .btn-cta:hover { background:#e0a800; }
    .btn-ghost { padding:10px 18px; border-radius:10px; border:1px solid #ddd; color:#444; text-decoration:none; background:transparent; }

    /* Responsividade */
    @media (max-width:900px) { .form-grid { grid-template-columns: 1fr; } .actions { justify-content:stretch; flex-direction:column-reverse; } .btn-cta, .btn-ghost { width:100%; } }
    </style>
</div>
@endsection

<!-- No client-side JS needed: form submits normally to the controller -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const biTipo = document.getElementById('bi_tipo');
    const biNumeroLabel = document.getElementById('labelBiNumero');
    const biOutroWrap = document.getElementById('bi_tipo_outro_wrap');
    function updateBiTipo() {
        if (biTipo.value === 'BI') {
            biNumeroLabel.textContent = 'BI *';
            biOutroWrap.style.display = 'none';
        } else if (biTipo.value === 'NIF') {
            biNumeroLabel.textContent = 'NIF *';
            biOutroWrap.style.display = 'none';
        } else {
            biNumeroLabel.textContent = 'Outro documento *';
            biOutroWrap.style.display = 'block';
        }
    }
    biTipo.addEventListener('change', updateBiTipo);
    updateBiTipo();
});
</script>
@endpush
