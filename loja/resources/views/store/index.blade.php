@extends('layouts.app')

@section('content')
  {{-- Hero Carousel --}}
  <section class="hero" id="hero" aria-roledescription="carousel" aria-label="Destaques">
    <div class="hero__track" id="heroTrack">

      <div class="hero__slide" style="background-image:url('/img/carrossel1.webp')">
        <div class="hero__card">
          <span class="hero__eyebrow">Internet Residencial</span>
          <h1 class="hero__title">WiFi até<br><strong>100 Mbps</strong></h1>
          <p class="hero__desc">Planos para famílias, streaming e gaming sem interrupções. Velocidades reais e preços claros.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Ver Planos</a>
            <a href="/como-comprar" class="btn-ghost">Saiba Mais</a>
          </div>
        </div>
      </div>

      <div class="hero__slide" style="background-image:url('/img/carrossel2.webp')">
        <div class="hero__card">
          <span class="hero__eyebrow">Soluções Empresariais</span>
          <h1 class="hero__title">Internet<br><strong>Empresarial</strong></h1>
          <p class="hero__desc">SLA dedicado, suporte técnico prioritário e conexões estáveis para o seu negócio.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Ver Soluções</a>
            <a href="/como-comprar" class="btn-ghost">Contactar Vendas</a>
          </div>
        </div>
      </div>

      <div class="hero__slide" style="background-image:url('/img/carrossel3.webp')">
        <div class="hero__card">
          <span class="hero__eyebrow">Instalação Rápida</span>
          <h1 class="hero__title">Instalado em<br><strong>24–48 horas</strong></h1>
          <p class="hero__desc">Assistência técnica local disponível sempre que precisar. Agenda-se online em minutos.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Agendar Instalação</a>
            <a href="/como-comprar" class="btn-ghost">Fale Connosco</a>
          </div>
        </div>
      </div>

    </div>

    <button class="hero__arrow hero__arrow--prev" aria-label="Slide anterior">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M13 4L7 10L13 16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <button class="hero__arrow hero__arrow--next" aria-label="Próximo slide">
      <svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M7 4L13 10L7 16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    <div class="hero__dots" role="tablist" aria-label="Navegar slides">
      <button class="hero__dot is-active" role="tab" aria-selected="true"  aria-label="Slide 1" data-slide="0"></button>
      <button class="hero__dot"           role="tab" aria-selected="false" aria-label="Slide 2" data-slide="1"></button>
      <button class="hero__dot"           role="tab" aria-selected="false" aria-label="Slide 3" data-slide="2"></button>
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


