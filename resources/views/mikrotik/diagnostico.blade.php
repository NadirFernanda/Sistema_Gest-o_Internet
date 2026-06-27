@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
<style>
.diag-wrap  { max-width:1140px; margin:0 auto; padding:0 16px 48px; }
.diag-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:24px 0 18px;
}
.diag-header h1 { font-size:1.35rem; font-weight:800; color:#1a1a2e; margin:0; }
.diag-header p  { font-size:0.83rem; color:#999; margin:4px 0 0; }
.back-link { font-size:0.83rem; color:#888; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
.back-link:hover { color:#444; }

/* ─── Site block ─── */
.site-block { margin-bottom:32px; }
.site-banner {
    background:#fff; border-radius:12px 12px 0 0;
    border-left:5px solid #f5a623;
    box-shadow:0 2px 12px rgba(0,0,0,.07);
    padding:14px 20px;
    display:flex; align-items:center; gap:16px; flex-wrap:wrap;
}
.site-banner__name { font-weight:800; font-size:1rem; color:#1a1a2e; }
.site-banner__host { font-size:0.78rem; color:#aaa; font-family:monospace; }
.site-banner__stats { display:flex; gap:10px; flex-wrap:wrap; margin-left:auto; align-items:center; }
.stat-pill {
    display:inline-flex; align-items:center; gap:5px;
    padding:4px 12px; border-radius:20px; font-size:0.8rem; font-weight:700;
}
.sp-total   { background:#f0f4f9; color:#555; }
.sp-ok      { background:#e8f7ef; color:#2a8a55; }
.sp-warn    { background:#fdecea; color:#c0392b; }
.sp-neutral { background:#f7f8fa; color:#888; }

.btn-check-router {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:8px;
    background:#4a90d9; color:#fff; font-size:0.83rem; font-weight:700;
    border:none; cursor:pointer; transition:opacity .15s; white-space:nowrap;
}
.btn-check-router:hover { opacity:.85; }
.btn-check-router:disabled { opacity:.5; cursor:default; }

/* ─── Tables ─── */
.diag-section { background:#fff; border-top:1px solid #f0f2f6; }
.diag-section:last-child { border-radius:0 0 12px 12px; }
.diag-section-head {
    display:flex; align-items:center; gap:10px;
    padding:12px 20px; border-bottom:1px solid #f2f4f7;
}
.diag-section-head h4 { font-size:0.88rem; font-weight:700; color:#333; margin:0; }
.count-badge {
    background:#f0f2f6; color:#666; font-size:0.74rem; font-weight:700;
    padding:2px 9px; border-radius:12px;
}
.count-badge.red   { background:#fdecea; color:#c0392b; }
.count-badge.green { background:#e8f7ef; color:#2a8a55; }

.diag-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
.diag-table thead { background:#f7f9fb; }
.diag-table th {
    padding:8px 14px; text-align:left; font-size:0.71rem; font-weight:700;
    color:#aaa; text-transform:uppercase; letter-spacing:.05em;
    border-bottom:2px solid #edf0f4; white-space:nowrap;
}
.diag-table td { padding:9px 14px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
.diag-table tbody tr:last-child td { border-bottom:none; }
.diag-table tbody tr:hover { background:#fafbfd; }
.diag-table tbody tr.selected { background:#f0f6ff; }

.empty-note { padding:20px 14px; text-align:center; color:#bbb; font-size:0.85rem; }

.ebadge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; }
.eb-ativo    { background:#e8f7ef; color:#2a8a55; }
.eb-suspenso { background:#fdecea; color:#c0392b; }
.eb-aviso    { background:#fef9e7; color:#b7770d; }

code.uname {
    background:#f4f6f9; padding:2px 8px; border-radius:5px;
    font-size:0.82rem; color:#555; font-family:monospace;
}
code.uname.missing { background:#fdecea; color:#c0392b; font-style:italic; }
code.uname.orphan  { background:#fff8e1; color:#7a5c00; }

/* ─── Router check loading/error ─── */
.router-check-area { padding:0; }
.router-loading { padding:16px 20px; font-size:0.84rem; color:#888; display:none; }
.router-error   { padding:12px 20px; font-size:0.83rem; color:#c0392b; background:#fdecea; display:none; }

/* ─── Bulk fix ─── */
.fix-cb {
    width:16px; height:16px; cursor:pointer;
    accent-color:#4a90d9; flex-shrink:0;
}
.fix-input {
    width:150px; padding:5px 8px;
    border:1px solid #dde1e9; border-radius:6px;
    font-size:0.82rem; font-family:monospace; color:#333; background:#f9fafc;
    transition:border-color .12s;
}
.fix-input:focus { outline:none; border-color:#4a90d9; background:#fff; box-shadow:0 0 0 2px rgba(74,144,217,.12); }
.fix-input::placeholder { color:#ccc; }
.fix-select {
    padding:5px 8px; border:1px solid #dde1e9; border-radius:6px;
    font-size:0.82rem; color:#333; background:#f9fafc;
    min-width:150px; max-width:220px;
}
.fix-select:focus { outline:none; border-color:#4a90d9; }

.bulk-bar {
    display:none; align-items:center; justify-content:space-between;
    padding:10px 20px; background:#eef5ff;
    border-top:1px solid #cde0f9; flex-wrap:wrap; gap:10px;
}
.bulk-bar.visible { display:flex; }
.bulk-bar-left { display:flex; align-items:center; gap:12px; }
.bulk-count { font-size:0.83rem; color:#4a90d9; font-weight:700; }
.btn-bulk-fix {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 18px; border-radius:8px;
    background:#27ae60; color:#fff; font-size:0.83rem; font-weight:700;
    border:none; cursor:pointer; transition:opacity .15s;
}
.btn-bulk-fix:hover:not(:disabled) { opacity:.85; }
.btn-bulk-fix:disabled { opacity:.45; cursor:default; }
.bulk-result { font-size:0.82rem; font-weight:700; padding:4px 12px; border-radius:6px; display:none; }
.bulk-result.ok  { background:#e8f7ef; color:#2a8a55; }
.bulk-result.err { background:#fdecea; color:#c0392b; }
</style>
@endpush

@section('content')
<div class="diag-wrap">
    <div class="diag-header">
        <div>
            <h1>Diagnóstico PPPoE — Associação de Usernames</h1>
            <p>Clientes sem username configurado não podem ser suspensos/reactivados automaticamente.</p>
        </div>
        <a href="{{ route('mikrotik.index') }}" class="back-link">&#8592; Voltar ao painel</a>
    </div>

    @php $bulkUrl = route('mikrotik.diagnostico.bulk-fix') @endphp

    @foreach($dadosPorSite as $siteId => $dados)
    @php
        $site        = $dados['site'];
        $semUsername = $dados['semUsername'];
        $totalPlanos = $dados['totalPlanos'];
        $nSemUser    = $semUsername->count();
        $routerUrl   = route('mikrotik.sites.diagnostico-router', $site);
    @endphp

    <div class="site-block" data-site-id="{{ $site->id }}">

        {{-- Banner do site --}}
        <div class="site-banner">
            <div>
                <div class="site-banner__name">{{ $site->nome }}</div>
                <div class="site-banner__host">{{ $site->host }}</div>
            </div>
            <div class="site-banner__stats">
                <span class="stat-pill sp-total">{{ $totalPlanos }} planos</span>
                @if($nSemUser > 0)
                    <span class="stat-pill sp-warn">&#9888; {{ $nSemUser }} sem username</span>
                @else
                    <span class="stat-pill sp-ok">&#10003; Todos com username</span>
                @endif
                <button class="btn-check-router"
                        onclick="verificarRouter({{ $site->id }}, '{{ $routerUrl }}')"
                        id="btn-router-{{ $site->id }}">
                    &#128268; Verificar no router
                </button>
            </div>
        </div>

        {{-- Secção 1: sem username (BD only) --}}
        <div class="diag-section">
            <div class="diag-section-head">
                <h4>Sem username configurado</h4>
                <span class="count-badge {{ $nSemUser > 0 ? 'red' : 'green' }}">{{ $nSemUser }}</span>
                <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">detectado na base de dados</span>
                @if($nSemUser > 0)
                <label style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;color:#666;cursor:pointer;user-select:none;">
                    <input type="checkbox" class="fix-cb" id="chk-all-sem-{{ $site->id }}"
                           onchange="toggleAllSem({{ $site->id }}, this.checked)">
                    Seleccionar todos
                </label>
                @endif
            </div>
            @if($semUsername->isNotEmpty())
            <table class="diag-table" id="tbl-sem-{{ $site->id }}">
                <thead>
                    <tr>
                        <th style="width:28px;"></th>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Estado</th>
                        <th>Renova&ccedil;&atilde;o</th>
                        <th>Novo username</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semUsername as $plano)
                    @php $tel = preg_replace('/\D/', '', $plano->cliente?->contato ?? '') @endphp
                    <tr id="sem-row-{{ $plano->id }}">
                        <td>
                            <input type="checkbox" class="fix-cb sem-cb-{{ $site->id }}"
                                   data-plano-id="{{ $plano->id }}"
                                   onchange="updateBulkBarSem({{ $site->id }})">
                        </td>
                        <td style="font-weight:600;color:#222;">{{ $plano->cliente?->nome ?? '—' }}</td>
                        <td><code class="uname">{{ $tel ?: '—' }}</code></td>
                        <td>
                            @php $est = $plano->estado @endphp
                            <span class="ebadge {{ $est === 'Ativo' ? 'eb-ativo' : ($est === 'Suspenso' ? 'eb-suspenso' : 'eb-aviso') }}">
                                {{ $est }}
                            </span>
                        </td>
                        <td style="font-size:0.82rem;color:#777;">
                            {{ $plano->proxima_renovacao ? \Carbon\Carbon::parse($plano->proxima_renovacao)->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <input type="text" class="fix-input sem-input-{{ $site->id }}"
                                   id="sem-input-{{ $plano->id }}"
                                   data-plano-id="{{ $plano->id }}"
                                   placeholder="{{ $tel ?: 'ex: 9XXXXXXXX' }}"
                                   value="{{ $tel }}"
                                   oninput="updateBulkBarSem({{ $site->id }})">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Barra de acção bulk para "sem username" --}}
            <div class="bulk-bar" id="bulk-bar-sem-{{ $site->id }}">
                <div class="bulk-bar-left">
                    <span class="bulk-count" id="bulk-count-sem-{{ $site->id }}">0 seleccionados</span>
                    <button class="btn-bulk-fix" id="btn-bulk-sem-{{ $site->id }}"
                            onclick="corrigirSeleccionados('sem', {{ $site->id }}, '{{ $bulkUrl }}')">
                        &#10003; Corrigir seleccionados
                    </button>
                </div>
                <span class="bulk-result" id="bulk-result-sem-{{ $site->id }}"></span>
            </div>
            @else
            <div class="empty-note">&#10003; Nenhum plano sem username neste site.</div>
            @endif
        </div>

        {{-- Secção 2: resultado da verificação no router (carregado via AJAX) --}}
        <div class="diag-section router-check-area" id="router-area-{{ $site->id }}" style="display:none;">
            <div class="router-loading" id="router-loading-{{ $site->id }}">
                A consultar o router&hellip; (pode demorar alguns segundos)
            </div>
            <div class="router-error" id="router-error-{{ $site->id }}"></div>

            {{-- Planos com username inválido --}}
            <div id="router-invalid-{{ $site->id }}" style="display:none;">
                <div class="diag-section-head" style="border-top:1px solid #f2f4f7;">
                    <h4>Username configurado mas n&atilde;o existe no router</h4>
                    <span class="count-badge red" id="router-invalid-count-{{ $site->id }}">0</span>
                    <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">seleccione o secret correcto e clique Corrigir</span>
                    <label style="margin-left:auto;display:inline-flex;align-items:center;gap:6px;font-size:0.8rem;color:#666;cursor:pointer;user-select:none;">
                        <input type="checkbox" class="fix-cb" id="chk-all-inv-{{ $site->id }}"
                               onchange="toggleAllInv({{ $site->id }}, this.checked)">
                        Seleccionar todos
                    </label>
                </div>
                <table class="diag-table">
                    <thead>
                        <tr>
                            <th style="width:28px;"></th>
                            <th>Cliente</th>
                            <th>Telefone</th>
                            <th>Username actual (errado)</th>
                            <th>Novo username (secret do router)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="router-invalid-body-{{ $site->id }}"></tbody>
                </table>
                {{-- Barra de acção bulk para "username errado" --}}
                <div class="bulk-bar" id="bulk-bar-inv-{{ $site->id }}">
                    <div class="bulk-bar-left">
                        <span class="bulk-count" id="bulk-count-inv-{{ $site->id }}">0 seleccionados</span>
                        <button class="btn-bulk-fix" id="btn-bulk-inv-{{ $site->id }}"
                                onclick="corrigirSeleccionados('inv', {{ $site->id }}, '{{ $bulkUrl }}')">
                            &#10003; Corrigir seleccionados
                        </button>
                    </div>
                    <span class="bulk-result" id="bulk-result-inv-{{ $site->id }}"></span>
                </div>
            </div>

            {{-- Secrets órfãos --}}
            <div id="router-orphan-{{ $site->id }}" style="display:none;">
                <div class="diag-section-head" style="border-top:1px solid #f2f4f7;">
                    <h4>Secrets no router sem plano associado</h4>
                    <span class="count-badge" id="router-orphan-count-{{ $site->id }}">0</span>
                    <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">podem ser usados como username para os planos acima</span>
                </div>
                <table class="diag-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Perfil</th>
                            <th>Desactivado</th>
                            <th>Coment&aacute;rio</th>
                        </tr>
                    </thead>
                    <tbody id="router-orphan-body-{{ $site->id }}"></tbody>
                </table>
            </div>

            {{-- Resumo OK --}}
            <div id="router-ok-{{ $site->id }}" style="display:none;" class="empty-note"></div>
        </div>

    </div>{{-- .site-block --}}
    @endforeach
</div>
@endsection

@push('scripts')
<script>
const _csrf = document.querySelector('meta[name=csrf-token]').content;

// ──────────────────────────────────────────────
// Verificar router (AJAX)
// ──────────────────────────────────────────────
function verificarRouter(siteId, url) {
    const btn      = document.getElementById('btn-router-' + siteId);
    const area     = document.getElementById('router-area-' + siteId);
    const loading  = document.getElementById('router-loading-' + siteId);
    const errBox   = document.getElementById('router-error-' + siteId);

    btn.disabled        = true;
    btn.textContent     = 'A verificar…';
    area.style.display  = '';
    loading.style.display = '';
    errBox.style.display  = 'none';

    // Ocultar secções de resultado anteriores
    ['router-invalid-', 'router-orphan-', 'router-ok-'].forEach(p => {
        const el = document.getElementById(p + siteId);
        if (el) el.style.display = 'none';
    });

    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': _csrf } })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            loading.style.display = 'none';

            if (!ok) {
                errBox.textContent   = '⚠ ' + (data.error || 'Erro ao contactar o router.');
                errBox.style.display = '';
                btn.disabled         = false;
                btn.textContent      = '🔌 Tentar novamente';
                return;
            }

            const orphanList  = data.secretsOrfaos || [];
            const invalidList = data.planosUsernameErrado || [];

            // ── Planos com username inválido ──
            const invalidWrap  = document.getElementById('router-invalid-' + siteId);
            const invalidBody  = document.getElementById('router-invalid-body-' + siteId);
            const invalidCount = document.getElementById('router-invalid-count-' + siteId);

            if (invalidList.length > 0) {
                invalidCount.textContent = invalidList.length;

                // Opções de select: secrets órfãos + placeholder
                const optionsHtml = '<option value="">— escolher secret —</option>' +
                    orphanList.map(s =>
                        `<option value="${esc(s.name)}">${esc(s.name)}${s.disabled==='yes' ? ' (desact.)' : ''}</option>`
                    ).join('');

                invalidBody.innerHTML = invalidList.map(p => `
                    <tr id="inv-row-${p.plano_id}">
                        <td><input type="checkbox" class="fix-cb inv-cb-${siteId}"
                                   data-plano-id="${p.plano_id}"
                                   onchange="updateBulkBarInv(${siteId})"></td>
                        <td style="font-weight:600;color:#222;">${esc(p.cliente_nome)}</td>
                        <td><code class="uname">${esc(p.cliente_tel || '—')}</code></td>
                        <td><code class="uname missing">${esc(p.mikrotik_username)}</code></td>
                        <td>
                            <select class="fix-select inv-select-${siteId}" id="inv-sel-${p.plano_id}"
                                    data-plano-id="${p.plano_id}"
                                    onchange="updateBulkBarInv(${siteId})">
                                ${optionsHtml}
                            </select>
                        </td>
                        <td><span class="ebadge ${ p.estado==='Ativo' ? 'eb-ativo' : (p.estado==='Suspenso' ? 'eb-suspenso' : 'eb-aviso') }">${esc(p.estado)}</span></td>
                    </tr>`).join('');

                invalidWrap.style.display = '';
            }

            // ── Secrets órfãos ──
            const orphanWrap  = document.getElementById('router-orphan-' + siteId);
            const orphanBody  = document.getElementById('router-orphan-body-' + siteId);
            const orphanCount = document.getElementById('router-orphan-count-' + siteId);

            if (orphanList.length > 0) {
                orphanCount.textContent = orphanList.length;
                orphanBody.innerHTML = orphanList.map(s => `
                    <tr>
                        <td><code class="uname orphan">${esc(s.name)}</code></td>
                        <td style="color:#666;">${esc(s.profile || '—')}</td>
                        <td style="color:${ s.disabled==='yes' ? '#c0392b' : '#2a8a55' };font-weight:700;">
                            ${ s.disabled==='yes' ? 'Sim' : 'Não' }
                        </td>
                        <td style="color:#888;font-size:0.8rem;">${esc(s.comment || '—')}</td>
                    </tr>`).join('');
                orphanWrap.style.display = '';
            }

            // Injectar secrets órfãos como sugestões nos inputs "sem username" deste site
            if (orphanList.length > 0) {
                const datalistId = 'orphan-dl-' + siteId;
                let dl = document.getElementById(datalistId);
                if (!dl) {
                    dl = document.createElement('datalist');
                    dl.id = datalistId;
                    document.body.appendChild(dl);
                }
                dl.innerHTML = orphanList.map(s => `<option value="${esc(s.name)}">`).join('');
                // Ligar datalist a todos os inputs "sem username" deste site
                document.querySelectorAll('.sem-input-' + siteId).forEach(inp => {
                    inp.setAttribute('list', datalistId);
                    inp.placeholder = 'escolher ou digitar…';
                });
            }

            // ── Resumo OK ──
            const okDiv    = document.getElementById('router-ok-' + siteId);
            const planosOk = data.planosOk || 0;
            if (invalidList.length === 0 && orphanList.length === 0) {
                okDiv.innerHTML = `✓ Tudo correcto — ${planosOk} plano(s) com username válido no router.`;
                okDiv.style.display = '';
            } else {
                okDiv.innerHTML = `✓ ${planosOk} plano(s) correctamente associados | ${data.totalSecrets} secrets no router.`;
                okDiv.style.display = '';
            }

            btn.disabled    = false;
            btn.textContent = '🔌 Verificar novamente';
        })
        .catch(err => {
            loading.style.display  = 'none';
            errBox.textContent     = '⚠ Erro de rede: ' + err.message;
            errBox.style.display   = '';
            btn.disabled           = false;
            btn.textContent        = '🔌 Tentar novamente';
        });
}

// ──────────────────────────────────────────────
// Select All helpers
// ──────────────────────────────────────────────
function toggleAllSem(siteId, checked) {
    document.querySelectorAll('.sem-cb-' + siteId).forEach(cb => { cb.checked = checked; });
    updateBulkBarSem(siteId);
}

function toggleAllInv(siteId, checked) {
    document.querySelectorAll('.inv-cb-' + siteId).forEach(cb => { cb.checked = checked; });
    updateBulkBarInv(siteId);
}

// ──────────────────────────────────────────────
// Bulk bar update (sem username)
// ──────────────────────────────────────────────
function updateBulkBarSem(siteId) {
    const checked = [...document.querySelectorAll('.sem-cb-' + siteId)].filter(c => c.checked);
    const bar     = document.getElementById('bulk-bar-sem-' + siteId);
    const countEl = document.getElementById('bulk-count-sem-' + siteId);
    const btn     = document.getElementById('btn-bulk-sem-' + siteId);

    const allHaveValue = checked.every(cb => {
        const inp = document.getElementById('sem-input-' + cb.dataset.planoId);
        return inp && inp.value.trim() !== '';
    });

    countEl.textContent = checked.length + ' seleccionado' + (checked.length !== 1 ? 's' : '');
    btn.disabled        = checked.length === 0 || !allHaveValue;

    if (checked.length > 0) {
        bar.classList.add('visible');
    } else {
        bar.classList.remove('visible');
        document.getElementById('bulk-result-sem-' + siteId).style.display = 'none';
    }
}

// ──────────────────────────────────────────────
// Bulk bar update (username errado)
// ──────────────────────────────────────────────
function updateBulkBarInv(siteId) {
    const checked = [...document.querySelectorAll('.inv-cb-' + siteId)].filter(c => c.checked);
    const bar     = document.getElementById('bulk-bar-inv-' + siteId);
    const countEl = document.getElementById('bulk-count-inv-' + siteId);
    const btn     = document.getElementById('btn-bulk-inv-' + siteId);

    const allHaveValue = checked.every(cb => {
        const sel = document.getElementById('inv-sel-' + cb.dataset.planoId);
        return sel && sel.value.trim() !== '';
    });

    countEl.textContent = checked.length + ' seleccionado' + (checked.length !== 1 ? 's' : '');
    btn.disabled        = checked.length === 0 || !allHaveValue;

    if (checked.length > 0) {
        bar.classList.add('visible');
    } else {
        bar.classList.remove('visible');
        document.getElementById('bulk-result-inv-' + siteId).style.display = 'none';
    }
}

// ──────────────────────────────────────────────
// Corrigir seleccionados (bulk POST)
// ──────────────────────────────────────────────
function corrigirSeleccionados(tipo, siteId, url) {
    const cbClass  = tipo === 'sem' ? '.sem-cb-' + siteId : '.inv-cb-' + siteId;
    const inputFn  = tipo === 'sem'
        ? id => { const el = document.getElementById('sem-input-' + id); return el ? el.value.trim() : ''; }
        : id => { const el = document.getElementById('inv-sel-' + id);   return el ? el.value.trim() : ''; };

    const btn      = document.getElementById('btn-bulk-' + tipo + '-' + siteId);
    const resultEl = document.getElementById('bulk-result-' + tipo + '-' + siteId);

    const checked = [...document.querySelectorAll(cbClass)].filter(c => c.checked);
    if (checked.length === 0) return;

    const fixes = checked.map(cb => ({
        plano_id: parseInt(cb.dataset.planoId),
        username: inputFn(cb.dataset.planoId),
    })).filter(f => f.username !== '');

    if (fixes.length === 0) {
        resultEl.textContent  = '⚠ Preenche o username para os planos seleccionados.';
        resultEl.className    = 'bulk-result err';
        resultEl.style.display = '';
        return;
    }

    btn.disabled    = true;
    btn.textContent = 'A guardar…';
    resultEl.style.display = 'none';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  _csrf,
        },
        body: JSON.stringify({ fixes }),
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.updated > 0) {
            // Remover linhas corrigidas da tabela
            fixes.forEach(f => {
                const row = document.getElementById(
                    tipo === 'sem' ? 'sem-row-' + f.plano_id : 'inv-row-' + f.plano_id
                );
                if (row) row.remove();
            });

            const plural = data.updated !== 1 ? 's' : '';
            const msg = `✓ ${data.updated} plano${plural} corrigido${plural}`;
            const warn = data.errors && data.errors.length
                ? ' (' + data.errors.join('; ') + ')'
                : '';

            resultEl.textContent   = msg + warn;
            resultEl.className     = 'bulk-result ok';
            resultEl.style.display = '';

            // Atualizar contador no badge
            const remaining = document.querySelectorAll(cbClass).length;
            if (remaining === 0) {
                document.getElementById('bulk-bar-' + tipo + '-' + siteId).classList.remove('visible');
            }
        } else {
            resultEl.textContent  = '⚠ ' + (data.message || data.error || 'Erro desconhecido');
            resultEl.className    = 'bulk-result err';
            resultEl.style.display = '';
        }

        btn.disabled    = false;
        btn.textContent = '✓ Corrigir seleccionados';
    })
    .catch(err => {
        resultEl.textContent   = '⚠ Erro de rede: ' + err.message;
        resultEl.className     = 'bulk-result err';
        resultEl.style.display = '';
        btn.disabled           = false;
        btn.textContent        = '✓ Corrigir seleccionados';
    });
}

function esc(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
