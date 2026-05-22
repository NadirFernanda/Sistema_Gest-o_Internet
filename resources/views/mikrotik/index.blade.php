@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'MikroTik',
        'subtitle' => 'Gestão de sites e utilizadores HotSpot',
    ])

    {{-- ── Toolbar padrão ──────────────────────────────────────────────────── --}}
    <div class="clientes-toolbar" style="max-width:1100px;margin:18px auto 0;display:flex;flex-wrap:nowrap;gap:8px;align-items:center;">
        <a href="{{ route('mikrotik.sites.create') }}" class="btn btn-cta" style="white-space:nowrap;">+ Novo Site</a>

        <button onclick="runSync(this)" class="btn btn-ghost" style="white-space:nowrap;" title="Percorre todos os sites e actualiza/cria utilizadores HotSpot para planos activos ainda não sincronizados">
            ▶ Sync todos agora
        </button>

        <span style="font-size:0.88rem;color:#888;white-space:nowrap;">
            {{ $planosPending }} por sincronizar
        </span>

        <div id="syncResult" style="font-size:0.88rem;color:#555;flex:1;"></div>

        <a href="{{ route('dashboard') }}" class="btn btn-ghost" style="white-space:nowrap;">Painel</a>
    </div>

    {{-- ── Sites / RouterBoards ─────────────────────────────────────────────── --}}
    <div style="max-width:1100px;margin:16px auto 0;display:flex;gap:14px;flex-wrap:wrap;">
        @forelse($sites as $site)
        <div style="flex:1 1 300px;background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 4px 14px rgba(0,0,0,0.06);border-left:6px solid {{ $site->active ? '#3bb273' : '#b0b8c1' }};">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
                <div>
                    <div style="font-weight:700;font-size:1rem;">{{ $site->nome }}</div>
                    @if($site->localizacao)
                    <div style="font-size:0.82rem;color:#888;margin-top:2px;">{{ $site->localizacao }}</div>
                    @endif
                    <div style="margin-top:6px;font-size:0.88rem;color:#555;">
                        <code>{{ $site->host }}:{{ $site->port }}</code> &middot; {{ $site->clientes_count }} cliente(s)
                    </div>
                    @if(! $site->active)
                    <span style="font-size:0.8rem;color:#b0b8c1;font-weight:600;">INACTIVO</span>
                    @endif
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    <button onclick="testSite({{ $site->id }}, this)" class="btn btn-ghost" style="height:30px;font-size:0.8rem;padding:0 10px;">Testar</button>
                    <a href="{{ route('mikrotik.sites.edit', $site) }}" class="btn btn-ghost" style="height:30px;font-size:0.8rem;padding:0 10px;display:inline-flex;align-items:center;">Editar</a>
                </div>
            </div>
            <div id="test-result-{{ $site->id }}" style="margin-top:8px;font-size:0.82rem;color:#555;"></div>
        </div>
        @empty
        <div style="flex:1;background:#fff;border-radius:10px;padding:28px;text-align:center;color:#888;box-shadow:0 4px 14px rgba(0,0,0,0.06);">
            Nenhum site configurado. <a href="{{ route('mikrotik.sites.create') }}">Adicionar o primeiro site</a>.
        </div>
        @endforelse
    </div>

    {{-- ── Planos sincronizados ─────────────────────────────────────────────── --}}
    <div style="max-width:1100px;margin:26px auto 0;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:10px;">Planos com utilizador MikroTik</h3>

        <div class="estoque-tabela-moderna">
            <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Site</th>
                        <th>Plano</th>
                        <th>Username MikroTik</th>
                        <th>Estado</th>
                        <th>Renovação</th>
                        <th>Última sync</th>
                        <th>Acções</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planosSync as $plano)
                    <tr id="row-{{ $plano->id }}">
                        <td>{{ $plano->cliente?->nome ?? '—' }}</td>
                        <td style="font-size:0.85rem;color:#666;">{{ $plano->cliente?->mikrotikSite?->nome ?? '—' }}</td>
                        <td>{{ $plano->nome }}</td>
                        <td><code>{{ $plano->mikrotik_username }}</code></td>
                        <td>
                            @php
                                $ec = match($plano->estado) {
                                    'Ativo'    => '#3bb273',
                                    'Em aviso' => '#f1c453',
                                    'Suspenso' => '#e05a4f',
                                    default    => '#b0b8c1',
                                };
                            @endphp
                            <span style="color:{{ $ec }};font-weight:700;">{{ $plano->estado }}</span>
                        </td>
                        <td>{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
                        <td style="font-size:0.88rem;color:#777;">{{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td style="white-space:nowrap;">
                            <button onclick="syncPlano({{ $plano->id }}, this)" class="btn-icon btn-primary" title="Sincronizar" style="width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;background:#4a90d9;color:#fff;font-size:1rem;">↻</button>
                            <button onclick="suspendPlano({{ $plano->id }}, this)" class="btn-icon" title="Suspender" style="width:34px;height:34px;border-radius:8px;border:1px solid #e05a4f;background:#fff;color:#e05a4f;cursor:pointer;font-size:1rem;">⏸</button>
                            <button onclick="removePlano({{ $plano->id }}, this)" class="btn-icon" title="Remover do MikroTik" style="width:34px;height:34px;border-radius:8px;border:1px solid #ccc;background:#f3f3f3;color:#555;cursor:pointer;font-size:1rem;">✕</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" style="text-align:center;color:#888;padding:24px;">Nenhum plano sincronizado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">{{ $planosSync->links() }}</div>
    </div>
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
    .catch(() => { el.textContent = '✗ Falha de rede'; btn.disabled = false; btn.textContent = 'Testar'; });
}

function runSync(btn) {
    var el = document.getElementById('syncResult');
    btn.disabled = true;
    btn.textContent = 'A sincronizar…';
    el.textContent = '';
    fetch('{{ route('mikrotik.run-sync') }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => {
        el.textContent = d.message || (d.ok ? 'Concluído.' : 'Erro.');
        el.style.color = d.ok ? '#3bb273' : '#e05a4f';
        btn.disabled = false;
        btn.textContent = '▶ Sync todos agora';
    })
    .catch(() => {
        el.textContent = 'Falha de rede.';
        el.style.color = '#e05a4f';
        btn.disabled = false;
        btn.textContent = '▶ Sync todos agora';
    });
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
            if (row) row.cells[6].textContent = d.mikrotik_synced_at;
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
    .then(d => { btn.textContent = d.ok ? '✓' : '✗'; setTimeout(() => { btn.disabled = false; btn.textContent = '⏸'; }, 2000); });
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
