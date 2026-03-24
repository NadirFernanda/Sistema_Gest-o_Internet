@extends('layouts.app')

@push('styles')
<style>
/* ── Painel Revendedor ── */
.rv-page {
  min-height: 80vh;
  background: #f8fafc;
  padding: 2.5rem 1rem 4rem;
}

/* Login */
.rv-login-wrap {
  max-width: 460px;
  margin: 3rem auto 0;
}
.rv-login-card {
  background: #fff;
  border-radius: 1.25rem;
  box-shadow: 0 4px 32px rgba(0,0,0,.08);
  padding: 2.5rem 2.25rem 2rem;
}
.rv-login-card .rv-brand {
  display: flex;
  align-items: center;
  gap: .75rem;
  margin-bottom: 1.75rem;
}
.rv-login-card .rv-brand img {
  width: 44px;
  height: 44px;
  border-radius: .5rem;
  object-fit: cover;
}
.rv-login-card .rv-brand-text {
  font-size: 1rem;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.2;
}
.rv-login-card .rv-brand-text span {
  display: block;
  font-size: .78rem;
  font-weight: 500;
  color: #64748b;
}
.rv-login-card h2 {
  font-size: 1.45rem;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: .35rem;
}
.rv-login-card p.rv-sub {
  font-size: .9rem;
  color: #64748b;
  margin-bottom: 1.5rem;
}
.rv-field { margin-bottom: 1.1rem; }
.rv-field label {
  display: block;
  font-size: .85rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: .35rem;
}
.rv-field input {
  width: 100%;
  padding: .7rem 1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .6rem;
  font-size: .95rem;
  color: #0f172a;
  background: #f8fafc;
  transition: border-color .2s, box-shadow .2s;
  box-sizing: border-box;
}
.rv-field input:focus {
  outline: none;
  border-color: #f7b500;
  box-shadow: 0 0 0 3px rgba(247,181,0,.15);
  background: #fff;
}
.rv-login-note {
  font-size: .82rem;
  color: #64748b;
  margin-bottom: 1.25rem;
  line-height: 1.5;
}
.rv-btn-login {
  display: block;
  width: 100%;
  padding: .8rem 1.5rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .65rem;
  font-size: 1rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s, transform .15s;
  text-align: center;
}
.rv-btn-login:hover { background: #e0a800; transform: translateY(-1px); }

/* Dashboard */
.rv-dash {
  max-width: 1100px;
  margin: 0 auto;
}
.rv-topbar {
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
.rv-topbar-left { display: flex; align-items: center; gap: .9rem; }
.rv-avatar {
  width: 42px; height: 42px;
  border-radius: 50%;
  background: linear-gradient(135deg,#0d9488,#0891b2);
  color: #fff;
  font-size: 1.15rem;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.rv-topbar-name { font-size: 1rem; font-weight: 700; color: #0f172a; }
.rv-topbar-email { font-size: .82rem; color: #64748b; margin-top: .1rem; }
.rv-mode-badge {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .25rem .7rem;
  border-radius: 9999px;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .01em;
}
.rv-mode-badge.own  { background: #dbeafe; color: #1d4ed8; }
.rv-mode-badge.wifi { background: #fef3c7; color: #92400e; }
.rv-logout-btn {
  padding: .5rem 1.1rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .6rem;
  background: #fff;
  font-size: .85rem;
  font-weight: 600;
  color: #64748b;
  cursor: pointer;
  transition: border-color .2s, color .2s;
}
.rv-logout-btn:hover { border-color: #dc2626; color: #dc2626; }

/* Alerts */
.rv-alert {
  border-radius: .75rem;
  padding: .9rem 1.1rem;
  margin-bottom: 1rem;
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  font-size: .92rem;
}
.rv-alert.danger  { background: #fef2f2; border: 1.5px solid #fecaca; color: #991b1b; }
.rv-alert.warning { background: #fffbeb; border: 1.5px solid #fde68a; color: #92400e; }
.rv-alert-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: -.05rem; }
.rv-alert strong { font-weight: 700; display: block; margin-bottom: .15rem; }

/* Stats grid */
.rv-stats {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.rv-stat-card {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.25rem 1.35rem;
  border-top: 3px solid transparent;
}
.rv-stat-card.green  { border-color: #16a34a; }
.rv-stat-card.blue   { border-color: #2563eb; }
.rv-stat-card.amber  { border-color: #f59e0b; }
.rv-stat-card.purple { border-color: #7c3aed; }
.rv-stat-icon { font-size: 1.6rem; margin-bottom: .55rem; }
.rv-stat-label { font-size: .78rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: .04em; margin-bottom: .25rem; }
.rv-stat-value { font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1; margin-bottom: .35rem; }
.rv-stat-value.green  { color: #16a34a; }
.rv-stat-value.blue   { color: #2563eb; }
.rv-stat-sub { font-size: .82rem; color: #64748b; }
.rv-progress-bar {
  background: #e5e7eb;
  border-radius: 9999px;
  height: 8px;
  margin-top: .6rem;
  overflow: hidden;
}
.rv-progress-fill {
  height: 8px;
  border-radius: 9999px;
  transition: width .5s ease;
}

/* Panels */
.rv-panel {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 10px rgba(0,0,0,.055);
  padding: 1.5rem 1.5rem;
  margin-bottom: 1.25rem;
}
.rv-panel-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #0f172a;
  padding-bottom: .75rem;
  margin-bottom: 1rem;
  border-bottom: 1.5px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .5rem;
}
.rv-panel-title .rv-panel-icon { font-size: 1.1rem; }

/* Discount table */
.rv-disc-table {
  width: 100%;
  max-width: 460px;
  border-collapse: collapse;
  font-size: .92rem;
}
.rv-disc-table th {
  text-align: left;
  padding: .5rem .85rem;
  font-size: .78rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
  background: #f8fafc;
  border-bottom: 1.5px solid #e2e8f0;
}
.rv-disc-table th:last-child { text-align: right; }
.rv-disc-table td {
  padding: .55rem .85rem;
  border-bottom: 1px solid #f1f5f9;
  color: #374151;
}
.rv-disc-table td:last-child { text-align: right; font-weight: 700; color: #0f172a; }
.rv-disc-table tr.active td {
  background: #f0fdf4;
  color: #166534;
  font-weight: 700;
}
.rv-disc-table tr.active td:last-child { color: #16a34a; }
.rv-disc-table .rv-disc-current-chip {
  display: inline-block;
  background: #dcfce7;
  color: #15803d;
  font-size: .7rem;
  font-weight: 700;
  padding: .1rem .5rem;
  border-radius: 9999px;
  margin-left: .5rem;
  letter-spacing: .03em;
  vertical-align: middle;
}

/* Purchase form */
.rv-purchase-form {
  display: flex;
  gap: .75rem;
  align-items: flex-end;
  flex-wrap: wrap;
}
.rv-purchase-form .rv-field { flex: 1; min-width: 200px; margin-bottom: 0; }
.rv-btn-buy {
  padding: .72rem 1.5rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .65rem;
  font-size: .97rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s, transform .15s;
  white-space: nowrap;
}
.rv-btn-buy:hover { background: #e0a800; transform: translateY(-1px); }
.rv-field-error { color: #dc2626; font-size: .85rem; margin-top: .4rem; }

/* Plan catalog grid */
.rv-plan-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1.1rem;
}
.rv-plan-card {
  border: 1.5px solid #e2e8f0;
  border-radius: .9rem;
  padding: 1.2rem;
  background: #fff;
  display: flex;
  flex-direction: column;
  gap: .75rem;
  transition: box-shadow .2s, border-color .2s;
}
.rv-plan-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.09); border-color: #f7b500; }
.rv-plan-card--out { opacity: .55; }
.rv-plan-card-header { display:flex; align-items:center; gap:.6rem; }
.rv-plan-emoji { font-size: 1.6rem; line-height:1; }
.rv-plan-name {
  font-size: 1.1rem;
  font-weight: 800;
  color: #0f172a;
}
/* Public price — big, same prominence as main page */
.rv-plan-price-public-big {
  display: flex;
  align-items: baseline;
  gap: .25rem;
}
.rv-price-big-num {
  font-size: 2rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1;
}
.rv-price-big-cur {
  font-size: 1rem;
  font-weight: 600;
  color: #64748b;
}
/* Feature list — same as main page */
.rv-plan-features {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: .3rem;
  font-size: .88rem;
  color: #374151;
}
.rv-plan-features li::before { content: "✔ "; color: #16a34a; font-weight: 700; }
.rv-plan-desc {
  font-size: .83rem;
  color: #64748b;
  line-height: 1.45;
  margin: 0;
}
/* Separator before reseller-specific section */
.rv-plan-reseller-sep {
  border: none;
  border-top: 1px solid #e2e8f0;
  margin: .25rem 0;
}
.rv-plan-prices { display: flex; flex-direction: column; gap: .3rem; }
.rv-plan-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .88rem;
}
.rv-price-label { color: #64748b; }
.rv-price-reseller { font-weight: 800; color: #0f172a; font-size: 1rem; }
.rv-price-profit   { font-weight: 700; color: #16a34a; }
.rv-plan-stock { font-size: .8rem; font-weight: 600; }
.rv-plan-stock.ok { color: #15803d; }
.rv-plan-stock.out { color: #dc2626; }
.rv-plan-add-form { display: flex; gap: .5rem; align-items: center; margin-top: auto; }
.rv-qty-input {
  width: 70px;
  padding: .5rem .65rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .5rem;
  font-size: .92rem;
  color: #0f172a;
  background: #f8fafc;
  text-align: center;
}
.rv-qty-input:focus { outline: none; border-color: #f7b500; }
.rv-btn-add {
  flex: 1;
  padding: .5rem .85rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .5rem;
  font-size: .85rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s;
  white-space: nowrap;
}
.rv-btn-add:hover { background: #e0a800; }

/* Cart panel */
.rv-cart-panel { border: 1.5px solid #fde68a; }
.rv-cart-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: .75rem;
  margin-top: 1rem;
  padding-top: .85rem;
  border-top: 1.5px solid #f1f5f9;
}
.rv-cart-totals { display: flex; flex-direction: column; gap: .2rem; font-size: .95rem; color: #374151; }
.rv-cart-actions { display: flex; gap: .65rem; align-items: center; flex-wrap: wrap; }

/* History table */
.rv-hist-wrap { overflow-x: auto; }
.rv-hist-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .9rem;
  min-width: 580px;
}
.rv-hist-table th {
  text-align: left;
  padding: .55rem .85rem;
  font-size: .75rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: .04em;
  background: #f8fafc;
  border-bottom: 1.5px solid #e2e8f0;
}
.rv-hist-table th.r { text-align: right; }
.rv-hist-table td {
  padding: .6rem .85rem;
  border-bottom: 1px solid #f8fafc;
  color: #374151;
}
.rv-hist-table td.r { text-align: right; }
.rv-hist-table td.muted { color: #94a3b8; font-size: .82rem; }
.rv-hist-table td.bold { font-weight: 700; color: #0f172a; }
.rv-hist-table td.disc { color: #16a34a; font-weight: 700; }
.rv-hist-table tr:last-child td { border-bottom: none; }
.rv-hist-table tr:hover td { background: #f8fafc; }
.rv-csv-btn {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .3rem .7rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .45rem;
  font-size: .8rem;
  font-weight: 600;
  color: #64748b;
  text-decoration: none;
  transition: border-color .2s, color .2s, background .2s;
}
.rv-csv-btn:hover { border-color: #f7b500; color: #92400e; background: #fffbeb; }
.rv-empty { color: #94a3b8; font-size: .95rem; text-align: center; padding: 2rem 0; }
.rv-pagination { margin-top: .75rem; }

@media (max-width: 640px) {
  .rv-topbar { flex-direction: column; align-items: flex-start; }
  .rv-panel { padding: 1.1rem 1rem; }
  .rv-purchase-form { flex-direction: column; }
  .rv-btn-buy { width: 100%; text-align: center; }
  .rv-disc-table { max-width: 100%; }
  .rv-disc-table th, .rv-disc-table td { padding: .4rem .55rem; font-size: .82rem; }
  .rv-hist-table { min-width: 420px; }
  .rv-hist-table th, .rv-hist-table td { padding: .45rem .55rem; font-size: .8rem; }
  .rv-stats { grid-template-columns: 1fr 1fr; gap: .75rem; }
  .rv-plan-grid { grid-template-columns: 1fr; }
  .rv-plan-add-form { flex-direction: column; }
  .rv-qty-input { width: 100%; }
  .rv-btn-add { width: 100%; text-align: center; }
  .rv-cart-footer { flex-direction: column; align-items: stretch; text-align: center; }
  .rv-cart-actions { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="rv-page">

  {{-- ── Flash messages ──────────────────────────────────────── --}}
  @if(session('status'))
    <div style="max-width:{{ $application ? '1100px' : '460px' }};margin:0 auto 1rem;">
      <div class="rv-alert" style="background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;">
        <span class="rv-alert-icon">✅</span>
        <div>{{ session('status') }}</div>
      </div>
    </div>
  @endif
  @if(session('error'))
    <div style="max-width:{{ $application ? '1100px' : '460px' }};margin:0 auto 1rem;">
      <div class="rv-alert danger">
        <span class="rv-alert-icon">⚠️</span>
        <div>{{ session('error') }}</div>
      </div>
    </div>
  @endif

  {{-- Pending payment banner --}}
  @if(session()->has('reseller_pending_order') && $application)
    <div style="max-width:1100px;margin:0 auto 1rem;">
      <div class="rv-alert warning">
        <span class="rv-alert-icon">💳</span>
        <div>
          <strong>Tem uma encomenda pendente de pagamento.</strong>
          Os vouchers estão reservados aguardando confirmação do pagamento.
          <a href="{{ route('reseller.panel.payment') }}" style="margin-left:.5rem;color:#92400e;font-weight:700;text-decoration:underline;">Completar pagamento →</a>
        </div>
      </div>
    </div>
  @endif

  @if(!$application)
    {{-- ════════════════ LOGIN / OTP ════════════════ --}}
    <div class="rv-login-wrap">
      <div class="rv-login-card">
        <div class="rv-brand">
          <img src="{{ asset('img/logo2.jpeg') }}" alt="AngolaWiFi">
          <div class="rv-brand-text">
            AngolaWiFi
            <span>Portal de Revendedores</span>
          </div>
        </div>

        @if($otpPending)
          {{-- PASSO 2: introduzir código --}}
          <h2>Verificação por email</h2>
          <p class="rv-sub">
            Enviámos um código de <strong>6 dígitos</strong> para <strong>{{ $otpEmail }}</strong>.<br>
            Verifique a caixa de entrada (e o spam). Válido durante <strong>10 minutos</strong>.
          </p>
          <form action="{{ route('reseller.panel.verify') }}" method="POST" novalidate autocomplete="off">
            @csrf
            <div class="rv-field">
              <label for="rev-otp">Código de verificação</label>
              <input id="rev-otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}"
                     maxlength="6" placeholder="_ _ _ _ _ _" required autofocus
                     style="font-size:1.6rem;letter-spacing:.35em;text-align:center;font-family:monospace;" />
            </div>
            <button type="submit" class="rv-btn-login">Confirmar código →</button>
          </form>
          <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin-top:.75rem;">
            @csrf
            <button type="submit" class="rv-logout-btn" style="width:100%;text-align:center;">← Usar outro email</button>
          </form>

        @else
          {{-- PASSO 1: introduzir email --}}
          <h2>Entrar no painel</h2>
          <p class="rv-sub">Acesso exclusivo para revendedores aprovados.</p>
          <form action="{{ route('reseller.panel.login') }}" method="POST" novalidate>
            @csrf
            <div class="rv-field">
              <label for="rev-email">Email de revendedor</label>
              <input id="rev-email" name="email" type="email"
                     placeholder="revendedor@exemplo.ao"
                     value="{{ old('email') }}" required autocomplete="email" />
            </div>
            <p class="rv-login-note">
              Receberá um código de verificação neste endereço.<br>
              Acesso restrito a revendedores com candidatura <strong>aprovada</strong>.
            </p>
            <button type="submit" class="rv-btn-login">Enviar código →</button>
          </form>
        @endif

      </div>
    </div>

  @else
    {{-- ════════════════ DASHBOARD ════════════════ --}}
    <div class="rv-dash">

      {{-- Top bar --}}
      <div class="rv-topbar">
        <div class="rv-topbar-left">
          <div class="rv-avatar">{{ mb_strtoupper(mb_substr($application->full_name, 0, 1)) }}</div>
          <div>
            <div class="rv-topbar-name">{{ $application->full_name }}</div>
            <div class="rv-topbar-email">{{ $application->email }}</div>
          </div>
          @if($application->reseller_mode === 'own')
            <span class="rv-mode-badge own">Modo 1 — Internet Própria</span>
          @elseif($application->reseller_mode === 'angolawifi')
            <span class="rv-mode-badge wifi">Modo 2 — Internet AngolaWiFi</span>
          @endif
        </div>
        <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin:0;">
          @csrf
          <button type="submit" class="rv-logout-btn">Terminar sessão</button>
        </form>
      </div>

      {{-- Alerts --}}
      @if($application->maintenanceDueThisMonth())
        <div class="rv-alert danger" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
          <div style="display:flex;align-items:flex-start;gap:.65rem;">
            <span class="rv-alert-icon">🔔</span>
            <div>
              <strong>Taxa de manutenção em atraso</strong><br>
              Valor em dívida: {{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz.
            </div>
          </div>
          <a href="{{ route('reseller.maintenance.payment') }}"
             style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:#dc2626;color:#fff;border-radius:.6rem;font-size:.85rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:background .15s;"
             onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
            💳 Pagar agora
          </a>
        </div>
      @endif
      @if($application->monthly_target_aoa > 0 && !$application->metMonthlyTarget())
        @php $remaining = $application->monthly_target_aoa - $application->monthlySpendings(); @endphp
        <div class="rv-alert warning">
          <span class="rv-alert-icon">🎯</span>
          <div>
            <strong>Meta mensal ainda não atingida</strong>
            Faltam <strong>{{ number_format($remaining, 0, ',', '.') }} Kz</strong> para atingir a meta
            de {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz este mês.
          </div>
        </div>
      @endif

      {{-- Stats --}}
      <div class="rv-stats">
        <div class="rv-stat-card green">
          <div class="rv-stat-icon">💰</div>
          <div class="rv-stat-label">Lucro estimado</div>
          <div class="rv-stat-value green">{{ number_format($totals['profit_total'] ?: $estimatedProfit, 0, ',', '.') }} Kz</div>
          <div class="rv-stat-sub">Total de lucro nas compras</div>
        </div>

        <div class="rv-stat-card blue">
          <div class="rv-stat-icon">📦</div>
          <div class="rv-stat-label">Vouchers adquiridos</div>
          <div class="rv-stat-value">{{ number_format($totals['vouchers_total'], 0, ',', '.') }}</div>
          <div class="rv-stat-sub">
            Investido: {{ number_format($totals['total_invested'], 0, ',', '.') }} Kz
          </div>
        </div>

        @if($application->monthly_target_aoa > 0)
          @php
            $pct = min(100, round($application->monthlySpendings() * 100 / $application->monthly_target_aoa));
          @endphp
          <div class="rv-stat-card amber">
            <div class="rv-stat-icon">🎯</div>
            <div class="rv-stat-label">Meta mensal</div>
            <div class="rv-stat-value">{{ number_format($application->monthlySpendings(), 0, ',', '.') }} Kz</div>
            <div class="rv-stat-sub">de {{ number_format($application->monthly_target_aoa, 0, ',', '.') }} Kz · {{ $pct }}%</div>
            <div class="rv-progress-bar">
              <div class="rv-progress-fill" style="width:{{ $pct }}%;background:{{ $pct >= 100 ? '#16a34a' : '#f59e0b' }};"></div>
            </div>
          </div>
        @endif

        @if($application->bonus_vouchers_aoa > 0)
          <div class="rv-stat-card purple">
            <div class="rv-stat-icon">🎁</div>
            <div class="rv-stat-label">Bónus de arranque</div>
            <div class="rv-stat-value blue">{{ number_format($application->bonus_vouchers_aoa, 0, ',', '.') }} Kz</div>
            <div class="rv-stat-sub">50% da taxa de instalação em vouchers</div>
          </div>
        @endif
      </div>

      {{-- Sell button --}}
      @if($totals['vouchers_total'] > 0)
      <div style="margin-bottom:1.25rem;">
        <a href="{{ route('reseller.sell') }}"
           style="display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 1.75rem;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border-radius:.75rem;font-size:1.05rem;font-weight:800;text-decoration:none;transition:transform .15s,box-shadow .15s;box-shadow:0 4px 14px rgba(22,163,74,.25);"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 20px rgba(22,163,74,.35)'"
           onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 14px rgba(22,163,74,.25)'">
          🏷️ Vender vouchers ao cliente
        </a>
        <span style="font-size:.85rem;color:#64748b;margin-left:.75rem;">Seleccione vouchers, venda e gere o PDF para o cliente.</span>
      </div>
      @endif

      {{-- Tabela de descontos --}}
      <div class="rv-panel">
        <div class="rv-panel-title"><span class="rv-panel-icon">🏷️</span> Tabela de descontos</div>
        @if($application->reseller_mode === 'own')
          <p style="font-size:.95rem;color:#374151;">
            Modo 1 — Internet Própria: desconto fixo de
            <strong style="color:#0d9488;">{{ config('reseller.mode_own_discount_percent', 70) }}%</strong>
            em todas as compras.
          </p>
        @else
          @php
            $tiers    = config('reseller.mode_angolawifi_discount_tiers', []);
            $tierKeys = array_values(array_keys($tiers));
            $mySpend  = $application->monthlySpendings();
          @endphp
          <table class="rv-disc-table">
            <thead>
              <tr>
                <th>Compra mensal (Kz)</th>
                <th style="text-align:right;">Desconto</th>
              </tr>
            </thead>
            <tbody>
              @foreach($tiers as $min => $pct)
                @php
                  $idx     = array_search($min, $tierKeys);
                  $nextMin = isset($tierKeys[$idx + 1]) ? $tierKeys[$idx + 1] : null;
                  $active  = $mySpend >= $min && (!$nextMin || $mySpend < $nextMin);
                @endphp
                <tr class="{{ $active ? 'active' : '' }}">
                  <td>
                    {{ number_format($min, 0, ',', '.') }}{{ $nextMin ? ' – '.number_format($nextMin - 1, 0, ',', '.') : '+' }} Kz
                    @if($active)<span class="rv-disc-current-chip">actual</span>@endif
                  </td>
                  <td>{{ $pct }}%</td>
                </tr>
              @endforeach
            </tbody>
          </table>
          <p style="font-size:.8rem;color:#94a3b8;margin-top:.65rem;">O escalão em destaque corresponde ao seu volume de compras deste mês.</p>
        @endif
      </div>

      {{-- ── Catálogo de planos ── --}}
      <div class="rv-panel">
        <div class="rv-panel-title"><span class="rv-panel-icon">📦</span> Planos disponíveis</div>

        @if($voucherPlans->isEmpty())
          <p class="rv-empty">Nenhum plano disponível de momento.</p>
        @else
          <div class="rv-plan-grid">
            @foreach($voucherPlans as $plan)
              @php
                $pcfg    = $storePlansConfig->get($plan->slug);
                $emoji   = $pcfg
                  ? (str_contains(strtolower($pcfg['name']), 'dia') ? '🌞'
                    : (str_contains(strtolower($pcfg['name']), 'semana') ? '📅'
                    : (str_contains(strtolower($pcfg['name']), 'mês') || str_contains(strtolower($pcfg['name']), 'mensal') ? '🗓️' : '💡')))
                  : '💡';
              @endphp
              <div class="rv-plan-card">

                {{-- Cabeçalho: igual à página pública --}}
                <div class="rv-plan-card-header">
                  <span class="rv-plan-emoji" aria-hidden="true">{{ $emoji }}</span>
                  <span class="rv-plan-name">{{ $plan->name }}</span>
                </div>

                {{-- Preço público em destaque, igual à página pública --}}
                <div class="rv-plan-price-public-big">
                  <span class="rv-price-big-num">{{ number_format($plan->price_public_aoa, 0, ',', '.') }}</span>
                  <span class="rv-price-big-cur">Kz</span>
                </div>

                {{-- Features: duração, velocidade, downloads — mesmas da página pública --}}
                <ul class="rv-plan-features">
                  <li><strong>{{ $plan->validity_label }}</strong></li>
                  <li>{{ $plan->speed_label }}</li>
                  @if($pcfg && !empty($pcfg['download']))<li>{{ $pcfg['download'] }}</li>@endif
                </ul>

                @if($pcfg && !empty($pcfg['description']))
                  <p class="rv-plan-desc">{{ $pcfg['description'] }}</p>
                @endif

                {{-- Separador antes da secção exclusiva do revendedor --}}
                <hr class="rv-plan-reseller-sep">

                {{-- Preço revendedor + lucro (info extra para o agente) --}}
                <div class="rv-plan-prices">
                  <div class="rv-plan-price-row">
                    <span class="rv-price-label">Preço de custo</span>
                    <span class="rv-price-reseller">{{ number_format($plan->resellerPriceFor($application), 0, ',', '.') }} Kz</span>
                  </div>
                  <div class="rv-plan-price-row">
                    <span class="rv-price-label">Lucro / voucher</span>
                    <span class="rv-price-profit">+{{ number_format($plan->profitForReseller($application), 0, ',', '.') }} Kz ({{ $plan->marginPercentForReseller($application) }}%)</span>
                  </div>
                </div>

                <form action="{{ route('reseller.cart.add') }}" method="POST" class="rv-plan-add-form">
                    @csrf
                    <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                    <input type="number" name="quantity" min="1" max="500" value="1"
                           class="rv-qty-input" required aria-label="Quantidade">
                    <button type="submit" class="rv-btn-add">Adicionar ao carrinho</button>
                  </form>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- ── Carrinho ── --}}
      @if(!empty($cartItems))
      <div class="rv-panel rv-cart-panel">
        <div class="rv-panel-title"><span class="rv-panel-icon">🛒</span> Carrinho
          <span style="font-size:.85rem;font-weight:500;color:#64748b;margin-left:.5rem;">{{ $cartVouchers }} voucher(s)</span>
        </div>

        <div class="rv-hist-wrap">
          <table class="rv-hist-table">
            <thead>
              <tr>
                <th>Plano</th>
                <th class="r">Qtd.</th>
                <th class="r">Preço unit. (Kz)</th>
                <th class="r">Subtotal (Kz)</th>
                <th class="r">Lucro potencial (Kz)</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach($cartItems as $item)
              <tr>
                <td><strong>{{ $item['plan']->name }}</strong>
                    <small class="muted" style="display:block;">{{ $item['plan']->validity_label }} · {{ $item['plan']->speed_label }}</small>
                </td>
                <td class="r">{{ $item['qty'] }}</td>
                <td class="r">{{ number_format($item['plan']->price_reseller_aoa, 0, ',', '.') }}</td>
                <td class="r bold">{{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                <td class="r" style="color:#16a34a;font-weight:700;">+{{ number_format($item['profit'], 0, ',', '.') }}</td>
                <td>
                  <form action="{{ route('reseller.cart.remove') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_slug" value="{{ $item['plan']->slug }}">
                    <button type="submit" class="rv-csv-btn" style="color:#dc2626;border-color:#fecaca;">✕</button>
                  </form>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <div class="rv-cart-footer">
          <div class="rv-cart-totals">
            <span>Total a pagar: <strong>{{ number_format($cartTotal, 0, ',', '.') }} Kz</strong></span>
            <span style="color:#16a34a;">Lucro potencial: <strong>+{{ number_format($cartProfit, 0, ',', '.') }} Kz</strong></span>
          </div>
          @if($cartTotal < $minPurchaseAoa)
          <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.65rem 1rem;margin:.5rem 0;color:#b91c1c;font-size:.9rem;font-weight:600;">
            ⚠️ Compra mínima obrigatória: <strong>{{ number_format($minPurchaseAoa, 0, ',', '.') }} Kz</strong>.
            Adicione mais vouchers ao carrinho para poder finalizar a compra.
          </div>
          @endif
          <div class="rv-cart-actions">
            <form action="{{ route('reseller.cart.clear') }}" method="POST" style="display:inline;">
              @csrf
              <button type="submit" class="rv-csv-btn">Limpar carrinho</button>
            </form>
            @if(session()->has('reseller_pending_order'))
              <a href="{{ route('reseller.panel.payment') }}" class="rv-btn-buy">💳 Completar pagamento pendente</a>
            @elseif($cartTotal < $minPurchaseAoa)
              <button type="button" class="rv-btn-buy" disabled style="opacity:.45;cursor:not-allowed;">✅ Finalizar compra &amp; pagar</button>
            @else
              <form action="{{ route('reseller.panel.checkout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="rv-btn-buy">✅ Finalizar compra &amp; pagar</button>
              </form>
            @endif
          </div>
        </div>
      </div>
      @endif

      {{-- Histórico --}}
      <div class="rv-panel" style="margin-bottom:1.25rem;">
        <div class="rv-panel-title"><span class="rv-panel-icon">📋</span> Histórico de compras</div>
        @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->count())
          <div class="rv-hist-wrap">
            <table class="rv-hist-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Data</th>
                  <th>Plano</th>
                  <th class="r">Qtd.</th>
                  <th class="r">Desconto</th>
                  <th class="r">Pago (Kz)</th>
                  <th class="r">Lucro (Kz)</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @foreach($purchases as $purchase)
                  <tr @if($purchase->status === 'cancelled') style="opacity:.5;" @endif>
                    <td class="muted">#{{ $purchase->id }}</td>
                    <td>{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                    <td>
                      {{ $purchase->plan_name ?? '—' }}
                      @if($purchase->status === 'cancelled')
                        <span style="display:inline-block;margin-left:.35rem;font-size:.7rem;font-weight:700;background:#fee2e2;color:#b91c1c;border-radius:.25rem;padding:.1rem .4rem;">ANULADA</span>
                      @endif
                    </td>
                    <td class="r">{{ $purchase->codes_count }}</td>
                    <td class="r" style="color:#0d9488;font-weight:700;">
                      {{ $purchase->discount_percent ? $purchase->discount_percent.'%' : '—' }}
                    </td>
                    <td class="r bold">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }}</td>
                    <td class="r" style="color:#16a34a;font-weight:700;">
                      @if($purchase->profit_aoa)
                        +{{ number_format($purchase->profit_aoa, 0, ',', '.') }}
                      @else
                        —
                      @endif
                    </td>
                    <td style="white-space:nowrap;display:flex;gap:.4rem;flex-wrap:wrap;">
                      @if($purchase->status !== 'cancelled')
                      <a href="{{ route('reseller.panel.purchase.codes', $purchase) }}"
                         class="rv-csv-btn" style="border-color:#93c5fd;color:#2563eb;background:#eff6ff;">📦 Códigos</a>
                      <a href="{{ route('reseller.sell') }}"
                         class="rv-csv-btn" style="border-color:#86efac;color:#16a34a;background:#f0fdf4;">🏷 Vender</a>
                      <a href="{{ route('reseller.panel.purchase.pdf', $purchase) }}"
                         class="rv-csv-btn" style="border-color:#fecaca;color:#b91c1c;">📄 PDF</a>
                      <a href="{{ route('reseller.panel.purchase.vouchers', ['purchase' => $purchase->id]) }}"
                         class="rv-csv-btn">⬇ CSV</a>
                      @else
                        <span style="color:#9ca3af;font-size:.82rem;">Compra anulada</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="rv-pagination">{{ $purchases->links() }}</div>
        @else
          <p class="rv-empty">Ainda não existem compras registadas.</p>
        @endif
      </div>

      {{-- ── Relatório de Vendas (por plano) ── --}}
      @if(!empty($salesReport))
      <div class="rv-panel" style="margin-bottom:0;">
        <div class="rv-panel-title"><span class="rv-panel-icon">📈</span> Relatório de Vendas por Plano</div>
        <div class="rv-hist-wrap">
          <table class="rv-hist-table">
            <thead>
              <tr>
                <th>Plano</th>
                <th class="r">Comprados</th>
                <th class="r">Vendidos ao cliente</th>
                <th class="r">Em stock</th>
                <th class="r">Taxa de venda</th>
                <th class="r">Investido (Kz)</th>
                <th class="r">Lucro realizado (Kz)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($salesReport as $slug => $row)
                @php
                  $stockLeft  = $row['vouchers_bought'] - $row['vouchers_sold'];
                  $sellRate   = $row['vouchers_bought'] > 0
                    ? round($row['vouchers_sold'] * 100 / $row['vouchers_bought'])
                    : 0;
                @endphp
                <tr>
                  <td><strong>{{ $row['plan_name'] }}</strong></td>
                  <td class="r">{{ $row['vouchers_bought'] }}</td>
                  <td class="r" style="color:#16a34a;font-weight:700;">{{ $row['vouchers_sold'] }}</td>
                  <td class="r" style="color:{{ $stockLeft > 0 ? '#2563eb' : '#94a3b8' }};font-weight:700;">{{ $stockLeft }}</td>
                  <td class="r">
                    <div style="display:flex;align-items:center;gap:.4rem;justify-content:flex-end;">
                      <span>{{ $sellRate }}%</span>
                      <div style="width:60px;height:6px;background:#e5e7eb;border-radius:9999px;overflow:hidden;">
                        <div style="height:6px;width:{{ $sellRate }}%;background:{{ $sellRate >= 80 ? '#16a34a' : ($sellRate >= 40 ? '#f59e0b' : '#e5e7eb') }};border-radius:9999px;"></div>
                      </div>
                    </div>
                  </td>
                  <td class="r bold">{{ number_format($row['invested_aoa'], 0, ',', '.') }}</td>
                  <td class="r" style="color:#16a34a;font-weight:700;">
                    +{{ number_format($row['profit_aoa'], 0, ',', '.') }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr style="background:#f8fafc;font-weight:700;">
                <td>Total</td>
                <td class="r">{{ array_sum(array_column($salesReport, 'vouchers_bought')) }}</td>
                <td class="r" style="color:#16a34a;">{{ array_sum(array_column($salesReport, 'vouchers_sold')) }}</td>
                <td class="r">{{ array_sum(array_column($salesReport, 'vouchers_bought')) - array_sum(array_column($salesReport, 'vouchers_sold')) }}</td>
                <td class="r">—</td>
                <td class="r">{{ number_format(array_sum(array_column($salesReport, 'invested_aoa')), 0, ',', '.') }}</td>
                <td class="r" style="color:#16a34a;">+{{ number_format(array_sum(array_column($salesReport, 'profit_aoa')), 0, ',', '.') }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <p style="font-size:.78rem;color:#94a3b8;margin-top:.65rem;">
          * "Vendidos ao cliente" = vouchers que marcou como entregues no painel. "Taxa de venda" = % vendidos do total comprado.
        </p>
      </div>
      @endif

    </div>
  @endif

</div>
@endsection
