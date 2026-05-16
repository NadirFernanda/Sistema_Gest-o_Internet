@extends('layouts.app')

@section('title', 'Compra Concluída – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">

  <div class="checkout-success-header">
    <span class="checkout-success-icon" aria-hidden="true">✅</span>
    <h1 class="checkout-title">Compra Concluída!</h1>
    <p class="checkout-subtitle">O seu código WiFi está pronto. Pode utilizá-lo imediatamente em qualquer ponto da rede AngolaWiFi.</p>
  </div>

  <div class="checkout-layout">

    {{-- Código WiFi --}}
    <section class="checkout-summary-card">
      <h2>O Seu Código WiFi</h2>

      @isset($order)
        @if (!empty($order->wifi_code))
          <div class="wifi-code-box">
            <span class="wifi-code-label">Código de acesso:</span>
            <code class="wifi-code-value" id="wifiCodeText">{{ $order->wifi_code }}</code>
            <button type="button" class="btn-copy" onclick="copyWifiCode()" title="Copiar código">
              📋 Copiar
            </button>
          </div>

          {{-- Entrega por email --}}
          @if (!empty($order->customer_email))
            <p class="checkout-note" style="margin-top:0.75rem;">
              📧 Código enviado para <strong>{{ $order->customer_email }}</strong>
            </p>
          @endif

          {{-- Botão WhatsApp --}}
          @if (!empty($order->customer_phone))
            @php
              $waPhone = $order->customer_phone;
              $waMsg = urlencode("Olá! O seu código WiFi AngolaWiFi é: *{$order->wifi_code}*\n\nPlano: {$order->plan_name}\nReferência: #{$order->id}\n\nPara usar: ligue-se à rede AngolaWiFi e introduza este código no portal de acesso.\n\nObrigado pela sua compra! 🌐");
            @endphp
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waMsg }}"
               target="_blank" rel="noopener"
               class="btn-primary"
               style="display:block; text-align:center; margin-top:0.75rem; background:#25D366; border-color:#25D366;">
              📲 Enviar por WhatsApp
            </a>
          @endif

        @else
          <p class="checkout-note">O código está a ser processado. Se não aparecer em breve, contacte o suporte AngolaWiFi.</p>
        @endif
      @endisset

      @if ($plan)
        <hr class="checkout-divider">
        <p><span class="label">Plano:</span> {{ $plan->name }}</p>
        <p><span class="label">Duração:</span> {{ $plan->validity_label }}</p>
        <p><span class="label">Velocidade:</span> {{ $plan->speed_label }}</p>
        <p class="total">Total pago: {{ number_format($plan->price_public_aoa, 0, ',', '.') }} AOA</p>
      @endif
    </section>

    {{-- Instruções --}}
    <section class="checkout-form-card">
      <h2>Como Utilizar</h2>

      <ol class="checkout-instructions">
        <li>Ligue-se à rede WiFi <strong>AngolaWiFi</strong> no seu dispositivo.</li>
        <li>Abra o browser — será redirecionado para o portal de acesso.</li>
        <li>Introduza o código acima no campo de voucher e clique em <strong>Ligar</strong>.</li>
        <li>Pronto — está a navegar!</li>
      </ol>

      @isset($order)
        <p class="checkout-note" style="margin-top:1rem;">
          Ref. pedido: <strong>#{{ $order->id }}</strong><br>
          Método de pagamento: <strong>EMIS · Gateway de Pagamentos Online</strong>
        </p>
      @endisset

      <div class="checkout-actions" style="margin-top:1.5rem;">
        <a href="{{ url('/') }}" class="btn-primary">Comprar outro plano</a>
      </div>
    </section>

  </div>
</div>

@push('scripts')
<script>
function copyWifiCode() {
  var code = document.getElementById('wifiCodeText');
  if (!code) return;
  navigator.clipboard.writeText(code.textContent.trim()).then(function() {
    var btn = document.querySelector('.btn-copy');
    if (btn) { btn.textContent = '✅ Copiado!'; setTimeout(function(){ btn.textContent = '📋 Copiar'; }, 2000); }
  });
}
</script>
@endpush
@endsection
