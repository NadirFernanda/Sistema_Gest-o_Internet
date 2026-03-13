@extends('layouts.app')

@section('content')
<style>
/* â”€â”€ Admin Dashboard â”€â”€ */
.adm { --primary:#4f46e5; --success:#16a34a; --warning:#d97706; --danger:#dc2626;
       --bg:#f1f5f9; --surface:#fff; --border:#e2e8f0; --text:#1e293b; --muted:#64748b; --faint:#94a3b8; }
.adm-page { background:var(--bg); padding:2rem 0 3rem; }
.adm-wrap { max-width:1200px; margin:0 auto; padding:0 1.5rem; }
.adm-header { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:.75rem; margin-bottom:2rem; }
.adm-header h1 { font-size:1.45rem; font-weight:800; color:var(--text); margin:0 0 .15rem; letter-spacing:-.02em; }
.adm-crumb { font-size:.78rem; color:var(--faint); }
.adm-btn { display:inline-flex; align-items:center; justify-content:center; gap:.4rem; padding:.6rem 1.2rem;
           border-radius:8px; font-size:.875rem; font-weight:600; border:none; cursor:pointer;
           transition:all .15s; text-decoration:none; white-space:nowrap; font-family:inherit; }
.adm-btn-logout { background:#fee2e2; color:#dc2626; }
.adm-btn-logout:hover { background:#fecaca; }
.adm-btn-ghost { background:transparent; color:var(--muted); border:1.5px solid var(--border); }
.adm-btn-ghost:hover { background:var(--border); color:var(--text); }
.adm-btn-sm { padding:.38rem .85rem; font-size:.8rem; }
.adm-btn-primay-soft { background:#eef2ff; color:#4f46e5; }
.adm-btn-primay-soft:hover { background:#e0e7ff; }

/* Quick nav nav */
.adm-quicknav { display:flex; flex-wrap:wrap; gap:.5rem; margin-bottom:2rem; }
.adm-qnav-item { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .9rem; border-radius:8px;
                 background:var(--surface); border:1px solid var(--border); font-size:.82rem; font-weight:600;
                 color:var(--muted); text-decoration:none; transition:all .15s; }
.adm-qnav-item:hover { background:#eef2ff; border-color:#c7d2fe; color:#4f46e5; }
.adm-qnav-item.active { background:#eef2ff; border-color:#6366f1; color:#4f46e5; }

/* KPI grid */
.adm-kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:1rem; margin-bottom:2rem; }
.adm-kpi { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:1.25rem 1.4rem;
           box-shadow:0 1px 3px rgba(0,0,0,.05); border-left:4px solid var(--border); }
.adm-kpi.kpi-blue   { border-left-color:#3b82f6; }
.adm-kpi.kpi-green  { border-left-color:#22c55e; }
.adm-kpi.kpi-amber  { border-left-color:#f59e0b; }
.adm-kpi.kpi-purple { border-left-color:#8b5cf6; }
.adm-kpi.kpi-rose   { border-left-color:#f43f5e; }
.adm-kpi-icon { font-size:1.3rem; margin-bottom:.5rem; }
.adm-kpi-val  { font-size:1.75rem; font-weight:800; color:var(--text); line-height:1; margin:0 0 .2rem; letter-spacing:-.03em; }
.adm-kpi-lbl  { font-size:.75rem; color:var(--muted); font-weight:500; }
.adm-kpi-sub  { font-size:.78rem; color:var(--faint); margin-top:.3rem; border-top:1px solid var(--border); padding-top:.4rem; }

/* Section title */
.adm-sec { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--faint);
           margin:2rem 0 1rem; display:flex; align-items:center; gap:.5rem; }
.adm-sec::after { content:''; flex:1; height:1px; background:var(--border); }

/* Cards grid */
.adm-cards { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1rem; margin-bottom:1rem; }
.adm-card { background:var(--surface); border:1px solid var(--border); border-radius:12px; padding:1.4rem;
            box-shadow:0 1px 3px rgba(0,0,0,.05); }
.adm-card h3 { font-size:.9rem; font-weight:700; color:var(--text); margin:0 0 .85rem; display:flex; align-items:center; gap:.4rem; }
.adm-card p  { font-size:.85rem; color:var(--muted); margin:.3rem 0; }
.adm-card p strong { color:var(--text); }
.adm-card-actions { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:1rem; }
.adm-card-actions a { display:inline-flex; align-items:center; gap:.3rem; padding:.38rem .8rem; border-radius:7px;
                      font-size:.78rem; font-weight:600; background:#f1f5f9; color:#374151; text-decoration:none; border:1px solid var(--border); transition:all .15s; }
.adm-card-actions a:hover { background:#e0e7ff; border-color:#c7d2fe; color:#4f46e5; }

/* WiFi plan mini bars */
.plan-bars { display:flex; flex-direction:column; gap:.4rem; margin:.6rem 0; }
.plan-bar-row { display:flex; align-items:center; gap:.6rem; font-size:.8rem; }
.plan-bar-name { width:50px; color:var(--muted); font-weight:600; flex-shrink:0; }
.plan-bar-track { flex:1; height:6px; background:#f1f5f9; border-radius:999px; overflow:hidden; }
.plan-bar-fill { height:100%; border-radius:999px; }
.plan-bar-fill.diario  { background:#3b82f6; }
.plan-bar-fill.semanal { background:#8b5cf6; }
.plan-bar-fill.mensal  { background:#f59e0b; }
.plan-bar-count { font-weight:700; min-width:24px; text-align:right; }
.plan-bar-count.ok  { color:var(--success); }
.plan-bar-count.low { color:var(--warning); }
.plan-bar-count.out { color:var(--danger); }

/* Badges */
.adm-badge { display:inline-flex; align-items:center; gap:.25rem; padding:.18rem .55rem; border-radius:999px; font-size:.73rem; font-weight:700; }
.badge-green  { background:#dcfce7; color:#15803d; }
.badge-amber  { background:#fef3c7; color:#b45309; }
.badge-red    { background:#fee2e2; color:#b91c1c; }
.badge-blue   { background:#dbeafe; color:#1d4ed8; }
.badge-gray   { background:#f1f5f9; color:#475569; }

/* Alert banner */
.adm-banner { padding:.75rem 1rem; border-radius:8px; margin-bottom:1rem; font-size:.85rem; font-weight:600; display:flex; align-items:center; gap:.5rem; }
.adm-banner-warn { background:#fff7ed; border:1px solid #fed7aa; color:#c2410c; }
.adm-banner-ok   { background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; }

/* Table */
.adm-tcard  { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.06); }
.adm-table  { width:100%; border-collapse:collapse; font-size:.845rem; }
.adm-table thead tr { background:#f8fafc; }
.adm-table th { text-align:left; padding:.7rem 1rem; font-size:.7rem; font-weight:700; text-transform:uppercase;
                letter-spacing:.06em; color:var(--faint); border-bottom:1px solid var(--border); }
.adm-table td { padding:.6rem 1rem; border-bottom:1px solid #f8fafc; color:#374151; vertical-align:middle; }
.adm-table tbody tr:last-child td { border-bottom:none; }
.adm-table tbody tr:hover td { background:#fafbff; }
.adm-tcard-title { padding:.85rem 1.25rem; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; }
.adm-tcard-title strong { font-size:.85rem; font-weight:700; color:var(--text); }
</style>

<div class="adm">
<div class="adm-page">
<div class="adm-wrap">

  {{-- Header --}}
  <div class="adm-header">
    <div>
      <h1>Dashboard</h1>
      <p class="adm-crumb">Painel Administrativo â€” AngolaWiFi</p>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}">
      @csrf
      <button type="submit" class="adm-btn adm-btn-logout adm-btn-sm">âŽ‹ Sair</button>
    </form>
  </div>

  {{-- Quick nav --}}
  <nav class="adm-quicknav" aria-label="NavegaÃ§Ã£o admin">
    <a href="{{ route('admin.dashboard') }}"            class="adm-qnav-item active">ðŸ  VisÃ£o Geral</a>
    <a href="{{ route('admin.autovenda.index') }}"      class="adm-qnav-item">âš¡ Recargas</a>
    <a href="{{ route('admin.wifi_codes.index') }}"     class="adm-qnav-item">ðŸ”‘ CÃ³digos WiFi</a>
    <a href="{{ route('admin.resellers.index') }}"      class="adm-qnav-item">ðŸ¤ Revendedores</a>
    <a href="{{ route('admin.equipment.orders.index') }}" class="adm-qnav-item">ðŸ“¦ Encomendas</a>
    <a href="{{ route('admin.equipment.products.index') }}" class="adm-qnav-item">ðŸ›’ Produtos</a>
    <a href="{{ route('admin.family_requests.index') }}" class="adm-qnav-item">ðŸ  Planos Fam.</a>
    <a href="{{ route('admin.site_stats.index') }}"     class="adm-qnav-item">ðŸ“Š EstatÃ­sticas</a>
    <a href="{{ route('admin.reports') }}"              class="adm-qnav-item">ðŸ“ˆ RelatÃ³rios</a>
  </nav>

  {{-- KPI row --}}
  @php
    $wifiPlanLabels = ['diario' => 'DiÃ¡rio', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
    $maxPlan = max(1, max(array_map(fn($p) => $wifiCodesByPlan[$p] ?? 0, ['diario','semanal','mensal'])));
  @endphp
  <div class="adm-kpi-grid">
    <div class="adm-kpi kpi-blue">
      <div class="adm-kpi-icon">âš¡</div>
      <p class="adm-kpi-val">{{ $paidOrders }}</p>
      <p class="adm-kpi-lbl">Vendas confirmadas</p>
      <p class="adm-kpi-sub">{{ $totalOrders }} total Â· {{ $awaitingPayment }} pendentes</p>
    </div>
    <div class="adm-kpi kpi-green">
      <div class="adm-kpi-icon">ðŸ’°</div>
      <p class="adm-kpi-val">{{ number_format($totalRevenueAoa, 0, ',', '.') }}</p>
      <p class="adm-kpi-lbl">Receita AOA (autovenda)</p>
      <p class="adm-kpi-sub">Ordens pagas</p>
    </div>
    <div class="adm-kpi kpi-amber">
      <div class="adm-kpi-icon">ðŸ”‘</div>
      <p class="adm-kpi-val" style="color:{{ $availableWifiCodes > 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $availableWifiCodes }}</p>
      <p class="adm-kpi-lbl">CÃ³digos disponÃ­veis</p>
      <p class="adm-kpi-sub">{{ $usedWifiCodes }} utilizados no total</p>
    </div>
    <div class="adm-kpi kpi-purple">
      <div class="adm-kpi-icon">ðŸ¤</div>
      <p class="adm-kpi-val">{{ $pendingResellers }}</p>
      <p class="adm-kpi-lbl">Revendedores pendentes</p>
      <p class="adm-kpi-sub">{{ $totalResellers }} candidaturas total</p>
    </div>
    <div class="adm-kpi kpi-rose">
      <div class="adm-kpi-icon">ðŸ“¦</div>
      <p class="adm-kpi-val">{{ $newEquipOrders }}</p>
      <p class="adm-kpi-lbl">Encomendas novas</p>
      <p class="adm-kpi-sub">{{ $totalEquipOrders }} total Â· {{ number_format($totalEquipRevenue,0,',','.') }} AOA</p>
    </div>
  </div>

  {{-- Alerts --}}
  @if($availableWifiCodes === 0)
    <div class="adm-banner adm-banner-warn">âš  Sem stock de cÃ³digos WiFi! Importe cÃ³digos urgentemente.</div>
  @elseif($availableWifiCodes < 10)
    <div class="adm-banner adm-banner-warn">âš  Stock baixo ({{ $availableWifiCodes }} disponÃ­veis). Importe mÃ¡s cÃ³digos em breve.</div>
  @endif
  @if($pendingFamilyRequests > 0)
    <div class="adm-banner adm-banner-warn">âš  {{ $pendingFamilyRequests }} pedido(s) de plano familiar/empresarial aguardam confirmaÃ§Ã£o.</div>
  @endif

  {{-- Detail cards --}}
  <p class="adm-sec">MÃ³dulos</p>
  <div class="adm-cards">

    {{-- Autovenda --}}
    <div class="adm-card">
      <h3>âš¡ Autovenda</h3>
      <p>Total ordens: <strong>{{ $totalOrders }}</strong></p>
      <p>Pagas: <strong>{{ $paidOrders }}</strong></p>
      <p>A aguardar pagamento: <strong>{{ $awaitingPayment }}</strong></p>
      <p style="margin-top:.6rem;padding-top:.6rem;border-top:1px solid var(--border);">
        Receita: <strong>{{ number_format($totalRevenueAoa, 0, ',', '.') }} AOA</strong>
      </p>
      <div class="adm-card-actions">
        <a href="{{ route('admin.autovenda.index') }}">Ver ordens â†’</a>
        <a href="{{ route('admin.reports') }}">RelatÃ³rios</a>
      </div>
    </div>

    {{-- WiFi codes --}}
    <div class="adm-card">
      <h3>ðŸ”‘ Stock de CÃ³digos WiFi</h3>
      <div class="plan-bars">
        @foreach($wifiPlanLabels as $pid => $plabel)
          @php $n = $wifiCodesByPlan[$pid] ?? 0; $pct = min(100, round($n / $maxPlan * 100)); @endphp
          <div class="plan-bar-row">
            <span class="plan-bar-name">{{ $plabel }}</span>
            <div class="plan-bar-track"><div class="plan-bar-fill {{ $pid }}" style="width:{{ $pct }}%"></div></div>
            <span class="plan-bar-count {{ $n===0 ? 'out' : ($n<5 ? 'low' : 'ok') }}">{{ $n }}</span>
          </div>
        @endforeach
      </div>
      <p style="font-size:.78rem;color:var(--faint)">{{ $usedWifiCodes }} cÃ³digos utilizados</p>
      <div class="adm-card-actions"><a href="{{ route('admin.wifi_codes.index') }}">Gerir e importar â†’</a></div>
    </div>

    {{-- Revenda --}}
    <div class="adm-card">
      <h3>ðŸ¤ Revenda</h3>
      <p>Pendentes:
        @if($pendingResellers > 0)
          <strong><span class="adm-badge badge-amber">{{ $pendingResellers }} pendentes</span></strong>
        @else
          <strong>0</strong> â€” nenhum pendente
        @endif
      </p>
      <p>Total de candidaturas: <strong>{{ $totalResellers }}</strong></p>
      <div class="adm-card-actions">
        <a href="{{ route('admin.resellers.index') }}">Candidaturas â†’</a>
        <a href="{{ route('admin.resellers.purchases.index') }}">Compras em Bloco</a>
      </div>
    </div>

    {{-- Equipamentos --}}
    <div class="adm-card">
      <h3>ðŸ“¦ Equipamentos</h3>
      <p>Produtos no catÃ¡logo: <strong>{{ $totalProducts }}</strong></p>
      <p>Encomendas totais: <strong>{{ $totalEquipOrders }}</strong></p>
      <p>Novas (pendentes): <strong>{{ $newEquipOrders }}</strong></p>
      <p style="margin-top:.5rem;padding-top:.5rem;border-top:1px solid var(--border);">
        Receita: <strong>{{ number_format($totalEquipRevenue, 0, ',', '.') }} AOA</strong>
      </p>
      <div class="adm-card-actions">
        <a href="{{ route('admin.equipment.orders.index') }}">Encomendas â†’</a>
        <a href="{{ route('admin.equipment.products.index') }}">Produtos</a>
      </div>
    </div>

    {{-- Planos Fam/Emp --}}
    <div class="adm-card">
      <h3>ðŸ  Planos Familiares / Empresariais</h3>
      <p>Pendentes:
        @if($pendingFamilyRequests > 0)
          <strong><span class="adm-badge badge-amber">{{ $pendingFamilyRequests }}</span></strong>
        @else
          <strong>0</strong> â€” nenhum pendente
        @endif
      </p>
      <div class="adm-card-actions">
        <a href="{{ route('admin.family_requests.index') }}">Ver pedidos â†’</a>
      </div>
    </div>

    {{-- EstatÃ­sticas --}}
    <div class="adm-card">
      <h3>ðŸ“Š EstatÃ­sticas da PÃ¡gina</h3>
      <p style="color:var(--faint);font-size:.82rem;line-height:1.5;">Os 4 nÃºmeros de destaque na pÃ¡gina inicial (clientes activos, uptime, instalaÃ§Ã£o, suporte).</p>
      <div class="adm-card-actions">
        <a href="{{ route('admin.site_stats.index') }}">Editar estatÃ­sticas â†’</a>
      </div>
    </div>
  </div>

  {{-- Recent orders --}}
  <p class="adm-sec">Ãšltimas recargas</p>
  <div class="adm-tcard">
    <div class="adm-tcard-title">
      <strong>Ordens recentes de autovenda</strong>
      <a href="{{ route('admin.autovenda.index') }}" style="font-size:.78rem;color:#4f46e5;text-decoration:none;font-weight:600;">Ver todas â†’</a>
    </div>
    <div style="overflow-x:auto;">
      <table class="adm-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Plano</th>
            <th>Valor</th>
            <th>Estado</th>
            <th>MÃ©todo</th>
            <th>Criada em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($recentOrders as $order)
            <tr>
              <td style="color:var(--faint);font-size:.8rem;">#{{ $order->id }}</td>
              <td><strong>{{ $order->plan_name ?? $order->plan_id }}</strong></td>
              <td style="font-weight:700;">{{ number_format($order->amount_aoa, 0, ',', '.') }} <span style="color:var(--faint);font-weight:400;font-size:.78rem;">AOA</span></td>
              <td>
                @if($order->status === 'paid')
                  <span class="adm-badge badge-green">âœ“ Pago</span>
                @elseif($order->status === 'awaiting_payment')
                  <span class="adm-badge badge-amber">â³ Aguarda</span>
                @elseif($order->status === 'cancelled')
                  <span class="adm-badge badge-gray">âœ• Cancelado</span>
                @else
                  <span class="adm-badge badge-gray">{{ $order->status }}</span>
                @endif
              </td>
              <td style="color:var(--muted);font-size:.82rem;">{{ $order->payment_method ?? 'â€”' }}</td>
              <td style="color:var(--faint);font-size:.82rem;">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="6" style="padding:2rem;text-align:center;color:var(--faint);">Nenhuma ordem registada ainda.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>{{-- /wrap --}}
</div>{{-- /page --}}
</div>{{-- /adm --}}
@endsection
