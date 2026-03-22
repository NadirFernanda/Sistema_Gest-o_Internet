@extends('layouts.app')

@section('content')
<div class="page-hero page-hero--compact">
  <div class="container">
    <nav class="breadcrumb" aria-label="Navegação">
      <a href="{{ route('equipment.index') }}">Equipamentos</a>
      <span class="breadcrumb__sep" aria-hidden="true">›</span>
      <span class="breadcrumb__current">{{ $product->name }}</span>
    </nav>
    <h1 class="page-hero__title">{{ $product->name }}</h1>
  </div>
</div>

<section class="page-body">
  <div class="container">
    <div class="product-detail">

      {{-- Left: Image --}}
      <div class="product-detail__gallery">
        <div class="product-detail__image-wrap">
          @if ($product->image_path)
            <img src="{{ asset($product->image_path) }}"
                 alt="{{ $product->name }}"
                 class="product-detail__img"
                 loading="eager">
          @else
            <div class="product-detail__placeholder">📦</div>
          @endif
        </div>
        @if ($product->category)
          <span class="product-detail__cat-badge">{{ $product->category }}</span>
        @endif
      </div>

      {{-- Right: Info --}}
      <div class="product-detail__info">

        <div class="product-detail__price-block">
          <span class="product-detail__price">{{ number_format($product->price_aoa, 0, ',', '.') }}</span>
          <span class="product-detail__currency">Kz</span>
        </div>

        @if ($product->description)
          <p class="product-detail__desc">{{ $product->description }}</p>
        @endif

        {{-- Stock status --}}
        @if ($product->isInStock())
          <div class="product-detail__stock product-detail__stock--in">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Em armazém — entrega em ~2 dias úteis
          </div>
        @else
          <div class="product-detail__stock product-detail__stock--out">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Disponível por encomenda — 2 a 30 dias úteis
          </div>
        @endif

        {{-- Add to cart --}}
        <form method="POST" action="{{ route('equipment.cart.add') }}" class="product-detail__form">
          @csrf
          <input type="hidden" name="product_id" value="{{ $product->id }}">
          <input type="hidden" name="order_type" value="{{ $product->isInStock() ? 'immediate' : 'backorder' }}">

          <div class="product-detail__qty">
            <label for="qty" class="product-detail__qty-label">Quantidade</label>
            <input id="qty" type="number" name="quantity" value="1" min="1" max="99" class="product-detail__qty-input">
          </div>

          <button type="submit" class="{{ $product->isInStock() ? 'btn-buy-now' : 'btn-order' }} product-detail__cta">
            @if ($product->isInStock())
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9M9 21a1 1 0 100-2 1 1 0 000 2zm10 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
              Comprar Agora
            @else
              <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
              Encomendar
            @endif
          </button>
        </form>

        @if ($errors->any())
          <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif

        {{-- Back link --}}
        <a href="{{ route('equipment.index') }}" class="product-detail__back">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          Voltar ao catálogo
        </a>
      </div>

    </div>
  </div>
</section>
@endsection
