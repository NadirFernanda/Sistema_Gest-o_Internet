@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        /* ── Site cards ── */
        .mkt-sites { display:flex; gap:14px; flex-wrap:wrap; max-width:1100px; margin:20px auto 0; }
        .mkt-card {
            flex:1 1 280px; background:#fff; border-radius:12px;
            padding:18px 20px; box-shadow:0 2px 12px rgba(0,0,0,0.07);
            border-top:4px solid #f5a623; position:relative; transition:box-shadow .2s;
        }
        .mkt-card:hover { box-shadow:0 6px 22px rgba(0,0,0,0.11); }
        .mkt-card.inactive { border-top-color:#b0b8c1; opacity:.8; }
        .mkt-card__name  { font-weight:800; font-size:1rem; color:#222; }
        .mkt-card__loc   { font-size:0.81rem; color:#999; margin-top:3px; }
        .mkt-card__meta  { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-top:10px; }
        .mkt-card__ip    { background:#f4f6f9; padding:3px 8px; border-radius:6px; font-size:0.8rem; font-family:monospace; color:#555; }
        .mkt-card__count { font-size:0.83rem; color:#666; }
        .mkt-card__status { font-size:0.75rem; font-weight:700; padding:2px 9px; border-radius:20px; }
        .status-on  { background:#e8f7ef; color:#2a8a55; }
        .status-off { background:#f0f0f0; color:#999; }
        .mkt-card__btns  { display:flex; gap:6px; margin-top:12px; }
        .mkt-test-result { margin-top:8px; font-size:0.81rem; min-height:18px; }

        /* ── Section ── */
        .mkt-section { max-width:1100px; margin:24px auto 0; }
        .mkt-section-head {
            display:flex; align-items:center; justify-content:space-between;
            margin-bottom:12px;
        }
        .mkt-section-head h3 { font-size:1rem; font-weight:700; color:#222; margin:0; }
        .mkt-count-pill { background:#f4f6f9; color:#666; font-size:0.8rem; padding:3px 11px; border-radius:20px; }

        /* ── Table ── */
        .mkt-table-card { background:#fff; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.07); overflow:hidden; }
        .mkt-table { width:100%; border-collapse:collapse; font-size:0.88rem; }
        .mkt-table thead { background:#f7f9fb; }
        .mkt-table th {
            padding:10px 14px; text-align:left; font-size:0.75rem; font-weight:700;
            color:#999; text-transform:uppercase; letter-spacing:.04em; white-space:nowrap;
            border-bottom:2px solid #edf0f4;
        }
        .mkt-table td { padding:11px 14px; border-bottom:1px solid #f2f4f7; vertical-align:middle; }
        .mkt-table tbody tr:last-child td { border-bottom:none; }
        .mkt-table tbody tr:hover { background:#fafbfd; }

        .col-n   { width:40px; text-align:center; }
        .col-n td, .col-n th { color:#bbb; font-size:0.8rem; }
        .col-name { min-width:170px; font-weight:600; color:#222; }
        .col-site { min-width:130px; color:#777; font-size:0.84rem; }
        .col-plan { min-width:140px; color:#555; }
        .col-user code { background:#f4f6f9; padding:2px 7px; border-radius:5px; font-size:0.82rem; color:#555; }
        .col-renov { font-size:0.84rem; color:#555; }
        .col-sync  { font-size:0.81rem; color:#aaa; }
        .col-acts  { white-space:nowrap; }

        /* ── Estado badges ── */
        .ebadge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.76rem; font-weight:700; }
        .eb-ativo    { background:#e8f7ef; color:#2a8a55; }
        .eb-suspenso { background:#fdecea; color:#c0392b; }
        .eb-aviso    { background:#fef9e7; color:#b7770d; }
        .eb-outro    { background:#f0f0f0; color:#888; }

        /* ── Action buttons ── */
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
        'subtitle' => 'Gestão de sites e utilizadores HotSpot',
    ])

    {{-- ── Toolbar — mesmo padrão que /planos ── --}}
    <div class="alertas-toolbar" style="flex-wrap:nowrap; gap:8px; max-width:1100px;">
        <div class="alertas-toolbar-left" style="flex:1; display:flex; flex-wrap:nowrap; gap:8px; align-items:center; min-width:0;">
            <span style="font-size:0.88rem; color:#888; white-space:nowrap;">
                {{ $planosPending }} por sincronizar
            </span>
            <div id="syncResult" style="font-size:0.86rem; color:#555;"></div>
        </div>
        <div class="alertas-toolbar-actions" style="flex-shrink:0; display:flex; gap:8px; align-items:center;">
            <button onclick="runSync(this)" class="btn btn-ghost" style="white-space:nowrap;"
                title="Sincroniza utilizadores HotSpot para planos activos ainda não sincronizados">
                ▶ Sync agora
            </button>
            <a href="{{ route('mikrotik.export') }}" class="btn btn-ghost" style="white-space:nowrap;">⬇ PDF</a>
            <a href="{{ route('mikrotik.sites.create') }}" class="btn btn-cta" style="white-space:nowrap;">+ Novo Site</a>
            <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="white-space:nowrap;">Painel</a>
        </div>
    </div>

    {{-- ── Cards dos sites ── --}}
    <div class="mkt-sites">
        @forelse($sites as $site)
        <div class="mkt-card {{ $site->active ? '' : 'inactive' }}" style="border-top-color:{{ $site->active ? '#f5a623' : '#b0b8c1' }}">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                <div style="min-width:0;">
                    <div class="mkt-card__name">{{ $site->nome }}</div>
                    @if($site->localizacao)
                        <div class="mkt-card__loc">{{ $site->localizacao }}</div>
                    @endif
                    <div class="mkt-card__meta">
                        <span class="mkt-card__ip">{{ $site->host }}:{{ $site->port }}</span>
                        <span class="mkt-card__count">{{ $site->clientes_count }} cliente(s)</span>
                        <span class="mkt-card__status {{ $site->active ? 'status-on' : 'status-off' }}">
                            {{ $site->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="mkt-card__btns">
                <button onclick="testSite({{ $site->id }}, this)" class="btn btn-ghost" style="height:30px; font-size:0.8rem; padding:0 12px;">Testar</button>
                <a href="{{ route('mikrotik.sites.edit', $site) }}" class="btn btn-ghost" style="height:30px; font-size:0.8rem; padding:0 12px; display:inline-flex; align-items:center;">Editar</a>
            </div>
            <div id="test-result-{{ $site->id }}" class="mkt-test-result"></div>
        </div>
        @empty
        <div style="flex:1; background:#fff; border-radius:12px; padding:28px; text-align:center; color:#aaa; box-shadow:0 2px 12px rgba(0,0,0,0.07);">
            Nenhum site configurado. <a href="{{ route('mikrotik.sites.create') }}">Adicionar o primeiro site</a>.
        </div>
        @endforelse
    </div>

    {{-- ── Tabela de planos ── --}}
    <div class="mkt-section">
        <div class="mkt-section-head">
            <h3>Planos com utilizador MikroTik</h3>
            <span class="mkt-count-pill">{{ $planosSync->total() }} registos</span>
        </div>

        <div class="mkt-table-card">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th class="col-n">#</th>
                        <th class="col-name">Cliente</th>
                        <th class="col-site">Site</th>
                        <th class="col-plan">Plano</th>
                        <th>Username MikroTik</th>
                        <th>Estado</th>
                        <th class="col-renov">Renovação</th>
                        <th class="col-sync">Última sync</th>
                        <th class="col-acts">Acções</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planosSync as $i => $plano)
                    @php
                        $ec = match($plano->estado) {
                            'Ativo'    => 'eb-ativo',
                            'Em aviso' => 'eb-aviso',
                            'Suspenso' => 'eb-suspenso',
                            default    => 'eb-outro',
                        };
                    @endphp
                    <tr id="row-{{ $plano->id }}">
                        <td class="col-n">{{ $planosSync->firstItem() + $i }}</td>
                        <td class="col-name">{{ $plano->cliente?->nome ?? '—' }}</td>
                        <td class="col-site">{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
                        <td class="col-plan">{{ $plano->nome }}</td>
                        <td class="col-user"><code>{{ $plano->mikrotik_username }}</code></td>
                        <td><span class="ebadge {{ $ec }}">{{ $plano->estado }}</span></td>
                        <td class="col-renov">{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
                        <td class="col-sync">{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="col-acts">
                            <button onclick="syncPlano({{ $plano->id }}, this)" class="abtn abtn-sync" title="Sincronizar">↻</button>
                            <button onclick="suspendPlano({{ $plano->id }}, this)" class="abtn abtn-suspend" title="Suspender">⏸</button>
                            <button onclick="removePlano({{ $plano->id }}, this)" class="abtn abtn-remove" title="Remover do MikroTik">✕</button>
                        </td>
                    </tr>
                    @empty
                    <tr class="empty-row"><td colspan="9">Nenhum plano sincronizado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $planosSync->links() }}</div>
    </div>

</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function testSite(id, btn) {
    const el = document.getElementById('test-result-' + id);
    btn.disabled = true; btn.textContent = '…';
    fetch('{{ url("mikrotik/sites") }}/' + id + '/test', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            el.textContent = d.ok ? '✓ Ligado — ' + (d.identity || '') : '✗ ' + (d.error || 'Erro');
            el.style.color = d.ok ? '#2a8a55' : '#e05a4f';
            btn.disabled = false; btn.textContent = 'Testar';
        })
        .catch(() => { el.textContent = '✗ Falha de rede'; el.style.color = '#e05a4f'; btn.disabled = false; btn.textContent = 'Testar'; });
}

function runSync(btn) {
    const el = document.getElementById('syncResult');
    btn.disabled = true; btn.textContent = 'A sincronizar…'; el.textContent = '';
    fetch('{{ route('mikrotik.run-sync') }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        el.textContent = d.message || (d.ok ? 'Concluído.' : 'Erro.');
        el.style.color = d.ok ? '#2a8a55' : '#e05a4f';
        btn.disabled = false; btn.textContent = '▶ Sync agora';
    })
    .catch(() => { el.textContent = 'Falha de rede.'; el.style.color = '#e05a4f'; btn.disabled = false; btn.textContent = '▶ Sync agora'; });
}

function syncPlano(id, btn) {
    btn.disabled = true; btn.textContent = '…';
    fetch('{{ url('mikrotik/sync') }}/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        btn.textContent = d.ok ? '✓' : '✗';
        if (d.ok && d.mikrotik_synced_at) {
            const row = document.getElementById('row-' + id);
            if (row) row.cells[7].textContent = d.mikrotik_synced_at;
        }
        setTimeout(() => { btn.disabled = false; btn.textContent = '↻'; }, 2000);
    });
}

function suspendPlano(id, btn) {
    if (!confirm('Suspender este utilizador no MikroTik?')) return;
    btn.disabled = true;
    fetch('{{ url('mikrotik/suspend') }}/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) {
            const row = document.getElementById('row-' + id);
            if (row) row.cells[5].innerHTML = '<span class="ebadge eb-suspenso">Suspenso</span>';
        }
        btn.textContent = d.ok ? '✓' : '✗';
        setTimeout(() => { btn.disabled = false; btn.textContent = '⏸'; }, 2000);
    });
}

function removePlano(id, btn) {
    if (!confirm('Remover permanentemente do MikroTik?')) return;
    btn.disabled = true;
    fetch('{{ url('mikrotik/remove') }}/' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        if (d.ok) document.getElementById('row-' + id)?.remove();
        else { btn.disabled = false; btn.textContent = '✕'; }
    });
}
</script>
@endpush
@endsection
