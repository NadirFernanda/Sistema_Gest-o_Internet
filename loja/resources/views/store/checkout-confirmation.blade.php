{{--
    AUTOVENDA — CONFIRMAÇÃO DE PLANO INDIVIDUAL
    ════════════════════════════════════════════
    Esta página é exclusiva para os planos individuais rápidos (Dia, Semana, Mês).
    NÃO existe integração com o Sistema de Gestão (SG) neste fluxo.

    O que chegou aqui:
      - $plan  → array com dados do plano (de config/store_plans.php)
      - $order → AutovendaOrder já em estado "paid" com o $order->wifi_code preenchido

    Não há $customer porque planos individuais não recolhem dados pessoais.
    O código WiFi é exibido directamente nesta página.
--}}
@extends('layouts.app')

@section('title', 'Compra Concluída – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">

  {{-- Cabeçalho de sucesso --}}
  <div class="checkout-success-header">
    <span class="checkout-success-icon" aria-hidden="true">✅</span>
    <h1 class="checkout-title">Compra Concluída!</h1>
    <p class="checkout-subtitle">O seu código WiFi está pronto. Pode utilizá-lo imediatamente em qualquer ponto da rede AngolaWiFi.</p>
  </div>

  <div class="checkout-layout">

    {{-- Código WiFi — destaque principal --}}
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
          <p class="checkout-note">Guarde este código. A AngolaWiFi <strong>não armazena</strong> dados pessoais para planos individuais — se perder o código, não poderemos recuperá-lo.</p>
        @else
          <p class="checkout-note">O código está a ser processado. Se não aparecer em breve, contacte o suporte AngolaWiFi.</p>
        @endif
      @endisset

      {{-- Resumo do plano --}}
      @if ($plan)
        <hr class="checkout-divider">
        <p><span class="label">Plano:</span> {{ $plan['name'] }}</p>
        <p><span class="label">Duração:</span> {{ $plan['duration_label'] }}</p>
        <p><span class="label">Velocidade:</span> {{ $plan['max_speed'] ?? $plan['speed'] }}</p>
        <p><span class="label">Download:</span> {{ $plan['download'] ?? 'Downloads Ilimitados' }}</p>
        <p><span class="label">Quantidade:</span> 1 código</p>
        <p class="total">Total pago: {{ number_format($plan['price_kwanza'], 0, ',', '.') }} AOA</p>
      @endif
    </section>

    {{-- Instruções + detalhes da ordem --}}
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
          Método de pagamento:
          @if ($order->payment_method === \App\Models\AutovendaOrder::METHOD_MULTICAIXA)
            Multicaixa Express
          @elseif ($order->payment_method === \App\Models\AutovendaOrder::METHOD_PAYPAL)
            PayPal
          @else
            {{ $order->payment_method }}
          @endif
        </p>
      @endisset

      <p class="checkout-note" style="margin-top:0.5rem;">
        Nenhum dado pessoal foi recolhido para esta compra, em conformidade
        com a política de planos individuais AngolaWiFi.
      </p>

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

