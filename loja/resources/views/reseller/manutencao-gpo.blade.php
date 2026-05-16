@extends('layouts.app')

@section('title', 'Taxa de Manutenção – Pagamento Seguro')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Taxa de Manutenção</h1>
  <p class="checkout-subtitle">Complete o pagamento em segurança no ecrã abaixo (fornecido pela <strong>EMIS</strong>).</p>

  <div class="checkout-layout">

    {{-- Resumo --}}
    <section class="checkout-summary-card">
      <h2>Detalhe do Pagamento</h2>

      <div style="background:linear-gradient(135deg,#f7b500 0%,#e6a800 100%); border-radius:0.75rem; padding:1.25rem; color:#1a1100; margin-bottom:1.25rem;">
        <div style="font-size:0.85rem; font-weight:600; margin-bottom:0.25rem;">Taxa de Manutenção Mensal</div>
        <div style="font-size:2rem; font-weight:900; letter-spacing:-.03em;">
          {{ number_format($amount, 0, ',', '.') }}<span style="font-size:1rem; font-weight:600;"> Kz</span>
        </div>
        <div style="font-size:0.8rem; margin-top:0.4rem; opacity:.85;">{{ $periodLabel }}</div>
      </div>

      <div style="display:flex; flex-direction:column; gap:0.4rem; font-size:0.85rem; color:#374151;">
        <div style="display:flex; justify-content:space-between;">
          <span style="color:#6b7280;">Agente</span>
          <span style="font-weight:600;">{{ $application->full_name }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
          <span style="color:#6b7280;">Modo</span>
          <span style="font-weight:600;">{{ $application->reseller_mode === 'own' ? 'Modo 1 – Internet Própria' : 'Modo 2 – AngolaWiFi' }}</span>
        </div>
        <div style="display:flex; justify-content:space-between;">
          <span style="color:#6b7280;">Referência</span>
          <span style="font-weight:600; font-size:0.8rem;">{{ $reference }}</span>
        </div>
      </div>

      <p style="margin-top:1rem; font-size:0.82rem;">
        <span class="label">Estado: </span>
        <strong id="statusTexto">A aguardar pagamento</strong>
      </p>

      {{-- Sucesso --}}
      <div id="successSection" style="display:none; margin-top:1rem;">
        <hr class="checkout-divider">
        <div style="background:#d1fae5; border:1.5px solid #6ee7b7; border-radius:0.6rem; padding:0.8rem 1rem; font-size:0.85rem; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> Taxa de manutenção registada com sucesso.
        </div>
        <a href="{{ route('reseller.maintenance.invoice') }}" class="btn-primary"
           style="display:block; text-align:center; margin-top:0.75rem;">
          📄 Descarregar Factura
        </a>
        <a href="#" id="linkConfirmacao" class="btn-secondary"
           style="display:block; text-align:center; margin-top:0.5rem;">
          Ir para o painel →
        </a>
      </div>
    </section>

    {{-- Painel principal --}}
    <section class="checkout-form-card" style="padding:0; overflow:hidden;">

      <div id="painelIframe">
        <iframe
          id="gpoFrame"
          src="{{ $iframeUrl }}"
          style="width:100%; min-height:520px; border:none; display:block;"
          title="Pagamento seguro EMIS GPO"
          allow="payment"
          sandbox="allow-scripts allow-forms allow-same-origin allow-popups"
        ></iframe>
        <p class="checkout-note" style="padding:0.75rem 1rem; margin:0; border-top:1px solid #e5e7eb;">
          Pagamento processado pela <strong>EMIS – Gateway de Pagamentos Online</strong>.
        </p>
      </div>

      <div id="painelAprovado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors" style="background:#d1fae5; border-color:#6ee7b7; color:#065f46;">
          ✅ <strong>Pagamento da taxa confirmado!</strong>
        </div>
        <a href="#" id="linkAprovado" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Ir para o painel →
        </a>
      </div>

      <div id="painelRecusado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors">
          ❌ <strong>Pagamento não concluído.</strong>
        </div>
        <a href="{{ route('reseller.maintenance.payment') }}" class="btn-primary"
           style="margin-top:1rem; display:block; text-align:center;">
          Tentar novamente
        </a>
      </div>

    </section>
  </div>

  <div style="text-align:center; margin-top:1rem;">
    <small class="checkout-note">Pagamento processado por <strong>EMIS · Gateway de Pagamentos Online</strong></small>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  const statusUrl     = '{{ route("reseller.maintenance.gpo.status") }}';
  const confirmUrl    = '{{ route("reseller.maintenance.gpo.confirm") }}';
  const gpoOrigin     = 'https://pagamentonline.emis.co.ao';
  let tentativas      = 0;
  const maxTentativas = 36;

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('statusTexto');
        if (el) el.textContent = data.status_pt || data.status;

        if (data.is_paid) {
          mostrarAprovado(confirmUrl);
          return;
        }
        if (data.status === 'failed') { mostrarRecusado(); return; }
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      })
      .catch(() => {
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      });
  }

  window.addEventListener('message', function(event) {
    if (event.origin !== gpoOrigin) return;
    if (event.data) { tentativas = 0; poll(); }
  }, false);

  function mostrarAprovado(url) {
    document.getElementById('painelIframe').style.display    = 'none';
    document.getElementById('painelAprovado').style.display  = 'block';
    document.getElementById('painelRecusado').style.display  = 'none';
    document.getElementById('statusTexto').textContent       = 'Pago ✅';
    document.getElementById('successSection').style.display  = 'block';
    document.getElementById('linkAprovado').href             = url;
    document.getElementById('linkConfirmacao').href          = url;
  }

  function mostrarRecusado() {
    document.getElementById('painelIframe').style.display    = 'none';
    document.getElementById('painelAprovado').style.display  = 'none';
    document.getElementById('painelRecusado').style.display  = 'block';
    document.getElementById('statusTexto').textContent       = 'Não concluído';
  }

  setTimeout(poll, 6000);
})();
</script>
@endpush
