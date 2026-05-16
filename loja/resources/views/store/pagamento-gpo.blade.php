@extends('layouts.app')

@section('title', 'Pagamento Seguro – AngolaWiFi')

@push('styles')
<style>
/* ══════════════════════════════════════════════
   PAGAMENTO GPO — PLANOS INDIVIDUAIS
   ══════════════════════════════════════════════ */
.gpo-page {
  min-height: 80vh;
  background: #f0f2f5;
  padding: 2.5rem 0 5rem;
  font-family: Inter, system-ui, -apple-system, sans-serif;
}
.gpo-wrap {
  max-width: 940px;
  margin: 0 auto;
}
.gpo-back {
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
.gpo-back:hover { color: #1a202c; }

/* ── 2-col layout ──────────────────────────── */
.gpo-layout {
  display: grid;
  grid-template-columns: 1fr 1.7fr;
  gap: 1.5rem;
  align-items: start;
}

/* ── Cards ─────────────────────────────────── */
.gpo-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}

/* ── Plan badge ────────────────────────────── */
.gpo-plan-badge {
  background: linear-gradient(135deg, #fffbea 0%, #fff8d6 100%);
  border-bottom: 1px solid rgba(247,181,0,.25);
  padding: 1.4rem 1.5rem 1.2rem;
  position: relative;
}
.gpo-plan-badge::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f7b500, #ffd95a);
}
.gpo-plan-label {
  font-size: .68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: #b8860b;
  margin: 0 0 .3rem;
}
.gpo-plan-name {
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .7rem;
  letter-spacing: -.02em;
  line-height: 1.2;
}
.gpo-plan-price {
  display: flex;
  align-items: baseline;
  gap: .3rem;
}
.gpo-price-val {
  font-size: 1.85rem;
  font-weight: 900;
  color: #1a202c;
  letter-spacing: -.03em;
  line-height: 1;
}
.gpo-price-cur {
  font-size: .85rem;
  font-weight: 700;
  color: #64748b;
}

/* ── Order details ─────────────────────────── */
.gpo-details {
  padding: 1.2rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: .5rem;
}
.gpo-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .84rem;
}
.gpo-dr-label { color: #6b7280; }
.gpo-dr-val   { font-weight: 600; color: #1a202c; font-family: 'Courier New', monospace; font-size: .82rem; }
.gpo-dr-val-normal { font-weight: 600; color: #1a202c; font-family: inherit; }
.gpo-detail-divider {
  height: 1px;
  background: #e5e7eb;
  margin: .15rem 0;
}
.gpo-detail-total {
  display: flex;
  justify-content: space-between;
  font-size: .95rem;
  font-weight: 800;
  color: #1a202c;
}

/* ── Status badge ──────────────────────────── */
.gpo-status-row {
  padding: .85rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  gap: .6rem;
  font-size: .82rem;
}
.gpo-status-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
}
.gpo-status-dot--waiting {
  background: #f7b500;
  box-shadow: 0 0 0 0 rgba(247,181,0,.5);
  animation: gpo-pulse 1.6s ease-in-out infinite;
}
.gpo-status-dot--paid    { background: #16a34a; }
.gpo-status-dot--failed  { background: #dc2626; }
@keyframes gpo-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(247,181,0,.5); }
  70%  { box-shadow: 0 0 0 7px rgba(247,181,0,0); }
  100% { box-shadow: 0 0 0 0 rgba(247,181,0,0); }
}
.gpo-status-label { font-weight: 600; color: #374151; }

/* ── Payment card ──────────────────────────── */
.gpo-payment-header {
  padding: 1.1rem 1.5rem .9rem;
  border-bottom: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.gpo-payment-title {
  font-size: .95rem;
  font-weight: 800;
  color: #1a202c;
  letter-spacing: -.01em;
}
.gpo-emis-badge {
  display: flex;
  align-items: center;
}
.gpo-iframe-wrap {
  padding: 0;
}
.gpo-iframe-wrap iframe {
  display: block;
  width: 100%;
  min-height: 540px;
  border: none;
}
.gpo-iframe-note {
  padding: .7rem 1.25rem;
  border-top: 1px solid #f1f5f9;
  font-size: .74rem;
  color: #94a3b8;
  display: flex;
  align-items: center;
  gap: .4rem;
}
.gpo-iframe-note svg { flex-shrink: 0; color: #16a34a; }

/* ── Success panel ─────────────────────────── */
.gpo-success {
  padding: 2rem 1.75rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.25rem;
}
.gpo-success-icon {
  width: 4rem;
  height: 4rem;
  background: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.gpo-success-icon svg { color: #fff; }
.gpo-success-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #14532d;
  margin: 0;
}
.gpo-success-sub {
  font-size: .84rem;
  color: #16a34a;
  margin: .2rem 0 0;
}

/* ── WiFi code box ─────────────────────────── */
.gpo-code-wrap {
  width: 100%;
  background: #1a202c;
  border-radius: 16px;
  padding: 1.5rem 1.75rem;
  box-sizing: border-box;
}
.gpo-code-label {
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: #64748b;
  margin-bottom: .65rem;
}
.gpo-code-value {
  display: block;
  font-family: 'Courier New', 'Lucida Console', monospace;
  font-size: 1.65rem;
  font-weight: 700;
  color: #f7b500;
  letter-spacing: .12em;
  word-break: break-all;
  line-height: 1.3;
  margin-bottom: 1rem;
}
.gpo-copy-btn {
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  padding: .55rem 1.1rem;
  background: rgba(247,181,0,.12);
  border: 1px solid rgba(247,181,0,.3);
  border-radius: 8px;
  color: #f7b500;
  font-size: .82rem;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s;
}
.gpo-copy-btn:hover { background: rgba(247,181,0,.22); }
.gpo-copy-btn svg { flex-shrink: 0; }
.gpo-code-note {
  font-size: .74rem;
  color: #64748b;
  margin-top: .85rem;
  text-align: left;
  line-height: 1.55;
}

.gpo-success-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .85rem 1.75rem;
  background: #1a202c;
  color: #f7b500;
  border-radius: 12px;
  font-weight: 800;
  font-size: .95rem;
  text-decoration: none;
  transition: background .15s, transform .12s;
  box-shadow: 0 2px 12px rgba(26,32,44,.2);
}
.gpo-success-link:hover { background: #111827; transform: translateY(-1px); }

/* ── Error panel ───────────────────────────── */
.gpo-error {
  padding: 2rem 1.75rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1rem;
}
.gpo-error-icon {
  width: 3.5rem;
  height: 3.5rem;
  background: #fef2f2;
  border: 2px solid #fca5a5;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.gpo-error-icon svg { color: #dc2626; }
.gpo-error-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #7f1d1d;
  margin: 0;
}
.gpo-error-sub {
  font-size: .84rem;
  color: #94a3b8;
  margin: 0;
}
.gpo-error-actions {
  display: flex;
  flex-direction: column;
  gap: .6rem;
  width: 100%;
  max-width: 280px;
}
.gpo-retry-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  padding: .85rem 1.5rem;
  background: #1a202c;
  color: #f7b500;
  border-radius: 12px;
  font-weight: 800;
  font-size: .9rem;
  text-decoration: none;
  transition: background .15s;
}
.gpo-retry-btn:hover { background: #111827; }
.gpo-back-btn {
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
.gpo-back-btn:hover { background: #f8fafc; }

/* ── Mobile ────────────────────────────────── */
@media (max-width: 680px) {
  .gpo-page { padding: 1.25rem 0 4rem; }
  .gpo-layout { grid-template-columns: 1fr; }
  .gpo-code-value { font-size: 1.3rem; }
}
</style>
@endpush

@section('content')
<div class="gpo-page">
<div class="gpo-wrap">

  <a href="{{ url('/') }}" class="gpo-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar à loja
  </a>

  <div class="gpo-layout">

    {{-- ── Resumo ───────────────────────────────────────────────────── --}}
    <div class="gpo-card">

      <div class="gpo-plan-badge">
        <p class="gpo-plan-label">Resumo do pedido</p>
        <h1 class="gpo-plan-name">{{ $order->plan_name }}</h1>
        <div class="gpo-plan-price">
          <span class="gpo-price-val">{{ number_format($order->amount_aoa, 0, ',', '.') }}</span>
          <span class="gpo-price-cur">AOA</span>
        </div>
      </div>

      <div class="gpo-details">
        @if(!empty($order->plan_speed))
          <div class="gpo-detail-row">
            <span class="gpo-dr-label">Velocidade</span>
            <span class="gpo-dr-val-normal">{{ $order->plan_speed }}</span>
          </div>
        @endif
        <div class="gpo-detail-row">
          <span class="gpo-dr-label">Referência</span>
          <span class="gpo-dr-val">{{ $order->payment_reference }}</span>
        </div>
        <div class="gpo-detail-divider"></div>
        <div class="gpo-detail-total">
          <span>Total</span>
          <span>{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</span>
        </div>
      </div>

      <div class="gpo-status-row">
        <span class="gpo-status-dot gpo-status-dot--waiting" id="statusDot"></span>
        <span class="gpo-status-label" id="statusTexto">A aguardar pagamento</span>
      </div>

      {{-- Código WiFi (aparece no painel esquerdo após aprovação) --}}
      <div id="wifiCodeSection" style="display:none; padding: 1.25rem 1.5rem; border-top: 1px solid #f1f5f9;">
        <div class="gpo-code-wrap">
          <p class="gpo-code-label">O seu código WiFi</p>
          <code class="gpo-code-value" id="wifiCodeValue"></code>
          <button type="button" class="gpo-copy-btn" onclick="copyCodigo()">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
            <span id="copyBtnText">Copiar código</span>
          </button>
          <p class="gpo-code-note">Guarde este código. A AngolaWiFi não armazena dados pessoais para planos individuais.</p>
        </div>
      </div>

    </div>

    {{-- ── Painel de pagamento ──────────────────────────────────────── --}}
    <div class="gpo-card">

      {{-- Iframe EMIS (aguarda pagamento) --}}
      <div id="painelIframe">
        <div class="gpo-payment-header">
          <span class="gpo-payment-title">Pagamento seguro</span>
          <span class="gpo-emis-badge">
            <img src="/img/emis.png" alt="EMIS" height="24" style="display:block;">
          </span>
        </div>
        <div class="gpo-iframe-wrap">
          <iframe
            id="gpoFrame"
            src="{{ $iframeUrl }}"
            title="Pagamento seguro EMIS GPO"
            allow="payment"
            sandbox="allow-scripts allow-forms allow-same-origin allow-popups"
          ></iframe>
        </div>
        <div class="gpo-iframe-note">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Os dados do cartão são processados directamente pela EMIS e nunca passam pelos nossos servidores.
        </div>
      </div>

      {{-- Pagamento confirmado --}}
      <div id="painelAprovado" style="display:none;">
        <div class="gpo-success">
          <div class="gpo-success-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <p class="gpo-success-title">Pagamento confirmado!</p>
            <p class="gpo-success-sub">O seu código WiFi foi gerado com sucesso.</p>
          </div>
          <a href="#" id="linkConfirmacao" class="gpo-success-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Ver detalhes do pedido
          </a>
        </div>
      </div>

      {{-- Pagamento recusado --}}
      <div id="painelRecusado" style="display:none;">
        <div class="gpo-error">
          <div class="gpo-error-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          </div>
          <p class="gpo-error-title">Pagamento não concluído</p>
          <p class="gpo-error-sub">O pagamento foi recusado ou cancelado. Pode tentar novamente.</p>
          <div class="gpo-error-actions">
            <a href="{{ route('gpo.show', $order) }}" class="gpo-retry-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
              Tentar novamente
            </a>
            <a href="{{ url('/') }}" class="gpo-back-btn">
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
  var statusUrl     = '{{ route("gpo.status", $order) }}';
  var gpoOrigin     = 'https://pagamentonline.emis.co.ao';
  var tentativas    = 0;
  var maxTentativas = 36;

  function setStatus(label, dotClass) {
    var dot = document.getElementById('statusDot');
    var txt = document.getElementById('statusTexto');
    if (dot) { dot.className = 'gpo-status-dot ' + dotClass; }
    if (txt) { txt.textContent = label; }
  }

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.status_pt || data.status) {
          setStatus(data.status_pt || data.status, 'gpo-status-dot--waiting');
        }
        if (data.is_paid) {
          mostrarAprovado(data.wifi_code, data.redirect_url);
          return;
        }
        if (['failed', 'cancelled', 'expired'].indexOf(data.status) !== -1) {
          mostrarRecusado();
          return;
        }
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
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

  function mostrarAprovado(code, redirectUrl) {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'block';
    document.getElementById('painelRecusado').style.display = 'none';
    setStatus('Pago', 'gpo-status-dot--paid');

    if (code) {
      document.getElementById('wifiCodeSection').style.display = 'block';
      document.getElementById('wifiCodeValue').textContent     = code;
    }
    if (redirectUrl) {
      document.getElementById('linkConfirmacao').href = redirectUrl;
    }
  }

  function mostrarRecusado() {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'none';
    document.getElementById('painelRecusado').style.display = 'block';
    setStatus('Não concluído', 'gpo-status-dot--failed');
  }

  window.copyCodigo = function () {
    var code    = document.getElementById('wifiCodeValue').textContent;
    var btnText = document.getElementById('copyBtnText');
    navigator.clipboard.writeText(code)
      .then(function () {
        btnText.textContent = 'Copiado!';
        setTimeout(function () { btnText.textContent = 'Copiar código'; }, 2000);
      })
      .catch(function () {});
  };

  @if($order->isPaid())
    mostrarAprovado('{{ $order->wifi_code }}', '{{ route("store.checkout.confirm", $order->id) }}');
  @else
    setTimeout(poll, 6000);
  @endif
})();
</script>
@endpush
