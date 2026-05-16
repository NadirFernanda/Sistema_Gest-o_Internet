@extends('layouts.app')

@section('title', 'Checkout – AngolaWiFi')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   CHECKOUT DIÁRIO — PLANOS INDIVIDUAIS
   ══════════════════════════════════════════════ */
.ck-page {
  min-height: 80vh;
  background: #f0f2f5;
  padding: 2.5rem 1rem 5rem;
  font-family: Inter, system-ui, -apple-system, sans-serif;
}
.ck-wrap {
  max-width: 900px;
  margin: 0 auto;
}

.ck-back {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-size: .82rem;
  font-weight: 600;
  color: #64748b;
  text-decoration: none;
  margin-bottom: 1.5rem;
  transition: color .15s;
}
.ck-back:hover { color: #1a202c; }

/* ── 2-col layout ─────────────────────────────── */
.ck-layout {
  display: grid;
  grid-template-columns: 1fr 1.6fr;
  gap: 1.5rem;
  align-items: start;
}

/* ── Cards ────────────────────────────────────── */
.ck-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}

/* ── Plan badge (summary top) ─────────────────── */
.ck-plan-badge {
  background: linear-gradient(135deg, #fffbea 0%, #fff8d6 100%);
  border-bottom: 1px solid rgba(247,181,0,.25);
  padding: 1.5rem 1.5rem 1.25rem;
  position: relative;
}
.ck-plan-badge::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f7b500, #ffd95a);
}
.ck-plan-label {
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: #b8860b;
  margin: 0 0 .3rem;
}
.ck-plan-name {
  font-size: 1.15rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .75rem;
  letter-spacing: -.02em;
  line-height: 1.2;
}
.ck-plan-price {
  display: flex;
  align-items: baseline;
  gap: .3rem;
}
.ck-price-val {
  font-size: 2rem;
  font-weight: 900;
  color: #1a202c;
  letter-spacing: -.03em;
  line-height: 1;
}
.ck-price-cur {
  font-size: .88rem;
  font-weight: 700;
  color: #64748b;
}

