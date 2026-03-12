@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Detalhe do produto">
  <div class="container" style="max-width:760px;">
    <a href="{{ route('equipment.index') }}" class="store-link" style="font-size:0.95rem;">&larr; Voltar à loja</a>

    <div class="plan-card-modern" style="max-width:100%;margin-top:1.5rem;">
      <div class="plan-card-modern-inner">

        @if ($product->image_path)
          <img src="{{ asset($product->image_path) }}"
               alt="{{ $product->name }}"
               style="width:100%;max-height:320px;object-fit:cover;border-radius:0.75rem;margin-bottom:1rem;">
        @else
          <div style="width:100%;height:180px;background:#f1f5f9;border-radius:0.75rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:center;font-size:4rem;">📦</div>
        @endif

        <div class="plan-card-modern-header">
          <h2 class="plan-title" style="font-size:1.6rem;">{{ $product->name }}</h2>
        </div>

        @if ($product->category)
          <span class="plan-feature" style="margin-bottom:0.75rem;">{{ $product->category }}</span>
        @endif

        <div class="plan-price-row">
          <span class="plan-price" style="font-size:2.4rem;">{{ number_format($product->price_aoa, 0, ',', '.') }}</span>
          <span class="plan-currency">Kz</span>
        </div>

        @if ($product->description)
          <p style="color:#334155;line-height:1.7;margin:1rem 0;">{{ $product->description }}</p>
        @endif

        @if ($product->isInStock())
          <p style="color:#16a34a;font-weight:600;font-size:0.97rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:0.4rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Em armazém — entrega em aproximadamente 2 dias úteis
          </p>

          <form method="POST" action="{{ route('equipment.cart.add') }}" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="order_type" value="immediate">
            <label for="qty" style="font-weight:600;">Qtd:</label>
            <input id="qty" type="number" name="quantity" value="1" min="1" max="99"
                   style="width:70px;padding:0.5rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;text-align:center;">
            <button type="submit" class="btn-buy-now">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 21a1 1 0 100-2 1 1 0 000 2zm10 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
              Comprar Agora
            </button>
          </form>
        @else
          <p style="color:#d97706;font-weight:600;font-size:0.97rem;margin-bottom:0.5rem;display:flex;align-items:center;gap:0.4rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Produto disponível por encomenda
          </p>
          <p style="font-size:0.88rem;color:#64748b;margin-bottom:1.25rem;">Prazo estimado de entrega: <strong>2 a 30 dias úteis</strong> após confirmação do pagamento.</p>

          <form method="POST" action="{{ route('equipment.cart.add') }}" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="order_type" value="backorder">
            <label for="qty" style="font-weight:600;">Qtd:</label>
            <input id="qty" type="number" name="quantity" value="1" min="1" max="99"
                   style="width:70px;padding:0.5rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;text-align:center;">
            <button type="submit" class="btn-order">
              <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Encomendar
            </button>
          </form>
        @endif

        @if ($errors->any())
          <p style="color:#ef4444;margin-top:0.5rem;font-size:0.93rem;">{{ $errors->first() }}</p>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
