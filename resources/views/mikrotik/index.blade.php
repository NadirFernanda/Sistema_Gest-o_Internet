@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
    <style>
        .mkt-page { max-width: 1200px; margin: 0 auto; padding: 0 16px 40px; }

        /* ── Toolbar ── */
        .mkt-toolbar {
            display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
            margin: 18px 0 0;
        }
        .mkt-toolbar .spacer { flex: 1; }

        /* ── Site cards ── */
        .mkt-sites { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 20px; }
        .mkt-site-card {
            flex: 1 1 300px; background: #fff; border-radius: 12px;
            padding: 18px 22px; box-shadow: 0 4px 18px rgba(0,0,0,0.07);
            border-left: 6px solid #3bb273; transition: box-shadow .2s;
        }
        .mkt-site-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.12); }
        .mkt-site-card.inactive { border-left-color: #b0b8c1; }
        .mkt-site-card__header { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .mkt-site-card__name { font-weight: 700; font-size: 1rem; color: #222; }
        .mkt-site-card__loc { font-size: 0.82rem; color: #888; margin-top: 3px; }
        .mkt-site-card__meta { margin-top: 8px; font-size: 0.85rem; color: #555; display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
        .mkt-site-card__badge {
            display: inline-block; padding: 2px 9px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600;
        }
        .badge-active   { background: #e8f7ef; color: #2a8a55; }
        .badge-inactive { background: #f0f0f0; color: #999; }
        .mkt-site-card__actions { display: flex; gap: 6px; flex-shrink: 0; margin-top: 2px; }
        .mkt-test-result { margin-top: 8px; font-size: 0.82rem; }

        /* ── Section header ── */
        .mkt-section-header {
            display: flex; align-items: center; justify-content: space-between;
            margin: 28px 0 12px;
        }
        .mkt-section-header h3 { font-size: 1rem; font-weight: 700; margin: 0; color: #222; }
        .mkt-total-badge {
            font-size: 0.82rem; color: #666; background: #f0f4f8;
            padding: 3px 10px; border-radius: 20px;
        }

        /* ── Table ── */
        .mkt-table-wrap { background: #fff; border-radius: 12px; box-shadow: 0 4px 18px rgba(0,0,0,0.07); overflow-x: auto; }
        .mkt-table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        .mkt-table thead tr { background: #f7f9fb; border-bottom: 2px solid #e8ecf0; }
        .mkt-table th {
            padding: 11px 14px; text-align: left; font-size: 0.78rem;
            font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .04em; white-space: nowrap;
        }
        .mkt-table td { padding: 11px 14px; border-bottom: 1px solid #f0f2f5; vertical-align: middle; }
        .mkt-table tbody tr:last-child td { border-bottom: none; }
        .mkt-table tbody tr:hover { background: #fafbfd; }
        .mkt-table .col-num  { width: 44px; text-align: center; color: #aaa; font-size: 0.82rem; }
        .mkt-table .col-name { min-width: 180px; font-weight: 600; color: #222; }
        .mkt-table .col-site { min-width: 140px; font-size: 0.83rem; color: #666; }
        .mkt-table .col-plan { min-width: 150px; font-size: 0.85rem; color: #444; }
        .mkt-table .col-user { font-size: 0.83rem; }
        .mkt-table .col-actions { white-space: nowrap; }

        /* ── Estado badges ── */
        .estado-badge {
            display: inline-block; padding: 3px 10px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 700;
        }
        .estado-ativo     { background: #e8f7ef; color: #2a8a55; }
        .estado-suspenso  { background: #fdecea; color: #c0392b; }
        .estado-emaviso   { background: #fef9e7; color: #b7770d; }
        .estado-outro     { background: #f0f0f0; color: #777; }

        /* ── Action buttons ── */
        .btn-act {
            width: 32px; height: 32px; border-radius: 7px; border: none;
            cursor: pointer; font-size: 0.95rem; display: inline-flex;
            align-items: center; justify-content: center; transition: opacity .15s;
        }
        .btn-act:hover { opacity: .75; }
        .btn-act-sync    { background: #4a90d9; color: #fff; }
        .btn-act-suspend { background: #fff; border: 1px solid #e05a4f !important; color: #e05a4f; }
        .btn-act-remove  { background: #f3f3f3; border: 1px solid #ddd !important; color: #666; }
    </style>
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'MikroTik',
        'subtitle' => 'Gestão de sites e utilizadores HotSpot',
    ])

    {{-- ── Toolbar padrão ── --}}
    <div class="clientes-toolbar" style="max-width:1200px;margin:18px auto 0;display:flex;flex-wrap:nowrap;gap:8px;align-items:center;">
        <a href="{{ route('mikrotik.sites.create') }}" class="btn btn-cta" style="white-space:nowrap;">+ Novo Site</a>

        <button onclick="runSync(this)" class="btn btn-ghost" style="white-space:nowrap;"
            title="Percorre todos os sites e actualiza/cria utilizadores HotSpot para planos activos ainda não sincronizados">
            ▶ Sync todos agora
        </button>

        <span style="font-size:0.88rem;color:#888;white-space:nowrap;">
            {{ $planosPending }} por sincronizar
        </span>

        <div id="syncResult" style="font-size:0.88rem;color:#555;flex:1;"></div>

        <a href="{{ route('mikrotik.export') }}" class="btn btn-ghost" style="white-space:nowrap;" title="Exportar tabela em CSV">
            ⬇ Exportar CSV
        </a>

        <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="white-space:nowrap;">Painel</a>
    </div>

    <div class="mkt-page">

        {{-- ── Sites / RouterBoards ── --}}
        <div class="mkt-sites">
            @forelse($sites as $site)
            <div class="mkt-site-card {{ $site->active ? '' : 'inactive' }}" style="border-left-color: {{ $site->active ? '#3bb273' : '#b0b8c1' }}">
                <div class="mkt-site-card__header">
                    <div>
                        <div class="mkt-site-card__name">{{ $site->nome }}</div>
                        @if($site->localizacao)
                            <div class="mkt-site-card__loc">{{ $site->localizacao }}</div>
                        @endif
                        <div class="mkt-site-card__meta">
                            <code style="background:#f0f4f8;padding:2px 7px;border-radius:5px;font-size:0.82rem;">{{ $site->host }}:{{ $site->port }}</code>
                            <span>·</span>
                            <span><strong>{{ $site->clientes_count }}</strong> cliente(s)</span>
                            <span class="mkt-site-card__badge {{ $site->active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $site->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                    <div class="mkt-site-card__actions">
                        <button onclick="testSite({{ $site->id }}, this)" class="btn btn-ghost" style="height:30px;font-size:0.8rem;padding:0 12px;">Testar</button>
                        <a href="{{ route('mikrotik.sites.edit', $site) }}" class="btn btn-ghost" style="height:30px;font-size:0.8rem;padding:0 12px;display:inline-flex;align-items:center;">Editar</a>
                    </div>
                </div>
                <div id="test-result-{{ $site->id }}" class="mkt-test-result"></div>
            </div>
            @empty
            <div style="flex:1;background:#fff;border-radius:12px;padding:28px;text-align:center;color:#888;box-shadow:0 4px 14px rgba(0,0,0,0.06);">
                Nenhum site configurado. <a href="{{ route('mikrotik.sites.create') }}">Adicionar o primeiro site</a>.
            </div>
            @endforelse
        </div>

        {{-- ── Tabela de planos ── --}}
        <div class="mkt-section-header">
            <h3>Planos com utilizador MikroTik</h3>
            <span class="mkt-total-badge">{{ $planosSync->total() }} registos</span>
        </div>

        <div class="mkt-table-wrap">
            <table class="mkt-table">
                <thead>
                    <tr>
                        <th class="col-num">#</th>
                        <th class="col-name">Cliente</th>
                        <th class="col-site">Site</th>
                        <th class="col-plan">Plano</th>
                        <th class="col-user">Username MikroTik</th>
                        <th>Estado</th>
                        <th>Renovação</th>
                        <th>Última sync</th>
                        <th class="col-actions">Acções</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planosSync as $i => $plano)
                    @php
                        $estadoClass = match($plano->estado) {
                            'Ativo'     => 'estado-ativo',
                            'Em aviso'  => 'estado-emaviso',
                            'Suspenso'  => 'estado-suspenso',
                            default     => 'estado-outro',
                        };
                    @endphp
                    <tr id="row-{{ $plano->id }}">
                        <td class="col-num">{{ $planosSync->firstItem() + $i }}</td>
                        <td class="col-name">{{ $plano->cliente?->nome ?? '—' }}</td>
                        <td class="col-site">{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
                        <td class="col-plan">{{ $plano->nome }}</td>
                        <td class="col-user"><code style="background:#f0f4f8;padding:2px 6px;border-radius:4px;">{{ $plano->mikrotik_username }}</code></td>
                        <td><span class="estado-badge {{ $estadoClass }}">{{ $plano->estado }}</span></td>
                        <td style="font-size:0.88rem;color:#555;">{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
                        <td style="font-size:0.83rem;color:#888;">{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="col-actions">
                            <button onclick="syncPlano({{ $plano->id }}, this)" class="btn-act btn-act-sync" title="Sincronizar">↻</button>
                            <button onclick="suspendPlano({{ $plano->id }}, this)" class="btn-act btn-act-suspend" title="Suspender">⏸</button>
                            <button onclick="removePlano({{ $plano->id }}, this)" class="btn-act btn-act-remove" title="Remover do MikroTik">✕</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;color:#aaa;padding:32px;">Nenhum plano sincronizado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:16px;">{{ $planosSync->links() }}</div>

    </div>{{-- /mkt-page --}}
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function testSite(id, btn) {
    var el = document.getElementById('test-result-' + id);
    btn.disabled = true; btn.textContent = '…';
    fetch('{{ url("mikrotik/sites") }}/' + id + '/test', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(d => {
        el.textContent = d.ok ? '✓ Ligado — ' + (d.identity || '') : '✗ ' + (d.error || 'Erro');
        el.style.color = d.ok ? '#3bb273' : '#e05a4f';
        btn.disabled = false; btn.textContent = 'Testar';
    })
    .catch(() => { el.textContent = '✗ Falha de rede'; el.style.color = '#e05a4f'; btn.disabled = false; btn.textContent = 'Testar'; });
}

function runSync(btn) {
    var el = document.getElementById('syncResult');
    btn.disabled = true; btn.textContent = 'A sincronizar…'; el.textContent = '';
    fetch('{{ route('mikrotik.run-sync') }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        el.textContent = d.message || (d.ok ? 'Concluído.' : 'Erro.');
        el.style.color = d.ok ? '#3bb273' : '#e05a4f';
        btn.disabled = false; btn.textContent = '▶ Sync todos agora';
    })
    .catch(() => { el.textContent = 'Falha de rede.'; el.style.color = '#e05a4f'; btn.disabled = false; btn.textContent = '▶ Sync todos agora'; });
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
            var row = document.getElementById('row-' + id);
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
            var row = document.getElementById('row-' + id);
            if (row) row.cells[5].innerHTML = '<span class="estado-badge estado-suspenso">Suspenso</span>';
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
