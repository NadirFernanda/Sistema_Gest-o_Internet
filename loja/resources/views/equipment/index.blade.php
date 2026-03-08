@extends('layouts.app')

@section('content')
<div class="page-hero">
  <div class="container">
    <span class="page-hero__eyebrow">Catálogo</span>
    <h1 class="page-hero__title">Equipamentos &amp; Produtos</h1>
    <p class="page-hero__desc">Routers, repetidores, antenas e acessórios para a sua rede WiFi. Encomende online e receba em casa.</p>
  </div>
</div>

<div class="page-body">
  <div class="container">

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Filtro por categoria --}}
    @if ($categories->isNotEmpty())
      <div class="category-filter" role="group" aria-label="Filtrar por categoria">
        <a href="{{ route('equipment.index') }}" class="{{ !request('categoria') ? 'is-active' : '' }}">Todos</a>
        @foreach ($categories as $cat)
          <a href="{{ route('equipment.index', ['categoria' => $cat]) }}" class="{{ request('categoria') === $cat ? 'is-active' : '' }}">{{ $cat }}</a>
        @endforeach
      </div>
    @endif

    {{-- Grid de produtos --}}
    <div class="plans-grid" aria-live="polite">
      @forelse ($products as $product)
        <div class="plan-card-modern">
          <div class="plan-card-modern-inner">
            @if ($product->image_path)
              <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="product-img">
            @else
              <div class="product-placeholder">📦</div>
            @endif

            <div class="plan-card-modern-header">
              <h3 class="plan-title">{{ $product->name }}</h3>
            </div>

            @if ($product->category)
              <span class="plan-feature" style="margin-bottom:0.5rem;">{{ $product->category }}</span>
            @endif

            <div class="plan-card-modern-body">
              <div class="plan-price-row">
                <span class="plan-price">{{ number_format($product->price_aoa, 0, ',', '.') }}</span>
                <span class="plan-currency">Kz</span>
              </div>

              @if ($product->description)
                <p class="plan-desc">{{ Str::limit($product->description, 100) }}</p>
              @endif

              @if (!$product->isInStock())
                <p class="product-stock-warn">Sem stock</p>
              @endif
            </div>

            <div class="product-actions">
              <a href="{{ route('equipment.show', $product->slug) }}" class="btn-modern">Ver Detalhes</a>
              @if ($product->isInStock())
                <form method="POST" action="{{ route('equipment.cart.add') }}">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <button type="submit" class="btn-modern">🛒 Adicionar</button>
                </form>
              @endif
            </div>
          </div>
        </div>
      @empty
        <div class="plan-card-modern" style="grid-column:1/-1;">
          <p>Nenhum produto disponível de momento. Volte em breve.</p>
        </div>
      @endforelse
    </div>
  </div>
</div>
@endsection
