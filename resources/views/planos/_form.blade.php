<form id="formPlano" class="form-cadastro" method="POST" action="{{ route('planos.store') }}">
    @csrf
    <select id="templateSelector" class="select" name="template_id">
        <option value="">Usar modelo (opcional)</option>
    </select>

    <select id="clientePlano" name="cliente_id" class="select" required>
        <option value="">Selecione o cliente</option>
        @if(isset($clientes) && count($clientes))
            @foreach($clientes as $c)
                <option value="{{ $c->id }}">{{ $c->nome }}{{ $c->bi ? ' — ' . $c->bi : '' }}</option>
            @endforeach
        @endif
    </select>
    <input type="text" id="nomePlano" name="nome" placeholder="Nome do plano" required>
    <input type="text" id="descricaoPlano" name="descricao" placeholder="Descrição" required>
    <input type="hidden" name="preco" id="precoPlano">
    <input type="text" id="precoPlanoDisplay" placeholder="Preço (Kz)" required>
    <input type="number" id="cicloPlano" name="ciclo" placeholder="Ciclo de serviço (dias)" min="1" required>
    <input type="date" id="dataAtivacaoPlano" name="data_ativacao" placeholder="Data de ativação" required>
    <select id="estadoPlano" name="estado" required>
        <option value="">Estado do plano</option>
        <option value="Ativo">Ativo</option>
        <option value="Em aviso">Em aviso</option>
        <option value="Suspenso">Suspenso</option>
        <option value="Cancelado">Cancelado</option>
    </select>
    <button type="submit" class="btn btn-cta">Cadastrar Plano</button>
</form>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    (function(){
        const display = document.getElementById('precoPlanoDisplay');
        const hidden = document.getElementById('precoPlano');
        if (!display) return;
        function unformat(value){
            if(!value) return '';
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
            const raw = hidden.value;
            if(raw) this.value = raw.replace('.', ',');
            else this.value = '';
        });
        const form = document.getElementById('formPlano');
        if(form){
            form.addEventListener('submit', function(e){
                const raw = unformat(display.value);
                const n = parseFloat(raw);
                if(!isNaN(n)) hidden.value = n.toFixed(2);
            });
        }

        // Load templates into selector (if plan templates present)
        (function(){
            const tplSelect = document.getElementById('templateSelector');
            if(!tplSelect) return;
            window.loadTemplates = function(){
                fetch('{{ route('plan-templates.list.json') }}')
                    .then(r => r.json())
                    .then(list => {
                        tplSelect.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
                        list.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2}) : '');
                            tplSelect.appendChild(opt);
                        });
                    }).catch(()=>{});
            };
            tplSelect.addEventListener('change', function(){
                const id = this.value;
                if(!id) return;
                fetch(`/plan-templates/${id}/json`).then(r => r.json()).then(t => {
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
            window.loadTemplates();
        })();

    })();
</script>
