@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
<style>
.diag-wrap  { max-width:1100px; margin:0 auto; padding:0 16px 48px; }
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
.count-badge.red { background:#fdecea; color:#c0392b; }
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

.empty-note { padding:20px 14px; text-align:center; color:#bbb; font-size:0.85rem; }

.ebadge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; }
.eb-ativo    { background:#e8f7ef; color:#2a8a55; }
.eb-suspenso { background:#fdecea; color:#c0392b; }
.eb-aviso    { background:#fef9e7; color:#b7770d; }

.link-btn {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:6px; font-size:0.79rem; font-weight:600;
    background:#f0f4f9; color:#4a90d9; text-decoration:none; transition:background .12s;
}
.link-btn:hover { background:#ddeaf9; color:#2a6db5; }

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

        {{-- Secção 1: sem username (BD only, carregado imediatamente) --}}
        <div class="diag-section">
            <div class="diag-section-head">
                <h4>Sem username configurado</h4>
                <span class="count-badge {{ $nSemUser > 0 ? 'red' : 'green' }}">{{ $nSemUser }}</span>
                <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">detectado na base de dados &mdash; sem ligação ao router</span>
            </div>
            @if($semUsername->isNotEmpty())
            <table class="diag-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Estado</th>
                        <th>Pr&oacute;xima renova&ccedil;&atilde;o</th>
                        <th>Ac&ccedil;&atilde;o</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semUsername as $plano)
                    <tr>
                        <td style="font-weight:600;color:#222;">{{ $plano->cliente?->nome ?? '—' }}</td>
                        <td><code class="uname">{{ preg_replace('/\D/', '', $plano->cliente?->contato ?? '') ?: '—' }}</code></td>
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
                            <a href="{{ route('mikrotik.planos.detalhes', $plano->id) }}" class="link-btn">
                                Configurar &#8599;
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
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
                    <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">o sistema vai tentar gerir o secret errado</span>
                </div>
                <table class="diag-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Telefone</th>
                            <th>Username actual (errado)</th>
                            <th>Estado</th>
                            <th>Ac&ccedil;&atilde;o</th>
                        </tr>
                    </thead>
                    <tbody id="router-invalid-body-{{ $site->id }}"></tbody>
                </table>
            </div>

            {{-- Secrets órfãos --}}
            <div id="router-orphan-{{ $site->id }}" style="display:none;">
                <div class="diag-section-head" style="border-top:1px solid #f2f4f7;">
                    <h4>Secrets no router sem plano associado</h4>
                    <span class="count-badge" id="router-orphan-count-{{ $site->id }}">0</span>
                    <span style="font-size:0.78rem;color:#aaa;margin-left:4px;">podem ser contas antigas ou criadas manualmente no WinBox</span>
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
function verificarRouter(siteId, url) {
    const btn      = document.getElementById('btn-router-' + siteId);
    const area     = document.getElementById('router-area-' + siteId);
    const loading  = document.getElementById('router-loading-' + siteId);
    const errBox   = document.getElementById('router-error-' + siteId);

    btn.disabled   = true;
    btn.textContent = 'A verificar…';
    area.style.display  = '';
    loading.style.display = '';
    errBox.style.display  = 'none';

    fetch(url, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content } })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            loading.style.display = 'none';

            if (!ok) {
                errBox.textContent  = '⚠ ' + (data.error || 'Erro ao contactar o router.');
                errBox.style.display = '';
                btn.disabled        = false;
                btn.textContent     = '🔌 Tentar novamente';
                return;
            }

            // Planos com username inválido
            const invalidWrap = document.getElementById('router-invalid-' + siteId);
            const invalidBody = document.getElementById('router-invalid-body-' + siteId);
            const invalidCount = document.getElementById('router-invalid-count-' + siteId);
            const invalidList  = data.planosUsernameErrado || [];

            if (invalidList.length > 0) {
                invalidCount.textContent = invalidList.length;
                invalidBody.innerHTML = invalidList.map(p => `
                    <tr>
                        <td style="font-weight:600;color:#222;">${esc(p.cliente_nome)}</td>
                        <td><code class="uname">${esc(p.cliente_tel || '—')}</code></td>
                        <td><code class="uname missing">${esc(p.mikrotik_username)}</code></td>
                        <td><span class="ebadge ${ p.estado === 'Ativo' ? 'eb-ativo' : (p.estado === 'Suspenso' ? 'eb-suspenso' : 'eb-aviso') }">${esc(p.estado)}</span></td>
                        <td><a href="${esc(p.detalhes_url)}" class="link-btn">Corrigir ↗</a></td>
                    </tr>`).join('');
                invalidWrap.style.display = '';
            }

            // Secrets órfãos
            const orphanWrap  = document.getElementById('router-orphan-' + siteId);
            const orphanBody  = document.getElementById('router-orphan-body-' + siteId);
            const orphanCount = document.getElementById('router-orphan-count-' + siteId);
            const orphanList  = data.secretsOrfaos || [];

            if (orphanList.length > 0) {
                orphanCount.textContent = orphanList.length;
                orphanBody.innerHTML = orphanList.map(s => `
                    <tr>
                        <td><code class="uname orphan">${esc(s.name)}</code></td>
                        <td style="color:#666;">${esc(s.profile || '—')}</td>
                        <td style="color:${ s.disabled === 'yes' ? '#c0392b' : '#2a8a55' };font-weight:700;">
                            ${ s.disabled === 'yes' ? 'Sim' : 'Não' }
                        </td>
                        <td style="color:#888;font-size:0.8rem;">${esc(s.comment || '—')}</td>
                    </tr>`).join('');
                orphanWrap.style.display = '';
            }

            // Resumo OK
            const okDiv = document.getElementById('router-ok-' + siteId);
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

function esc(str) {
    if (str == null) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
