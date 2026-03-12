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
      <div style="text-align:center;padding:3rem;color:#64748b;">
        <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 1rem;display:block;opacity:.4"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a1 1 0 00-1 1v10a2 2 0 002 2h14a2 2 0 002-2V8a1 1 0 00-1-1zm-9-2h2a2 2 0 012 2H7a2 2 0 012-2z"/></svg>
        <p style="font-size:1.1rem;font-weight:600;">Nenhum produto disponível de momento.</p>
      </div>
    @else
      <div class="equipment-grid">
        @foreach ($products as $product)
          <div class="equipment-card">
            <a href="{{ route('equipment.show', $product->slug) }}" class="equipment-card__img-link" tabindex="-1" aria-hidden="true">
              @if ($product->image_path)
                <img src="{{ asset($product->image_path) }}" alt="{{ $product->name }}" class="equipment-card__img" loading="lazy">
              @else
                <div class="equipment-card__img-placeholder">
                  <svg width="52" height="52" fill="currentColor" viewBox="0 0 24 24"><path d="M20 7h-4V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2H4a1 1 0 00-1 1v11a2 2 0 002 2h14a2 2 0 002-2V8a1 1 0 00-1-1zM10 5h4v2h-4V5zm9 14H5V9h14v10z"/></svg>
                </div>
              @endif
            </a>
            <div class="equipment-card__body">
              @if ($product->category)
                <span class="equipment-card__cat">{{ $product->category }}</span>
              @endif
              <h3 class="equipment-card__name">
                <a href="{{ route('equipment.show', $product->slug) }}">{{ $product->name }}</a>
              </h3>
              <div class="equipment-card__price">
                {{ number_format($product->price_aoa, 0, ',', '.') }}&nbsp;<span>Kz</span>
              </div>
              @if ($product->stock > 0)
                <span class="equipment-card__badge equipment-card__badge--in-stock">
                  <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                  Em armazém
                </span>
              @else
                <span class="equipment-card__badge equipment-card__badge--backorder">
                  <svg width="10" height="10" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                  Encomenda: 2–30 dias
                </span>
              @endif
              <a href="{{ route('equipment.show', $product->slug) }}" class="equipment-card__btn">Ver Detalhes</a>
            </div>
          </div>
        @endforeach
      </div>
    @endif

  </div>
</div>
@endsection
