@extends('layouts.app')

@push('styles')
<style>
/* ── Admin Dashboard — rv-style ── */
.adm-page {
  min-height: 80vh;
  background: #f8fafc;
  padding: 2.5rem 1rem 6rem;
}
.adm-wrap {
  max-width: 1100px;
  margin: 0 auto;
}

/* Topbar */
.adm-topbar {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
  padding: 1.1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: .75rem;
  margin-bottom: 1.5rem;
}
.adm-topbar-left { display: flex; align-items: center; gap: .9rem; }
.adm-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg,#f7b500,#d97706);
  color: #1a202c;
  font-size: 1.15rem;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.adm-topbar-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
.adm-topbar-sub  { font-size: .82rem; color: #64748b; margin-top: .1rem; }
.adm-logout-btn {
  padding: .5rem 1.1rem;
  border: 1.5px solid #fecaca;
  border-radius: .6rem;
  background: #fff1f2;
  font-size: .85rem;
  font-weight: 600;
  color: #dc2626;
  cursor: pointer;
  transition: background .2s;
}
.adm-logout-btn:hover { background: #fecaca; }

/* Alerts */
.adm-alert {
  border-radius: .75rem;
  padding: .9rem 1.1rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  font-size: .92rem;
}
.adm-alert.warn { background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; }
.adm-alert-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: -.05rem; }
.adm-alert strong { font-weight: 700; display: block; margin-bottom: .15rem; }

/* KPI strip */
.adm-kpis {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(185px, 1fr));
  gap: .85rem;
  margin-bottom: .5rem;
}
.adm-kpi {
  background: #fff;
  border-radius: .9rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.1rem 1.2rem;
  border-top: 3px solid transparent;
}
.adm-kpi.k-brand  { border-color: #f7b500; }
.adm-kpi.k-green  { border-color: #16a34a; }
.adm-kpi.k-amber  { border-color: #d97706; }
.adm-kpi.k-purple { border-color: #7c3aed; }
.adm-kpi.k-rose   { border-color: #e11d48; }
.adm-kpi.k-teal   { border-color: #0d9488; }
.adm-kpi-val { font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: .2rem; }
.adm-kpi-lbl { font-size: .74rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; }
.adm-kpi-sub { font-size: .72rem; color: #9aa5b4; margin-top: .35rem; padding-top: .35rem; border-top: 1px solid #f1f5f9; }

/* Accordion menu */
.adm-menu { display: flex; flex-direction: column; gap: .65rem; margin-top: 1.5rem; }
.adm-menu-item { border-radius: .85rem; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.07); border: 1.5px solid #e2e8f0; background: #fff; }
.adm-menu-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 1.1rem 1.4rem; background: #f7b500; color: #1a202c;
  font-size: 1.05rem; font-weight: 700; cursor: pointer;
  border: none; outline: none; text-align: left; gap: .75rem;
  transition: background .15s;
}
.adm-menu-btn:hover, .adm-menu-btn.open { background: #e0a800; }
.adm-menu-btn-left { display: flex; align-items: center; gap: .65rem; flex: 1; }
.adm-menu-icon  { font-size: 1.2rem; flex-shrink: 0; }
.adm-menu-label { flex: 1; }
.adm-menu-badge {
  font-size: .72rem; font-weight: 700; padding: .2rem .65rem;
  border-radius: 999px; white-space: nowrap;
}
.adm-menu-chevron {
  font-size: 1.4rem; line-height: 1; transition: transform .25s; color: rgba(26,32,44,.5);
}
.adm-menu-chevron.open { transform: rotate(90deg); color: #1a202c; }
.adm-menu-body { padding: 1.25rem; background: #f8fafc; border-top: 1.5px solid #e2e8f0; }

/* Panel inside accordion */
.adm-panel {
  background: #fff;
  border-radius: .9rem;
  box-shadow: 0 1px 6px rgba(0,0,0,.05);
  padding: 1.25rem 1.35rem;
  margin-bottom: 1rem;
}
.adm-panel:last-child { margin-bottom: 0; }
.adm-panel-title {
  font-size: .95rem;
  font-weight: 800;
  color: #0f172a;
  padding-bottom: .65rem;
  margin-bottom: .9rem;
  border-bottom: 1.5px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .45rem;
}

/* Action links */
.adm-actions { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .9rem; }
.adm-btn {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .42rem .95rem;
  border-radius: .55rem;
  font-size: .83rem;
  font-weight: 700;
  text-decoration: none;
  border: 1.5px solid #e2e8f0;
  background: #fff;
  color: #374151;
  transition: border-color .15s, background .15s, color .15s;
  cursor: pointer;
}
.adm-btn:hover { background: #fffbeb; border-color: #f7b500; color: #92400e; }
.adm-btn.primary { background: #f7b500; border-color: #f7b500; color: #1a202c; }
.adm-btn.primary:hover { background: #e0a800; border-color: #e0a800; }
.adm-btn.purple { background: #f5f3ff; border-color: #c4b5fd; color: #7c3aed; }
.adm-btn.purple:hover { background: #ede9fe; }
.adm-btn.red { background: #fff1f2; border-color: #fecaca; color: #dc2626; }
.adm-btn.red:hover { background: #fecaca; }

/* Stats row */
.adm-stats-row { display: flex; flex-wrap: wrap; gap: .65rem; margin-bottom: .75rem; }
.adm-stat-pill {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: .6rem;
  padding: .45rem .9rem;
  font-size: .85rem;
  color: #374151;
}
.adm-stat-pill strong { color: #0f172a; font-size: 1rem; }

/* Plan bars */
.adm-bars { display: flex; flex-direction: column; gap: .45rem; margin: .5rem 0; }
.adm-bar { display: flex; align-items: center; gap: .6rem; font-size: .82rem; }
.adm-bar-name  { width: 56px; font-weight: 600; color: #64748b; flex-shrink: 0; }
.adm-bar-track { flex: 1; height: 7px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
.adm-bar-fill  { height: 100%; border-radius: 999px; }
.adm-bar-count { font-weight: 700; min-width: 28px; text-align: right; }
.c-ok  { color: #16a34a; }
.c-low { color: #d97706; }
.c-out { color: #dc2626; }

/* Recent table */
.adm-tbl-wrap { overflow-x: auto; }
.adm-tbl {
  width: 100%;
  border-collapse: collapse;
  font-size: .855rem;
  min-width: 520px;
}
.adm-tbl thead { background: #f8fafc; }
.adm-tbl th {
  text-align: left;
  padding: .55rem 1rem;
  font-size: .7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #9aa5b4;
  border-bottom: 1.5px solid #e2e8f0;
  white-space: nowrap;
}
.adm-tbl td {
  padding: .58rem 1rem;
  border-bottom: 1px solid #f4f6f9;
  vertical-align: middle;
  color: #374151;
}
.adm-tbl tbody tr:last-child td { border-bottom: none; }
.adm-tbl tbody tr:hover td { background: #fffdf5; }
.adm-tbl .dim { color: #9aa5b4; font-size: .82rem; }
.badge-pill {
  display: inline-block;
  padding: .18rem .55rem;
  border-radius: 999px;
  font-size: .72rem;
  font-weight: 700;
}
.bp-green  { background: #dcfce7; color: #15803d; }
.bp-amber  { background: #fef3c7; color: #b45309; }
.bp-gray   { background: #f1f5f9; color: #475569; }
.bp-red    { background: #fee2e2; color: #b91c1c; }

@media (max-width: 640px) {
  .adm-page { padding: 1.25rem .65rem 6rem; }
  .adm-kpis { grid-template-columns: 1fr 1fr; gap: .6rem; }
  .adm-menu-btn { font-size: .97rem; padding: .9rem 1rem; }
  .adm-menu-body { padding: .9rem; }
  .adm-panel { padding: 1rem .9rem; }
  .adm-topbar { flex-direction: column; align-items: flex-start; }
}
@media (max-width: 480px) {
  .adm-kpis { grid-template-columns: 1fr; }
  .adm-kpi-val { font-size: 1.4rem; }
}
</style>
@endpush

@section('content')
<div class="adm-page">
<div class="adm-wrap">

  {{-- Topbar --}}
  <div class="adm-topbar">
    <div class="adm-topbar-left">
      <div class="adm-avatar">A</div>
      <div>
        <div class="adm-topbar-name">Painel Administrativo</div>
        <div class="adm-topbar-sub">AngolaWiFi &mdash; Área de Gestão</div>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
      @csrf
      <button type="submit" class="adm-logout-btn">Sair</button>
    </form>
  </div>

  {{-- Alertas --}}
  @if($availableWifiCodes === 0)
    <div class="adm-alert warn">
      <span class="adm-alert-icon">⚠️</span>
      <div><strong>Sem stock de códigos WiFi</strong>Importe urgentemente novos códigos.</div>
    </div>
  @elseif($availableWifiCodes < 10)
    <div class="adm-alert warn">
      <span class="adm-alert-icon">⚠️</span>
      <div><strong>Stock baixo</strong>Apenas {{ $availableWifiCodes }} código(s) disponíveis. Importe mais.</div>
    </div>
  @endif
  @if($pendingFamilyRequests > 0)
    <div class="adm-alert warn">
      <span class="adm-alert-icon">🏠</span>
      <div><strong>{{ $pendingFamilyRequests }} pedido(s) de plano familiar</strong>aguardam confirmação.</div>
    </div>
  @endif
  @if($pendingAppointments > 0)
    <div class="adm-alert warn">
      <span class="adm-alert-icon">🗓️</span>
      <div>
        <strong>{{ $pendingAppointments }} pré-cadastro(s) de instalação</strong>aguardam contacto.
        <a href="{{ route('admin.appointments.index') }}" style="color:inherit;font-weight:800;text-decoration:underline;margin-left:.4rem;">Ver agora →</a>
      </div>
    </div>
  @endif
  @if(($openTickets ?? 0) > 0)
    <div class="adm-alert warn">
      <span class="adm-alert-icon">🎫</span>
      <div>
        <strong>{{ $openTickets }} ticket(s) de suporte aberto(s)</strong>aguardam resposta.
        <a href="{{ route('admin.tickets.index') }}" style="color:inherit;font-weight:800;text-decoration:underline;margin-left:.4rem;">Ver tickets →</a>
      </div>
    </div>
  @endif

  {{-- KPIs --}}
  <div class="adm-kpis">
    <div class="adm-kpi k-brand">
      <div class="adm-kpi-val">{{ $paidOrders }}</div>
      <div class="adm-kpi-lbl">Vendas confirmadas</div>
      <div class="adm-kpi-sub">{{ $totalOrders }} total &middot; {{ $awaitingPayment }} pendentes</div>
    </div>
    <div class="adm-kpi k-green">
      <div class="adm-kpi-val">{{ number_format($totalRevenueAoa, 0, ',', '.') }}</div>
      <div class="adm-kpi-lbl">Receita AOA</div>
      <div class="adm-kpi-sub">Ordens pagas (autovenda)</div>
    </div>
    <div class="adm-kpi k-amber">
      <div class="adm-kpi-val" style="color:{{ $availableWifiCodes > 0 ? '#16a34a' : '#dc2626' }}">{{ $availableWifiCodes }}</div>
      <div class="adm-kpi-lbl">Códigos WiFi</div>
      <div class="adm-kpi-sub">{{ $usedWifiCodes }} utilizados</div>
    </div>
    <div class="adm-kpi k-purple">
      <div class="adm-kpi-val">{{ $pendingResellers }}</div>
      <div class="adm-kpi-lbl">Revendedores pendentes</div>
      <div class="adm-kpi-sub">{{ $totalResellers }} candidaturas</div>
    </div>
    <div class="adm-kpi k-rose">
      <div class="adm-kpi-val">{{ $newEquipOrders }}</div>
      <div class="adm-kpi-lbl">Encomendas novas</div>
      <div class="adm-kpi-sub">{{ number_format($totalEquipRevenue,0,',','.') }} AOA receita</div>
    </div>
    <div class="adm-kpi k-teal">
      <div class="adm-kpi-val" style="display:flex;align-items:center;gap:.4rem;">
        <span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#0d9488;flex-shrink:0;"></span>
        {{ $activeUsers }}
      </div>
      <div class="adm-kpi-lbl">Online na loja</div>
      <div class="adm-kpi-sub">Sessões únicas · últ. 5 min</div>
    </div>
  </div>

  {{-- Accordion --}}
  <div class="adm-menu">

    {{-- ① RECARGAS --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('recargas')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🔄</span>
          <span class="adm-menu-label">Recargas</span>
          @if($awaitingPayment > 0)
            <span class="adm-menu-badge" style="background:#fef3c7;color:#92400e;">{{ $awaitingPayment }} pendentes</span>
          @endif
        </span>
        <span class="adm-menu-chevron open" id="adm-chev-recargas">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-recargas">
        <div class="adm-panel">
          <div class="adm-panel-title">📋 Resumo de Autovenda</div>
          <div class="adm-stats-row">
            <div class="adm-stat-pill">Total: <strong>{{ $totalOrders }}</strong></div>
            <div class="adm-stat-pill" style="border-color:#86efac;">Pagas: <strong style="color:#16a34a;">{{ $paidOrders }}</strong></div>
            <div class="adm-stat-pill" style="border-color:#fde68a;">Pendentes: <strong style="color:#d97706;">{{ $awaitingPayment }}</strong></div>
            <div class="adm-stat-pill" style="border-color:#f7b500;">Receita: <strong>{{ number_format($totalRevenueAoa, 0, ',', '.') }} AOA</strong></div>
          </div>
          <div class="adm-actions">
            <a href="{{ route('admin.autovenda.index') }}" class="adm-btn primary">Ver todas as recargas →</a>
            <a href="{{ route('admin.reports') }}" class="adm-btn">Relatórios</a>
          </div>
        </div>

        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">🕐 Últimas recargas</div>
          <div class="adm-tbl-wrap">
            <table class="adm-tbl">
              <thead>
                <tr>
                  <th>#</th><th>Plano</th><th>Valor</th><th>Estado</th><th>Criada em</th>
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
                        <span class="badge-pill bp-green">Pago</span>
                      @elseif($order->status === 'awaiting_payment')
                        <span class="badge-pill bp-amber">Aguarda</span>
                      @else
                        <span class="badge-pill bp-gray">{{ $order->status }}</span>
                      @endif
                    </td>
                    <td class="dim">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                  </tr>
                @empty
                  <tr><td colspan="5" style="padding:2rem;text-align:center;color:#9aa5b4;">Nenhuma ordem registada.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- ② CÓDIGOS WIFI --}}
    @php
      $wifiPlanText = ['diario' => 'Diário', 'semanal' => 'Semanal', 'mensal' => 'Mensal'];
      $maxPlan = max(1, ...array_map(fn($p) => $wifiCodesByPlan[$p] ?? 0, ['diario','semanal','mensal']));
    @endphp
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('wifi')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">📶</span>
          <span class="adm-menu-label">Códigos WiFi</span>
          @if($availableWifiCodes === 0)
            <span class="adm-menu-badge" style="background:#fee2e2;color:#b91c1c;">sem stock</span>
          @else
            <span class="adm-menu-badge" style="background:#dcfce7;color:#15803d;">{{ $availableWifiCodes }} disponíveis</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-wifi">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-wifi" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">📦 Stock por Plano</div>
          <div class="adm-bars">
            @foreach(['diario','semanal','mensal'] as $pid)
              @php $n = $wifiCodesByPlan[$pid] ?? 0; $pct = min(100, $maxPlan > 0 ? round($n / $maxPlan * 100) : 0); @endphp
              <div class="adm-bar">
                <span class="adm-bar-name">{{ $wifiPlanText[$pid] }}</span>
                <div class="adm-bar-track">
                  <div class="adm-bar-fill" style="width:{{ $pct }}%;background:{{ $pid === 'diario' ? '#f7b500' : ($pid === 'semanal' ? '#7c3aed' : '#d97706') }};"></div>
                </div>
                <span class="adm-bar-count {{ $n===0 ? 'c-out' : ($n<5 ? 'c-low' : 'c-ok') }}">{{ $n }}</span>
              </div>
            @endforeach
          </div>
          <p style="font-size:.78rem;color:#9aa5b4;margin:.3rem 0 .75rem;">{{ $usedWifiCodes }} códigos já utilizados</p>
          <div class="adm-actions">
            <a href="{{ route('admin.wifi_codes.index') }}" class="adm-btn primary">Gerir e importar →</a>
            <a href="{{ route('admin.voucher_plans.index') }}" class="adm-btn">Planos Voucher</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ③ REVENDEDORES --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('revendedores')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🤝</span>
          <span class="adm-menu-label">Revendedores</span>
          @if($pendingResellers > 0)
            <span class="adm-menu-badge" style="background:#fef3c7;color:#92400e;">{{ $pendingResellers }} pendentes</span>
          @else
            <span class="adm-menu-badge" style="background:#dcfce7;color:#15803d;">em dia</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-revendedores">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-revendedores" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">👥 Candidaturas</div>
          <div class="adm-stats-row">
            <div class="adm-stat-pill">Total: <strong>{{ $totalResellers }}</strong></div>
            @if($pendingResellers > 0)
              <div class="adm-stat-pill" style="border-color:#fde68a;">Pendentes: <strong style="color:#d97706;">{{ $pendingResellers }}</strong></div>
            @endif
          </div>
          <div class="adm-actions">
            <a href="{{ route('admin.resellers.index') }}" class="adm-btn primary">Ver candidaturas →</a>
            <a href="{{ route('admin.resellers.history') }}" class="adm-btn" style="background:#f0fdf4;border-color:#86efac;color:#15803d;">📊 Compras vs Vendas</a>
            <a href="{{ route('admin.resellers.purchases.index') }}" class="adm-btn">Compras em Bloco</a>
            <a href="{{ route('admin.manual_voucher_sale.create') }}" class="adm-btn purple">🛒 Venda Manual</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ④ AGENDAMENTOS --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('agendamentos')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🗓️</span>
          <span class="adm-menu-label">Agendamentos</span>
          @if($pendingAppointments > 0)
            <span class="adm-menu-badge" style="background:#fef3c7;color:#92400e;">{{ $pendingAppointments }} por contactar</span>
          @else
            <span class="adm-menu-badge" style="background:#dcfce7;color:#15803d;">em dia</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-agendamentos">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-agendamentos" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">📋 Pré-Cadastros / Instalações</div>
          <div class="adm-stats-row">
            <div class="adm-stat-pill">Total: <strong>{{ $totalAppointments }}</strong></div>
            @if($pendingAppointments > 0)
              <div class="adm-stat-pill" style="border-color:#fde68a;">Por contactar: <strong style="color:#d97706;">{{ $pendingAppointments }}</strong></div>
            @endif
          </div>
          <div class="adm-actions">
            <a href="{{ route('admin.appointments.index') }}" class="adm-btn primary">Ver todos os pedidos →</a>
            <a href="{{ route('admin.appointments.index') }}?status=pending" class="adm-btn">Só pendentes</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ⑤ ENCOMENDAS DE EQUIPAMENTOS --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('encomendas')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">📦</span>
          <span class="adm-menu-label">Encomendas</span>
          @if($newEquipOrders > 0)
            <span class="adm-menu-badge" style="background:#fef3c7;color:#92400e;">{{ $newEquipOrders }} novas</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-encomendas">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-encomendas" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">🖥️ Equipamentos</div>
          <div class="adm-stats-row">
            <div class="adm-stat-pill">Produtos: <strong>{{ $totalProducts }}</strong></div>
            <div class="adm-stat-pill">Encomendas: <strong>{{ $totalEquipOrders }}</strong></div>
            @if($newEquipOrders > 0)
              <div class="adm-stat-pill" style="border-color:#fde68a;">Novas: <strong style="color:#d97706;">{{ $newEquipOrders }}</strong></div>
            @endif
            <div class="adm-stat-pill" style="border-color:#86efac;">Receita: <strong style="color:#16a34a;">{{ number_format($totalEquipRevenue, 0, ',', '.') }} AOA</strong></div>
          </div>
          <div class="adm-actions">
            <a href="{{ route('admin.equipment.orders.index') }}" class="adm-btn primary">Ver encomendas →</a>
            <a href="{{ route('admin.equipment.products.index') }}" class="adm-btn">Produtos</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ⑥ PLANOS FAMILIARES --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('planos')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🏠</span>
          <span class="adm-menu-label">Planos Familiares / Empresariais</span>
          @if($pendingFamilyRequests > 0)
            <span class="adm-menu-badge" style="background:#fef3c7;color:#92400e;">{{ $pendingFamilyRequests }} pendentes</span>
          @else
            <span class="adm-menu-badge" style="background:#dcfce7;color:#15803d;">em dia</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-planos">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-planos" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">📋 Pedidos de Planos</div>
          @if($pendingFamilyRequests > 0)
            <p style="font-size:.9rem;color:#374151;margin:0 0 .75rem;">
              Existem <strong style="color:#d97706;">{{ $pendingFamilyRequests }} pedido(s)</strong> aguardam activação no Sistema de Gestão.
            </p>
          @else
            <p style="font-size:.9rem;color:#64748b;margin:0 0 .75rem;">Sem pedidos pendentes.</p>
          @endif
          <div class="adm-actions">
            <a href="{{ route('admin.family_requests.index') }}" class="adm-btn primary">Ver todos os pedidos →</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ⑦ TICKETS --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('tickets')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🎫</span>
          <span class="adm-menu-label">Tickets de Suporte</span>
          @if(($openTickets ?? 0) > 0)
            <span class="adm-menu-badge" style="background:#fee2e2;color:#b91c1c;">{{ $openTickets }} abertos</span>
          @else
            <span class="adm-menu-badge" style="background:#dcfce7;color:#15803d;">tudo tratado</span>
          @endif
        </span>
        <span class="adm-menu-chevron" id="adm-chev-tickets">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-tickets" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">💬 Suporte ao Cliente</div>
          @if(($openTickets ?? 0) > 0)
            <p style="font-size:.9rem;color:#374151;margin:0 0 .75rem;">
              <strong style="color:#dc2626;">{{ $openTickets }} ticket(s) aberto(s)</strong> aguardam resposta da equipa.
            </p>
          @else
            <p style="font-size:.9rem;color:#64748b;margin:0 0 .75rem;">Sem tickets em aberto.</p>
          @endif
          <div class="adm-actions">
            <a href="{{ route('admin.tickets.index') }}" class="adm-btn primary">Ver todos os tickets →</a>
          </div>
        </div>
      </div>
    </div>

    {{-- ⑧ ESTATÍSTICAS DO SITE --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('estatisticas')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">📈</span>
          <span class="adm-menu-label">Estatísticas do Site</span>
          <span class="adm-menu-badge" style="background:#dbeafe;color:#1d4ed8;" id="adm-stat-badge">A carregar…</span>
        </span>
        <span class="adm-menu-chevron" id="adm-chev-estatisticas">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-estatisticas" style="display:none;">

        {{-- Totais --}}
        <div class="adm-panel">
          <div class="adm-panel-title">🕐 Acessos</div>
          <div class="adm-stats-row">
            <div class="adm-stat-pill">Agora: <strong id="as-now">—</strong></div>
            <div class="adm-stat-pill">Hoje: <strong id="as-today">—</strong></div>
            <div class="adm-stat-pill">7 dias: <strong id="as-week">—</strong></div>
            <div class="adm-stat-pill">Mês: <strong id="as-month">—</strong></div>
            <div class="adm-stat-pill" style="border-color:#f7b500;">Total: <strong id="as-total" style="color:#92400e;">—</strong></div>
          </div>
          <p style="font-size:.75rem;color:#9aa5b4;margin:.5rem 0 0;" id="as-updated"></p>
        </div>

        {{-- Países em tempo real --}}
        <div class="adm-panel">
          <div class="adm-panel-title">🌍 Agora — por país</div>
          <div id="as-live-countries" style="font-size:.85rem;color:#9aa5b4;">A carregar…</div>
        </div>

        {{-- Países histórico --}}
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">🗺️ Histórico — por país</div>
          <div id="as-hist-countries" style="font-size:.85rem;color:#9aa5b4;">A carregar…</div>
        </div>

      </div>
    </div>

    {{-- ⑨ FERRAMENTAS --}}
    <div class="adm-menu-item">
      <button class="adm-menu-btn" onclick="admToggle('ferramentas')">
        <span class="adm-menu-btn-left">
          <span class="adm-menu-icon">🔧</span>
          <span class="adm-menu-label">Ferramentas & Relatórios</span>
        </span>
        <span class="adm-menu-chevron" id="adm-chev-ferramentas">›</span>
      </button>
      <div class="adm-menu-body" id="adm-body-ferramentas" style="display:none;">
        <div class="adm-panel" style="margin-bottom:0;">
          <div class="adm-panel-title">⚙️ Gestão avançada</div>
          <div class="adm-actions">
            <a href="{{ route('admin.reports') }}" class="adm-btn primary">📈 Relatórios →</a>
            <a href="{{ route('admin.reconciliation.gpo') }}" class="adm-btn">💳 Reconciliação GPO</a>
            <a href="{{ route('admin.site_stats.index') }}" class="adm-btn">📊 Estatísticas da Página</a>
            <a href="{{ route('admin.manual_voucher_sale.create') }}" class="adm-btn purple">🛒 Venda Manual de Vouchers</a>
            <a href="{{ route('admin.activity.index') }}" class="adm-btn">🕐 Actividade</a>
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /.adm-menu --}}

</div>
</div>
@endsection

@push('scripts')
<script>
function admToggle(id) {
  var body = document.getElementById('adm-body-' + id);
  var chev = document.getElementById('adm-chev-' + id);
  var btn  = chev ? chev.closest('.adm-menu-btn') : null;
  var open = body && body.style.display !== 'none';
  if (body) body.style.display = open ? 'none' : 'block';
  if (chev) chev.classList.toggle('open', !open);
  if (btn)  btn.classList.toggle('open', !open);
}

// ── Estatísticas do site (polling /store/live-stats) ──────────────────────
(function () {
  var fmt = new Intl.NumberFormat('pt-PT');

  function el(id) { return document.getElementById(id); }
  function set(id, val) { var e = el(id); if (e && val !== null && val !== undefined) e.textContent = fmt.format(val); }

  function renderLiveCountries(countries) {
    var wrap = el('as-live-countries');
    if (!wrap) return;
    var entries = Object.entries(countries || {});
    if (!entries.length) { wrap.textContent = 'Sem visitantes activos agora.'; return; }
    wrap.innerHTML = entries.map(function(e) {
      return '<div style="display:flex;justify-content:space-between;padding:.25rem 0;border-bottom:1px solid #f1f5f9;">'
        + '<span style="font-weight:600;color:#0f172a;">' + e[0] + '</span>'
        + '<span style="font-weight:700;color:#f7b500;">' + e[1] + '</span>'
        + '</div>';
    }).join('');
  }

  function renderHistCountries(totals) {
    var wrap = el('as-hist-countries');
    if (!wrap) return;
    var entries = Object.entries(totals || {});
    if (!entries.length) { wrap.textContent = 'Sem dados registados.'; return; }
    var sum = entries.reduce(function(s, e) { return s + e[1]; }, 0) || 1;
    wrap.innerHTML = entries.map(function(e) {
      var pct = Math.max(1, Math.round(e[1] / sum * 100));
      return '<div style="margin-bottom:.55rem;">'
        + '<div style="display:flex;justify-content:space-between;margin-bottom:.2rem;">'
        +   '<span style="font-size:.85rem;font-weight:600;color:#0f172a;">' + e[0] + '</span>'
        +   '<span style="font-size:.82rem;font-weight:700;color:#92400e;">' + fmt.format(e[1]) + ' <span style="opacity:.6;">(' + pct + '%)</span></span>'
        + '</div>'
        + '<div style="background:#e2e8f0;border-radius:3px;height:5px;overflow:hidden;">'
        +   '<div style="width:' + pct + '%;height:5px;background:#f7b500;border-radius:3px;transition:width .4s;"></div>'
        + '</div>'
        + '</div>';
    }).join('');
  }

  function fetchSiteStats() {
    fetch('/store/live-stats')
      .then(function(r) { return r.ok ? r.json() : null; })
      .then(function(d) {
        if (!d) return;
        set('as-now',   d.visitors_now);
        set('as-today', d.visitors_today);
        set('as-week',  d.visitors_week);
        set('as-month', d.visitors_month);
        set('as-total', d.visitors_total);
        // Badge no header do accordion
        var badge = el('adm-stat-badge');
        if (badge && d.visitors_total !== null) badge.textContent = fmt.format(d.visitors_total) + ' visitas';
        renderLiveCountries(d.top_countries);
        renderHistCountries(d.country_totals);
        var upd = el('as-updated');
        if (upd) upd.textContent = 'Actualizado às ' + new Date().toLocaleTimeString('pt-PT', {hour:'2-digit',minute:'2-digit',second:'2-digit'});
      })
      .catch(function() {});
  }

  fetchSiteStats();
  setInterval(fetchSiteStats, 60000);
})();
</script>
@endpush
