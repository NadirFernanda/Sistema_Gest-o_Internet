@extends('layouts.app')

@section('content')
  {{-- Hero com carrossel --}}
  <section class="mb-8">
    <div class="container">
      <div class="hero-carousel" aria-roledescription="carousel">
        <div class="carousel-track">
          <div class="carousel-slide hero-1"></div>

          <div class="carousel-slide hero-2"></div>

          <div class="carousel-slide hero-3"></div>
        </div>

        <div class="hero-cards" aria-hidden="false">
          <div class="slide-card" data-index="0">
            <div class="container">
              <h1>Internet Residencial — até 100 Mbps</h1>
              <p>Planos para famílias, streaming e gaming sem interrupções. Velocidades reais e preços claros.</p>
              <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
                <a href="#planos" class="btn-primary">Ver Planos</a>
                <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Saiba Mais</a>
              </div>
            </div>
          </div>

          <div class="slide-card" data-index="1">
            <div class="container">
              <h1>Internet Empresarial — conexões estáveis</h1>
              <p>Soluções dedicadas para empresas com SLA e suporte técnico prioritário. Escalável e segura.</p>
              <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
                <a href="#planos" class="btn-primary">Ver Soluções</a>
                <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Contactar Vendas</a>
              </div>
            </div>
          </div>

          <div class="slide-card" data-index="2">
            <div class="container">
              <h1>Instalação Rápida & Suporte Local</h1>
              <p>Agende instalação em 24–48h e conte com assistência técnica local sempre que precisar.</p>
              <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
                <a href="#planos" class="btn-primary">Agendar Instalação</a>
                <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Fale Connosco</a>
              </div>
            </div>
          </div>
        </div>

        <div class="carousel-indicators" role="tablist">
          <button data-index="0" aria-label="Slide 1" class="active"></button>
          <button data-index="1" aria-label="Slide 2"></button>
          <button data-index="2" aria-label="Slide 3"></button>
        </div>
      </div>
    </div>
  </section>


  <section class="planos-section" id="planos">
    <div class="container">
      <h2>Planos Individuais</h2>
      <p class="lead">Os planos individuais garantem maior autonomia, permitindo que qualquer utilizador compre o seu código de acesso e navegue de forma independente em qualquer um dos vários pontos da rede Luanda WiFi.</p>

      <div class="plans-grid" aria-live="polite">
        @forelse ($individualPlans as $plan)
          <div class="plan-card plan-card--individual plan-card--{{ $plan['id'] }}">
            @if (!empty($plan['image']))
              <div class="plan-thumb">
                <img src="{{ $plan['image'] }}" alt="{{ $plan['name'] }}" loading="lazy">
              </div>
            @endif
            <h3>{{ $plan['name'] }}</h3>
            <div class="price">{{ number_format($plan['price_kwanza'], 0, ',', '.') }} <small>AOA</small></div>
            <p class="desc">
              {{ $plan['duration_label'] }} &mdash; {{ $plan['speed'] }}
            </p>
            @if (!empty($plan['description']))
              <p class="desc">{{ $plan['description'] }}</p>
            @endif
            <div class="plan-actions">
              <a class="btn-primary" href="{{ route('store.checkout', ['plan' => $plan['id']]) }}">Comprar</a>
            </div>
          </div>
        @empty
          <div class="plan-card empty">
            <p>Os planos individuais serão exibidos aqui após cadastro no painel administrativo.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="planos-section" id="planos-familia-empresarial">
    <div class="container">
      <h2>Planos Familiares & Empresariais</h2>
      <p class="lead">Soluções para famílias e empresas — planos com duração de 30 dias, partilháveis ou com SLA dedicado conforme necessidade.</p>

      <div class="plans-grid" id="family-business-plans" aria-live="polite">
        <div class="plan-card empty">
          <p>Carregando planos...</p>
        </div>
      </div>
    </div>
  </section>


