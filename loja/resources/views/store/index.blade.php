@extends('layouts.app')

@section('content')
  {{-- Hero Carousel --}}
  <section class="hero" id="hero" aria-roledescription="carousel" aria-label="Destaques">
    <div class="hero__track" id="heroTrack">

      <div class="hero__slide" style="background-image:url('/img/carrossel1.webp')">
        <div class="hero__card">
          <span class="hero__eyebrow">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true"><circle cx="5" cy="5" r="3"/></svg>
            Internet Residencial
          </span>
          <h1 class="hero__title">WiFi Ilimitada<br><strong>A partir de 6&nbsp;Mbps</strong></h1>
          <p class="hero__desc">Planos para famílias, estudantes e profissionais — streaming, jogos e navegação sem interrupções. Velocidades reais, preços claros.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Ver Planos</a>
            <a href="/como-comprar" class="btn-ghost">Saiba Mais</a>
          </div>
        </div>
      </div>

      <div class="hero__slide" data-bg="/img/carrossel2.webp">
        <div class="hero__card">
          <span class="hero__eyebrow">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true"><circle cx="5" cy="5" r="3"/></svg>
            Soluções Empresariais
          </span>
          <h1 class="hero__title">Internet para<br><strong>o seu negócio</strong></h1>
          <p class="hero__desc">SLA dedicado, suporte técnico prioritário e ligações estáveis para PMEs e grandes empresas. Planos de 25 a +150&nbsp;Mbps.</p>
          <div class="hero__actions">
            <a href="#planos-empresariais" class="btn-primary">Ver Soluções</a>
            <a href="/como-comprar" class="btn-ghost">Fale Connosco</a>
          </div>
        </div>
      </div>

      <div class="hero__slide" data-bg="/img/carrossel3.webp">
        <div class="hero__card">
          <span class="hero__eyebrow">
            <svg width="10" height="10" viewBox="0 0 10 10" fill="currentColor" aria-hidden="true"><circle cx="5" cy="5" r="3"/></svg>
            Instalação Rápida
          </span>
          <h1 class="hero__title">Instalado<br><strong>em até 48 horas</strong></h1>
          <p class="hero__desc">Técnicos especializados, equipamentos incluídos e activação imediata. Sem burocracia, sem surpresas.</p>
          <div class="hero__actions">
            <a href="#planos" class="btn-primary">Escolher Plano</a>
            <a href="/como-comprar" class="btn-ghost">Como Funciona</a>
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

    {{-- Scroll hint --}}
    <a href="#planos" class="hero__scroll-hint" aria-label="Ver planos">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </a>

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
            <span class="stat-bar__num js-active-clients" data-count-decimals="0" data-count-suffix="">—</span>
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
        <div class="section-header__tag">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Compra sem conta
        </div>
        <h2>Planos Individuais</h2>
        <div class="section-header__rule"></div>
        <p>Compre o seu código e conecte-se imediatamente. Sem contratos, sem burocracia — só internet.</p>
      </div>

      <div class="plans-grid plans-grid--individual" aria-live="polite">
        @forelse ($individualPlans as $plan)
          <div class="plan-card-modern plan-card--individual plan-card--{{ $plan->slug }}{{ $plan->slug === 'semanal' ? ' plan-card--featured' : '' }}">
            <div class="plan-card-modern-inner">
              @if($plan->slug === 'semanal')<div class="plan-badge">Mais Popular</div>@endif
              <div class="plan-card-modern-header">
                <span class="plan-emoji" aria-hidden="true">
                  @if(str_contains(strtolower($plan->name), 'hora'))⏰@elseif(str_contains(strtolower($plan->name), 'dia'))🌞@elseif(str_contains(strtolower($plan->name), 'semana'))📅@elseif(str_contains(strtolower($plan->name), 'mês') || str_contains(strtolower($plan->name), 'mensal'))🗓️@else💡@endif
                </span>
                <h3 class="plan-title">{{ $plan->name }}</h3>
              </div>
              <div class="plan-card-modern-body">
                <div class="plan-price-row">
                  <span class="plan-price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }}</span>
                  <span class="plan-currency">Kz</span>
                </div>
                <div class="plan-features">
                  <span class="plan-feature plan-feature--active"><strong>{{ $plan->validity_label }}</strong></span>
                  @if(!empty($plan->speed_label))<span class="plan-feature">{{ $plan->speed_label }}</span>@endif
                  <span class="plan-feature">Downloads Ilimitados</span>
                </div>
              </div>
              <div class="plan-card-modern-footer">
                <a class="btn-modern" href="{{ route('store.checkout', ['plan' => $plan->slug]) }}">Comprar Agora</a>
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

{{-- Como Funciona --}}
<section class="howto-section howto-section--home">
  <div class="container">
    <div class="section-header">
      <div class="section-header__tag">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        Rápido e fácil
      </div>
      <h2>Como Funciona</h2>
      <div class="section-header__rule"></div>
      <p>Em menos de 2 minutos tens internet. Sem conta, sem password, sem esperas.</p>
    </div>
    <div class="howto-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <h3>Escolhe o plano</h3>
        <p>Selecciona o plano que melhor se adapta — diário, semanal ou mensal.</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <h3>Preenche os dados</h3>
        <p>Nome, telefone e referência de pagamento. Simples, rápido e seguro.</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <h3>Efectua o pagamento</h3>
        <p>Paga por Multicaixa Express ou transferência bancária — tudo em segundos.</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <h3>Recebe o código</h3>
        <p>O seu código WiFi aparece imediatamente no ecrã após confirmação do pagamento.</p>
      </div>
    </div>
  </div>
</section>

{{-- Planos Familiares --}}
<section class="planos-section planos-section--family" id="planos-familiares">
  <div class="container">
    <div class="section-header">
      <div class="section-header__tag">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Para toda a família
      </div>
      <h2>Planos Familiares</h2>
      <div class="section-header__rule"></div>
      <p>Uma única ligação para todos em casa. Velocidades dedicadas, estabilidade garantida.</p>
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
      <div class="section-header__tag">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        SLA dedicado
      </div>
      <h2>Planos Empresariais</h2>
      <div class="section-header__rule"></div>
      <p>Conectividade de alta performance para PMEs e grandes empresas. Suporte prioritário 24/7.</p>
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
      <div class="section-header__tag">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
        Sector público e privado
      </div>
      <h2>Planos Institucionais</h2>
      <div class="section-header__rule"></div>
      <p>Conectividade escalável e fiável para instituições do Estado, escolas, hospitais e organizações.</p>
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

@push('scripts')
<script>
// Carrega o número de clientes activos de forma assíncrona para não bloquear o render
(function () {
  var el = document.querySelector('.js-active-clients');
  if (!el) return;
  fetch('/sg/active-clients')
    .then(function(r){ return r.ok ? r.json() : null; })
    .then(function(data){
      if (data && data.count !== null && data.count !== undefined) {
        el.dataset.countTo = data.count;
        el.textContent = new Intl.NumberFormat('pt-PT').format(data.count);
        // dispara animação se o contador global já estiver inicializado
        if (window.initCounters) window.initCounters();
      }
    })
    .catch(function(){});
})();
</script>
@endpush

