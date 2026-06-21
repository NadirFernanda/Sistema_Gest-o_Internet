<style>
/* ── Cards ─────────────────────────────────────────────── */
.pf-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
    padding: 22px 26px 24px;
    margin-bottom: 14px;
}
.pf-card__header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
}
.pf-card__step {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #f5a623;
    color: #fff;
    font-weight: 800;
    font-size: 0.82rem;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pf-card__title {
    font-weight: 700;
    font-size: 0.97rem;
    color: #1a1a2e;
}

/* ── Tipo pills ─────────────────────────────────────────── */
.tipo-pills { display: flex; gap: 10px; flex-wrap: wrap; }
.tipo-pill {
    cursor: pointer;
    border: 2px solid #e6e9ef;
    border-radius: 10px;
    padding: 9px 20px;
    font-size: 0.88rem;
    font-weight: 600;
    color: #666;
    transition: all .15s;
    user-select: none;
    flex: 1; min-width: 110px;
    text-align: center;
}
.tipo-pill input[type=radio] { display: none; }
.tipo-pill:hover { border-color: #f5a623; color: #333; background: #fffbf2; }
.tipo-pill.selected { border-color: #f5a623; background: #fff8ec; color: #d4820a; }

/* ── Template summary ───────────────────────────────────── */
.tpl-summary {
    background: #fff8ec;
    border: 1.5px solid #f5a623;
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 14px;
    display: none;
}
.tpl-summary.visible { display: block; }
.tpl-summary__name { font-weight: 700; font-size: 0.97rem; color: #1a1a2e; }
.tpl-summary__chips {
    display: flex; gap: 10px; flex-wrap: wrap; margin-top: 8px;
}
.tpl-chip {
    background: #fff;
    border: 1px solid #f0d9a8;
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 0.8rem;
    color: #7a5200;
    font-weight: 600;
}
.tpl-desc { font-size: 0.82rem; color: #999; margin-top: 7px; }

/* ── Fields ─────────────────────────────────────────────── */
.pf-field { margin-bottom: 15px; }
.pf-field:last-child { margin-bottom: 0; }
.pf-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    color: #999;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 7px;
}
.pf-input, .pf-select {
    width: 100%;
    height: 46px;
    padding: 0 14px;
    border: 1.5px solid #e8eaf0;
    border-radius: 10px;
    font-size: 0.93rem;
    color: #222;
    background: #fff;
    box-sizing: border-box;
    transition: border-color .15s, box-shadow .15s;
    appearance: none;
    -webkit-appearance: none;
}
.pf-input:focus, .pf-select:focus {
    outline: none;
    border-color: #f5a623;
    box-shadow: 0 0 0 3px rgba(245,166,35,.13);
}
.pf-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aaa' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
    padding-right: 38px;
    cursor: pointer;
}
.pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width:540px) {
    .pf-grid-2 { grid-template-columns: 1fr; }
    .tipo-pill { min-width: 80px; }
}

/* ── Submit ─────────────────────────────────────────────── */
.pf-submit {
    width: 100%;
    height: 54px;
    background: #f5a623;
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    letter-spacing: .02em;
    box-shadow: 0 6px 22px rgba(245,166,35,.32);
    transition: opacity .15s, transform .1s;
    margin-top: 6px;
}
.pf-submit:hover { opacity: .9; }
.pf-submit:active { transform: scale(.99); }
.pf-submit:disabled { opacity: .55; cursor: not-allowed; }

/* ── Alerts ─────────────────────────────────────────────── */
.pf-alert {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.88rem;
    margin-bottom: 14px;
    line-height: 1.5;
}
.pf-alert--success { background: #e8f7ef; border: 1px solid #a8e6c0; color: #1a6b3d; }
.pf-alert--error   { background: #fdecea; border: 1px solid #f5bab5; color: #922b21; }
.pf-alert--warn    { background: #fff8ec; border: 1px solid #f5dba0; color: #7a5200; }
.pf-alert ul { margin: 4px 0 0; padding-left: 18px; }

.reload-btn {
    background: none; border: none; cursor: pointer;
    color: #f5a623; font-size: 13px; padding: 0 3px; vertical-align: middle;
}
</style>

{{-- Flash / validation messages --}}
@if(session('success'))
    <div class="pf-alert pf-alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="pf-alert pf-alert--error">{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="pf-alert pf-alert--warn">
        <ul>@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
    </div>
@endif

<form id="formPlano" method="POST" action="{{ route('planos.store') }}" data-no-ajax="1">
    @csrf

    {{-- Hidden fields: populated by JS from template, used/overridden server-side --}}
    <input type="hidden" id="nomePlano"      name="nome">
    <input type="hidden" id="descricaoPlano" name="descricao">
    <input type="hidden" id="precoPlano"     name="preco">
    <input type="hidden" id="cicloPlano"     name="ciclo">

    {{-- ── Card 1: Tipo ── --}}
    <div class="pf-card">
        <div class="pf-card__header">
            <div class="pf-card__step">1</div>
            <div class="pf-card__title">Tipo de serviço</div>
        </div>
        <div class="tipo-pills" id="tipoPills">
            @foreach(['familiar' => 'Familiar', 'institucional' => 'Institucional', 'empresarial' => 'Empresarial', 'site' => 'Site'] as $val => $label)
                <label class="tipo-pill {{ old('tipo') == $val ? 'selected' : '' }}">
                    <input type="radio" name="tipo" value="{{ $val }}" {{ old('tipo') == $val ? 'checked' : '' }} required>
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- ── Card 2: Modelo ── --}}
    <div class="pf-card">
        <div class="pf-card__header">
            <div class="pf-card__step">2</div>
            <div class="pf-card__title">Modelo de plano</div>
        </div>
        <div class="pf-field">
            <label class="pf-label" for="templateSelector">Selecionar modelo <span style="color:#e05a4f">*</span></label>
            <select id="templateSelector" name="template_id" class="pf-select" required>
                <option value="" disabled selected>— Escolher modelo —</option>
            </select>
        </div>

        {{-- Resumo do modelo seleccionado --}}
        <div class="tpl-summary" id="tplSummary">
            <div class="tpl-summary__name" id="tplName">—</div>
            <div class="tpl-summary__chips">
                <span class="tpl-chip" id="tplPreco">—</span>
                <span class="tpl-chip" id="tplCiclo">— dias</span>
            </div>
            <div class="tpl-desc" id="tplDesc"></div>
        </div>
    </div>

    {{-- ── Card 3: Contrato ── --}}
    <div class="pf-card">
        <div class="pf-card__header">
            <div class="pf-card__step">3</div>
            <div class="pf-card__title">Dados do contrato</div>
        </div>

        <div class="pf-field">
            <label class="pf-label" for="clientePlano">
                Cliente <span style="color:#e05a4f">*</span>
                <button type="button" id="reloadClientesBtn" class="reload-btn" title="Recarregar lista">↺</button>
            </label>
            <select id="clientePlano" name="cliente_id" class="pf-select" required>
                <option value="">Selecionar cliente…</option>
                @if(old('cliente_id'))
                    @php $oldCliente = \App\Models\Cliente::find(old('cliente_id')); @endphp
                    @if($oldCliente)
                        <option value="{{ $oldCliente->id }}" selected>
                            {{ $oldCliente->nome }}{{ $oldCliente->bi ? ' — '.$oldCliente->bi : '' }}
                        </option>
                    @endif
                @endif
            </select>
            <div id="clientesLoadStatus" style="font-size:11px;color:#aaa;margin-top:4px;display:none;"></div>
        </div>

        <div class="pf-grid-2">
            <div class="pf-field">
                <label class="pf-label" for="dataAtivacaoPlano">Data de activação <span style="color:#e05a4f">*</span></label>
                <input type="date" id="dataAtivacaoPlano" name="data_ativacao" class="pf-input" required
                       value="{{ old('data_ativacao', date('Y-m-d')) }}">
            </div>
            <div class="pf-field">
                <label class="pf-label" for="estadoPlano">Estado <span style="color:#e05a4f">*</span></label>
                <select id="estadoPlano" name="estado" class="pf-select" required>
                    <option value="">Escolher estado</option>
                    <option value="Ativo"      {{ old('estado', 'Ativo') == 'Ativo'      ? 'selected' : '' }}>Ativo</option>
                    <option value="Em aviso"   {{ old('estado') == 'Em aviso'   ? 'selected' : '' }}>Em aviso</option>
                    <option value="Suspenso"   {{ old('estado') == 'Suspenso'   ? 'selected' : '' }}>Suspenso</option>
                    <option value="Cancelado"  {{ old('estado') == 'Cancelado'  ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
        </div>
    </div>

    <button type="submit" class="pf-submit" id="btnSubmit">Cadastrar Plano</button>

</form>

<script>
(function () {

    /* ── Tipo pills ─────────────────────────────────────────── */
    document.querySelectorAll('.tipo-pill').forEach(function (pill) {
        pill.addEventListener('click', function () {
            document.querySelectorAll('.tipo-pill').forEach(function (p) { p.classList.remove('selected'); });
            pill.classList.add('selected');
        });
    });

    /* ── Template selector ──────────────────────────────────── */
    var tplSelect  = document.getElementById('templateSelector');
    var tplSummary = document.getElementById('tplSummary');
    var tplName    = document.getElementById('tplName');
    var tplPreco   = document.getElementById('tplPreco');
    var tplCiclo   = document.getElementById('tplCiclo');
    var tplDesc    = document.getElementById('tplDesc');

    function loadTemplates() {
        fetch('{{ route('plan-templates.list.json') }}')
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (list) {
                tplSelect.querySelectorAll('option:not([value=""])').forEach(function (o) { o.remove(); });
                list.forEach(function (t) {
                    var opt = document.createElement('option');
                    opt.value = t.id;
                    opt.textContent = t.name + (t.preco ? ' — Kz ' + Number(t.preco).toLocaleString('pt-AO', { minimumFractionDigits: 2 }) : '');
                    tplSelect.appendChild(opt);
                });
            })
            .catch(function (err) { console.error('Falha ao carregar modelos', err); });
    }

    tplSelect.addEventListener('change', function () {
        var id = this.value;
        if (!id) { tplSummary.classList.remove('visible'); return; }

        fetch('/plan-templates/' + id + '/json')
            .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
            .then(function (t) {
                /* Preencher campos hidden */
                document.getElementById('nomePlano').value      = t.name        || '';
                document.getElementById('descricaoPlano').value = t.description || '';
                document.getElementById('precoPlano').value     = t.preco       ? Number(t.preco).toFixed(2) : '';
                document.getElementById('cicloPlano').value     = t.ciclo       || '';

                /* Actualizar estado se o template tiver default */
                if (t.estado) document.getElementById('estadoPlano').value = t.estado;

                /* Auto-selecionar tipo baseado no template */
                if (t.tipo) {
                    var tipoRadio = document.querySelector('input[name="tipo"][value="' + t.tipo + '"]');
                    if (tipoRadio) {
                        tipoRadio.checked = true;
                        document.querySelectorAll('.tipo-pill').forEach(function (p) { p.classList.remove('selected'); });
                        tipoRadio.closest('.tipo-pill').classList.add('selected');
                    }
                }

                /* Mostrar resumo */
                tplName.textContent  = t.name || '—';
                tplPreco.textContent = t.preco
                    ? 'Kz ' + Number(t.preco).toLocaleString('pt-AO', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    : '—';
                tplCiclo.textContent = (t.ciclo || '—') + ' dias';
                tplDesc.textContent  = t.description || '';
                tplSummary.classList.add('visible');
            })
            .catch(function (err) { console.error('Falha ao carregar modelo', err); });
    });

    loadTemplates();

    /* ── Clientes ────────────────────────────────────────────── */
    var clienteSel  = document.getElementById('clientePlano');
    var clienteSts  = document.getElementById('clientesLoadStatus');
    var reloadBtn   = document.getElementById('reloadClientesBtn');
    var preSelId    = clienteSel ? clienteSel.value : '';

    function loadClientes() {
        if (clienteSts) { clienteSts.textContent = 'A carregar…'; clienteSts.style.display = 'block'; }
        fetch('{{ route('clientes.search.json') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                var choicesInst = window._choicesMap && window._choicesMap['clientePlano'];
                if (choicesInst) {
                    var choices = list.map(function (c) {
                        return { value: String(c.id), label: c.nome + (c.bi ? ' — ' + c.bi : ''), selected: String(c.id) === String(preSelId) };
                    });
                    choicesInst.clearChoices();
                    choicesInst.setChoices(choices, 'value', 'label', true);
                    if (preSelId) choicesInst.setChoiceByValue(String(preSelId));
                } else {
                    var cur = clienteSel.value;
                    Array.from(clienteSel.options).slice(1).forEach(function (o) { o.remove(); });
                    list.forEach(function (c) {
                        var opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nome + (c.bi ? ' — ' + c.bi : '');
                        if (String(c.id) === String(preSelId)) opt.selected = true;
                        clienteSel.appendChild(opt);
                    });
                    if (cur) clienteSel.value = cur;
                }
                if (clienteSts) clienteSts.style.display = 'none';
            })
            .catch(function () {
                if (clienteSts) { clienteSts.textContent = 'Erro ao carregar. Clique ↺'; clienteSts.style.display = 'block'; }
            });
    }

    if (reloadBtn) reloadBtn.addEventListener('click', loadClientes);
    loadClientes();

})();
</script>
