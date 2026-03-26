@extends('layouts.app')

@section('content')
<div class="page-hero">
  <div class="container">
    <span class="page-hero__eyebrow">Equipamentos</span>
    <h1 class="page-hero__title">Finalizar Encomenda</h1>
  </div>
</div>

<div class="page-body">
  <div class="container container--720">

    @if ($errors->any())
      <div class="alert alert-error" role="alert">
        <ul style="margin:0;padding-left:1.2rem;">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Resumo do carrinho --}}
    <div class="card" style="margin-bottom:2rem;">
      <div class="card-body">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;font-weight:700;">Resumo do pedido</h3>
        @php $subtotalCheck = 0; @endphp
        @foreach ($cart as $item)
          @php $line = $item['unit_price_aoa'] * $item['quantity']; $subtotalCheck += $line; @endphp
          <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px solid var(--muted-border);font-size:0.97rem;">
            <span>{{ $item['product_name'] }} × {{ $item['quantity'] }}</span>
            <strong>{{ number_format($line, 0, ',', '.') }} Kz</strong>
          </div>
        @endforeach
        <div class="total" style="display:flex;justify-content:space-between;padding:0.75rem 0 0;">
          <span>Total</span>
          <span>{{ number_format($total, 0, ',', '.') }} Kz</span>
        </div>
      </div>
    </div>

    {{-- Formulário de dados do cliente --}}
    <form method="POST" action="{{ route('equipment.checkout.process') }}" class="card">
      @csrf
      <div class="card-body">
        <h3 style="margin:0 0 1.25rem;font-size:1.05rem;font-weight:700;">Dados para entrega</h3>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
          <div class="field" style="grid-column:1/-1;">
            <label for="customer_name">Nome Completo *</label>
            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" required>
          </div>

          <div class="field">
            <label for="customer_phone">Telefone / WhatsApp *</label>
            <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}" required>
          </div>

          <div class="field">
            <label for="customer_email">E-mail (opcional)</label>
            <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}">
          </div>

          <div class="field" style="grid-column:1/-1;">
            <label for="customer_address">Morada de entrega (opcional)</label>
            <input id="customer_address" type="text" name="customer_address" value="{{ old('customer_address') }}" placeholder="Bairro, Rua, Nº, Município, Província">
          </div>
        </div>

        <h3 style="margin:1.5rem 0 0.75rem;font-size:1.05rem;font-weight:700;">Método de Pagamento *</h3>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
          <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
            <input type="radio" name="payment_method" value="paypal"
                   {{ old('payment_method', 'paypal') === 'paypal' ? 'checked' : '' }} required>
            PayPal
          </label>
          <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
            <input type="radio" name="payment_method" value="cash"
                   {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
            Pagamento na entrega
          </label>
        </div>

        <div class="field">
          <label for="notes">Observações (opcional)</label>
          <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex;gap:0.75rem;justify-content:space-between;margin-top:1.5rem;flex-wrap:wrap;">
          <a href="{{ route('equipment.cart') }}" class="btn-ghost">← Voltar ao carrinho</a>
          <button type="submit" class="btn-primary" style="font-size:1.05rem;padding:0.75rem 2rem;">
            Confirmar Encomenda — {{ number_format($total, 0, ',', '.') }} Kz
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
