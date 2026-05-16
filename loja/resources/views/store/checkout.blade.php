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
          <p><span class="label">Plano:</span> {{ $plan->name }}</p>
          <p><span class="label">Duração:</span> {{ $plan->validity_label }}</p>
          <p><span class="label">Velocidade:</span> {{ $plan->speed_label }}</p>
          <p><span class="label">Download:</span> Downloads Ilimitados</p>
          <p><span class="label">Quantidade:</span> 1 código</p>
          <p class="total">Total: {{ number_format($plan->price_public_aoa, 0, ',', '.') }} AOA</p>
        @else
          <p>Não foi possível identificar o plano selecionado. Volte à página inicial e escolha um plano.</p>
        @endif
      </section>

      {{-- Dados do cliente + submissão --}}
      <section class="checkout-form-card">
        <h2>Os seus dados</h2>

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
          <input type="hidden" name="plan_id" value="{{ $plan->slug }}">

          <div class="checkout-field">
            <label for="customer_name">Nome completo *</label>
            <input type="text" id="customer_name" name="customer_name"
              class="checkout-input" placeholder="Ex: João Silva"
              value="{{ old('customer_name') }}" required autofocus maxlength="100">
          </div>

          <div class="checkout-field">
            <label for="customer_email">E-mail *</label>
            <input type="email" id="customer_email" name="customer_email"
              class="checkout-input" placeholder="Ex: joao@exemplo.com"
              value="{{ old('customer_email') }}" required maxlength="150">
            <small class="checkout-note">O código WiFi será enviado para este e-mail.</small>
          </div>

          <div class="checkout-field">
            <label for="customer_phone">Telemóvel *</label>
            <input type="tel" id="customer_phone" name="customer_phone"
              class="checkout-input" placeholder="9XXXXXXXX ou 2449XXXXXXXX"
              value="{{ old('customer_phone') }}" required
              pattern="^(244)?9[0-9]{8}$" inputmode="numeric" maxlength="12">
            <small class="checkout-note">O código WiFi será também enviado por WhatsApp.</small>
          </div>

          <div class="checkout-payment">
            <p class="checkout-payment-title">Método de Pagamento *</p>
            <div class="checkout-payment-options">
              <label>
                <input type="radio" name="payment_method" value="gpo" checked>
                <span>Pagamento Online (Cartão / Multicaixa Express)</span>
              </label>
            </div>
          </div>

          <p class="checkout-note">* Campos obrigatórios.</p>

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
