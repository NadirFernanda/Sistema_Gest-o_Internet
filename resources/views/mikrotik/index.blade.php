@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

@section('content')
<div class="estoque-container-moderna">

    @include('layouts.partials.clientes-hero', [
        'title'    => 'MikroTik',
        'subtitle' => 'Estado da ligação e gestão de utilizadores HotSpot',
    ])

    {{-- ── Status card ──────────────────────────────────────────────────────── --}}
    <div style="max-width:1100px;margin:18px auto 0;display:flex;gap:14px;flex-wrap:wrap;align-items:stretch;">

        {{-- Connection --}}
        <div style="flex:1 1 300px;background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 4px 14px rgba(0,0,0,0.06);border-left:6px solid {{ $connection['ok'] ? '#3bb273' : '#e05a4f' }};">
            <div style="font-weight:700;font-size:1rem;margin-bottom:6px;">
                {{ $connection['ok'] ? '🟢 MikroTik ligado' : '🔴 MikroTik desligado' }}
            </div>
            @if($connection['ok'])
                <div style="color:#444;font-size:0.93rem;">
                    Identidade: <strong>{{ $connection['identity'] ?? '—' }}</strong><br>
                    Host: <code>{{ $connection['host'] }}</code>
                </div>
            @else
                <div style="color:#c0392b;font-size:0.93rem;">{{ $connection['error'] ?? 'Erro de ligação' }}</div>
            @endif
            <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
                <button onclick="testConn()" class="btn btn-ghost" style="height:34px;font-size:0.88rem;">Testar ligação</button>
                <button onclick="runSync()" class="btn btn-cta" style="height:34px;font-size:0.88rem;">▶ Sync agora</button>
            </div>
            <div id="connResult" style="margin-top:8px;font-size:0.88rem;color:#555;"></div>
        </div>

        {{-- Stats --}}
        <div style="flex:1 1 180px;background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 4px 14px rgba(0,0,0,0.06);text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:#3bb273;">{{ $planosSync->total() }}</div>
            <div style="color:#666;font-size:0.93rem;">Planos sincronizados</div>
        </div>
        <div style="flex:1 1 180px;background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 4px 14px rgba(0,0,0,0.06);text-align:center;">
            <div style="font-size:2rem;font-weight:800;color:{{ $planosPending > 0 ? '#f1c453' : '#b0b8c1' }};">{{ $planosPending }}</div>
            <div style="color:#666;font-size:0.93rem;">Por sincronizar</div>
        </div>

        {{-- Profiles --}}
        @if(count($profiles) > 0)
        <div style="flex:2 1 260px;background:#fff;border-radius:10px;padding:18px 22px;box-shadow:0 4px 14px rgba(0,0,0,0.06);">
            <div style="font-weight:700;margin-bottom:8px;font-size:0.97rem;">Perfis HotSpot disponíveis</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                @foreach($profiles as $p)
                    <span style="background:#f0f4ff;color:#3b4fa8;border-radius:6px;padding:3px 10px;font-size:0.88rem;font-weight:600;">{{ $p['name'] ?? '?' }}</span>
                @endforeach
            </div>
            <div style="margin-top:8px;font-size:0.82rem;color:#888;">
                Configure o perfil de cada template em <strong>PlanTemplate → metadata.mikrotik_profile</strong>.
            </div>
        </div>
        @endif
    </div>

    {{-- ── Planos sincronizados ─────────────────────────────────────────────── --}}
    <div style="max-width:1100px;margin:22px auto 0;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:10px;">Planos com utilizador MikroTik</h3>

        <div class="estoque-tabela-moderna">
            <table class="tabela-estoque-moderna" style="width:100%;border-collapse:separate;">
                <thead>
                    <tr>
                        <th>Cliente</th>
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
                        <td>{{ $plano->nome }}</td>
                        <td><code>{{ $plano->mikrotik_username }}</code></td>
                        <td>
                            @php
                                $ec = match($plano->estado) {
                                    'Ativo'     => '#3bb273',
                                    'Em aviso'  => '#f1c453',
                                    'Suspenso'  => '#e05a4f',
                                    default     => '#b0b8c1',
                                };
                            @endphp
                            <span style="color:{{ $ec }};font-weight:700;">{{ $plano->estado }}</span>
                        </td>
                        <td>{{ $plano->proxima_renovacao?->format('d/m/Y') ?? '—' }}</td>
                        <td style="font-size:0.88rem;color:#777;">
                            {{ $plano->mikrotik_synced_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td style="white-space:nowrap;">
                            <button onclick="syncPlano({{ $plano->id }}, this)" class="btn-icon btn-primary" title="Sincronizar" style="width:34px;height:34px;border-radius:8px;border:none;cursor:pointer;background:#4a90d9;color:#fff;font-size:1rem;">↻</button>
                            <button onclick="suspendPlano({{ $plano->id }}, this)" class="btn-icon" title="Suspender" style="width:34px;height:34px;border-radius:8px;border:1px solid #e05a4f;background:#fff;color:#e05a4f;cursor:pointer;font-size:1rem;">⏸</button>
                            <button onclick="removePlano({{ $plano->id }}, this)" class="btn-icon" title="Remover do MikroTik" style="width:34px;height:34px;border-radius:8px;border:1px solid #ccc;background:#f3f3f3;color:#555;cursor:pointer;font-size:1rem;">✕</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;color:#888;padding:24px;">Nenhum plano sincronizado ainda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:14px;">
            {{ $planosSync->links() }}
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function testConn() {
    document.getElementById('connResult').textContent = 'A testar…';
    fetch('{{ route('mikrotik.test') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            document.getElementById('connResult').textContent =
                d.ok ? '✓ Ligado — ' + (d.identity || '') : '✗ ' + (d.error || 'Erro');
        })
        .catch(() => { document.getElementById('connResult').textContent = '✗ Falha de rede'; });
}

function runSync() {
    document.getElementById('connResult').textContent = 'A iniciar sync…';
    fetch('{{ route('mikrotik.run-sync') }}', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(d => { document.getElementById('connResult').textContent = d.message || (d.ok ? 'Sync iniciado.' : 'Erro.'); });
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
            if (row) row.cells[5].textContent = d.mikrotik_synced_at;
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
