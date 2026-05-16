@extends('layouts.app')

@section('title', 'Pagamento Seguro — AngolaWiFi')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   PAGAMENTO GPO — PLANOS FAMILIARES / EMPRESARIAIS
   ══════════════════════════════════════════════ */
.fgpo-page {
  min-height: 80vh;
  background: #f0f2f5;
  padding: 2.5rem 0 5rem;
  font-family: Inter, system-ui, -apple-system, sans-serif;
}
.fgpo-wrap {
  max-width: 940px;
  margin: 0 auto;
}
.fgpo-back {
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
.fgpo-back:hover { color: #1a202c; }

/* ── 2-col layout ──────────────────────────── */
.fgpo-layout {
  display: grid;
  grid-template-columns: 1fr 1.7fr;
  gap: 1.5rem;
  align-items: start;
}

/* ── Cards ─────────────────────────────────── */
.fgpo-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}

/* ── Plan badge ────────────────────────────── */
.fgpo-plan-badge {
  background: linear-gradient(135deg, #fffbea 0%, #fff8d6 100%);
  border-bottom: 1px solid rgba(247,181,0,.25);
  padding: 1.4rem 1.5rem 1.2rem;
  position: relative;
}
.fgpo-plan-badge::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f7b500, #ffd95a);
}
.fgpo-plan-label {
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: #b8860b;
  margin: 0 0 .3rem;
}
.fgpo-plan-name {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .7rem;
  letter-spacing: -.02em;
  line-height: 1.2;
}
.fgpo-plan-price {
  display: flex;
  align-items: baseline;
  gap: .3rem;
}
.fgpo-price-val {
  font-size: 1.85rem;
  font-weight: 900;
  color: #1a202c;
  letter-spacing: -.03em;
  line-height: 1;
}
.fgpo-price-cur {
  font-size: .85rem;
  font-weight: 700;
  color: #64748b;
}

/* ── Order details ─────────────────────────── */
.fgpo-details {
  padding: 1.2rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: .55rem;
}
.fgpo-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .84rem;
}
.fgpo-dr-label { color: #6b7280; }
.fgpo-dr-val   { font-weight: 600; color: #1a202c; font-family: 'Courier New', monospace; font-size: .82rem; }
.fgpo-dr-val-n { font-weight: 600; color: #1a202c; }
.fgpo-detail-divider {
  height: 1px;
  background: #e5e7eb;
  margin: .1rem 0;
}
.fgpo-detail-total {
  display: flex;
  justify-content: space-between;
  font-size: .95rem;
  font-weight: 800;
  color: #1a202c;
}

/* ── Status row ────────────────────────────── */
.fgpo-status-row {
  padding: .85rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .6rem;
  font-size: .82rem;
}
.fgpo-status-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
}
.fgpo-status-dot--waiting {
  background: #f7b500;
  animation: fgpo-pulse 1.6s ease-in-out infinite;
}
.fgpo-status-dot--paid    { background: #16a34a; }
.fgpo-status-dot--failed  { background: #dc2626; }
@keyframes fgpo-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(247,181,0,.5); }
  70%  { box-shadow: 0 0 0 7px rgba(247,181,0,0); }
  100% { box-shadow: 0 0 0 0 rgba(247,181,0,0); }
}
.fgpo-status-label { font-weight: 600; color: #374151; }

/* ── Payment card header ───────────────────── */
.fgpo-payment-header {
  padding: 1.1rem 1.5rem .9rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.fgpo-payment-title {
  font-size: .95rem;
  font-weight: 800;
  color: #1a202c;
}
.fgpo-iframe-wrap iframe {
  display: block;
  width: 100%;
  min-height: 540px;
  border: none;
}
.fgpo-iframe-note {
  padding: .7rem 1.25rem;
  border-top: 1px solid #f1f5f9;
  font-size: .74rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: .4rem;
}
.fgpo-iframe-note svg { flex-shrink: 0; color: #16a34a; }

/* ── Success panel ─────────────────────────── */
.fgpo-success {
  padding: 2.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.25rem;
}
.fgpo-success-icon {
  width: 4rem;
  height: 4rem;
  background: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.fgpo-success-icon svg { color: #fff; }
.fgpo-success-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #14532d;
  margin: 0;
}
.fgpo-success-sub {
  font-size: .84rem;
  color: #16a34a;
  margin: .2rem 0 0;
  line-height: 1.55;
}
.fgpo-success-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .85rem 1.75rem;
  background: #f7b500;
  color: #1a202c;
  border-radius: 12px;
  font-weight: 800;
  font-size: .95rem;
  text-decoration: none;
  transition: filter .15s, transform .12s;
  box-shadow: 0 2px 12px rgba(247,181,0,.35);
}
.fgpo-success-link:hover { filter: brightness(.95); transform: translateY(-1px); }

/* ── Error / Timeout panel ─────────────────── */
.fgpo-error {
  padding: 2.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1rem;
}
.fgpo-error-icon {
  width: 3.5rem;
  height: 3.5rem;
  background: #fef2f2;
  border: 2px solid #fca5a5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.fgpo-error-icon svg { color: #dc2626; }
.fgpo-timeout-icon {
  width: 3.5rem;
  height: 3.5rem;
  background: #fffbeb;
  border: 2px solid #fde68a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.fgpo-timeout-icon svg { color: #d97706; }
.fgpo-error-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #7f1d1d;
  margin: 0;
}
.fgpo-timeout-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #78350f;
  margin: 0;
}
.fgpo-error-sub {
  font-size: .84rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.55;
  max-width: 320px;
}
.fgpo-error-actions {
  display: flex;
  flex-direction: column;
  gap: .6rem;
  width: 100%;
  max-width: 300px;
}
.fgpo-cancel-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .85rem 1.5rem;
  background: #f7b500;
  color: #1a202c;
  border-radius: 12px;
  font-weight: 800;
  font-size: .9rem;
  border: none;
  cursor: pointer;
  font-family: inherit;
  width: 100%;
  transition: filter .15s;
  box-shadow: 0 2px 12px rgba(247,181,0,.3);
}
.fgpo-cancel-btn:hover { filter: brightness(.95); }
.fgpo-back-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .75rem 1.5rem;
  background: #fff;
  color: #374151;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-weight: 600;
  font-size: .9rem;
  text-decoration: none;
  transition: background .15s;
}
.fgpo-back-btn:hover { background: #f8fafc; }

/* ── Mobile ────────────────────────────────── */
@media (max-width: 680px) {
  .fgpo-page { padding: 1.25rem 0 4rem; }
  .fgpo-layout { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="fgpo-page">
<div class="fgpo-wrap">

  <a href="{{ url('/') }}" class="fgpo-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar à loja
  </a>

  <div class="fgpo-layout">

    {{-- ── Resumo ───────────────────────────────────────────────────── --}}
    <div class="fgpo-card">

      <div class="fgpo-plan-badge">
        <p class="fgpo-plan-label">Resumo do pedido</p>
        <h1 class="fgpo-plan-name">{{ $familyRequest->plan_name }}</h1>
        <div class="fgpo-plan-price">
          <span class="fgpo-price-val">{{ number_format($familyRequest->plan_preco, 0, ',', '.') }}</span>
          <span class="fgpo-price-cur">Kz</span>
        </div>
      </div>

      <div class="fgpo-details">
        @if($familyRequest->plan_ciclo_dias)
          <div class="fgpo-detail-row">
            <span class="fgpo-dr-label">Duração</span>
            <span class="fgpo-dr-val-n">{{ $familyRequest->plan_ciclo_dias }} dias</span>
          </div>
        @endif
        <div class="fgpo-detail-row">
          <span class="fgpo-dr-label">Cliente</span>
          <span class="fgpo-dr-val-n" style="max-width:55%;text-align:right;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $familyRequest->customer_name }}</span>
        </div>
        <div class="fgpo-detail-row">
          <span class="fgpo-dr-label">Referência</span>
          <span class="fgpo-dr-val">{{ $reference }}</span>
        </div>
        <div class="fgpo-detail-divider"></div>
        <div class="fgpo-detail-total">
          <span>Total</span>
          <span>{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} Kz</span>
        </div>
      </div>

      <div class="fgpo-status-row">
        <span class="fgpo-status-dot fgpo-status-dot--waiting" id="statusDot"></span>
        <span class="fgpo-status-label" id="statusTexto">A aguardar pagamento</span>
      </div>

    </div>

    {{-- ── Painel de pagamento ──────────────────────────────────────── --}}
    <div class="fgpo-card">

      {{-- Iframe EMIS --}}
      <div id="painelIframe">
        <div class="fgpo-payment-header">
          <span class="fgpo-payment-title">Pagamento seguro</span>
          <img src="/img/emis_logo.jpg" alt="EMIS" height="24" style="display:block;">
        </div>
        <div class="fgpo-iframe-wrap">
          <iframe
            id="gpoFrame"
            src="{{ $iframeUrl }}"
            title="Pagamento seguro EMIS GPO"
            allow="payment"
            sandbox="allow-scripts allow-forms allow-same-origin allow-popups"
          ></iframe>
        </div>
        <div class="fgpo-iframe-note">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Os dados do cartão são processados directamente pela EMIS e nunca passam pelos nossos servidores.
        </div>
      </div>

      {{-- Pagamento confirmado --}}
      <div id="painelAprovado" style="display:none;">
        <div class="fgpo-success">
          <div class="fgpo-success-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <p class="fgpo-success-title">Pagamento confirmado!</p>
            <p class="fgpo-success-sub">O seu plano está a ser activado.<br>Receberá uma confirmação por e-mail em breve.</p>
          </div>
          <a href="#" id="linkAprovado" class="fgpo-success-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Ver confirmação
          </a>
        </div>
      </div>

      {{-- Pagamento recusado --}}
      <div id="painelRecusado" style="display:none;">
        <div class="fgpo-error">
          <div class="fgpo-error-icon" id="errorIcon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          </div>
          <p class="fgpo-error-title" id="errorTitle">Pagamento não concluído</p>
          <p class="fgpo-error-sub" id="errorSub">O pagamento foi recusado ou cancelado. Pode tentar com outro método de pagamento.</p>
          <div class="fgpo-error-actions">
            <form method="POST" action="{{ route('family.payment.gpo.cancel', $familyRequest->id) }}" style="width:100%;">
              @csrf
              <button type="submit" class="fgpo-cancel-btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                Tentar outro método
              </button>
            </form>
            <a href="{{ url('/') }}" class="fgpo-back-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
              Voltar à loja
            </a>
          </div>
        </div>
      </div>

    </div>

  </div>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var statusUrl     = '{{ route("family.payment.gpo.status", $familyRequest->id) }}';
  var gpoOrigin     = 'https://pagamentonline.emis.co.ao';
  var tentativas    = 0;
  var maxTentativas = 36;

  function setStatus(label, dotClass) {
    var dot = document.getElementById('statusDot');
    var txt = document.getElementById('statusTexto');
    if (dot) dot.className = 'fgpo-status-dot ' + dotClass;
    if (txt) txt.textContent = label;
  }

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.is_paid) {
          mostrarAprovado(data.redirect_url);
          return;
        }
        if (data.status === 'failed') {
          mostrarRecusado();
          return;
        }
        tentativas++;
        if (tentativas < maxTentativas) {
          setTimeout(poll, 5000);
        } else {
          mostrarTimeout();
        }
      })
      .catch(function () {
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      });
  }

  window.addEventListener('message', function (event) {
    if (event.origin !== gpoOrigin) return;
    if (event.data) { tentativas = 0; poll(); }
  }, false);

  function mostrarAprovado(redirectUrl) {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'block';
    document.getElementById('painelRecusado').style.display = 'none';
    setStatus('Pago', 'fgpo-status-dot--paid');
    var url = redirectUrl || '{{ route("family.payment.gpo.confirm", $familyRequest->id) }}';
    document.getElementById('linkAprovado').href = url;
  }

  function mostrarRecusado() {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'none';
    document.getElementById('painelRecusado').style.display = 'block';
    setStatus('Não concluído', 'fgpo-status-dot--failed');
  }

  function mostrarTimeout() {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'none';
    document.getElementById('painelRecusado').style.display = 'block';
    setStatus('Tempo esgotado', 'fgpo-status-dot--failed');

    var icon  = document.getElementById('errorIcon');
    var title = document.getElementById('errorTitle');
    var sub   = document.getElementById('errorSub');
    if (icon)  { icon.className = 'fgpo-timeout-icon'; icon.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'; }
    if (title) { title.className = 'fgpo-timeout-title'; title.textContent = 'Tempo limite atingido'; }
    if (sub)   { sub.textContent = 'Se completou o pagamento aguarde a confirmação por e-mail. Caso contrário, tente outro método.'; }
  }

  setTimeout(poll, 6000);
})();
</script>
@endpush
