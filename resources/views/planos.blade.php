@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Planos</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
        <form id="formPlano" class="form-cadastro">
            <select id="clientePlano" required>
                <option value="">Selecione o cliente</option>
            </select>
            <input type="text" id="nomePlano" placeholder="Nome do plano" required>
            <input type="text" id="descricaoPlano" placeholder="Descrição" required>
            <input type="hidden" name="preco" id="precoPlano">
            <input type="text" id="precoPlanoDisplay" placeholder="Preço (Kz)" required>
            <input type="number" id="cicloPlano" placeholder="Ciclo de serviço (dias)" min="1" required>
            <input type="date" id="dataAtivacaoPlano" placeholder="Data de ativação" required>
            <select id="estadoPlano" required>
                <option value="">Estado do plano</option>
                <option value="Ativo">Ativo</option>
                <option value="Em aviso">Em aviso</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Cancelado">Cancelado</option>
            </select>
            <button type="submit">Cadastrar Plano</button>
        </form>
        <h2 style="margin-top:32px;">Lista de Planos</h2>
        <style>
            .busca-planos-form { margin:12px 0 4px 0; display:flex; gap:12px; align-items:center; }
            .busca-planos-input {
                flex:1; min-width:220px; height:48px; padding:12px 16px; border-radius:10px; border:1px solid #d9d9d9;
                background:#fff; font-size:1rem; box-shadow:0 6px 18px rgba(0,0,0,0.04); color:#222;
            }
            .busca-planos-input::placeholder { color:#9b9b9b; }
            .busca-planos-btn {
                height:48px; padding:0 18px; border-radius:10px; border:none; background:#e09b00; color:#fff; font-weight:700;
                box-shadow:0 6px 18px rgba(0,0,0,0.06); cursor:pointer; white-space:nowrap;
            }
            .busca-planos-btn:hover { background:#c88600; }
            @media (max-width:768px) { .busca-planos-form { flex-direction:column; align-items:stretch; } .busca-planos-btn { width:100%; } }
        </style>

        <div class="busca-planos-form">
            <input type="text" id="buscaPlanos" placeholder="Pesquisar por plano ou cliente..." class="busca-planos-input">
            <button type="button" id="btnBuscarPlanos" class="busca-planos-btn">Pesquisar</button>
        </div>
        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>
    <script>
        (function(){
            const display = document.getElementById('precoPlanoDisplay');
            const hidden = document.getElementById('precoPlano');
            function unformat(value){
                if(!value) return '';
                // remove non numeric except comma and dot
                let v = value.replace(/[^0-9,\.]/g, '');
                v = v.replace(/,/g, '.');
                return v;
            }
            function formatNumber(num){
                return 'Kz ' + Number(num).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
            display.addEventListener('input', function(e){
                const raw = unformat(this.value);
                const n = parseFloat(raw);
                if(!isNaN(n)) {
                    hidden.value = n.toFixed(2);
                } else {
                    hidden.value = '';
                }
            });
            display.addEventListener('blur', function(){
                const raw = unformat(this.value);
                const n = parseFloat(raw);
                if(!isNaN(n)) this.value = formatNumber(n);
            });
            display.addEventListener('focus', function(){
                // show editable raw number when focusing
                const raw = hidden.value;
                if(raw) this.value = raw.replace('.', ',');
                else this.value = '';
            });
            // ensure hidden has value before any submit triggered by other scripts
            const form = document.getElementById('formPlano');
            if(form){
                form.addEventListener('submit', function(){
                    // make sure hidden has plain dot-decimal string
                    const raw = unformat(display.value);
                    const n = parseFloat(raw);
                    if(!isNaN(n)) hidden.value = n.toFixed(2);
                });
            }
        })();
    </script>
@endsection
