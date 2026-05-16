@extends('layouts.app')

@section('title', 'Pagamento Seguro – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Pagamento Seguro</h1>
  <p class="checkout-subtitle">Complete o pagamento em segurança no ecrã abaixo (fornecido pela <strong>EMIS</strong>).</p>

  <div class="checkout-layout">

    {{-- Resumo do pedido --}}
    <section class="checkout-summary-card">
      <h2>Resumo do Pedido</h2>
      <p><span class="label">Plano:</span> {{ $order->plan_name }}</p>
      <p><span class="label">Velocidade:</span> {{ $order->plan_speed }}</p>
      <p><span class="label">Referência:</span> {{ $order->payment_reference }}</p>
      <p class="total">Total: {{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</p>

      <p style="margin-top:1rem;">
        <span class="label">Estado: </span>
        <strong id="statusTexto">A aguardar pagamento</strong>
      </p>

      {{-- Código WiFi (aparece após aprovação) --}}
      <div id="wifiCodeSection" style="display:none; margin-top:1rem;">
        <hr class="checkout-divider">
        <p class="label">O Seu Código WiFi:</p>
        <div class="wifi-code-box">
          <code class="wifi-code-value" id="wifiCodeValue"></code>
          <button type="button" class="btn-copy" onclick="copyCodigo()" title="Copiar código">📋 Copiar</button>
        </div>
        <p class="checkout-note">Guarde este código. A AngolaWiFi <strong>não armazena</strong> dados pessoais para planos individuais.</p>
      </div>
    </section>

    {{-- Painel principal --}}
    <section class="checkout-form-card" style="padding:0; overflow:hidden;">

      {{-- Iframe GPO (visível enquanto aguarda pagamento) --}}
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
          Pagamento processado pela <strong>EMIS – Gateway de Pagamentos Online</strong>. Os dados do cartão nunca passam pelos nossos servidores.
        </p>
      </div>

      {{-- Mensagem de sucesso (substituirá o iframe) --}}
      <div id="painelAprovado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors" style="background:#d1fae5; border-color:#6ee7b7; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> O seu código WiFi foi gerado com sucesso.
        </div>
        <a href="#" id="linkConfirmacao" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Ver o meu código WiFi
        </a>
      </div>

      {{-- Mensagem de erro --}}
      <div id="painelRecusado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors">
          ❌ <strong>Pagamento não concluído.</strong>
        </div>
        <a href="{{ route('gpo.show', $order) }}" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Tentar novamente
        </a>
        <a href="{{ route('store.checkout') }}" class="btn-secondary" style="margin-top:0.5rem; display:block; text-align:center;">
          Voltar à loja
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
  const statusUrl    = '{{ route("gpo.status", $order) }}';
  const gpoOrigin    = 'https://pagamentonline.emis.co.ao';
  let tentativas     = 0;
  const maxTentativas = 36; // ~3 minutos

  /* ── Polling do servidor ──────────────────────────────────────────── */
  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('statusTexto');
        if (el) el.textContent = data.status_pt || data.status;

        if (data.is_paid) {
          mostrarAprovado(data.wifi_code, data.redirect_url);
          return;
        }
        if (['failed', 'cancelled', 'expired'].includes(data.status)) {
          mostrarRecusado();
          return;
        }
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      })
      .catch(() => {
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      });
  }

  /* ── Notificação via postMessage do iframe GPO ───────────────────── */
  window.addEventListener('message', function(event) {
    if (event.origin !== gpoOrigin) return;
    const txId = event.data;
    if (txId) {
      // GPO confirmou: força polling imediato
      tentativas = 0;
      poll();
    }
  }, false);

  /* ── Funções de UI ───────────────────────────────────────────────── */
  function mostrarAprovado(code, redirectUrl) {
    document.getElementById('painelIframe').style.display   = 'none';
    document.getElementById('painelAprovado').style.display = 'block';
    document.getElementById('painelRecusado').style.display = 'none';
    document.getElementById('statusTexto').textContent      = 'Pago ✅';

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
    document.getElementById('statusTexto').textContent      = 'Não concluído';
  }

  window.copyCodigo = function() {
    const code = document.getElementById('wifiCodeValue').textContent;
    navigator.clipboard.writeText(code).catch(() => {});
  };

  @if($order->isPaid())
    mostrarAprovado('{{ $order->wifi_code }}', '{{ route("store.checkout.confirm", $order->id) }}');
  @else
    setTimeout(poll, 6000);
  @endif
})();
</script>
@endpush
