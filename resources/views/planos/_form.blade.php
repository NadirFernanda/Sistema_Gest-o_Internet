<form id="formPlano" class="form-cadastro" method="POST" action="{{ route('planos.store') }}" onsubmit="return true;" data-no-ajax="1">
    @csrf

    <style>
        .form-cadastro{ background:#fff; padding:14px; border-radius:8px; box-shadow:0 6px 20px rgba(0,0,0,0.06); display:block; max-width:640px; margin:0 auto; }
        .form-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:10px; align-items:start; }
        .form-row-full{ grid-column:1/-1; }
        .field-label{ font-size:12px; color:#444; margin-bottom:6px; display:block; }
        .input, .select, .textarea{ width:100%; padding:10px 12px; border:1px solid #e6e6e6; border-radius:8px; font-size:14px; color:#222; background:#fff; box-sizing:border-box; }
        .input:focus, .select:focus, .textarea:focus{ outline:none; border-color:#f7b500; box-shadow:0 6px 18px rgba(247,181,0,0.08); }
        .muted{ color:#666; font-size:13px; }
        .form-actions{ display:flex; justify-content:flex-end; gap:8px; margin-top:12px; }
        .btn-cta{ background:#f7b500; color:#fff; border:none; padding:10px 16px; border-radius:8px; cursor:pointer; box-shadow:0 8px 24px rgba(247,181,0,0.14); }
        @media (max-width:800px){ .form-grid{ grid-template-columns:1fr; } .form-actions{ justify-content:stretch; } .btn-cta{ width:100%; } }
    </style>

    <div class="form-grid">
        <div class="form-row-full">
            <label class="field-label">Usar modelo (opcional)</label>
            <select id="templateSelector" class="select" name="template_id">
                <option value="">-- Usar modelo --</option>
            </select>
            <div id="templateNote" class="muted" style="margin-top:8px;display:none">Campos travados — valores do modelo serão aplicados. Apenas Data de ativação e Estado podem ser editados.</div>
        </div>

        <div>
            <label class="field-label">Cliente</label>
            <select id="clientePlano" name="cliente_id" class="select" required>
                <option value="">Selecione o cliente</option>
                @if(isset($clientes) && count($clientes))
                    @foreach($clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}{{ $c->bi ? ' — ' . $c->bi : '' }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        <div>
            <label class="field-label">Nome do plano</label>
            <input type="text" id="nomePlano" name="nome" class="input" placeholder="Ex: Plano Residencial 10Mbps" required>
        </div>

        <div>
            <label class="field-label">Preço (Kz)</label>
            <input type="hidden" name="preco" id="precoPlano">
            <input type="text" id="precoPlanoDisplay" class="input" placeholder="0,00" required>
        </div>

        <div class="form-row-full">
            <label class="field-label">Descrição</label>
            <input type="text" id="descricaoPlano" name="descricao" class="input" placeholder="Breve descrição do plano" required>
        </div>

        <div>
            <label class="field-label">Ciclo (dias)</label>
            <input type="number" id="cicloPlano" name="ciclo" class="input" placeholder="30" min="1" required>
        </div>

        <div>
            <label class="field-label">Data de ativação</label>
            <input type="date" id="dataAtivacaoPlano" name="data_ativacao" class="input" required>
        </div>

        <div>
            <label class="field-label">Estado</label>
            <select id="estadoPlano" name="estado" class="select" required>
                <option value="">Escolha o estado</option>
                <option value="Ativo">Ativo</option>
                <option value="Em aviso">Em aviso</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Cancelado">Cancelado</option>
            </select>
        </div>

        <div class="form-row-full form-actions">
            <button type="submit" class="btn-cta">Cadastrar Plano</button>
        </div>
    </div>
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
                    .then(r => {
                        if(!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(list => {
                        tplSelect.querySelectorAll('option:not([value=""])').forEach(o => o.remove());
                        list.forEach(t => {
                            const opt = document.createElement('option');
                            opt.value = t.id;
                            opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2}) : '');
                            tplSelect.appendChild(opt);
                        });
                    }).catch(err => { console.error('loadTemplates failed', err); });
            };
            tplSelect.addEventListener('change', function(){
                const id = this.value;
                if(!id) return;
                fetch(`/plan-templates/${id}/json`)
                    .then(r => {
                        if(!r.ok) throw new Error('HTTP ' + r.status);
                        return r.json();
                    })
                    .then(t => {
                        const nome = document.getElementById('nomePlano');
                        const desc = document.getElementById('descricaoPlano');
                        const precoHidden = document.getElementById('precoPlano');
                        const precoDisplay = document.getElementById('precoPlanoDisplay');
                        const ciclo = document.getElementById('cicloPlano');
                        const estado = document.getElementById('estadoPlano');
                        const note = document.getElementById('templateNote');

                        if(t.name) nome.value = t.name;
                        if(t.description) desc.value = t.description;
                        if(t.preco){
                            precoHidden.value = Number(t.preco).toFixed(2);
                            precoDisplay.value = 'Kz ' + Number(t.preco).toLocaleString('pt-AO', {minimumFractionDigits:2, maximumFractionDigits:2});
                        }
                        if(t.ciclo) ciclo.value = t.ciclo;
                        if(t.estado) estado.value = t.estado;

                        // Lock fields to prevent edits (activation date and estado remain editable)
                        nome.readOnly = true;
                        desc.readOnly = true;
                        precoDisplay.readOnly = true;
                        ciclo.disabled = true;
                        if(note) note.style.display = 'block';
                    }).catch(err => { console.error('loadTemplate by id failed', err); });
            // when user clears the template, re-enable fields
            tplSelect.addEventListener('change', function(){
                if(!this.value){
                    const nome = document.getElementById('nomePlano');
                    const desc = document.getElementById('descricaoPlano');
                    const precoDisplay = document.getElementById('precoPlanoDisplay');
                    const ciclo = document.getElementById('cicloPlano');
                    const note = document.getElementById('templateNote');
                    if(nome) nome.readOnly = false;
                    if(desc) desc.readOnly = false;
                    if(precoDisplay) precoDisplay.readOnly = false;
                    if(ciclo) ciclo.disabled = false;
                    if(note) note.style.display = 'none';
                }
            });
            });
            window.loadTemplates();
        })();

    })();
</script>
