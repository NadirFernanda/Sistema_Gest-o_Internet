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
                <input type="text" id="nome" name="nome" class="input" placeholder="Nome completo" required value="{{ old('nome') }}">
                @if($errors->has('nome'))
                    <div class="invalid-feedback">{{ $errors->first('nome') }}</div>
                @endif
                @if($errors->has('nome'))
                    <div class="invalid-feedback">{{ $errors->first('nome') }}</div>
                @endif
            </div>

            <div class="field">
                <label for="bi_tipo">Tipo de documento *</label>
                <select id="bi_tipo" name="bi_tipo" class="select" required>
                    <option value="">Selecione...</option>
                    <option value="BI" @if(old('bi_tipo')=='BI') selected @endif>BI</option>
                    <option value="NIF" @if(old('bi_tipo')=='NIF') selected @endif>NIF</option>
                    <option value="Outro" @if(old('bi_tipo')=='Outro') selected @endif>Outro</option>
                </select>
                @if($errors->has('bi_tipo'))
                    <div class="invalid-feedback">{{ $errors->first('bi_tipo') }}</div>
                @endif
                    <option value="BI">BI</option>
                    <option value="NIF">NIF</option>
                    <option value="Outro">Outro</option>
                </select>
                @if($errors->has('bi_tipo'))
                    <div class="invalid-feedback">{{ $errors->first('bi_tipo') }}</div>
                @endif
            </div>

            <div class="field">
                <label for="bi_numero" id="labelBiNumero">BI / NIF *</label>
                <input type="text" id="bi_numero" name="bi_numero" class="input" placeholder="BI / NIF" required value="{{ old('bi_numero') }}">
                @if($errors->has('bi_numero'))
                    <div class="invalid-feedback">{{ $errors->first('bi_numero') }}</div>
                @endif
                @if($errors->has('bi_numero'))
                    <div class="invalid-feedback">{{ $errors->first('bi_numero') }}</div>
                @endif
            </div>

            <div class="field full" id="bi_tipo_outro_wrap" style="display:none;">
                <label for="bi_tipo_outro">Especificar (Outro) *</label>
                <input type="text" id="bi_tipo_outro" name="bi_tipo_outro" class="input" placeholder="Ex: Passaporte, Cartão Estrangeiro" value="{{ old('bi_tipo_outro') }}">
                @if($errors->has('bi_tipo_outro'))
                    <div class="invalid-feedback">{{ $errors->first('bi_tipo_outro') }}</div>
                @endif
                @if($errors->has('bi_tipo_outro'))
                    <div class="invalid-feedback">{{ $errors->first('bi_tipo_outro') }}</div>
                @endif
            </div>

            <div class="field">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" class="input" placeholder="email@exemplo.com" required value="{{ old('email') }}">
                @if($errors->has('email'))
                    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                @endif
                @if($errors->has('email'))
                    <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                @endif
            </div>

            <div class="field">
                <label for="contato">Contacto (WhatsApp) *</label>
                <input type="text" id="contato" name="contato" class="input" placeholder="+244 9XX XXX XXX" required value="{{ old('contato') }}">
                @if($errors->has('contato'))
                    <div class="invalid-feedback">{{ $errors->first('contato') }}</div>
                @endif
                @if($errors->has('contato'))
                    <div class="invalid-feedback">{{ $errors->first('contato') }}</div>
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
    /* Modern card + form styles (scoped to this view) */
    .client-card { max-width:880px; margin:20px auto; background:#fff; border-radius:12px; padding:20px 22px; box-shadow:0 8px 30px rgba(2,6,23,0.08); }
    .client-card-header h2 { margin:0 0 6px; font-size:1.65rem; text-align:center; }
    .client-card-header .muted { color:#666; text-align:center; margin-bottom:12px; }

    .form-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:14px 18px; align-items:start; }
    .form-grid .full { grid-column: 1 / -1; }
    .field label { display:block; margin-bottom:6px; font-weight:600; color:#333; }
    .input, .select { width:100%; padding:12px 14px; border-radius:10px; border:1px solid #e6e6e6; box-shadow:inset 0 1px 0 rgba(255,255,255,0.6); font-size:1rem; transition:box-shadow .12s, border-color .12s; }
    .input:focus, .select:focus { outline:none; border-color:#f7b500; box-shadow:0 6px 24px rgba(247,181,0,0.12); }

    .invalid-feedback { color:#c0392b; margin-top:6px; font-size:0.95rem; }

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
    (function(){
        var tipo = document.getElementById('bi_tipo');
        var label = document.getElementById('labelBiNumero');
        var numero = document.getElementById('bi_numero');
        var outroWrap = document.getElementById('bi_tipo_outro_wrap');
        var outro = document.getElementById('bi_tipo_outro');
        var form = document.getElementById('formClienteCreate');

        function clearErrors() {
            ['nome','bi_tipo','bi_numero','bi_tipo_outro','email','contato'].forEach(function(k){
                var el = document.getElementById('error_'+k);
                if (el) { el.textContent=''; el.classList.add('d-none'); }
            });
        }

        function showErrors(errors) {
            clearErrors();
            Object.keys(errors||{}).forEach(function(key){
                var el = document.getElementById('error_'+key);
                if (el) { el.textContent = errors[key][0] || errors[key]; el.classList.remove('d-none'); }
            });
        }

        function validateFields() {
            var errors = {};
            var nome = document.getElementById('nome').value.trim();
            var bi_tipo = document.getElementById('bi_tipo').value.trim();
            var bi_numero = document.getElementById('bi_numero').value.trim();
            var email = document.getElementById('email').value.trim();
            var contato = document.getElementById('contato').value.trim();
            if (!nome) errors.nome = ['Nome é obrigatório.'];
            if (!bi_tipo) errors.bi_tipo = ['Tipo de documento é obrigatório.'];
            if (!bi_numero) errors.bi_numero = ['Número do documento é obrigatório.'];
            if (!email) {
                errors.email = ['E-mail é obrigatório.'];
            } else if (!/^\S+@\S+\.\S+$/.test(email)) {
                errors.email = ['Digite um e-mail válido.'];
            }
            if (!contato) errors.contato = ['Contato é obrigatório.'];
            return errors;
        }

        if (form) {
            form.addEventListener('submit', function(ev){
                ev.preventDefault();
                clearErrors();
                var errors = validateFields();
                if (Object.keys(errors).length > 0) {
                    showErrors(errors);
                    return;
                }
                var url = form.getAttribute('action');
                var data = new FormData(form);
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
                    body: data
                }).then(function(res){
                    if (res.status === 201) return res.json();
                    if (res.status === 422) return res.json().then(function(j){ throw { validation: j.errors || j }; });
                    return res.json().then(function(j){ throw j; });
                }).then(function(j){
                    window.location = '{{ url('/clientes') }}';
                }).catch(function(err){
                    if (err && err.validation) {
                        showErrors(err.validation);
                    } else if (err && err.errors) {
                        showErrors(err.errors);
                    } else {
                        alert('Erro ao cadastrar cliente. Verifique o console.');
                        console.error(err);
                    }
                });
            });
        }

        function update() {
            var v = tipo.value;
            if (v === 'BI') {
                label.textContent = 'BI';
                numero.placeholder = 'BI';
                outroWrap.style.display = 'none';
                outro.removeAttribute('required');
            } else if (v === 'NIF') {
                label.textContent = 'NIF';
                numero.placeholder = 'NIF';
                outroWrap.style.display = 'none';
                outro.removeAttribute('required');
            } else {
                label.textContent = 'Número';
                numero.placeholder = 'Número do documento';
                outroWrap.style.display = '';
                outro.setAttribute('required','required');
            }
        }

        if (tipo) {
            tipo.addEventListener('change', update);
            update();
        }
    })();
</script>
@endpush
