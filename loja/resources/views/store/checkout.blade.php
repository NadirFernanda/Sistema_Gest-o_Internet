@extends('layouts.app')

@section('content')
  <div class="container--720 checkout-page">
    <h1 class="checkout-title">Checkout Rápido</h1>
    <p class="checkout-subtitle">Revise o seu plano e preencha os seus dados para receber o código AngolaWiFi.</p>

    <div class="checkout-layout">
      {{-- Resumo do pedido --}}
      <section class="checkout-summary-card">
        <h2>Resumo do Pedido</h2>
        @if($plan)
          <p><span class="label">Plano:</span> {{ $plan['name'] }}</p>
          <p><span class="label">Duração:</span> {{ $plan['duration_label'] }}</p>
          <p><span class="label">Velocidade:</span> {{ $plan['max_speed'] ?? $plan['speed'] }}</p>
          <p><span class="label">Download:</span> {{ $plan['download'] ?? 'Downloads Ilimitados' }}</p>
          <p><span class="label">Quantidade:</span> 1 código</p>
          <p class="total">Total: {{ number_format($plan['price_kwanza'], 0, ',', '.') }} AOA</p>
        @else
          <p>Não foi possível identificar o plano selecionado. Volte à página inicial e escolha um plano.</p>
        @endif
      </section>

      {{-- Dados do cliente + submissão --}}
      <section class="checkout-form-card">
        <h2>Pagamento</h2>

        @if ($errors->any())
          <div class="checkout-errors">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @if($plan)
        <form method="POST" action="{{ route('store.checkout.process') }}" class="checkout-form">
          @csrf
          <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">
          <p class="checkout-note">
            Para estes planos individuais não é necessário preencher dados pessoais.
            Basta escolher o método de pagamento e confirmar a compra.
          </p>

          <div class="checkout-payment">
            <p class="checkout-payment-title">Método de Pagamento *</p>
            <div class="checkout-payment-options">
              <label>
                <input type="radio" name="payment_method" value="multicaixa_express" checked>
                <span>Multicaixa Express</span>
              </label>
              <label>
                <input type="radio" name="payment_method" value="paypal">
                <span>PayPal</span>
              </label>
            </div>
          </div>

          <p class="checkout-note">* Campos obrigatórios. Não é necessário criar conta para concluir a compra.</p>

          <div class="checkout-actions">
            <button type="submit" class="btn-primary">
              Confirmar dados e prosseguir para pagamento
            </button>
          </div>
        </form>
        @else
          <p>Não é possível concluir o checkout sem um plano selecionado. Volte à página inicial e escolha um plano.</p>
        @endif
      </section>
    </div>
  </div>
@endsection
