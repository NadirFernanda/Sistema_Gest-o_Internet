@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Finalizar encomenda de equipamentos">
  <div class="container" style="max-width:760px;">
    <h2>Finalizar Encomenda</h2>

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
    <div class="plan-card-modern" style="max-width:100%;margin-bottom:2rem;">
      <h3 style="margin-bottom:0.75rem;font-size:1.1rem;">Resumo do pedido</h3>
      @php $subtotalCheck = 0; @endphp
      @foreach ($cart as $item)
        @php $line = $item['unit_price_aoa'] * $item['quantity']; $subtotalCheck += $line; @endphp
        <div style="display:flex;justify-content:space-between;padding:0.4rem 0;border-bottom:1px solid #f1f5f9;font-size:0.97rem;">
          <span>{{ $item['product_name'] }} × {{ $item['quantity'] }}</span>
          <strong>{{ number_format($line, 0, ',', '.') }} Kz</strong>
        </div>
      @endforeach
      <div style="display:flex;justify-content:space-between;padding:0.6rem 0;font-size:1.1rem;font-weight:800;color:#2563eb;">
        <span>Total</span>
        <span>{{ number_format($total, 0, ',', '.') }} Kz</span>
      </div>
    </div>

    {{-- Formulário de dados do cliente --}}
    <form method="POST" action="{{ route('equipment.checkout.process') }}" class="plan-card-modern" style="max-width:100%;">
      @csrf

      <h3 style="margin-bottom:1rem;font-size:1.1rem;">Dados para entrega</h3>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div style="grid-column:1/-1;">
          <label for="customer_name" style="display:block;font-weight:600;margin-bottom:0.3rem;">Nome Completo *</label>
          <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}"
                 required style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div>
          <label for="customer_phone" style="display:block;font-weight:600;margin-bottom:0.3rem;">Telefone / WhatsApp *</label>
          <input id="customer_phone" type="tel" name="customer_phone" value="{{ old('customer_phone') }}"
                 required style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div>
          <label for="customer_email" style="display:block;font-weight:600;margin-bottom:0.3rem;">E-mail (opcional)</label>
          <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email') }}"
                 style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>

        <div style="grid-column:1/-1;">
          <label for="customer_address" style="display:block;font-weight:600;margin-bottom:0.3rem;">Morada de entrega (opcional)</label>
          <input id="customer_address" type="text" name="customer_address" value="{{ old('customer_address') }}"
                 placeholder="Bairro, Rua, Nº, Município, Província"
                 style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
        </div>
      </div>

      <h3 style="margin:1.5rem 0 0.75rem;font-size:1.1rem;">Método de Pagamento *</h3>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
          <input type="radio" name="payment_method" value="multicaixa_express"
                 {{ old('payment_method') === 'multicaixa_express' ? 'checked' : '' }} required>
          Multicaixa Express
        </label>
        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
          <input type="radio" name="payment_method" value="paypal"
                 {{ old('payment_method') === 'paypal' ? 'checked' : '' }}>
          PayPal
        </label>
        <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;">
          <input type="radio" name="payment_method" value="cash"
                 {{ old('payment_method') === 'cash' ? 'checked' : '' }}>
          Pagamento na entrega
        </label>
      </div>

      <div>
        <label for="notes" style="display:block;font-weight:600;margin-bottom:0.3rem;">Observações (opcional)</label>
        <textarea id="notes" name="notes" rows="3"
                  style="width:100%;padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;resize:vertical;">{{ old('notes') }}</textarea>
      </div>

      <div style="display:flex;gap:0.75rem;justify-content:space-between;margin-top:1.5rem;flex-wrap:wrap;">
        <a href="{{ route('equipment.cart') }}" class="btn-modern" style="background:linear-gradient(90deg,#94a3b8,#64748b);">← Voltar ao carrinho</a>
        <button type="submit" class="btn-modern" style="font-size:1.15rem;padding:0.85rem 2.2rem;">
          Confirmar Encomenda — {{ number_format($total, 0, ',', '.') }} Kz
        </button>
      </div>
    </form>
  </div>
</section>
@endsection
