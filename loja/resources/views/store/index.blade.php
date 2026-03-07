@extends('layouts.app')

@section('content')
  {{-- Hero com carrossel --}}
  <section class="mb-8">
    <div class="container">
      <div class="hero-carousel" aria-roledescription="carousel">
        <button class="carousel-arrow left" aria-label="Anterior" tabindex="0">&#8592;</button>
        <button class="carousel-arrow right" aria-label="Próximo" tabindex="0">&#8594;</button>
        <div class="carousel-track">
          <div class="carousel-slide hero-1"></div>
          <div class="carousel-slide hero-2"></div>
          <div class="carousel-slide hero-3"></div>
        </div>

        <div class="hero-cards" aria-hidden="false">
          <div class="slide-card" data-index="0">
            <h1>Internet Residencial — até 100 Mbps</h1>
            <p>Planos para famílias, streaming e gaming sem interrupções. Velocidades reais e preços claros.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">
              <a href="#planos" class="btn-primary">Ver Planos</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Saiba Mais</a>
            </div>
          </div>

          <div class="slide-card" data-index="1">
            <h1>Internet Empresarial — conexões estáveis</h1>
            <p>Soluções dedicadas para empresas com SLA e suporte técnico prioritário. Escalável e segura.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">
              <a href="#planos" class="btn-primary">Ver Soluções</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Contactar Vendas</a>
            </div>
          </div>

          <div class="slide-card" data-index="2">
            <h1>Instalação Rápida & Suporte Local</h1>
            <p>Agende instalação em 24–48h e conte com assistência técnica local sempre que precisar.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap; justify-content:center;">
              <a href="#planos" class="btn-primary">Agendar Instalação</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Fale Connosco</a>
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
          <div class="plan-card-modern plan-card--individual plan-card--{{ $plan['id'] }}">
            <div class="plan-card-modern-inner">
              <div class="plan-card-modern-header">
                <span class="plan-emoji" aria-hidden="true">
                  @if(str_contains(strtolower($plan['name']), 'hora'))⏰@elseif(str_contains(strtolower($plan['name']), 'dia'))🌞@elseif(str_contains(strtolower($plan['name']), 'semana'))📅@elseif(str_contains(strtolower($plan['name']), 'mês') || str_contains(strtolower($plan['name']), 'mensal'))🗓️@else💡@endif
                </span>
                <h3 class="plan-title">{{ $plan['name'] }}</h3>
              </div>
              <div class="plan-card-modern-body">
                <div class="plan-price-row">
                  <span class="plan-price">{{ number_format($plan['price_kwanza'], 0, ',', '.') }}</span>
                  <span class="plan-currency">Kz</span>
                </div>
                <div class="plan-features">
                  <span class="plan-feature"><strong>{{ $plan['duration_label'] }}</strong></span>
                  <span class="plan-feature">{{ $plan['speed'] }}</span>
                </div>
                @if (!empty($plan['description']))
                  <p class="plan-desc">{{ $plan['description'] }}</p>
                @endif
              </div>
              <div class="plan-card-modern-footer">
                <a class="btn-modern" href="{{ route('store.checkout', ['plan' => $plan['id']]) }}">Comprar Agora</a>
              </div>
            </div>
          </div>
        @empty
          <div class="plan-card-modern empty">
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


