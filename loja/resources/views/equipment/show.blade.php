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
          <p style="color:#16a34a;font-weight:600;margin-bottom:1rem;">✔ Em stock</p>

          <form method="POST" action="{{ route('equipment.cart.add') }}" style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <label for="qty" style="font-weight:600;">Quantidade:</label>
            <input id="qty" type="number" name="quantity" value="1" min="1" max="99"
                   style="width:70px;padding:0.5rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
            <button type="submit" class="btn-modern">🛒 Adicionar ao Carrinho</button>
          </form>

          @if ($errors->any())
            <p style="color:#ef4444;margin-top:0.5rem;">{{ $errors->first() }}</p>
          @endif
        @else
          <p style="color:#ef4444;font-size:1.05rem;font-weight:600;">Produto sem stock disponível.</p>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
