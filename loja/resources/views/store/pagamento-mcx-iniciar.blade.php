@extends('layouts.app')

@section('title', 'Pagamento via Multicaixa Express – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Pagamento via Multicaixa Express</h1>
  <p class="checkout-subtitle">Introduza o número de telemóvel associado à sua app <strong>Multicaixa Express</strong> para receber a notificação de pagamento.</p>

  <div class="checkout-layout">

    {{-- Resumo do pedido --}}
    <section class="checkout-summary-card">
      <h2>Resumo do Pedido</h2>
      <p><span class="label">Plano:</span> {{ $order->plan_name }}</p>
      <p><span class="label">Velocidade:</span> {{ $order->plan_speed }}</p>
      <p><span class="label">Método:</span> Multicaixa Express</p>
      <p class="total">Total: {{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</p>
    </section>

    {{-- Formulário do número MCX --}}
    <section class="checkout-form-card">
      <h2>Número Multicaixa Express</h2>

      @if ($errors->any())
        <div class="checkout-errors">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('pay4all.processar', $order) }}" class="checkout-form" id="mcxForm">
        @csrf
        <div class="checkout-field">
          <label for="telefone">Número de telemóvel *</label>
          <input
            type="tel"
            id="telefone"
            name="telefone"
            class="checkout-input"
            placeholder="9XXXXXXXX ou 2449XXXXXXXX"
            pattern="^(244)?9[0-9]{8}$"
            inputmode="numeric"
            maxlength="12"
            value="{{ old('telefone') }}"
            required
            autofocus
          >
          <small class="checkout-note">
            Números de teste: <code>900000000</code> (sucesso), <code>900000001</code> (saldo insuficiente), <code>900000002</code> (timeout), <code>900000003</code> (recusado).
          </small>
        </div>

        <p class="checkout-note">
          Após confirmar, receberá uma notificação na app Multicaixa Express para autorizar o pagamento de <strong>{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</strong>.
        </p>

        <div class="checkout-actions">
          <button type="submit" class="btn-primary" id="btnSubmit">
            <span id="btnTexto">Enviar pedido de pagamento</span>
            <span id="btnSpinner" style="display:none;">A processar…</span>
          </button>
          <a href="{{ route('store.checkout') }}" class="btn-secondary" style="margin-top:0.5rem;">Cancelar</a>
        </div>
      </form>
    </section>

  </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('mcxForm').addEventListener('submit', function() {
  document.getElementById('btnTexto').style.display  = 'none';
  document.getElementById('btnSpinner').style.display = 'inline';
  document.getElementById('btnSubmit').disabled = true;
});
</script>
@endpush
