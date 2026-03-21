@extends('layouts.app')

@section('content')
  {{-- Hero Carousel --}}
  <section class="hero" id="hero" aria-roledescription="carousel" aria-label="Destaques">
    <div class="hero__track" id="heroTrack">

      <div class="hero__slide" style="background-image:url('/img/carrossel1.webp')">
        <div class="hero__card">
          <span class="hero__eyebrow">Internet Residencial</span>
          <h1 class="hero__title">WiFi Ilimitada<br><strong>A partir de 6 Mbps</strong></h1>
          <p class="hero__desc">Planos ideais para famílias que querem streaming, jogos e navegação sem interrupções. Conexão estável, velocidades reais e preços claros.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Ver Planos</a>
            <a href="/como-comprar" class="btn-ghost">Saiba Mais</a>
          </div>
        </div>
      </div>

      <div class="hero__slide" data-bg="/img/carrossel2.webp">
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

      <div class="hero__slide" data-bg="/img/carrossel3.webp">
        <div class="hero__card">
          <span class="hero__eyebrow">Instalação Rápida</span>
          <h1 class="hero__title">Conecte-se em<br><strong>até 48 horas!</strong></h1>
          <p class="hero__desc">Rápido, fácil e sem complicações.</p>
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

  {{-- Stat Bar --}}
  <div class="stat-bar">
    <div class="stat-bar__grid">
      @forelse($siteStats ?? [] as $stat)
        @php
          /* Para o item "Clientes activos" usa sempre o n.º real do SG.
             Se o SG estiver inacessível, exibe '—' em vez de um número falso. */
          $isClientsItem = mb_strtolower(trim($stat->legenda)) === 'clientes activos';
          $liveCount     = ($isClientsItem && isset($activeClientCount) && $activeClientCount !== null)
                         ? $activeClientCount : null;
        @endphp
        <div class="stat-bar__item">
          <span class="stat-bar__num"
            @if($liveCount !== null)
              data-count-to="{{ $liveCount }}"
              data-count-decimals="0"
              data-count-suffix=""
            @elseif(!$isClientsItem && $stat->count_to !== null)
              {{-- Nunca animar o item de clientes com um valor estático falso --}}
              data-count-to="{{ $stat->count_to }}"
              data-count-decimals="{{ $stat->count_decimals }}"
              data-count-suffix="{{ $stat->count_suffix }}"
            @else
              data-count-static="1"
            @endif
          >{{ $liveCount !== null
               ? number_format($liveCount, 0, ',', '.')
               : ($isClientsItem ? '—' : $stat->valor) }}</span>
          <span class="stat-bar__lbl">{{ $stat->legenda }}</span>
        </div>
      @empty
        <div class="stat-bar__item">
          @if(isset($activeClientCount) && $activeClientCount !== null)
            <span class="stat-bar__num" data-count-to="{{ $activeClientCount }}" data-count-decimals="0" data-count-suffix="">{{ number_format($activeClientCount, 0, ',', '.') }}</span>
          @else
            <span class="stat-bar__num" data-count-static="1">—</span>
          @endif
          <span class="stat-bar__lbl">Clientes activos</span>
        </div>
        <div class="stat-bar__item"><span class="stat-bar__num" data-count-to="99.8" data-count-decimals="1" data-count-suffix="%">99.8%</span><span class="stat-bar__lbl">Uptime garantido</span></div>
        <div class="stat-bar__item"><span class="stat-bar__num" data-count-static="1">24–48h</span><span class="stat-bar__lbl">Instalação rápida</span></div>
        <div class="stat-bar__item"><span class="stat-bar__num" data-count-static="1">24/7</span><span class="stat-bar__lbl">Suporte técnico</span></div>
      @endforelse
    </div>
  </div>

  <section class="planos-section planos-section--individual" id="planos">
    <div class="container">
      <div class="section-header">
        <h2>Planos Individuais</h2>
        <div class="section-header__rule"></div>
        <p><strong>Tenha autonomia total:</strong> compre seu código de acesso e conecte-se de forma independente em qualquer ponto da rede <strong>AngolaWiFi.</strong></p>
      </div>

      <div class="plans-grid plans-grid--individual" aria-live="polite">
        @forelse ($individualPlans as $plan)
          <div class="plan-card-modern plan-card--individual plan-card--{{ $plan['id'] }}{{ $plan['id'] === 'semanal' ? ' plan-card--featured' : '' }}">
            <div class="plan-card-modern-inner">
              @if($plan['id'] === 'semanal')<div class="plan-badge">Mais Popular</div>@endif
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
                  <span class="plan-feature plan-feature--active"><strong>{{ $plan['duration_label'] }}</strong></span>
                  <span class="plan-feature">{{ $plan['speed'] }}</span>
                  @if(!empty($plan['max_speed']))<span class="plan-feature">{{ $plan['max_speed'] }}</span>@endif
                  @if(!empty($plan['download']))<span class="plan-feature">{{ $plan['download'] }}</span>@endif
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

{{-- Planos Familiares --}}
<section class="planos-section planos-section--family" id="planos-familiares">
  <div class="container">
    <div class="section-header">
      <h2>Planos Familiares</h2>
      <div class="section-header__rule"></div>
      <p>Planos residenciais para famílias — navegação partilhada, velocidades reais e preços acessíveis. Carregados directamente do sistema de gestão.</p>
    </div>
    <div class="plans-grid plans-grid--individual" id="familiar-plans-grid" aria-live="polite">
      <div class="family-loading">
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
      </div>
    </div>
  </div>
</section>

{{-- Planos Empresariais --}}
<section class="planos-section planos-section--company" id="planos-empresariais">
  <div class="container">
    <div class="section-header">
      <h2>Planos Empresariais</h2>
      <div class="section-header__rule"></div>
      <p>Soluções para empresas — SLA dedicado, suporte técnico prioritário e conexões estáveis para o seu negócio.</p>
    </div>
    <div class="plans-grid plans-grid--individual" id="empresarial-plans-grid" aria-live="polite">
      <div class="family-loading">
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
      </div>
    </div>
  </div>
</section>

{{-- Planos Institucionais --}}
<section class="planos-section planos-section--institutional" id="planos-institucionais">
  <div class="container">
    <div class="section-header">
      <h2>Planos Institucionais</h2>
      <div class="section-header__rule"></div>
      <p>Planos para instituições públicas e privadas — conectividade fiável, escalável e adaptada às necessidades organizacionais.</p>
    </div>
    <div class="plans-grid plans-grid--individual" id="institucional-plans-grid" aria-live="polite">
      <div class="family-loading">
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
        <div class="family-loading__dot"></div>
      </div>
    </div>
  </div>
</section>

@push('styles')
<style>
  .planos-section--individual .plan-features {
    flex-direction: column;
    align-items: flex-start;
  }
</style>
@endpush