/* ── Summary details ──────────────────────────── */
.ck-summary-details {
  padding: 1.25rem 1.5rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: .55rem;
}
.ck-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .84rem;
}
.ck-detail-row .ck-dr-label { color: #6b7280; }
.ck-detail-row .ck-dr-val   { font-weight: 600; color: #1a202c; }
.ck-detail-divider {
  height: 1px;
  background: #e5e7eb;
  margin: .2rem 0;
}
.ck-detail-total {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .95rem;
  font-weight: 800;
  color: #1a202c;
}

/* ── Security note ────────────────────────────── */
.ck-security {
  padding: .85rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .5rem;
  font-size: .74rem;
  color: #94a3b8;
}
.ck-security svg { flex-shrink: 0; color: #16a34a; }

/* ── Form card ────────────────────────────────── */
.ck-form-body {
  padding: 1.75rem 1.75rem 2rem;
}
.ck-section-title {
  font-size: 1rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .25rem;
  letter-spacing: -.01em;
}
.ck-section-sub {
  font-size: .8rem;
  color: #94a3b8;
  margin: 0 0 1.25rem;
}

/* ── Errors ───────────────────────────────────── */
.ck-errors {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  border-left: 4px solid #dc2626;
  border-radius: 10px;
  padding: .85rem 1rem;
  margin-bottom: 1.25rem;
  font-size: .84rem;
  color: #7f1d1d;
}
.ck-errors ul { margin: .3rem 0 0 1.1rem; padding: 0; }
.ck-errors li { margin: .12rem 0; }

/* ── Fields ───────────────────────────────────── */
.ck-fields {
  display: flex;
  flex-direction: column;
  gap: .85rem;
  margin-bottom: 1.5rem;
}
.ck-label {
  display: block;
  font-size: .8rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: .3rem;
}
.ck-input-wrap {
  position: relative;
}
.ck-input-icon {
  position: absolute;
  left: .85rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #94a3b8;
  display: flex;
  align-items: center;
}
.ck-input {
  display: block;
  width: 100%;
  box-sizing: border-box;
  padding: .72rem .9rem .72rem 2.5rem;
  border: 1.5px solid #e5e7eb;
  border-radius: 10px;
  font-size: .9rem;
  font-family: inherit;
  color: #1a202c;
  background: #fafafa;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
}
.ck-input:focus {
  border-color: #f7b500;
  box-shadow: 0 0 0 3px rgba(247,181,0,.15);
  background: #fff;
}

/* ── Create account box ───────────────────────── */
.ck-account-box {
  background: #fffbeb;
  border: 1.5px solid #fde68a;
  border-radius: 10px;
  padding: 1rem 1.1rem;
  margin-bottom: 1.5rem;
}
.ck-account-label {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  cursor: pointer;
}
.ck-account-label input[type="checkbox"] {
  accent-color: #f7b500;
  width: 15px;
  height: 15px;
  margin-top: .15rem;
  flex-shrink: 0;
}
.ck-account-title {
  font-size: .88rem;
  font-weight: 700;
  color: #111827;
}
.ck-account-desc {
  font-size: .76rem;
  color: #6b7280;
  margin: .15rem 0 0;
  line-height: 1.5;
}

/* ── Payment method ───────────────────────────── */
.ck-payment-section {
  border: 1.5px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 1.5rem;
}
.ck-payment-header {
  padding: .55rem 1rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
  font-size: .7rem;
  font-weight: 700;
  color: #9ca3af;
  text-transform: uppercase;
  letter-spacing: .07em;
}
.ck-payment-option {
  display: flex;
  align-items: center;
  gap: .85rem;
  padding: 1rem;
  background: #fff;
  cursor: pointer;
}
.ck-payment-option input[type="radio"] {
  accent-color: #f7b500;
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}
.ck-payment-info { flex: 1; min-width: 0; }
.ck-payment-name {
  font-size: .88rem;
  font-weight: 600;
  color: #111827;
}
.ck-payment-desc {
  font-size: .74rem;
  color: #9ca3af;
  margin-top: .1rem;
}
.ck-payment-secure {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  font-size: .7rem;
  font-weight: 600;
  color: #059669;
  background: #d1fae5;
  padding: .22rem .6rem;
  border-radius: 999px;
  white-space: nowrap;
  flex-shrink: 0;
}
.ck-payment-secure svg { flex-shrink: 0; }

/* ── Submit button ────────────────────────────── */
.ck-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .6rem;
  width: 100%;
  padding: 1rem 1.5rem;
  background: #1a202c;
  color: #f7b500;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 800;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s, transform .12s;
  letter-spacing: -.01em;
  box-shadow: 0 2px 12px rgba(26,32,44,.2);
}
.ck-submit:hover  { background: #111827; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,32,44,.28); }
.ck-submit:active { transform: translateY(0); }
.ck-submit:disabled { background: #6b7280; color: #9ca3af; cursor: default; transform: none; box-shadow: none; }

.ck-terms {
  text-align: center;
  font-size: .73rem;
  color: #9ca3af;
  margin-top: .75rem;
}
.ck-terms a { color: #b8860b; text-decoration: none; }
.ck-terms a:hover { text-decoration: underline; }

/* ── Spinner ──────────────────────────────────── */
@keyframes ck-spin { to { transform: rotate(360deg); } }
.ck-spinning { animation: ck-spin .75s linear infinite; display: inline-block; }

/* ── Mobile ───────────────────────────────────── */
@media (max-width: 680px) {
  .ck-page { padding: 1.25rem .75rem 4rem; }
  .ck-layout { grid-template-columns: 1fr; }
  .ck-plan-badge,
  .ck-summary-details,
  .ck-security,
  .ck-form-body { padding-left: 1.25rem; padding-right: 1.25rem; }
}
</style>
@endpush

@section('content')
<div class="ck-page">
<div class="ck-wrap">

  <a href="/" class="ck-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar à loja
  </a>

  @if($plan)
  <form method="POST" action="{{ route('store.checkout.process') }}" id="checkoutForm">
    @csrf
    <input type="hidden" name="plan_id" value="{{ $plan->slug }}">
    <input type="hidden" name="payment_method" value="gpo">

    <div class="ck-layout">

      {{-- ── Resumo ────────────────────────────────────────────────────── --}}
      <div class="ck-card">

        <div class="ck-plan-badge">
          <p class="ck-plan-label">Plano selecionado</p>
          <h1 class="ck-plan-name">{{ $plan->name }}</h1>
          <div class="ck-plan-price">
            <span class="ck-price-val">{{ number_format($plan->price_public_aoa, 0, ',', '.') }}</span>
            <span class="ck-price-cur">AOA</span>
          </div>
        </div>

        <div class="ck-summary-details">
          <div class="ck-detail-row">
            <span class="ck-dr-label">Velocidade</span>
            <span class="ck-dr-val">{{ $plan->speed_label }}</span>
          </div>
          <div class="ck-detail-row">
            <span class="ck-dr-label">Duração</span>
            <span class="ck-dr-val">{{ $plan->validity_label }}</span>
          </div>
          <div class="ck-detail-row">
            <span class="ck-dr-label">Download</span>
            <span class="ck-dr-val">Ilimitado</span>
          </div>
          <div class="ck-detail-row">
            <span class="ck-dr-label">Quantidade</span>
            <span class="ck-dr-val">1 código WiFi</span>
          </div>
          <div class="ck-detail-divider"></div>
          <div class="ck-detail-total">
            <span>Total</span>
            <span>{{ number_format($plan->price_public_aoa, 0, ',', '.') }} AOA</span>
          </div>
        </div>

        <div class="ck-security">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Pagamento seguro processado pela EMIS
        </div>

      </div>

      {{-- ── Formulário ───────────────────────────────────────────────── --}}
      <div class="ck-card">
        <div class="ck-form-body">

          @if ($errors->any())
            <div class="ck-errors">
              <strong>Por favor corrija os erros:</strong>
              <ul>
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <h2 class="ck-section-title">Os seus dados</h2>
          <p class="ck-section-sub">Opcional — para receber o código por e-mail e WhatsApp.</p>

          <div class="ck-fields">

            {{-- Nome --}}
            <div>
              <label for="customer_name" class="ck-label">Nome completo</label>
              <div class="ck-input-wrap">
                <span class="ck-input-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                </span>
                <input type="text" id="customer_name" name="customer_name" class="ck-input"
                  placeholder="Ex: João Silva"
                  value="{{ old('customer_name') }}" maxlength="100">
              </div>
            </div>

            {{-- E-mail --}}
            <div>
              <label for="customer_email" class="ck-label">E-mail</label>
              <div class="ck-input-wrap">
                <span class="ck-input-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
                </span>
                <input type="email" id="customer_email" name="customer_email" class="ck-input"
                  placeholder="Ex: joao@exemplo.com"
                  value="{{ old('customer_email') }}" maxlength="150">
              </div>
            </div>

            {{-- Telemóvel --}}
            <div>
              <label for="customer_phone" class="ck-label">Telemóvel</label>
              <div class="ck-input-wrap">
                <span class="ck-input-icon">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81 19.79 19.79 0 012 1.18 2 2 0 013.16 2l3.78-.84a2 2 0 012 1.32 12.74 12.74 0 001.24 3 2 2 0 01-.45 2.11L8.09 9a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.74 12.74 0 003 1.24 2 2 0 011.32 1.98z"/></svg>
                </span>
                <input type="tel" id="customer_phone" name="customer_phone" class="ck-input"
                  placeholder="9XXXXXXXX"
                  value="{{ old('customer_phone') }}"
                  pattern="^(244)?9[0-9]{8}$" inputmode="numeric" maxlength="12">
              </div>
            </div>

          </div>

          {{-- Criar conta (aparece quando email preenchido) --}}
          <div id="criarContaSection" class="ck-account-box" style="display:none;">
            <label class="ck-account-label">
              <input type="checkbox" id="criarContaCheck" name="create_account" value="1"
                {{ old('create_account') ? 'checked' : '' }}>
              <div>
                <div class="ck-account-title">Criar conta AngolaWiFi</div>
                <p class="ck-account-desc">Guarde o histórico das suas compras e aceda aos seus códigos em <strong>Minha Conta</strong>. Sem passwords — basta o seu e-mail.</p>
              </div>
            </label>
          </div>

          {{-- Método de pagamento --}}
          <div class="ck-payment-section">
            <div class="ck-payment-header">Método de Pagamento</div>
            <label class="ck-payment-option">
              <input type="radio" name="_payment_display" checked>
              <img src="/img/emis.png" alt="EMIS" height="26" style="display:block;flex-shrink:0;">
              <div class="ck-payment-info">
                <div class="ck-payment-name">Pagamento Online Seguro</div>
                <div class="ck-payment-desc">Cartão bancário · Multicaixa Express</div>
              </div>
              <span class="ck-payment-secure">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Seguro
              </span>
            </label>
          </div>

          {{-- Botão --}}
          <button type="submit" id="btnSubmit" class="ck-submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            <span id="btnTexto">Prosseguir para pagamento</span>
            <span id="btnSpinner" style="display:none;" class="ck-spinning">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </span>
          </button>

          <p class="ck-terms">
            Ao prosseguir aceita os <a href="#">Termos de Serviço</a> da AngolaWiFi.
          </p>

        </div>
      </div>

    </div>
  </form>

  @else
    <div style="background:#fff;border-radius:20px;padding:2.5rem;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,.08);">
      <p style="color:#6b7280;margin-bottom:1rem;">Não foi possível identificar o plano selecionado.</p>
      <a href="{{ url('/') }}" style="display:inline-flex;align-items:center;gap:.5rem;background:#1a202c;color:#f7b500;padding:.85rem 1.5rem;border-radius:12px;font-weight:700;text-decoration:none;font-size:.95rem;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Voltar à loja
      </a>
    </div>
  @endif

</div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkoutForm')?.addEventListener('submit', function () {
  document.getElementById('btnTexto').style.display   = 'none';
  document.getElementById('btnSpinner').style.display = 'inline-block';
  document.getElementById('btnSubmit').disabled = true;
});

(function () {
  var emailInput   = document.getElementById('customer_email');
  var criarSection = document.getElementById('criarContaSection');
  var criarCheck   = document.getElementById('criarContaCheck');
  if (!emailInput || !criarSection) return;

  function toggle() {
    var hasEmail = emailInput.value.trim().length > 0;
    criarSection.style.display = hasEmail ? 'block' : 'none';
    if (!hasEmail) criarCheck.checked = false;
  }

  emailInput.addEventListener('input', toggle);
  toggle();
})();
</script>
@endpush
