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
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
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
.rv-qty-row {
  display: flex;
  align-items: center;
  gap: .5rem;
}
.rv-qty-label { font-size: .78rem; color: #64748b; font-weight: 600; }
.rv-qty-input {
  width: 70px;
  padding: .45rem .6rem;
  border: 1.5px solid #e2e8f0;
  border-radius: .5rem;
  font-size: .92rem;
  color: #0f172a;
  background: #f8fafc;
  text-align: center;
}
.rv-qty-input:focus { outline: none; border-color: #f7b500; }
.rv-btn-add {
  width: 100%;
  padding: .55rem .85rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: .5rem;
  font-size: .88rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .2s;
  text-align: center;
}
.rv-btn-add:hover { background: #e0a800; }
.rv-btn-add:disabled { opacity: .45; cursor: not-allowed; background: #e2e8f0; color: #94a3b8; }
/* Stock badge */
.rv-plan-stock-badge {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  font-size: .78rem;
  font-weight: 700;
  padding: .2rem .6rem;
  border-radius: 999px;
}
.rv-plan-stock-badge.ok  { background: #dcfce7; color: #15803d; }
.rv-plan-stock-badge.low { background: #fef9c3; color: #92400e; }
.rv-plan-stock-badge.out { background: #fee2e2; color: #b91c1c; }

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

/* ── Accordion menu ── */
.rv-menu { display: flex; flex-direction: column; gap: .65rem; margin-top: .25rem; }
.rv-menu-item { border-radius: .85rem; overflow: hidden; box-shadow: 0 2px 8px rgba(15,23,42,.07); border: 1.5px solid #e2e8f0; background: #fff; }
.rv-menu-btn {
  width: 100%; display: flex; align-items: center; justify-content: space-between;
  padding: 1.1rem 1.4rem; background: #f7b500; color: #1a202c;
  font-size: 1.1rem; font-weight: 700; cursor: pointer;
  border: none; outline: none; text-align: left; gap: .75rem;
  transition: background .15s;
}
.rv-menu-btn:hover, .rv-menu-btn.open { background: #e0a800; }
.rv-menu-btn-left { display: flex; align-items: center; gap: .65rem; flex: 1; }
.rv-menu-icon { font-size: 1.25rem; flex-shrink: 0; }
.rv-menu-label { flex: 1; }
.rv-menu-badge {
  font-size: .72rem; font-weight: 700; padding: .2rem .65rem;
  border-radius: 999px; white-space: nowrap;
}
.rv-menu-chevron {
  font-size: 1.4rem; line-height: 1; transition: transform .25s; color: rgba(26,32,44,.5);
}
.rv-menu-chevron.open { transform: rotate(90deg); color: #1a202c; }
.rv-menu-body { padding: 1.25rem; background: #f8fafc; border-top: 1.5px solid #e2e8f0; }
@media (max-width: 640px) {
  .rv-menu-btn { font-size: .95rem; padding: .9rem 1rem; }
  .rv-menu-body { padding: .9rem; }
  .rv-menu-icon { font-size: 1.05rem; }
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
      @if(session('status') === 'Plano adicionado ao carrinho.')
        <div style="display:flex;gap:.75rem;margin-top:.6rem;flex-wrap:wrap;">
          <a href="#rv-sec-comprar"
             onclick="rvOpen('comprar')"
             class="rv-btn-buy"
             style="flex:1;text-align:center;text-decoration:none;min-width:160px;">
            🛒 Ver carrinho
          </a>
          <a href="#rv-sec-vender"
             onclick="rvOpen('vender')"
             class="rv-csv-btn"
             style="flex:1;text-align:center;text-decoration:none;min-width:160px;padding:.55rem 1rem;font-weight:600;">
            ➕ Continuar a comprar
          </a>
        </div>
      @endif
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

          <form id="otpForm" action="{{ route('reseller.panel.verify') }}" method="POST" novalidate autocomplete="off">
            @csrf
            <input type="hidden" name="otp" id="otpHidden">
            <div class="rv-field">
              <label>Código de verificação</label>
              <div id="otpBoxes" style="display:flex;gap:.5rem;">
                @for($i = 0; $i < 6; $i++)
                  <input type="text" inputmode="numeric" maxlength="1" pattern="[0-9]"
                    class="otp-digit"
                    autocomplete="off"
                    aria-label="Dígito {{ $i+1 }}"
                    {{ $i === 0 ? 'autofocus' : '' }}>
                @endfor
              </div>
            </div>
            <button type="submit" class="rv-btn-login" id="otpSubmitBtn" disabled style="opacity:.45;">
              Confirmar código →
            </button>
          </form>

          <form action="{{ route('reseller.panel.logout') }}" method="POST" style="margin-top:.75rem;">
            @csrf
            <button type="submit" class="rv-logout-btn" style="width:100%;text-align:center;">← Usar outro email</button>
          </form>

          <style>
            .otp-digit {
              flex: 1;
              min-width: 0;
              padding: .65rem .25rem;
              border: 1.5px solid #e2e8f0;
              border-radius: .6rem;
              font-size: 1.5rem;
              font-weight: 800;
              font-family: monospace;
              text-align: center;
              color: #0f172a;
              background: #f8fafc;
              transition: border-color .2s, box-shadow .2s;
              box-sizing: border-box;
            }
            .otp-digit:focus {
              outline: none;
              border-color: #f7b500;
              box-shadow: 0 0 0 3px rgba(247,181,0,.15);
              background: #fff;
            }
            .otp-digit.filled {
              border-color: #0f172a;
              background: #fff;
            }
          </style>
          <script>
          (function () {
            const boxes  = Array.from(document.querySelectorAll('.otp-digit'));
            const hidden = document.getElementById('otpHidden');
            const btn    = document.getElementById('otpSubmitBtn');
            const form   = document.getElementById('otpForm');

            function sync() {
              const val  = boxes.map(b => b.value).join('');
              hidden.value = val;
              const full = /^\d{6}$/.test(val);
              btn.disabled     = !full;
              btn.style.opacity = full ? '1' : '.45';
            }

            boxes.forEach((box, idx) => {
              box.addEventListener('input', () => {
                box.value = box.value.replace(/\D/g, '').slice(-1);
                box.classList.toggle('filled', box.value !== '');
                if (box.value && idx < 5) boxes[idx + 1].focus();
                sync();
              });
              box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && idx > 0) {
                  boxes[idx - 1].value = '';
                  boxes[idx - 1].classList.remove('filled');
                  boxes[idx - 1].focus();
                  sync();
                }
                if (e.key === 'ArrowLeft'  && idx > 0) boxes[idx - 1].focus();
                if (e.key === 'ArrowRight' && idx < 5) boxes[idx + 1].focus();
                if (e.key === 'Enter' && !btn.disabled) form.submit();
              });
              box.addEventListener('paste', e => {
                const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                if (!text) return;
                e.preventDefault();
                text.split('').forEach((ch, i) => { if (boxes[i]) { boxes[i].value = ch; boxes[i].classList.add('filled'); } });
                boxes[Math.min(text.length, 5)].focus();
                sync();
              });
            });
          })();
          </script>

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

      {{-- ══ Alertas globais (sempre visíveis) ══ --}}
      @if($application->maintenanceDueThisMonth())
        <div class="rv-alert danger" style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
          <div style="display:flex;align-items:flex-start;gap:.65rem;">
            <span class="rv-alert-icon">🔔</span>
            <div>
              <strong>Taxa de manutenção mensal em atraso</strong><br>
              Valor em dívida: {{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz.
            </div>
          </div>
          <a href="{{ route('reseller.maintenance.payment') }}"
             style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:#dc2626;color:#fff;border-radius:.6rem;font-size:.85rem;font-weight:700;text-decoration:none;white-space:nowrap;">
            💳 Pagar agora
          </a>
        </div>
      @endif
      @if($application->monthly_target_aoa > 0 && $application->metMonthlyTarget())
        <div class="rv-alert" style="background:#f0fdf4;border:1px solid #86efac;border-left:4px solid #16a34a;display:flex;align-items:flex-start;gap:.65rem;padding:.85rem 1rem;border-radius:.6rem;">
          <span class="rv-alert-icon">🏆</span>
          <div style="color:#166534;"><strong>Meta mensal atingida!</strong> Parabéns — bónus em vouchers creditado.</div>
        </div>
      @endif

      {{-- ══ MENU ACCORDION ══ --}}
      <div class="rv-menu">

        {{-- ① COMPRAR --}}
        <div class="rv-menu-item" id="rv-sec-comprar">
          <button class="rv-menu-btn" onclick="rvToggle('comprar')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">🛒</span>
              <span class="rv-menu-label">Comprar</span>
              @if(!empty($cartItems))
                <span class="rv-menu-badge" style="background:#f7b500;color:#1a202c;">{{ $cartVouchers }} no carrinho</span>
              @endif
            </span>
            <span class="rv-menu-chevron" id="rv-chev-comprar">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-comprar" style="display:none;">

            {{-- Tabela de descontos --}}
            <div class="rv-panel" style="margin-bottom:1rem;">
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
                  <thead><tr><th>Compra mensal (Kz)</th><th style="text-align:right;">Desconto</th></tr></thead>
                  <tbody>
                    @foreach($tiers as $min => $pct)
                      @php
                        $idx     = array_search($min, $tierKeys);
                        $nextMin = isset($tierKeys[$idx + 1]) ? $tierKeys[$idx + 1] : null;
                        $active  = $mySpend >= $min && (!$nextMin || $mySpend < $nextMin);
                      @endphp
                      <tr class="{{ $active ? 'active' : '' }}">
                        <td>{{ number_format($min,0,',','.') }}{{ $nextMin ? ' – '.number_format($nextMin-1,0,',','.') : '+' }} Kz @if($active)<span class="rv-disc-current-chip">actual</span>@endif</td>
                        <td>{{ $pct }}%</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                <p style="font-size:.8rem;color:#94a3b8;margin-top:.65rem;">O escalão em destaque corresponde ao seu volume de compras deste mês.</p>
              @endif
            </div>

            {{-- Catálogo de planos --}}
            <div class="rv-panel" style="margin-bottom:1rem;">
              <div class="rv-panel-title"><span class="rv-panel-icon">📦</span> Abastecimento de Stock</div>
              @if($voucherPlans->isEmpty())
                <p class="rv-empty">Nenhum plano disponível de momento.</p>
              @else

                {{-- Encomenda múltipla --}}
                <form action="{{ route('reseller.cart.add.all') }}" method="POST"
                      style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:.75rem;padding:.85rem 1rem;margin-bottom:1.1rem;display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
                  @csrf
                  @foreach($voucherPlans as $plan)
                    @php $saStock = \App\Models\WifiCode::where('plan_id', $plan->slug)->where('status', 'available')->count(); @endphp
                    <div style="display:flex;flex-direction:column;gap:.25rem;min-width:110px;">
                      <label style="font-size:.72rem;font-weight:700;color:#64748b;">{{ $plan->name }}</label>
                      <input type="number" name="plans[{{ $plan->slug }}]" value="0" min="0"
                             style="width:90px;padding:.38rem .5rem;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.88rem;text-align:center;"
                             {{ $saStock === 0 ? 'disabled' : '' }}>
                      <span style="font-size:.68rem;color:{{ $saStock > 0 ? '#16a34a' : '#94a3b8' }};">
                        {{ $saStock > 0 ? $saStock.' disponíveis' : 'Sem stock' }}
                      </span>
                    </div>
                  @endforeach
                  <button type="submit"
                          style="padding:.45rem 1.1rem;background:#f7b500;color:#1a202c;font-weight:700;font-size:.85rem;border:none;border-radius:7px;cursor:pointer;white-space:nowrap;align-self:flex-start;margin-top:1.1rem;">
                    🛒 Adicionar tudo ao carrinho
                  </button>
                </form>

                <div class="rv-plan-grid">
                  @foreach($voucherPlans as $plan)
                    @php
                      $pcfg  = $storePlansConfig->get($plan->slug);
                      $emoji = $pcfg
                        ? (str_contains(strtolower($pcfg['name']), 'dia') ? '🌞'
                          : (str_contains(strtolower($pcfg['name']), 'semana') ? '📅'
                          : (str_contains(strtolower($pcfg['name']), 'mês') || str_contains(strtolower($pcfg['name']), 'mensal') ? '🗓️' : '💡')))
                        : '💡';
                      $stockAvail = \App\Models\WifiCode::where('plan_id', $plan->slug)->where('status', 'available')->count();
                    @endphp
                    <div class="rv-plan-card{{ $stockAvail === 0 ? ' rv-plan-card--out' : '' }}">
                      <div class="rv-plan-card-header">
                        <div style="display:flex;align-items:center;gap:.6rem;">
                          <span class="rv-plan-emoji" aria-hidden="true">{{ $emoji }}</span>
                          <span class="rv-plan-name">{{ $plan->name }}</span>
                        </div>
                      </div>
                      <div class="rv-plan-price-public-big">
                        <span class="rv-price-big-num">{{ number_format($plan->price_public_aoa, 0, ',', '.') }}</span>
                        <span class="rv-price-big-cur">Kz</span>
                      </div>
                      <ul class="rv-plan-features">
                        <li><strong>{{ $plan->validity_label }}</strong></li>
                        <li>{{ $plan->speed_label }}</li>
                        @if($pcfg && !empty($pcfg['download']))<li>{{ $pcfg['download'] }}</li>@endif
                      </ul>
                      @if($pcfg && !empty($pcfg['description']))
                        <p class="rv-plan-desc">{{ $pcfg['description'] }}</p>
                      @endif
                      <hr class="rv-plan-reseller-sep">
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
                      <form action="{{ route('reseller.cart.add') }}" method="POST" class="rv-plan-add-form" style="flex-direction:column;gap:.45rem;margin-top:auto;">
                        @csrf
                        <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                        <div class="rv-qty-row">
                          <span class="rv-qty-label">Qtd.:</span>
                          <input type="number" name="quantity" min="1" value="1" class="rv-qty-input" required aria-label="Quantidade" {{ $stockAvail === 0 ? 'disabled' : '' }}>
                        </div>
                        <button type="submit" class="rv-btn-add" {{ $stockAvail === 0 ? 'disabled' : '' }}>
                          {{ $stockAvail === 0 ? 'Sem stock disponível' : '+ Adicionar ao carrinho' }}
                        </button>
                      </form>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>

            {{-- Carrinho --}}
            @if(!empty($cartItems))
            <div class="rv-panel rv-cart-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">🛒</span> Carrinho
                <span style="font-size:.85rem;font-weight:500;color:#64748b;margin-left:.5rem;">{{ $cartVouchers }} voucher(s)</span>
              </div>
              <div class="rv-hist-wrap">
                <table class="rv-hist-table">
                  <thead>
                    <tr>
                      <th>Plano</th><th class="r">Qtd.</th><th class="r">P. Custo</th>
                      <th class="r">P. Venda</th><th class="r">A Pagar (Kz)</th>
                      <th class="r">Lucro (Kz)</th><th class="r">Total (Kz)</th><th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($cartItems as $item)
                    @php
                      $unitCost   = $item['subtotal'] / $item['qty'];
                      $unitSell   = $item['plan']->price_public_aoa;
                      $valorPagar = $item['subtotal'];
                      $lucro      = $item['profit'];
                      $total      = $valorPagar + $lucro;
                    @endphp
                    <tr>
                      <td><strong>{{ $item['plan']->name }}</strong><small class="muted" style="display:block;">{{ $item['plan']->validity_label }} · {{ $item['plan']->speed_label }}</small></td>
                      <td class="r">{{ $item['qty'] }}</td>
                      <td class="r">{{ number_format($unitCost, 0, ',', '.') }}</td>
                      <td class="r">{{ number_format($unitSell, 0, ',', '.') }}</td>
                      <td class="r bold">{{ number_format($valorPagar, 0, ',', '.') }}</td>
                      <td class="r" style="color:#16a34a;font-weight:700;">+{{ number_format($lucro, 0, ',', '.') }}</td>
                      <td class="r bold">{{ number_format($total, 0, ',', '.') }}</td>
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
                  <span>Total da operação: <strong>{{ number_format($cartGrossTotal, 0, ',', '.') }} Kz</strong></span>
                  <span style="color:#16a34a;">Lucro (líquido): <strong>+{{ number_format($cartNetProfit, 0, ',', '.') }} Kz</strong></span>
                  <span style="color:#dc2626;">Impostos retidos (6,5%): <strong>{{ number_format($cartTax, 0, ',', '.') }} Kz</strong></span>
                  <span style="font-weight:700;">Valor a pagar: <strong>{{ number_format($cartPayAmount, 0, ',', '.') }} Kz</strong></span>
                </div>
                @if($cartTotal < $minPurchaseAoa)
                  <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.65rem 1rem;margin:.5rem 0;color:#b91c1c;font-size:.9rem;font-weight:600;">
                    ⚠️ Compra mínima: <strong>{{ number_format($minPurchaseAoa, 0, ',', '.') }} Kz</strong>. Adicione mais vouchers.
                  </div>
                @endif
                <div class="rv-cart-actions">
                  <form action="{{ route('reseller.cart.clear') }}" method="POST" style="display:inline;">@csrf
                    <button type="submit" class="rv-csv-btn">Limpar carrinho</button>
                  </form>
                  @if(session()->has('reseller_pending_order'))
                    <a href="{{ route('reseller.panel.payment') }}" class="rv-btn-buy">💳 Completar pagamento pendente</a>
                  @elseif($cartTotal < $minPurchaseAoa)
                    <button type="button" class="rv-btn-buy" disabled style="opacity:.45;cursor:not-allowed;">✅ Finalizar compra</button>
                  @else
                    <form action="{{ route('reseller.panel.checkout') }}" method="POST" style="display:inline;">@csrf
                      <button type="submit" class="rv-btn-buy">✅ Finalizar compra &amp; pagar</button>
                    </form>
                  @endif
                </div>
              </div>
            </div>
            @endif

          </div>
        </div>

        {{-- ② VENDER --}}
        <div class="rv-menu-item" id="rv-sec-vender">
          <button class="rv-menu-btn" onclick="rvToggle('vender')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">💵</span>
              <span class="rv-menu-label">Vender</span>
              @if($totals['vouchers_in_stock'] > 0)
                <span class="rv-menu-badge" style="background:#dcfce7;color:#15803d;">{{ $totals['vouchers_in_stock'] }} disponíveis</span>
              @endif
            </span>
            <span class="rv-menu-chevron" id="rv-chev-vender">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-vender" style="display:none;">
            <div class="rv-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">🏷️</span> Venda de vouchers ao cliente</div>
              <p style="font-size:.95rem;color:#374151;margin:0 0 1rem;">Seleccione os vouchers, gere o PDF e entregue ao cliente. O sistema desconta automaticamente o stock.</p>
              @if($totals['vouchers_in_stock'] > 0)
                <a href="{{ route('reseller.sell') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.85rem 1.75rem;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border-radius:.75rem;font-size:1.05rem;font-weight:800;text-decoration:none;box-shadow:0 4px 14px rgba(22,163,74,.25);">
                  🏷️ Ir para a página de venda →
                </a>
              @else
                <div class="rv-alert warning" style="margin-bottom:0;">
                  <span class="rv-alert-icon">📭</span>
                  <div><strong>Sem vouchers em stock.</strong> Compre vouchers primeiro para poder vender.</div>
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- ③ HISTÓRICO --}}
        <div class="rv-menu-item" id="rv-sec-historico">
          <button class="rv-menu-btn" onclick="rvToggle('historico')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">🕐</span>
              <span class="rv-menu-label">Histórico</span>
            </span>
            <span class="rv-menu-chevron" id="rv-chev-historico">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-historico" style="display:none;">
            <div class="rv-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">📋</span> Histórico de compras</div>
              @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->count())
                <div class="rv-hist-wrap">
                  <table class="rv-hist-table">
                    <thead>
                      <tr>
                        <th>#</th><th>Data</th><th>Plano</th>
                        <th class="r">Qtd.</th><th class="r">Desconto</th>
                        <th class="r">Pago (Kz)</th><th class="r">Impostos (Kz)</th><th class="r">Lucro líq. (Kz)</th><th></th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($purchases as $purchase)
                        <tr @if($purchase->status === 'cancelled') style="opacity:.5;" @endif>
                          <td class="muted">#{{ $purchase->id }}</td>
                          <td>{{ optional($purchase->created_at)->format('d/m/Y H:i') }}</td>
                          <td>{{ $purchase->plan_name ?? '—' }}
                            @if($purchase->status === 'cancelled')
                              <span style="display:inline-block;margin-left:.35rem;font-size:.7rem;font-weight:700;background:#fee2e2;color:#b91c1c;border-radius:.25rem;padding:.1rem .4rem;">ANULADA</span>
                            @endif
                          </td>
                          <td class="r">{{ $purchase->codes_count }}</td>
                          <td class="r" style="color:#0d9488;font-weight:700;">{{ $purchase->discount_percent ? $purchase->discount_percent.'%' : '—' }}</td>
                          <td class="r bold">{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }}</td>
                          <td class="r" style="color:#dc2626;font-weight:600;">
                            {{ ($purchase->tax_aoa ?? 0) > 0 ? number_format($purchase->tax_aoa, 0, ',', '.') : '—' }}
                          </td>
                          <td class="r" style="color:#16a34a;font-weight:700;">
                            @if($purchase->profit_aoa) +{{ number_format($purchase->profit_aoa, 0, ',', '.') }} @else — @endif
                          </td>
                          <td style="white-space:nowrap;display:flex;gap:.4rem;flex-wrap:wrap;">
                            @if($purchase->status === 'completed')
                              <a href="{{ route('reseller.panel.purchase.codes', $purchase) }}" class="rv-csv-btn" style="border-color:#93c5fd;color:#2563eb;background:#eff6ff;">📦 Códigos</a>
                              <a href="{{ route('reseller.sell') }}" class="rv-csv-btn" style="border-color:#86efac;color:#16a34a;background:#f0fdf4;">🏷 Vender</a>
                              <a href="{{ route('reseller.panel.purchase.pdf', $purchase) }}" class="rv-csv-btn" style="border-color:#fecaca;color:#b91c1c;">📄 PDF</a>
                              <a href="{{ route('reseller.panel.purchase.vouchers', ['purchase' => $purchase->id]) }}" class="rv-csv-btn">⬇ CSV</a>
                            @elseif($purchase->status === 'pending')
                              <a href="{{ route('reseller.panel.resume.payment', $purchase) }}" class="rv-csv-btn" style="border-color:#fde68a;color:#92400e;background:#fffbeb;">💳 Retomar</a>
                            @else
                              <span style="color:#9ca3af;font-size:.82rem;">Anulada</span>
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
          </div>
        </div>

        {{-- ④ RELATÓRIOS --}}
        <div class="rv-menu-item" id="rv-sec-relatorios">
          <button class="rv-menu-btn" onclick="rvToggle('relatorios')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">📊</span>
              <span class="rv-menu-label">Relatórios</span>
            </span>
            <span class="rv-menu-chevron" id="rv-chev-relatorios">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-relatorios" style="display:none;">

            {{-- Lucro + Vouchers stats --}}
            <div class="rv-stats" style="margin-bottom:1rem;">
              <div class="rv-stat-card green" style="grid-column:1/-1;">
                <div class="rv-stat-icon">💰</div>
                <div class="rv-stat-label">Lucro estimado</div>
                <div class="rv-stat-value green" style="margin-bottom:.5rem;">{{ number_format($totals['profit_total'] ?: $estimatedProfit, 0, ',', '.') }} Kz</div>
                @if(!empty($salesReport))
                  <table class="rv-hist-table" style="margin-bottom:.4rem;">
                    <thead><tr><th>Plano</th><th class="r">Vouchers</th><th class="r">Lucro (Kz)</th><th class="r">%</th></tr></thead>
                    <tbody>
                      @php $totalProfit = max(1, $totals['profit_total'] ?: $estimatedProfit); @endphp
                      @foreach($salesReport as $row)
                        @php $pct = round($row['profit_aoa'] * 100 / $totalProfit); @endphp
                        <tr>
                          <td>{{ $row['plan_name'] }}</td>
                          <td class="r">{{ number_format($row['vouchers_bought'], 0, ',', '.') }}</td>
                          <td class="r bold" style="color:#16a34a;">+{{ number_format($row['profit_aoa'], 0, ',', '.') }}</td>
                          <td class="r"><span style="display:inline-block;background:#dcfce7;color:#15803d;font-size:.75rem;font-weight:700;padding:.1rem .45rem;border-radius:.35rem;">{{ $pct }}%</span></td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot><tr><td colspan="2"><strong>TOTAL</strong></td><td class="r bold" style="color:#16a34a;">+{{ number_format($totals['profit_total'] ?: $estimatedProfit, 0, ',', '.') }}</td><td class="r bold">100%</td></tr></tfoot>
                  </table>
                @else
                  <div class="rv-stat-sub">Total de lucro nas compras</div>
                @endif
              </div>
              <div class="rv-stat-card blue" style="grid-column:1/-1;">
                <div class="rv-stat-icon">📦</div>
                <div class="rv-stat-label">Vouchers adquiridos</div>
                <div class="rv-stat-value" style="margin-bottom:.5rem;">{{ number_format($totals['vouchers_total'], 0, ',', '.') }}</div>
                @if(!empty($salesReport))
                  <table class="rv-hist-table" style="margin-bottom:.4rem;">
                    <thead><tr><th>Plano</th><th class="r">Qtd.</th><th class="r">Investido (Kz)</th></tr></thead>
                    <tbody>
                      @foreach($salesReport as $row)
                        <tr>
                          <td>{{ $row['plan_name'] }}</td>
                          <td class="r bold">{{ number_format($row['vouchers_bought'], 0, ',', '.') }}</td>
                          <td class="r">{{ number_format($row['invested_aoa'], 0, ',', '.') }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot><tr><td><strong>TOTAL</strong></td><td class="r bold">{{ number_format($totals['vouchers_total'], 0, ',', '.') }}</td><td class="r bold">{{ number_format($totals['total_invested'], 0, ',', '.') }}</td></tr></tfoot>
                  </table>
                @else
                  <div class="rv-stat-sub">Investido: {{ number_format($totals['total_invested'], 0, ',', '.') }} Kz</div>
                @endif
              </div>
            </div>

            {{-- Relatório por plano --}}
            @if(!empty($salesReport))
            <div class="rv-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">📈</span> Relatório de Vendas por Plano</div>
              <div class="rv-hist-wrap">
                <table class="rv-hist-table">
                  <thead>
                    <tr>
                      <th>Plano</th><th class="r">Stock Inicial</th><th class="r">Entradas</th>
                      <th class="r">Saídas</th><th class="r">Stock Final</th><th class="r">Lucro (Kz)</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($salesReport as $slug => $row)
                      @php
                        $stockFinal   = $row['vouchers_in_stock'] ?? ($row['vouchers_bought'] - $row['vouchers_sold']);
                        $stockInicial = max(0, $stockFinal + $row['vouchers_sold'] - $row['vouchers_bought']);
                      @endphp
                      <tr>
                        <td><strong>{{ $row['plan_name'] }}</strong></td>
                        <td class="r" style="color:#94a3b8;">{{ $stockInicial }}</td>
                        <td class="r" style="color:#2563eb;font-weight:700;">+{{ $row['vouchers_bought'] }}</td>
                        <td class="r" style="color:#16a34a;font-weight:700;">-{{ $row['vouchers_sold'] }}</td>
                        <td class="r" style="color:{{ $stockFinal > 0 ? '#0f172a' : '#94a3b8' }};font-weight:700;">{{ $stockFinal }}</td>
                        <td class="r" style="color:#16a34a;font-weight:700;">+{{ number_format($row['profit_aoa'], 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr style="background:#f8fafc;font-weight:700;">
                      <td>Total</td>
                      <td class="r" style="color:#94a3b8;">0</td>
                      <td class="r" style="color:#2563eb;">+{{ array_sum(array_column($salesReport, 'vouchers_bought')) }}</td>
                      <td class="r" style="color:#16a34a;">-{{ array_sum(array_column($salesReport, 'vouchers_sold')) }}</td>
                      <td class="r" style="color:{{ $totals['vouchers_in_stock'] > 0 ? '#0f172a' : '#94a3b8' }};">{{ $totals['vouchers_in_stock'] }}</td>
                      <td class="r" style="color:#16a34a;">+{{ number_format(array_sum(array_column($salesReport, 'profit_aoa')), 0, ',', '.') }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
              <p style="font-size:.78rem;color:#94a3b8;margin-top:.65rem;">Stock Final = Stock Inicial + Entradas − Saídas.</p>
            </div>
            @else
              <p class="rv-empty">Ainda não há dados de vendas.</p>
            @endif

          </div>
        </div>

        {{-- ⑤ MANUTENÇÃO MENSAL --}}
        <div class="rv-menu-item" id="rv-sec-manutencao">
          <button class="rv-menu-btn" onclick="rvToggle('manutencao')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">🔧</span>
              <span class="rv-menu-label">Manutenção Mensal</span>
              @if(!$application->maintenanceDueThisMonth())
                <span class="rv-menu-badge" style="background:#dcfce7;color:#15803d;">✔ paga</span>
              @else
                <span class="rv-menu-badge" style="background:#fef2f2;color:#dc2626;">⚠ obrigatória</span>
              @endif
            </span>
            <span class="rv-menu-chevron" id="rv-chev-manutencao">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-manutencao" style="display:none;">
            <div class="rv-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">📋</span> Taxa de Manutenção Mensal</div>
              @if(!$application->maintenanceDueThisMonth())
                <div style="display:flex;align-items:center;gap:.75rem;padding:.85rem 1rem;background:#f0fdf4;border:1px solid #86efac;border-radius:.6rem;">
                  <span style="font-size:1.4rem;">✅</span>
                  <div>
                    <strong style="color:#15803d;">Manutenção paga para: {{ now()->format('m/Y') }}</strong><br>
                    <span style="font-size:.83rem;color:#166534;">O pagamento mensal foi efectuado e o serviço está activo.</span>
                  </div>
                </div>
              @else
                <p style="font-size:.88rem;color:#374151;margin:0 0 .9rem;">
                  A Taxa de Manutenção Mensal deve ser paga de <strong>uma só vez</strong> pelo importador.
                  O valor em dívida é de <strong>{{ number_format($application->maintenanceFeeAoa(), 0, ',', '.') }} Kz</strong>.
                </p>
                <a href="{{ route('reseller.maintenance.payment') }}"
                   style="display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.4rem;background:#dc2626;color:#fff;border-radius:.6rem;font-size:.88rem;font-weight:700;text-decoration:none;">
                  💳 Pagar manutenção agora
                </a>
              @endif
            </div>
          </div>
        </div>
        
        {{-- ⑥ META MENSAL --}}
        @if($application->monthly_target_aoa > 0 || $application->bonus_vouchers_aoa > 0)
        @php
          $metaTarget = $application->monthly_target_aoa;
          $metaSpend  = $application->monthlySales();
          $metaPct    = $metaTarget > 0 ? min(100, (int) round($metaSpend * 100 / $metaTarget)) : 0;
          $metaMet    = $metaTarget > 0 && $metaSpend >= $metaTarget;
          $bonusBreakdown = config('reseller.bonus_breakdown', []);
          $bonusTotal = collect($bonusBreakdown)->sum('total');
        @endphp
        <div class="rv-menu-item" id="rv-sec-meta">
          <button class="rv-menu-btn" onclick="rvToggle('meta')">
            <span class="rv-menu-btn-left">
              <span class="rv-menu-icon">🏆</span>
              <span class="rv-menu-label">Meta Mensal</span>
              @if($metaMet)
                <span class="rv-menu-badge" style="background:#ede9fe;color:#6d28d9;">🎁 bónus atingido</span>
              @else
                <span class="rv-menu-badge" style="background:#fef3c7;color:#92400e;">facultativa</span>
              @endif
            </span>
            <span class="rv-menu-chevron" id="rv-chev-meta">›</span>
          </button>
          <div class="rv-menu-body" id="rv-body-meta" style="display:none;">
            <div class="rv-panel" style="margin-bottom:0;">
              <div class="rv-panel-title"><span class="rv-panel-icon">🎯</span> Meta Mensal — Bónus em Vouchers</div>
              <p style="font-size:.88rem;color:#374151;margin:0 0 .9rem;">
                A meta mensal é <strong>facultativa</strong>. Ao atingir o volume de
                <strong>{{ number_format($metaTarget, 0, ',', '.') }} Kz</strong> em <strong>vendas</strong> ao cliente final este mês recebe bónus em vouchers.
              </p>
              @if($metaTarget > 0)
                <div style="display:flex;justify-content:space-between;font-size:.85rem;color:#374151;margin-bottom:.4rem;">
                  <span>Vendas este mês:</span>
                  <span style="font-weight:700;color:{{ $metaPct >= 100 ? '#7c3aed' : '#d97706' }};">
                    {{ number_format($metaSpend, 0, ',', '.') }} / {{ number_format($metaTarget, 0, ',', '.') }} Kz ({{ $metaPct }}%)
                  </span>
                </div>
                <div class="rv-progress-bar" style="margin-bottom:.75rem;">
                  <div class="rv-progress-fill" style="width:{{ $metaPct }}%;background:{{ $metaPct >= 100 ? '#7c3aed' : '#f59e0b' }};"></div>
                </div>
                @if($metaMet)
                  <p style="font-size:.88rem;color:#7c3aed;font-weight:700;margin-bottom:.75rem;">🎁 Meta atingida! Bónus creditado.</p>
                @else
                  <p style="font-size:.82rem;color:#92400e;margin-bottom:.75rem;">
                    Faltam <strong>{{ number_format($metaTarget - $metaSpend, 0, ',', '.') }} Kz</strong> em vendas para atingir a meta e ganhar o bónus.
                  </p>
                @endif
              @endif

              {{-- TOP 10 LEADERBOARD --}}
              @if($topSellers->isNotEmpty())
              <div class="rv-panel-title" style="font-size:.9rem;margin-top:.5rem;border-top:1.5px solid #f1f5f9;padding-top:.75rem;">
                <span class="rv-panel-icon">🏅</span> Top 10 — Vendas deste mês
                @if($myRank)
                  <span style="margin-left:.5rem;font-size:.8rem;font-weight:700;background:#ede9fe;color:#6d28d9;padding:2px 8px;border-radius:999px;">
                    A sua posição: #{{ $myRank }}
                  </span>
                @endif
              </div>
              <table class="rv-hist-table" style="margin:.5rem 0 .35rem;">
                <thead>
                  <tr>
                    <th style="width:28px;">#</th>
                    <th>Agente</th>
                    <th class="r">Vouchers</th>
                    <th class="r">Vendas (Kz)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($topSellers as $i => $seller)
                  @php
                    $isMe      = $application->id === $seller->reseller_id;
                    $qualifies = $seller->total_sales_aoa >= $metaTarget;
                    $pos       = $i + 1;
                  @endphp
                  <tr style="{{ $isMe ? 'background:#faf5ff;font-weight:700;' : '' }}">
                    <td style="text-align:center;color:{{ $pos <= 3 ? '#d97706' : '#9ca3af' }};font-weight:700;">
                      @if($pos === 1) 🥇
                      @elseif($pos === 2) 🥈
                      @elseif($pos === 3) 🥉
                      @else {{ $pos }}
                      @endif
                    </td>
                    <td>
                      {{ $isMe ? 'Você (' . Str::before($seller->full_name, ' ') . ')' : Str::before($seller->full_name, ' ') . ' ' . Str::substr(Str::after($seller->full_name, ' '), 0, 1) . '.' }}
                      @if($qualifies)
                        <span style="font-size:.75rem;background:#dcfce7;color:#166534;padding:1px 6px;border-radius:999px;margin-left:3px;">bónus</span>
                      @endif
                    </td>
                    <td class="r">{{ number_format($seller->vouchers_sold, 0, ',', '.') }}</td>
                    <td class="r bold" style="color:#7c3aed;">{{ number_format($seller->total_sales_aoa, 0, ',', '.') }}</td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
              <p style="font-size:.78rem;color:#9ca3af;margin-top:.2rem;">
                Agentes com vendas &ge; {{ number_format($metaTarget, 0, ',', '.') }} Kz recebem bónus em vouchers.
                Nomes apresentados de forma abreviada para privacidade.
              </p>
              @endif

              @if($application->bonus_vouchers_aoa > 0)
                <div class="rv-panel-title" style="font-size:.9rem;margin-top:.5rem;border-top:1.5px solid #f1f5f9;padding-top:.75rem;"><span class="rv-panel-icon">🎁</span> Bónus de arranque</div>
                <table class="rv-hist-table" style="margin:.5rem 0 .35rem;">
                  <thead><tr><th>Planos</th><th class="r">P. Unit.</th><th class="r">Qtd.</th><th class="r">Valor</th></tr></thead>
                  <tbody>
                    @foreach($bonusBreakdown as $row)
                      <tr>
                        <td>{{ $row['name'] }}</td>
                        <td class="r">{{ number_format($row['unit_price'], 0, ',', '.') }}</td>
                        <td class="r"><strong>{{ $row['qty'] }}</strong></td>
                        <td class="r bold">{{ number_format($row['total'], 0, ',', '.') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                  <tfoot><tr><td colspan="3"><strong>TOTAL</strong></td><td class="r bold">{{ number_format($bonusTotal, 0, ',', '.') }}</td></tr></tfoot>
                </table>
                <div class="rv-stat-sub">Vouchers atribuídos no arranque da parceria</div>
              @endif
            </div>
          </div>
        </div>
        @endif

      </div>{{-- /.rv-menu --}}

    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
function rvToggle(id) {
  var body  = document.getElementById('rv-body-' + id);
  var chev  = document.getElementById('rv-chev-' + id);
  var btn   = chev ? chev.closest('.rv-menu-btn') : null;
  var open  = body && body.style.display !== 'none';
  if (body)  body.style.display = open ? 'none' : 'block';
  if (chev)  chev.classList.toggle('open', !open);
  if (btn)   btn.classList.toggle('open', !open);
}
function rvOpen(id) {
  var body = document.getElementById('rv-body-' + id);
  var chev = document.getElementById('rv-chev-' + id);
  var btn  = chev ? chev.closest('.rv-menu-btn') : null;
  if (body) body.style.display = 'block';
  if (chev) chev.classList.add('open');
  if (btn)  btn.classList.add('open');
}
@if(!empty($cartItems) || str_contains(session('status', ''), 'carrinho') || str_contains(session('error', ''), 'carrinho'))
document.addEventListener('DOMContentLoaded', function() { rvOpen('comprar'); });
@endif

</script>
@endpush
