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
          <a href="{{ route('equipment.index', ['categoria' => $cat]) }}"
             class="{{ request('categoria') === $cat ? 'is-active' : '' }}">{{ $cat }}</a>
        @endforeach
      </div>
    @endif

    {{-- Grid de produtos --}}
    @if ($products->isEmpty())
      <div class="equip-empty">
        <svg width="64" height="64" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path stroke-linecap="round" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
        <p>Nenhum produto disponível de momento.</p>
      </div>
    @else
      <div class="equipment-grid">
        @foreach ($products as $product)
          <article class="equipment-card">

            {{-- Visual area: imagem + badges sobrepostos --}}
            <a href="{{ route('equipment.show', $product->slug) }}" class="equipment-card__visual" tabindex="-1" aria-hidden="true">
              @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="equipment-card__img" loading="lazy">
              @else
                <div class="equipment-card__placeholder">
                  <svg width="52" height="52" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path stroke-linecap="round" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </div>
              @endif
              <div class="equipment-card__overlay"></div>
              <div class="equipment-card__badges">
                @if ($product->category)
                  <span class="equipment-card__cat-badge">{{ $product->category }}</span>
                @else
                  <span></span>
                @endif
                @if ($product->stock > 0)
                  <span class="equipment-card__stock-badge equipment-card__stock-badge--in">
                    <span class="equipment-card__stock-dot"></span>Em armazém
                  </span>
                @else
                  <span class="equipment-card__stock-badge equipment-card__stock-badge--out">
                    <span class="equipment-card__stock-dot"></span>Encomenda
                  </span>
                @endif
              </div>
            </a>

            {{-- Corpo: nome + preço + CTA --}}
            <div class="equipment-card__body">
              <h3 class="equipment-card__name">
                <a href="{{ route('equipment.show', $product->slug) }}">{{ $product->name }}</a>
              </h3>
              <div class="equipment-card__footer">
                <div class="equipment-card__price-wrap">
                  <span class="equipment-card__price">{{ number_format($product->price_aoa, 0, ',', '.') }}</span>
                  <span class="equipment-card__kz">Kz</span>
                </div>
                <a href="{{ route('equipment.show', $product->slug) }}" class="equipment-card__cta" aria-label="Ver detalhes de {{ $product->name }}">
                  Ver
                  <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
              </div>
            </div>

          </article>
        @endforeach
      </div>
    @endif

  </div>
</div>
@endsection
