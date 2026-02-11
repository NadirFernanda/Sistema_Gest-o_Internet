@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Cadastrar Cliente</h2>

    <form id="formClienteCreate" class="form-cadastro" method="POST" action="{{ route('clientes.store') }}">
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome completo</label>
            <input type="text" id="nome" name="nome" class="form-control" placeholder="Nome completo" required>
            <div class="invalid-feedback d-none" id="error_nome"></div>
        </div>
        <div class="mb-3">
            <label for="bi_tipo" class="form-label">Tipo de documento</label>
            <select id="bi_tipo" name="bi_tipo" class="form-select" required>
                <option value="BI">BI</option>
                <option value="NIF">NIF</option>
                <option value="Outro">Outro</option>
            </select>
            <div class="invalid-feedback d-none" id="error_bi_tipo"></div>
        </div>
        <div class="mb-3">
            <label for="bi_numero" class="form-label" id="labelBiNumero">BI / NIF</label>
            <input type="text" id="bi_numero" name="bi_numero" class="form-control" placeholder="BI / NIF" required>
            <div class="invalid-feedback d-none" id="error_bi_numero"></div>
        </div>
        <div class="mb-3" id="bi_tipo_outro_wrap" style="display:none;">
            <label for="bi_tipo_outro" class="form-label">Especificar (Outro)</label>
            <input type="text" id="bi_tipo_outro" name="bi_tipo_outro" class="form-control" placeholder="Ex: Passaporte, Cartão Estrangeiro">
            <div class="invalid-feedback d-none" id="error_bi_tipo_outro"></div>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="email@exemplo.com" required>
            <div class="invalid-feedback d-none" id="error_email"></div>
        </div>
        <div class="mb-3">
            <label for="contato" class="form-label">Contacto (WhatsApp)</label>
            <input type="text" id="contato" name="contato" class="form-control" placeholder="+244 9XX XXX XXX" required>
            <div class="invalid-feedback d-none" id="error_contato"></div>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Cliente</button>
        <a href="{{ route('clientes') }}" class="btn btn-secondary">Voltar</a>
    </form>
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

        if (form) {
            form.addEventListener('submit', function(ev){
                ev.preventDefault();
                clearErrors();
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
                    // success: redirect to clients list
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
