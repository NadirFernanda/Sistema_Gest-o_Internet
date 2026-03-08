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

    {{-- Grid de produtos — carregado assíncronamente do SG --}}
    <div class="plans-grid" id="sg-equipment-grid" aria-live="polite">
      {{-- Skeleton de carregamento (3 pontos) — substituído pelo JS --}}
      <div class="family-loading" style="grid-column:1/-1">
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
      </div>
    </div>
  </div>
</div>
@endsection
