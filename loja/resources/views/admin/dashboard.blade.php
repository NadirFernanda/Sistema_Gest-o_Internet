@extends('layouts.app')

@section('content')
<style>
/* ── Admin Dashboard ─────────────────────────────────── */
:root {
  --a-bg:     #f4f6f9;
  --a-surf:   #ffffff;
  --a-border: #dde2ea;
  --a-text:   #1a202c;
  --a-muted:  #64748b;
  --a-faint:  #9aa5b4;
  --a-indigo: #4f46e5;
  --a-blue:   #3b82f6;
  --a-green:  #16a34a;
  --a-amber:  #d97706;
  --a-red:    #dc2626;
  --a-purple: #7c3aed;
  --a-rose:   #e11d48;
}
.ap { font-family: Inter, system-ui, sans-serif; background: var(--a-bg); min-height: 60vh; padding: 2rem 0 4rem; color: var(--a-text); }
.ap-wrap { max-width: 1140px; margin: 0 auto; padding: 0 1.5rem; }
.ap-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.5rem; }
.ap-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .15rem; letter-spacing: -.02em; }
.ap-sub { font-size: .78rem; color: var(--a-faint); margin: 0; }
.ap-btn-logout { font-size: .82rem; font-weight: 600; padding: .4rem .85rem; border: 1px solid #fecaca; border-radius: 7px; background: #fff1f2; color: var(--a-red); cursor: pointer; font-family: inherit; }
.ap-btn-logout:hover { background: #fecaca; }
.ap-nav { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: 1.75rem; }
.ap-nav a { font-size: .8rem; font-weight: 600; padding: .38rem .85rem; border-radius: 7px; border: 1px solid var(--a-border); background: var(--a-surf); color: var(--a-muted); text-decoration: none; white-space: nowrap; }
.ap-nav a:hover, .ap-nav a.here { background: #eef2ff; border-color: #c7d2fe; color: var(--a-indigo); }
.ap-sec { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--a-faint); margin: 2rem 0 1rem; display: flex; align-items: center; gap: .6rem; }
.ap-sec::after { content: ''; flex: 1; height: 1px; background: var(--a-border); }
.ap-kpis { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .85rem; margin-bottom: 1.5rem; }
.ap-kpi { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; padding: 1.2rem 1.3rem; border-left: 4px solid var(--a-border); }
.ap-kpi.k-blue   { border-left-color: var(--a-blue); }
.ap-kpi.k-green  { border-left-color: var(--a-green); }
.ap-kpi.k-amber  { border-left-color: var(--a-amber); }
.ap-kpi.k-purple { border-left-color: var(--a-purple); }
.ap-kpi.k-rose   { border-left-color: var(--a-rose); }
.ap-kpi-val { font-size: 1.75rem; font-weight: 800; line-height: 1; margin: 0 0 .2rem; }
.ap-kpi-lbl { font-size: .75rem; color: var(--a-muted); font-weight: 500; line-height: 1.3; }
.ap-kpi-sub { font-size: .72rem; color: var(--a-faint); margin: .35rem 0 0; padding-top: .35rem; border-top: 1px solid var(--a-border); }
.ap-banner-warn { padding: .7rem 1rem; border-radius: 8px; font-size: .84rem; font-weight: 600; margin-bottom: .6rem; background: #fff7ed; border: 1px solid #fed7aa; color: #c2410c; }
.ap-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)); gap: .85rem; }
.ap-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; padding: 1.3rem; }
.ap-card h3 { font-size: .88rem; font-weight: 700; margin: 0 0 .75rem; color: var(--a-text); }
.ap-card p  { font-size: .84rem; color: var(--a-muted); margin: .25rem 0; }
.ap-card p strong { color: var(--a-text); }
.ap-card-actions { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .9rem; padding-top: .75rem; border-top: 1px solid var(--a-border); }
.ap-card-actions a { font-size: .78rem; font-weight: 600; padding: .32rem .75rem; border-radius: 6px; background: #f1f5f9; color: #374151; text-decoration: none; border: 1px solid var(--a-border); }
.ap-card-actions a:hover { background: #eef2ff; border-color: #c7d2fe; color: var(--a-indigo); }
.plan-bars { display: flex; flex-direction: column; gap: .45rem; margin: .5rem 0; }
.plan-bar { display: flex; align-items: center; gap: .6rem; font-size: .8rem; }
.plan-bar-name  { width: 52px; font-weight: 600; color: var(--a-muted); flex-shrink: 0; }
.plan-bar-track { flex: 1; height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.plan-bar-fill  { height: 100%; border-radius: 999px; }
.fill-blue   { background: var(--a-blue); }
.fill-purple { background: var(--a-purple); }
.fill-amber  { background: var(--a-amber); }
.plan-bar-count { font-weight: 700; min-width: 24px; text-align: right; }
.c-ok  { color: var(--a-green); }
.c-low { color: var(--a-amber); }
.c-out { color: var(--a-red); }
.badge { display: inline-block; padding: .2rem .6rem; border-radius: 999px; font-size: .73rem; font-weight: 700; }
.bg-green  { background: #dcfce7; color: #15803d; }
.bg-amber  { background: #fef3c7; color: #b45309; }
.bg-gray   { background: #f1f5f9; color: #475569; }
.ap-tcard { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 10px; overflow: hidden; margin-top: 2rem; }
.ap-tcard-head { padding: .75rem 1.1rem; border-bottom: 1px solid var(--a-border); display: flex; justify-content: space-between; align-items: center; }
.ap-tcard-head strong { font-size: .84rem; font-weight: 700; }
.ap-tcard-head a { font-size: .78rem; color: var(--a-indigo); text-decoration: none; font-weight: 600; }
.ap-table { width: 100%; border-collapse: collapse; font-size: .845rem; }
.ap-table thead { background: #f8fafc; }
.ap-table th { text-align: left; padding: .65rem 1rem; font-size: .69rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--a-faint); border-bottom: 1px solid var(--a-border); white-space: nowrap; }
.ap-table td { padding: .6rem 1rem; border-bottom: 1px solid #f4f6f9; vertical-align: middle; color: #374151; }
.ap-table tbody tr:last-child td { border-bottom: none; }
.ap-table tbody tr:hover td { background: #fafbff; }
.ap-table .dim { color: var(--a-faint); font-size: .82rem; }
</style>

<div class="ap">
<div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Dashboard</h1>
      <p class="ap-sub">Painel Administrativo &mdash; AngolaWiFi</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit" class="ap-btn-logout">Sair</button>
    </form>
  </div>

  <nav class="ap-nav">
    <a href="{{ route('admin.dashboard') }}" class="here">Dashboard</a>
    <a href="{{ route('admin.autovenda.index') }}">Recargas</a>
    <a href="{{ route('admin.wifi_codes.index') }}">C&oacute;digos WiFi</a>
    <a href="{{ route('admin.resellers.index') }}">Revendedores</a>
    <a href="{{ route('admin.equipment.orders.index') }}">Encomendas</a>
    <a href="{{ route('admin.equipment.products.index') }}">Produtos</a>
    <a href="{{ route('admin.family_requests.index') }}">Planos Fam.</a>
    <a href="{{ route('admin.site_stats.index') }}">Estat&iacute;sticas</a>
    <a href="{{ route('admin.reports') }}">Relat&oacute;rios</a>
  </nav>

  @if($availableWifiCodes === 0)
    <div class="ap-banner-warn">&#9888; Sem stock de c&oacute;digos WiFi &mdash; importe urgentemente.</div>
  @elseif($availableWifiCodes < 10)
    <div class="ap-banner-warn">&#9888; Stock baixo ({{ $availableWifiCodes }} dispon&iacute;veis). Importe mais c&oacute;digos.</div>
  @endif
  @if($pendingFamilyRequests > 0)
    <div class="ap-banner-warn">&#9888; {{ $pendingFamilyRequests }} pedido(s) de plano familiar aguardam confirma&ccedil;&atilde;o.</div>
  @endif

  <p class="ap-sec">Resumo</p>
  <div class="ap-kpis">
    <div class="ap-kpi k-blue">
      <p class="ap-kpi-val">{{ $paidOrders }}</p>
      <p class="ap-kpi-lbl">Vendas confirmadas</p>
      <p class="ap-kpi-sub">{{ $totalOrders }} total &middot; {{ $awaitingPayment }} pendentes</p>
    </div>
    <div class="ap-kpi k-green">
      <p class="ap-kpi-val">{{ number_format($totalRevenueAoa, 0, ',', '.') }}</p>
      <p class="ap-kpi-lbl">Receita AOA (autovenda)</p>
      <p class="ap-kpi-sub">Ordens pagas</p>
    </div>
    <div class="ap-kpi k-amber">
      <p class="ap-kpi-val" style="color:{{ $availableWifiCodes > 0 ? 'var(--a-green)' : 'var(--a-red)' }}">{{ $availableWifiCodes }}</p>
      <p class="ap-kpi-lbl">C&oacute;digos WiFi dispon&iacute;veis</p>
      <p class="ap-kpi-sub">{{ $usedWifiCodes }} utilizados</p>
    </div>
    <div class="ap-kpi k-purple">
      <p class="ap-kpi-val">{{ $pendingResellers }}</p>
      <p class="ap-kpi-lbl">Revendedores pendentes</p>
      <p class="ap-kpi-sub">{{ $totalResellers }} candidaturas</p>
    </div>
    <div class="ap-kpi k-rose">
      <p class="ap-kpi-val">{{ $newEquipOrders }}</p>
      <p class="ap-kpi-lbl">Encomendas novas</p>
      <p class="ap-kpi-sub">{{ $totalEquipOrders }} total &middot; {{ number_format($totalEquipRevenue,0,',','.') }} AOA</p>
    </div>
  </div>

  <p class="ap-sec">M&oacute;dulos</p>
  @php
    $wifiPlanText = ['diario' => 'Di&aacute;rio', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
    $maxPlan = max(1, ...array_map(fn($p) => $wifiCodesByPlan[$p] ?? 0, ['diario','semanal','mensal']));
  @endphp
  <div class="ap-cards">

    <div class="ap-card">
      <h3>Autovenda</h3>
      <p>Total: <strong>{{ $totalOrders }}</strong></p>
      <p>Pagas: <strong>{{ $paidOrders }}</strong></p>
      <p>Aguarda pagamento: <strong>{{ $awaitingPayment }}</strong></p>
      <p style="padding-top:.5rem;margin-top:.5rem;border-top:1px solid var(--a-border);">Receita: <strong>{{ number_format($totalRevenueAoa, 0, ',', '.') }} AOA</strong></p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.autovenda.index') }}">Ver ordens &rarr;</a>
        <a href="{{ route('admin.reports') }}">Relat&oacute;rios</a>
      </div>
    </div>

    <div class="ap-card">
      <h3>Stock de C&oacute;digos WiFi</h3>
      <div class="plan-bars">
        @foreach(['diario','semanal','mensal'] as $pid)
          @php $n = $wifiCodesByPlan[$pid] ?? 0; $pct = min(100, round($n / $maxPlan * 100)); @endphp
          <div class="plan-bar">
            <span class="plan-bar-name">{!! $wifiPlanText[$pid] !!}</span>
            <div class="plan-bar-track"><div class="plan-bar-fill fill-{{ $pid === 'diario' ? 'blue' : ($pid === 'semanal' ? 'purple' : 'amber') }}" style="width:{{ $pct }}%"></div></div>
            <span class="plan-bar-count {{ $n===0 ? 'c-out' : ($n<5 ? 'c-low' : 'c-ok') }}">{{ $n }}</span>
          </div>
        @endforeach
      </div>
      <p style="font-size:.78rem;color:var(--a-faint);margin:.3rem 0 0;">{{ $usedWifiCodes }} utilizados</p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.wifi_codes.index') }}">Gerir e importar &rarr;</a>
      </div>
    </div>

    <div class="ap-card">
      <h3>Revenda</h3>
      <p>Pendentes:
        @if($pendingResellers > 0)
          <span class="badge bg-amber">{{ $pendingResellers }}</span>
        @else <strong>0</strong>
        @endif
      </p>
      <p>Total de candidaturas: <strong>{{ $totalResellers }}</strong></p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.resellers.index') }}">Candidaturas &rarr;</a>
        <a href="{{ route('admin.resellers.purchases.index') }}">Compras em Bloco</a>
      </div>
    </div>

    <div class="ap-card">
      <h3>Equipamentos</h3>
      <p>Produtos: <strong>{{ $totalProducts }}</strong></p>
      <p>Encomendas: <strong>{{ $totalEquipOrders }}</strong></p>
      <p>Pendentes: <strong>{{ $newEquipOrders }}</strong></p>
      <p style="padding-top:.5rem;margin-top:.5rem;border-top:1px solid var(--a-border);">Receita: <strong>{{ number_format($totalEquipRevenue, 0, ',', '.') }} AOA</strong></p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.equipment.orders.index') }}">Encomendas &rarr;</a>
        <a href="{{ route('admin.equipment.products.index') }}">Produtos</a>
      </div>
    </div>

    <div class="ap-card">
      <h3>Planos Familiares / Empresariais</h3>
      <p>Pendentes:
        @if($pendingFamilyRequests > 0)
          <span class="badge bg-amber">{{ $pendingFamilyRequests }}</span>
        @else <strong>0</strong>
        @endif
      </p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.family_requests.index') }}">Ver pedidos &rarr;</a>
      </div>
    </div>

    <div class="ap-card">
      <h3>Estat&iacute;sticas da P&aacute;gina</h3>
      <p style="font-size:.82rem;line-height:1.5;">Os 4 n&uacute;meros de destaque na p&aacute;gina inicial.</p>
      <div class="ap-card-actions">
        <a href="{{ route('admin.site_stats.index') }}">Editar &rarr;</a>
      </div>
    </div>
  </div>

  <div class="ap-tcard">
    <div class="ap-tcard-head">
      <strong>&Uacute;ltimas recargas</strong>
      <a href="{{ route('admin.autovenda.index') }}">Ver todas &rarr;</a>
    </div>
    <div style="overflow-x:auto;">
      <table class="ap-table">
        <thead>
          <tr>
            <th>#</th><th>Plano</th><th>Valor</th><th>Estado</th><th>M&eacute;todo</th><th>Criada em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentOrders as $order)
            <tr>
              <td class="dim">#{{ $order->id }}</td>
              <td><strong>{{ $order->plan_name ?? $order->plan_id }}</strong></td>
              <td style="font-weight:700;">{{ number_format($order->amount_aoa, 0, ',', '.') }} <span class="dim">AOA</span></td>
              <td>
                @if($order->status === 'paid')
                  <span class="badge bg-green">Pago</span>
                @elseif($order->status === 'awaiting_payment')
                  <span class="badge bg-amber">Aguarda</span>
                @else
                  <span class="badge bg-gray">{{ $order->status }}</span>
                @endif
              </td>
              <td class="dim">{{ $order->payment_method ?? '&mdash;' }}</td>
              <td class="dim">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;color:var(--a-faint);">Nenhuma ordem registada ainda.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>
@endsection