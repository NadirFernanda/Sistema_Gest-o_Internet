@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* ── Searchable dropdown ── */
        .site-picker { position:relative; min-width:260px; max-width:340px; }
        .site-picker__input {
            width:100%; height:40px; padding:0 36px 0 12px;
            border:1px solid #dde3ec; border-radius:8px;
            font-size:0.93rem; background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 10px center;
            cursor:pointer; appearance:none; color:#333;
        }
        .site-picker__input:focus { outline:none; border-color:#f5a623; box-shadow:0 0 0 3px rgba(245,166,35,.15); }
        .site-picker__dropdown {
            display:none; position:absolute; top:calc(100% + 4px); left:0; right:0;
            background:#fff; border:1px solid #e0e6f0; border-radius:10px;
            box-shadow:0 8px 28px rgba(0,0,0,.12); z-index:500; overflow:hidden;
        }
        .site-picker__dropdown.open { display:block; }
        .site-picker__search {
            width:100%; padding:10px 12px; border:none; border-bottom:1px solid #f0f2f5;
            font-size:0.88rem; outline:none; background:#fafbfc;
        }
        .site-picker__list { max-height:260px; overflow-y:auto; }
        .site-picker__item {
            padding:9px 14px; cursor:pointer; font-size:0.88rem;
            display:flex; align-items:center; gap:8px; transition:background .1s;
        }
        .site-picker__item:hover, .site-picker__item.active { background:#fff8ec; }
        .site-picker__item .dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
        .site-picker__item .dot-on  { background:#3bb273; }
        .site-picker__item .dot-off { background:#ccc; }
        .site-picker__item .s-name  { font-weight:600; color:#222; flex:1; }
        .site-picker__item .s-count { font-size:0.78rem; color:#aaa; white-space:nowrap; }
        .site-picker__empty { padding:16px; text-align:center; font-size:0.86rem; color:#bbb; }

        /* ── Site detail panel ── */
        .site-detail {
            max-width:1100px; margin:14px auto 0;
            background:#fff; border-radius:12px;
            border-left:5px solid #f5a623;
            box-shadow:0 2px 12px rgba(0,0,0,.07);
            padding:16px 20px;
            display:none;
        }
        .site-detail.visible { display:flex; align-items:flex-start; gap:24px; flex-wrap:wrap; }
        .site-detail__name  { font-weight:800; font-size:1.05rem; color:#1a1a2e; }
        .site-detail__loc   { font-size:0.82rem; color:#999; margin-top:2px; }
        .site-detail__chips { display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; align-items:center; }
        .chip { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:20px; font-size:0.8rem; }
        .chip-ip      { background:#f0f4f9; color:#555; font-family:monospace; }
        .chip-clients { background:#e8f7ef; color:#2a8a55; font-weight:600; }
        .chip-active  { background:#e8f7ef; color:#2a8a55; font-weight:700; }
        .chip-inactive{ background:#f0f0f0; color:#999; font-weight:700; }
        .site-detail__actions { margin-left:auto; display:flex; gap:8px; align-items:center; flex-shrink:0; }
        .site-detail__test-result { font-size:0.82rem; margin-top:4px; min-height:16px; }

        /* ── Section ── */
        .mkt-section { max-width:1100px; margin:20px auto 0; }
        .mkt-section-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
        .mkt-section-head h3 { font-size:1rem; font-weight:700; color:#222; margin:0; }
        .mkt-count-pill { background:#f4f6f9; color:#666; font-size:0.8rem; padding:3px 11px; border-radius:20px; }

        /* ── Table ── */
        .mkt-table-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,.07); }
        .mkt-table { width:100%; border-collapse:collapse; font-size:0.86rem; table-layout:fixed; }
        .mkt-table thead { background:#f7f9fb; }
        .mkt-table th {
            padding:9px 10px; text-align:left; font-size:0.73rem; font-weight:700;
            color:#999; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap;
            border-bottom:2px solid #edf0f4; overflow:hidden;
        }
        .mkt-table td { padding:9px 10px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
        .mkt-table tbody tr:last-child td { border-bottom:none; }
        .mkt-table tbody tr:hover { background:#fafbfd; }
        .col-n    { width:36px; text-align:center; color:#bbb; font-size:0.8rem; }
        .col-nm   { width:18%; font-weight:600; color:#222; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .col-si   { width:13%; color:#777; font-size:0.83rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .col-pl   { width:17%; color:#555; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .col-user { width:18%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .col-user code { background:#f4f6f9; padding:1px 6px; border-radius:5px; font-size:0.81rem; color:#555; }
        .col-est  { width:10%; }
        .col-ren  { width:10%; font-size:0.83rem; color:#555; white-space:nowrap; }
        .col-online { width:11%; text-align:center; }
        .col-acts { width:112px; white-space:nowrap; }

        /* Status online/offline badge */
        .status-badge {
            display:inline-flex; align-items:center; gap:4px;
            padding:4px 9px; border-radius:20px; font-size:0.75rem; font-weight:700;
            white-space:nowrap;
        }
        .status-online { background:#d4edda; color:#155724; }
        .status-offline { background:#f8d7da; color:#721c24; }
        .status-unknown { background:#e2e3e5; color:#383d41; }
        .status-dot {
            display:inline-block; width:6px; height:6px; border-radius:50%;
        }
        .status-dot-on { background:#28a745; }
        .status-dot-off { background:#dc3545; }
        .status-dot-unknown { background:#999; }
        .downtime-info {
            font-size:0.75rem; color:#999; margin-top:2px; line-height:1.3;
        }

        .ebadge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.76rem; font-weight:700; }
        .eb-ativo    { background:#e8f7ef; color:#2a8a55; }
        .eb-suspenso { background:#fdecea; color:#c0392b; }
        .eb-aviso    { background:#fef9e7; color:#b7770d; }
        .eb-outro    { background:#f0f0f0; color:#888; }

        .abtn { width:31px; height:31px; border-radius:7px; border:none; cursor:pointer; font-size:0.9rem; display:inline-flex; align-items:center; justify-content:center; transition:opacity .15s; }
        .abtn:hover { opacity:.72; }
        .abtn-sync    { background:#4a90d9; color:#fff; }
        .abtn-suspend { background:#fff5f5; border:1px solid #e05a4f !important; color:#e05a4f; }
        .abtn-remove  { background:#f5f5f5; border:1px solid #ddd !important; color:#888; }
        .empty-row td { text-align:center; color:#bbb; padding:36px; font-size:0.9rem; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'MikroTik',
        'subtitle' => 'Gestão de sites e utilizadores PPPoE',
    ])

    {{-- ── Toolbar ── --}}
    <div class="alertas-toolbar" style="flex-wrap:nowrap; gap:8px; max-width:1100px;">
        <div class="alertas-toolbar-left" style="flex:1; display:flex; flex-wrap:nowrap; gap:8px; align-items:center; min-width:0;">

            {{-- Dropdown pesquisável de sites --}}
            <div class="site-picker" id="sitePicker">
                <input type="text" class="site-picker__input" id="sitePickerInput" readonly
                    placeholder="— Todos os sites —"
                    value="{{ $selectedSite ? $selectedSite->nome : '' }}"
                    autocomplete="off">
                <div class="site-picker__dropdown" id="siteDropdown">
                    <input type="text" class="site-picker__search" id="siteSearch" placeholder="Pesquisar site…">
                    <div class="site-picker__list" id="siteList">
                        <div class="site-picker__item {{ !$selectedSiteId ? 'active' : '' }}" data-id="" data-name="Todos os sites">
                            <span class="dot dot-on" style="background:#aaa;"></span>
                            <span class="s-name">Todos os sites</span>
                        </div>
                        @foreach($sites as $site)
                        <div class="site-picker__item {{ $selectedSiteId == $site->id ? 'active' : '' }}"
                            data-id="{{ $site->id }}"
                            data-name="{{ $site->nome }}"
                            data-loc="{{ $site->localizacao }}"
                            data-host="{{ $site->host }}"
                            data-port="{{ $site->port }}"
                            data-clients="{{ $site->clientes_count }}"
                            data-active="{{ $site->active ? '1' : '0' }}">
                            <span class="dot {{ $site->active ? 'dot-on' : 'dot-off' }}"></span>
                            <span class="s-name">{{ $site->nome }}</span>
                            <span class="s-count">{{ $site->clientes_count }} cliente(s)</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <span style="font-size:0.86rem; color:#999; white-space:nowrap;">{{ $planosPending }} por sincronizar</span>
        </div>

        <div class="alertas-toolbar-actions" style="flex-shrink:0; display:flex; gap:8px; align-items:center;">
            <button onclick="runSync(this)" class="btn btn-ghost" style="white-space:nowrap;">▶ Sync agora</button>
            <a href="{{ route('mikrotik.export-pdf', request()->only(['site_id','search','estado'])) }}" class="btn btn-ghost" style="white-space:nowrap;">⬇ PDF</a>
            <a href="{{ route('mikrotik.export-excel', request()->only(['site_id','search','estado'])) }}" class="btn btn-ghost" style="white-space:nowrap;">⬇ Excel</a>
            <a href="{{ route('mikrotik.sites.create') }}" class="btn btn-cta" style="white-space:nowrap;">+ Novo Site</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="white-space:nowrap;">Painel</a>
        </div>
    </div>
    {{-- ── Barra de pesquisa e filtros ── --}}
    <div style="max-width:1100px; margin:8px auto 0; display:flex; gap:8px; align-items:center;">

        {{-- Pesquisa por nome / username --}}
        <div style="position:relative; flex:1;">
            <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); pointer-events:none;"
                 xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                 fill="none" stroke="#aaa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="clienteSearch"
                placeholder="Pesquisar cliente ou username MikroTik…"
                value="{{ $search }}"
                autocomplete="off"
                style="width:100%; height:40px; padding:0 40px 0 38px; border:1px solid #dde3ec; border-radius:8px; font-size:0.9rem; color:#333; outline:none; box-sizing:border-box;">
            @if($search)
            <button onclick="limparPesquisa()" title="Limpar pesquisa"
                style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; font-size:1rem; color:#aaa; line-height:1; padding:0;">✕</button>
            @endif
        </div>

        {{-- Filtro por estado --}}
        <select id="estadoFiltro" onchange="aplicarFiltroEstado(this.value)"
            style="height:40px; padding:0 12px; border:1px solid #dde3ec; border-radius:8px; font-size:0.9rem; background:#fff; color:#333; cursor:pointer; white-space:nowrap; flex-shrink:0;">
            <option value=""           {{ $estadoFiltro === ''               ? 'selected' : '' }}>Todos os estados</option>
            <option value="Ativo"      {{ $estadoFiltro === 'Ativo'          ? 'selected' : '' }}>Activo</option>
            <option value="Em aviso"   {{ $estadoFiltro === 'Em aviso'       ? 'selected' : '' }}>Em aviso</option>
            <option value="Suspenso"   {{ $estadoFiltro === 'Suspenso'       ? 'selected' : '' }}>Suspenso</option>
            <option value="Cancelado"  {{ $estadoFiltro === 'Cancelado'      ? 'selected' : '' }}>Cancelado</option>
            <option value="nao_sincronizado" {{ $estadoFiltro === 'nao_sincronizado' ? 'selected' : '' }}>Não sincronizado</option>
        </select>

    </div>

    <div id="syncResult" style="max-width:1100px; margin:6px auto 0; font-size:0.85rem; color:#555; white-space:pre-line;"></div>

    {{-- ── Painel de detalhes do site seleccionado ── --}}
    <div class="site-detail {{ $selectedSite ? 'visible' : '' }}" id="siteDetail"
        @if($selectedSite)
        data-site-id="{{ $selectedSite->id }}"
        @endif>
        <div style="flex:1; min-width:0;">
            <div class="site-detail__name" id="detailName">{{ $selectedSite?->nome ?? '' }}</div>
            <div class="site-detail__loc" id="detailLoc">{{ $selectedSite?->localizacao ?? '' }}</div>
            <div class="site-detail__chips">
                <span class="chip chip-ip" id="detailIp">{{ $selectedSite ? $selectedSite->host.':'.$selectedSite->port : '' }}</span>
                <span class="chip chip-clients" id="detailClients">{{ $selectedSite ? $selectedSite->clientes_count.' cliente(s)' : '' }}</span>
                <span class="chip {{ $selectedSite?->active ? 'chip-active' : 'chip-inactive' }}" id="detailStatus">
                    {{ $selectedSite ? ($selectedSite->active ? 'Activo' : 'Inactivo') : '' }}
                </span>
            </div>
            <div class="site-detail__test-result" id="detailTestResult"></div>
        </div>
        <div class="site-detail__actions">
            <button onclick="testSelectedSite()" class="btn btn-ghost" style="height:32px; font-size:0.82rem; padding:0 14px;">Testar ligação</button>
            <button onclick="verPerfis(this)" class="btn btn-ghost" style="height:32px; font-size:0.82rem; padding:0 14px;">Ver perfis</button>
            <button id="detailSyncPendentesBtn" onclick="syncPendentesSite(this)" class="btn btn-ghost" style="height:32px; font-size:0.82rem; padding:0 14px; color:#e05a4f; border-color:#e05a4f;">Sync pendentes</button>
            <a id="detailEditLink" href="#" class="btn btn-ghost" style="height:32px; font-size:0.82rem; padding:0 14px; display:inline-flex; align-items:center;">Editar</a>
        </div>
    </div>

    {{-- ── Tabela de planos ── --}}
    <div class="mkt-section">
        <div class="mkt-section-head">
            <h3>
                Planos MikroTik
                @if($selectedSite)
                    <span style="font-weight:400; color:#999; font-size:0.9rem;">— {{ $selectedSite->nome }}</span>
                @endif
            </h3>
            <span class="mkt-count-pill">{{ $clientes->total() }} registos</span>
        </div>

        <div class="mkt-table-card">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <th class="col-nm">Cliente</th>
                        <th class="col-si">Site</th>
                        <th class="col-pl">Plano</th>
                        <th class="col-user">Username MikroTik</th>
                        <th class="col-online">Online</th>
                        <th class="col-est">Estado</th>
                        <th class="col-ren">Renovação</th>
                        <th class="col-acts">Acções</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $i => $item)
                    @php
                        $estado = $item->plano_estado ?? null;
                        $ec = match($estado) {
                            'Ativo'    => 'eb-ativo',
                            'Em aviso' => 'eb-aviso',
                            'Suspenso' => 'eb-suspenso',
                            default    => 'eb-outro',
                        };
                    @endphp
                    <tr id="row-{{ $item->plano_id ?? 'c'.$item->cliente_id }}">
                        <td class="col-n">{{ $clientes->firstItem() + $i }}</td>
                        <td class="col-nm">{{ $item->cliente_nome }}</td>
                        <td class="col-si">{{ $item->site_nome ?? '—' }}</td>
                        <td class="col-pl">
                            @if($item->plano_nome)
                                {{ $item->plano_nome }}
                            @else
                                <span style="color:#bbb; font-style:italic;">Sem plano</span>
                            @endif
                        </td>
                        <td class="col-user">
                            @if($item->mikrotik_username)
                                <code title="Sync: {{ $item->mikrotik_synced_at ? \Carbon\Carbon::parse($item->mikrotik_synced_at)->format('d/m/Y H:i') : '—' }}">{{ $item->mikrotik_username }}</code>
                            @elseif($item->plano_id)
                                <span style="color:#e05a4f; font-size:0.8rem; font-weight:600;">Não sincronizado</span>
                            @else
                                <span style="color:#bbb; font-size:0.8rem;">—</span>
                            @endif
                        </td>
                        <td class="col-online">
                            @if($item->plano_id && $item->mikrotik_username)
                                @php
                                    $onlineStatus = $item->mikrotik_online_status ?? null;
                                    $isOnline = $onlineStatus?->is_online ?? null;
                                @endphp
                                @if($isOnline === true)
                                    <span class="status-badge status-online">
                                        <span class="status-dot status-dot-on"></span>
                                        Online
                                    </span>
                                    <div class="downtime-info" title="Último acesso: {{ $onlineStatus->last_seen_online_at?->format('d/m/Y H:i') }}">
                                        👁 {{ $onlineStatus->last_seen_online_at?->diffForHumans() ?? '—' }}
                                    </div>
                                @elseif($isOnline === false)
                                    <span class="status-badge status-offline">
                                        <span class="status-dot status-dot-off"></span>
                                        Offline
                                    </span>
                                    <div class="downtime-info" title="Última queda: {{ $onlineStatus->last_seen_offline_at?->format('d/m/Y H:i') }}">
                                        ⏱ {{ $onlineStatus->getDowntimeDays() >= 1
                                            ? number_format($onlineStatus->getDowntimeDays(), 1) . ' dias'
                                            : ($onlineStatus->getDowntimeHours() >= 1
                                                ? intval($onlineStatus->getDowntimeHours()) . 'h'
                                                : ($onlineStatus->getDowttimeMinutes() > 0 ? $onlineStatus->getDowttimeMinutes() . 'min' : 'agora')
                                            )
                                        }}
                                    </div>
                                @else
                                    <span class="status-badge status-unknown">
                                        <span class="status-dot status-dot-unknown"></span>
                                        Aguardando...
                                    </span>
                                @endif
                            @else
                                <span style="color:#bbb; font-size:0.8rem;">—</span>
                            @endif
                        </td>
                        <td class="col-est">
                            @if($estado)
                                <span class="ebadge {{ $ec }}">{{ $estado }}</span>
                            @else
                                <span style="color:#bbb;">—</span>
                            @endif
                        </td>
                        <td class="col-ren">
                            {{ $item->proxima_renovacao ? \Carbon\Carbon::parse($item->proxima_renovacao)->format('d/m/Y') : '—' }}
                        </td>
                        <td class="col-acts">
                            @if($item->plano_id)
                            <a href="{{ route('mikrotik.planos.detalhes', $item->plano_id) }}" class="abtn" title="Ver detalhes" style="background:#6c757d; color:#fff; text-decoration:none; display:inline-flex;">📊</a>
                            <button onclick="syncPlano({{ $item->plano_id }}, this)" class="abtn abtn-sync" title="Sincronizar">↻</button>
                            <button onclick="suspendPlano({{ $item->plano_id }}, this)" class="abtn abtn-suspend" title="Suspender">⏸</button>
                            <button onclick="removePlano({{ $item->plano_id }}, this)" class="abtn abtn-remove" title="Remover do MikroTik">✕</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="8">Nenhum cliente MikroTik encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $clientes->links() }}</div>
    </div>

</div>

@push('scripts')
<script>
const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content || '';
const siteRoutes = @json($siteRoutes);
let currentSiteId = {{ $selectedSiteId ? (int)$selectedSiteId : 'null' }};

// Limpar o site_id do URL após carregar — refresh volta ao estado limpo
if (currentSiteId) history.replaceState(null, '', window.location.pathname);

// Inicializar o href do botão Editar para o site actualmente seleccionado
if (currentSiteId && siteRoutes[currentSiteId]?.edit) {
    document.getElementById('detailEditLink').href = siteRoutes[currentSiteId].edit;
}

/* ── Pesquisa de clientes ── */
const clienteSearchInput = document.getElementById('clienteSearch');
let searchTimer;
clienteSearchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        navegarComPesquisa(this.value.trim());
    }, 500);
});
clienteSearchInput.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') { clearTimeout(searchTimer); navegarComPesquisa(this.value.trim()); }
    if (e.key === 'Escape') { clearTimeout(searchTimer); navegarComPesquisa(''); }
});
clienteSearchInput.addEventListener('focus', function () {
    this.style.borderColor = '#f5a623';
    this.style.boxShadow   = '0 0 0 3px rgba(245,166,35,.15)';
});
clienteSearchInput.addEventListener('blur', function () {
    this.style.borderColor = '#dde3ec';
    this.style.boxShadow   = '';
});
function navegarComPesquisa(val) {
    const url = new URL(window.location.href);
    if (val) url.searchParams.set('search', val);
    else url.searchParams.delete('search');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
function limparPesquisa() { navegarComPesquisa(''); }
function aplicarFiltroEstado(val) {
    const url = new URL(window.location.href);
    if (val) url.searchParams.set('estado', val);
    else url.searchParams.delete('estado');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

/* ── Dropdown pesquisável ── */
const picker   = document.getElementById('sitePicker');
const input    = document.getElementById('sitePickerInput');
const dropdown = document.getElementById('siteDropdown');
const search   = document.getElementById('siteSearch');
const list     = document.getElementById('siteList');
const items    = list.querySelectorAll('.site-picker__item');

input.addEventListener('click', () => { dropdown.classList.toggle('open'); if (dropdown.classList.contains('open')) search.focus(); });
document.addEventListener('click', e => { if (!picker.contains(e.target)) dropdown.classList.remove('open'); });

search.addEventListener('input', () => {
    const q = search.value.toLowerCase();
    let found = 0;
    items.forEach(item => {
        const match = item.dataset.name.toLowerCase().includes(q);
        item.style.display = match ? '' : 'none';
        if (match) found++;
    });
    let empty = list.querySelector('.site-picker__empty');
    if (!found) {
        if (!empty) { empty = document.createElement('div'); empty.className = 'site-picker__empty'; empty.textContent = 'Nenhum site encontrado.'; list.appendChild(empty); }
    } else if (empty) empty.remove();
});

items.forEach(item => {
    item.addEventListener('click', () => {
        const id   = item.dataset.id;
        const name = item.dataset.name;
        input.value = name === 'Todos os sites' ? '' : name;
        input.placeholder = name === 'Todos os sites' ? '— Todos os sites —' : name;
        items.forEach(i => i.classList.remove('active'));
        item.classList.add('active');
        dropdown.classList.remove('open');
        search.value = '';
        items.forEach(i => i.style.display = '');

        // Actualizar painel de detalhes
        updateDetailPanel(item);

        // Recarregar tabela com filtro
        const url = new URL(window.location.href);
        if (id) url.searchParams.set('site_id', id);
        else url.searchParams.delete('site_id');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });
});

function updateDetailPanel(item) {
    const id = item.dataset.id;
    const panel = document.getElementById('siteDetail');
    if (!id) { panel.classList.remove('visible'); return; }

    document.getElementById('detailName').textContent    = item.dataset.name;
    document.getElementById('detailLoc').textContent     = item.dataset.loc || '';
    document.getElementById('detailIp').textContent      = item.dataset.host + ':' + item.dataset.port;
    document.getElementById('detailClients').textContent = item.dataset.clients + ' cliente(s)';
    const statusEl = document.getElementById('detailStatus');
    statusEl.textContent = item.dataset.active === '1' ? 'Activo' : 'Inactivo';
    statusEl.className   = 'chip ' + (item.dataset.active === '1' ? 'chip-active' : 'chip-inactive');
    document.getElementById('detailTestResult').textContent = '';
    document.getElementById('detailEditLink').href = siteRoutes[id]?.edit || '#';
    panel.dataset.siteId = id;
    currentSiteId = id;
    panel.classList.add('visible');
}

/* ── Ver perfis do router ── */
function verPerfis(btn) {
    const panel = document.getElementById('siteDetail');
    const id    = panel.dataset.siteId;
    if (!id) return;
    const url = siteRoutes[id]?.profiles;
    if (!url) return;
    btn.disabled = true; btn.textContent = 'A carregar…';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            btn.disabled = false; btn.textContent = 'Ver perfis';
            if (d.ok && d.profiles) {
                alert('Perfis PPPoE no router:\n\n' + (d.profiles.length ? d.profiles.join('\n') : '(nenhum)'));
            } else {
                alert('Erro ao obter perfis.');
            }
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Ver perfis'; alert('Falha de ligação.'); });
}

/* ── Sync pendentes do site seleccionado ── */
function syncPendentesSite(btn) {
    const panel = document.getElementById('siteDetail');
    const id    = panel.dataset.siteId;
    if (!id) return;
    const url = siteRoutes[id]?.syncPendentes;
    if (!url) return;
    btn.disabled = true; btn.textContent = 'A sincronizar…';

    const controller = new AbortController();
    const tId = setTimeout(() => { controller.abort(); }, 20000); // 20s timeout

    fetch(url, { method: 'POST', signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken } })
        .then(r => r.json())
        .then(d => {
            clearTimeout(tId);
            const resultEl = document.getElementById('detailTestResult');
            resultEl.textContent = d.message || (d.ok ? 'Concluído.' : 'Erro.');
            resultEl.style.color = (d.failed > 0 || !d.ok) ? '#e05a4f' : '#2a8a55';
            btn.disabled = false; btn.textContent = 'Sync pendentes';
            if (d.errors && d.errors.length > 0) {
                alert('Falhas na sincronização:\n\n' + d.errors.join('\n'));
            }
            if (d.synced > 0) setTimeout(() => window.location.reload(), 1500);
        })
        .catch(err => {
            clearTimeout(tId);
            btn.disabled = false; btn.textContent = 'Sync pendentes';
            const msg = err.name === 'AbortError' ? 'Timeout — router demorou demasiado a responder' : String(err);
            alert('Erro: ' + msg);
        });
}

/* ── Testar ligação do site seleccionado ── */
function testSelectedSite() {
    const panel  = document.getElementById('siteDetail');
    const result = document.getElementById('detailTestResult');
    const id     = panel.dataset.siteId;
    if (!id) return;
    result.textContent = 'A testar…'; result.style.color = '#999';
    fetch(siteRoutes[id]?.test, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            let msg = d.site_nome || '';
            if (d.identity && d.identity !== d.site_nome) msg += ' (MikroTik: ' + d.identity + ')';
            result.textContent = d.ok ? '✓ Ligado — ' + msg : '✗ ' + (d.error || 'Erro');
            result.style.color = d.ok ? '#2a8a55' : '#e05a4f';
        })
        .catch(() => { result.textContent = '✗ Falha de rede'; result.style.color = '#e05a4f'; });
}

/* ── Sync geral ── */
function runSync(btn) {
    const el = document.getElementById('syncResult');
    btn.disabled = true; btn.textContent = 'A sincronizar…'; el.textContent = '';
    fetch('{{ route('mikrotik.run-sync') }}', {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        el.textContent = d.message || (d.ok ? 'Concluído.' : 'Erro.');
        el.style.color = d.ok ? '#2a8a55' : '#e05a4f';
        btn.disabled = false; btn.textContent = '▶ Sync agora';
    })
    .catch(() => { el.textContent = 'Falha de rede.'; el.style.color = '#e05a4f'; btn.disabled = false; btn.textContent = '▶ Sync agora'; });
}

/* ── Acções na tabela ── */
function syncPlano(id, btn) {
    btn.disabled = true; btn.textContent = '…';
    fetch('{{ url('mikrotik/sync') }}/' + id, {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            btn.textContent = '✓';
            const row = document.getElementById('row-' + id);
            if (row) {
                if (d.mikrotik_username) {
                    const syncTitle = d.mikrotik_synced_at ? 'Sync: ' + d.mikrotik_synced_at : '';
                    row.cells[4].innerHTML = '<code title="' + syncTitle + '">' + d.mikrotik_username + '</code>';
                }
            }
        } else {
            btn.textContent = '✗';
            const msg = d.error || 'Falha desconhecida';
            alert('Erro ao sincronizar:\n' + msg);
        }
        setTimeout(() => { btn.disabled = false; btn.textContent = '↻'; }, 2000);
    })
    .catch(err => {
        btn.disabled = false; btn.textContent = '✗';
        alert('Erro de rede: ' + err);
    });
}

function suspendPlano(id, btn) {
    if (!confirm('Suspender este utilizador no MikroTik?')) return;
    btn.disabled = true;
    fetch('{{ url('mikrotik/suspend') }}/' + id, {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            const row = document.getElementById('row-' + id);
            if (row) row.cells[5].innerHTML = '<span class="ebadge eb-suspenso">Suspenso</span>';
        } else {
            alert('Erro ao suspender: ' + (d.error || 'Falha desconhecida'));
        }
        btn.textContent = d.ok ? '✓' : '✗';
        setTimeout(() => { btn.disabled = false; btn.textContent = '⏸'; }, 2000);
    })
    .catch(err => { btn.disabled = false; btn.textContent = '✗'; alert('Erro de rede: ' + err); });
}

function removePlano(id, btn) {
    if (!confirm('Remover permanentemente do MikroTik?')) return;
    btn.disabled = true;
    fetch('{{ url('mikrotik/remove') }}/' + id, {
        method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            document.getElementById('row-' + id)?.remove();
        } else {
            btn.disabled = false; btn.textContent = '✕';
            alert('Erro ao remover: ' + (d.error || 'Falha desconhecida'));
        }
    })
    .catch(err => { btn.disabled = false; btn.textContent = '✕'; alert('Erro de rede: ' + err); });
}
</script>
@endpush
@endsection
