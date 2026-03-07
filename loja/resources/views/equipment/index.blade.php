@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Loja de equipamentos">
  <div class="container">
    <h2>Equipamentos & Produtos</h2>
    <p class="lead">Routers, repetidores, antenas e acessórios para a sua rede WiFi. Encomende online e receba em casa.</p>

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
    @endif

    {{-- Filtro por categoria --}}
    @if ($categories->isNotEmpty())
      <div class="flex flex-wrap gap-2 mb-6" role="group" aria-label="Filtrar por categoria">
        <a href="{{ route('equipment.index') }}"
           class="plan-feature {{ !request('categoria') ? 'plan-feature--active' : '' }}">
          Todos
        </a>
        @foreach ($categories as $cat)
          <a href="{{ route('equipment.index', ['categoria' => $cat]) }}"
             class="plan-feature {{ request('categoria') === $cat ? 'plan-feature--active' : '' }}">
            {{ $cat }}
          </a>
        @endforeach
      </div>
    @endif

    {{-- Grid de produtos --}}
    <div class="plans-grid" aria-live="polite">
      @forelse ($products as $product)
        <div class="plan-card-modern">
          <div class="plan-card-modern-inner">
            @if ($product->image_path)
              <img src="{{ asset($product->image_path) }}"
                   alt="{{ $product->name }}"
                   style="width:100%;height:160px;object-fit:cover;border-radius:0.75rem;margin-bottom:0.75rem;">
            @else
              <div style="width:100%;height:120px;background:#f1f5f9;border-radius:0.75rem;margin-bottom:0.75rem;display:flex;align-items:center;justify-content:center;font-size:2.5rem;">📦</div>
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
                <p style="color:#ef4444;font-size:0.9rem;font-weight:600;">Sem stock</p>
              @endif
            </div>

            <div class="plan-card-modern-footer" style="gap:0.5rem;display:flex;flex-wrap:wrap;">
              <a href="{{ route('equipment.show', $product->slug) }}" class="btn-modern" style="flex:1;text-align:center;">Ver Detalhes</a>
              @if ($product->isInStock())
                <form method="POST" action="{{ route('equipment.cart.add') }}" style="flex:1;">
                  @csrf
                  <input type="hidden" name="product_id" value="{{ $product->id }}">
                  <button type="submit" class="btn-modern" style="width:100%;">🛒 Adicionar</button>
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
</section>
@endsection
