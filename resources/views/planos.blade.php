@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Planos</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
        <form id="formPlano" class="form-cadastro">
            <select id="templateSelector">
                <option value="">Usar modelo (opcional)</option>
            </select>
            
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
        <div class="busca-planos-form" style="margin:12px 0 4px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input
                type="text"
                id="buscaPlanos"
                placeholder="Pesquisar por plano ou cliente..."
                style="flex:1; min-width:220px; padding:8px 10px; border-radius:8px; border:1px solid #ccc;"
            >
            <button
                type="button"
                id="btnBuscarPlanos"
                style="padding:8px 16px; border-radius:8px; border:none; background:#f7b500; color:#fff; cursor:pointer; white-space:nowrap;"
            >
                Pesquisar
            </button>
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
            // load templates and hook selector
            (function(){
                const tplSelect = document.getElementById('templateSelector');
                if(!tplSelect) return;
                fetch('{{ route('plan-templates.list.json') }}')
                    .then(r => r.json())
                    .then(list => {
                        list.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2}) : '');
                            tplSelect.appendChild(opt);
                        });
                    }).catch(()=>{});

                tplSelect.addEventListener('change', function(){
                    const id = this.value;
                    if(!id) return;
                    fetch(`/plan-templates/${id}/json`)
                        .then(r => r.json())
                        .then(t => {
                            if(t.name) document.getElementById('nomePlano').value = t.name;
                            if(t.description) document.getElementById('descricaoPlano').value = t.description;
                            if(t.preco){
                                document.getElementById('precoPlano').value = Number(t.preco).toFixed(2);
                                document.getElementById('precoPlanoDisplay').value = 'Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2});
                            }
                            if(t.ciclo) document.getElementById('cicloPlano').value = t.ciclo;
                            if(t.estado) document.getElementById('estadoPlano').value = t.estado;
                        }).catch(()=>{});
                });
            })();
        })();
    </script>
@endsection
