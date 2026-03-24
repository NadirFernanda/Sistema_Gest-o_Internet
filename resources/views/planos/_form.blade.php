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

    {{-- Flash / validation messages --}}
    @if(session('success'))
        <div style="background:#e6ffea;border:1px solid #b7f0c6;padding:10px;border-radius:6px;margin-bottom:12px;color:#1a7f3a">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background:#fff4f4;border:1px solid #f5c0c0;padding:10px;border-radius:6px;margin-bottom:12px;color:#8a1a1a">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div style="background:#fff8e6;border:1px solid #ffe4b3;padding:10px;border-radius:6px;margin-bottom:12px;color:#7a5600">
            <ul style="margin:0;padding-left:18px">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-grid">

        <div class="form-row-full">
            <label class="field-label">Tipo do plano <span style="color:#d00">*</span></label>
            <select name="tipo" class="select" required>
                <option value="">Selecione o tipo</option>
                <option value="familiar" {{ (old('tipo') ?? (isset($plano) ? $plano->tipo : null)) == 'familiar' ? 'selected' : '' }}>Familiar</option>
                <option value="institucional" {{ (old('tipo') ?? (isset($plano) ? $plano->tipo : null)) == 'institucional' ? 'selected' : '' }}>Institucional</option>
                <option value="empresarial" {{ (old('tipo') ?? (isset($plano) ? $plano->tipo : null)) == 'empresarial' ? 'selected' : '' }}>Empresarial</option>
                <option value="site" {{ (old('tipo') ?? (isset($plano) ? $plano->tipo : null)) == 'site' ? 'selected' : '' }}>Site</option>
            </select>
        </div>
        <div class="form-row-full">
            <label class="field-label">Usar plano (opcional)</label>
            <select id="templateSelector" class="select" name="template_id" data-placeholder="Pesquisar modelos de plano..." required>
                <option value="" disabled selected>-- Selecionar modelo (obrigatório) --</option>
            </select>
            <div id="templateNote" class="muted" style="margin-top:8px;display:none">Campos travados — valores do plano serão aplicados. Apenas Data de ativação e Estado podem ser editados.</div>
        </div>

        <div>
            <label class="field-label">Cliente <span style="color:#d00">*</span>
                <button type="button" id="reloadClientesBtn" title="Recarregar lista de clientes"
                    style="background:none;border:none;cursor:pointer;color:#f7b500;font-size:13px;padding:0 4px;vertical-align:middle;">↺</button>
            </label>
            <select id="clientePlano" name="cliente_id" class="select" required data-placeholder="Pesquisar cliente...">
                <option value="">Selecione o cliente</option>
                {{-- Manter o cliente pré-selecionado após re-render por validação --}}
                @if(old('cliente_id'))
                    @php $oldCliente = \App\Models\Cliente::find(old('cliente_id')); @endphp
                    @if($oldCliente)
                        <option value="{{ $oldCliente->id }}" selected>{{ $oldCliente->nome }}{{ $oldCliente->bi ? ' — ' . $oldCliente->bi : '' }}</option>
                    @endif
                @endif
            </select>
            <div id="clientesLoadStatus" style="font-size:11px;color:#888;margin-top:3px;display:none;"></div>
        </div>

        <div>
            <label class="field-label">Nome do plano</label>
            <input type="text" id="nomePlano" name="nome" class="input" placeholder="Ex: Plano Residencial 10Mbps" required value="{{ old('nome') }}" readonly>
        </div>

        <div>
            <label class="field-label">Preço (Kz)</label>
            <input type="hidden" name="preco" id="precoPlano" value="{{ old('preco') }}">
            <input type="text" id="precoPlanoDisplay" class="input" placeholder="0,00" required value="{{ old('preco') ? old('preco') : '' }}" readonly>
        </div>

        <div class="form-row-full">
            <label class="field-label">Descrição</label>
            <input type="text" id="descricaoPlano" name="descricao" class="input" placeholder="Breve descrição do plano" required value="{{ old('descricao') }}" readonly>
        </div>

        <div>
            <label class="field-label">Ciclo (dias)</label>
            <input type="number" id="cicloPlano" name="ciclo" class="input" placeholder="30" min="1" required value="{{ old('ciclo', 30) }}" disabled>
        </div>

        <div>
            <label class="field-label">Data de ativação</label>
            <input type="date" id="dataAtivacaoPlano" name="data_ativacao" class="input" required value="{{ old('data_ativacao') }}">
        </div>

        <div>
            <label class="field-label">Estado</label>
            <select id="estadoPlano" name="estado" class="select" required data-placeholder="Filtrar por estado...">
                <option value="">Escolha o estado</option>
                <option value="Ativo" {{ old('estado') == 'Ativo' ? 'selected' : '' }}>Ativo</option>
                <option value="Em aviso" {{ old('estado') == 'Em aviso' ? 'selected' : '' }}>Em aviso</option>
                <option value="Suspenso" {{ old('estado') == 'Suspenso' ? 'selected' : '' }}>Suspenso</option>
                <option value="Cancelado" {{ old('estado') == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                <option value="Site" {{ old('estado') == 'Site' ? 'selected' : '' }}>Site</option>
                <option value="Agente Autorizado" {{ old('estado') == 'Agente Autorizado' ? 'selected' : '' }}>Agente Autorizado</option>
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

        // Carregar modelos de plano no seletor (se houver modelos)
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
                    }).catch(err => { console.error('Falha ao carregar modelos', err); });
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

    // ── Carregamento dinâmico de clientes ──────────────────────────────────
    (function(){
        const sel = document.getElementById('clientePlano');
        const status = document.getElementById('clientesLoadStatus');
        const reloadBtn = document.getElementById('reloadClientesBtn');
        if(!sel) return;

        const preSelectedId = sel.value; // valor pre-selecionado via old()

        function loadClientes(){
            if(status){ status.textContent = 'A carregar clientes…'; status.style.display='block'; }
            fetch('{{ route('clientes.search.json') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(function(list){
                    const choicesInst = window._choicesMap && window._choicesMap['clientePlano'];
                    if(choicesInst){
                        const choices = list.map(function(c){
                            return { value: String(c.id), label: c.nome + (c.bi ? ' — ' + c.bi : ''), selected: String(c.id) === String(preSelectedId) };
                        });
                        choicesInst.clearChoices();
                        choicesInst.setChoices(choices, 'value', 'label', true);
                        if(preSelectedId){ choicesInst.setChoiceByValue(String(preSelectedId)); }
                    } else {
                        // fallback: preencher select nativo
                        const currentVal = sel.value;
                        Array.from(sel.options).slice(1).forEach(o => o.remove()); // keep placeholder
                        list.forEach(function(c){
                            const opt = document.createElement('option');
                            opt.value = c.id;
                            opt.textContent = c.nome + (c.bi ? ' — ' + c.bi : '');
                            if(String(c.id) === String(preSelectedId)) opt.selected = true;
                            sel.appendChild(opt);
                        });
                        if(currentVal) sel.value = currentVal;
                    }
                    if(status){ status.style.display='none'; }
                })
                .catch(function(){
                    if(status){ status.textContent = 'Erro ao carregar clientes. Tente recarregar (↺).'; status.style.display='block'; }
                });
        }

        if(reloadBtn) reloadBtn.addEventListener('click', loadClientes);
        // Carrega sempre ao abrir a página — lista sempre fresca independentemente de quando o cliente foi criado
        loadClientes();
    })();

    })();
</script>
