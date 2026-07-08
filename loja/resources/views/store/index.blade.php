@extends('layouts.app')

@section('seo_title', 'AngolaWiFi — Internet WiFi em Angola | Planos a partir de 200 Kz')
@section('seo_description', 'Ligue-se à internet com a AngolaWiFi. Planos WiFi por hotspot a partir de 200 Kz/dia, planos família a partir de 27.500 Kz/mês e soluções empresariais até 100 Mbps. Instalação em 48h em todo Angola.')

@push('seo')
@verbatim
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "Organization",
      "@id": "https://angolawifi.ao/#organization",
      "name": "AngolaWiFi",
      "url": "https://angolawifi.ao",
      "logo": {
        "@type": "ImageObject",
        "url": "https://angolawifi.ao/img/logo2.jpeg"
      },
      "description": "Plataforma digital de internet WiFi em Angola. Planos residenciais, familiares, empresariais e institucionais. Revenda de serviços por hotspot, fibra e micro-ondas.",
      "areaServed": {
        "@type": "Country",
        "name": "Angola"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "availableLanguage": "Portuguese"
      }
    },
    {
      "@type": "WebSite",
      "@id": "https://angolawifi.ao/#website",
      "url": "https://angolawifi.ao",
      "name": "AngolaWiFi",
      "publisher": { "@id": "https://angolawifi.ao/#organization" },
      "inLanguage": "pt"
    },
    {
      "@type": "ItemList",
      "name": "Planos de Internet AngolaWiFi",
      "url": "https://angolawifi.ao/#planos",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Plano Diário — 200 Kz",
          "description": "Internet WiFi por hotspot durante 24 horas. Até 10 Mbps, download ilimitado."
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Plano Semanal — 500 Kz",
          "description": "Internet WiFi por hotspot durante 7 dias. Até 10 Mbps, download ilimitado."
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Plano Mensal — 1.000 Kz",
          "description": "Internet WiFi por hotspot durante 30 dias. Até 10 Mbps, download ilimitado."
        },
        {
          "@type": "ListItem",
          "position": 4,
          "name": "Plano Família 6 Mbps — 27.500 Kz",
          "description": "Internet residencial de 6 Mbps para famílias. 30 dias, instalação incluída."
        },
        {
          "@type": "ListItem",
          "position": 5,
          "name": "Plano Empresarial 25 Mbps — 195.000 Kz",
          "description": "Internet dedicada para micro e pequenas empresas. 30 dias, 25 Mbps."
        }
      ]
    }
  ]
}
</script>
@endverbatim
@endpush

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
          <h1 class="hero__title">Instalamos<br><strong>em até 48 horas</strong></h1>
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

      {{-- Indicador "Ao vivo" --}}
      <div class="stat-bar__live">
        <span class="live-dot"></span>
        <span class="live-label">Ao vivo</span>
      </div>

      {{-- Online agora (SG API) --}}
      <div class="stat-bar__item">
        @php $ac = $activeClientCount ?? null; @endphp
        <span class="stat-bar__num js-live-active"
          @if($ac !== null) data-count-to="{{ $ac }}" data-count-decimals="0" data-count-suffix="" @else data-count-static="1" @endif>
          {{ $ac !== null ? number_format($ac, 0, ',', '.') : '—' }}
        </span>
        <span class="stat-bar__lbl">Online agora</span>
      </div>

      {{-- Vouchers entregues hoje --}}
      <div class="stat-bar__item">
        @php $vt = $vouchersSoldToday ?? null; @endphp
        <span class="stat-bar__num js-live-today"
          @if($vt !== null) data-count-to="{{ $vt }}" data-count-decimals="0" data-count-suffix="" @else data-count-static="1" @endif>
          {{ $vt !== null ? number_format($vt, 0, ',', '.') : '—' }}
        </span>
        <span class="stat-bar__lbl">Vouchers hoje</span>
      </div>

      {{-- Total entregues --}}
      <div class="stat-bar__item">
        @php $td = $totalDelivered ?? null; @endphp
        <span class="stat-bar__num js-live-total"
          @if($td !== null) data-count-to="{{ $td }}" data-count-decimals="0" data-count-suffix="+" @else data-count-static="1" @endif>
          {{ $td !== null ? number_format($td, 0, ',', '.') . '+' : '—' }}
        </span>
        <span class="stat-bar__lbl">Vouchers entregues</span>
      </div>

      {{-- Stats configuráveis via DB (SiteStat), excluindo "Clientes activos" que já está acima) --}}
      @foreach($siteStats ?? [] as $stat)
        @if(mb_strtolower(trim($stat->legenda)) === 'clientes activos') @continue @endif
        <div class="stat-bar__item">
          <span class="stat-bar__num"
            @if($stat->count_to !== null)
              data-count-to="{{ $stat->count_to }}"
              data-count-decimals="{{ $stat->count_decimals }}"
              data-count-suffix="{{ $stat->count_suffix }}"
            @else
              data-count-static="1"
            @endif
          >{{ $stat->valor }}</span>
          <span class="stat-bar__lbl">{{ $stat->legenda }}</span>
        </div>
      @endforeach

      {{-- Fallback se SiteStat vazio: uptime + suporte --}}
      @if(($siteStats ?? collect())->isEmpty())
        <div class="stat-bar__item"><span class="stat-bar__num" data-count-to="99.8" data-count-decimals="1" data-count-suffix="%">99.8%</span><span class="stat-bar__lbl">Uptime garantido</span></div>
        <div class="stat-bar__item"><span class="stat-bar__num" data-count-static="1">24/7</span><span class="stat-bar__lbl">Suporte técnico</span></div>
      @endif

      {{-- Timestamp de última actualização --}}
      <div class="stat-bar__live stat-bar__live--right">
        <span class="live-updated js-live-updated"></span>
      </div>
    </div>
  </div>

  {{-- ══ Secção de Estatísticas Live ══ --}}
  <section class="live-stats-section" id="estatisticas">
    <div class="container">
      <div class="live-stats-header">
        <span class="live-dot"></span>
        <span class="live-stats-title">Em tempo real</span>
      </div>

      <div class="visitor-wrap">

        {{-- Card tempo real --}}
        <div class="visitor-card">
          <div class="visitor-card__left">
            <div class="visitor-card__num js-live-visitors">—</div>
            <div class="visitor-card__lbl">Visitantes agora</div>
            <div class="visitor-card__desc">Pessoas na loja neste momento</div>
            <p class="live-stats-update js-live-updated"></p>
          </div>
          <div class="visitor-card__right">
            <div class="visitor-chart__title">Por país</div>
            <div class="visitor-chart js-visitor-chart">
              <div class="visitor-chart__empty">A carregar...</div>
            </div>
          </div>
        </div>

        {{-- Totais históricos --}}
        <div class="visitor-history">
          <div class="vhist-totals">
            <div class="vhist-item">
              <span class="vhist-num js-hist-today"
                @if(($visitorsToday ?? null) !== null) data-count-to="{{ $visitorsToday }}" data-count-decimals="0" data-count-suffix="" @endif>
                {{ ($visitorsToday ?? null) !== null ? number_format($visitorsToday, 0, ',', '.') : '—' }}
              </span>
              <span class="vhist-lbl">Hoje</span>
            </div>
            <div class="vhist-item">
              <span class="vhist-num js-hist-week"
                @if(($visitorsWeek ?? null) !== null) data-count-to="{{ $visitorsWeek }}" data-count-decimals="0" data-count-suffix="" @endif>
                {{ ($visitorsWeek ?? null) !== null ? number_format($visitorsWeek, 0, ',', '.') : '—' }}
              </span>
              <span class="vhist-lbl">Últimos 7 dias</span>
            </div>
            <div class="vhist-item">
              <span class="vhist-num js-hist-month"
                @if(($visitorsMonth ?? null) !== null) data-count-to="{{ $visitorsMonth }}" data-count-decimals="0" data-count-suffix="" @endif>
                {{ ($visitorsMonth ?? null) !== null ? number_format($visitorsMonth, 0, ',', '.') : '—' }}
              </span>
              <span class="vhist-lbl">Este mês</span>
            </div>
          </div>
          <div class="vhist-spark-wrap">
            <div class="vhist-spark-title">Total de acessos</div>
            <div class="vhist-total js-hist-total"
              @if(($visitorsTotal ?? null) !== null) data-count-to="{{ $visitorsTotal }}" data-count-decimals="0" data-count-suffix="" @endif>
              {{ ($visitorsTotal ?? null) !== null ? number_format($visitorsTotal, 0, ',', '.') : '—' }}
            </div>
            <div class="vhist-total-sub">visitas registadas</div>
          </div>

          <div class="vhist-countries-wrap">
            <div class="vhist-spark-title" style="margin-top:1rem;">Por país (histórico)</div>
            <div class="vhist-country-list js-country-totals">
              <div class="vhist-country-loading">A carregar...</div>
            </div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <section class="planos-section planos-section--individual" id="planos">
    <div class="container">
      <div class="section-header">
        <div class="section-header__tag">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Compra sem conta
        </div>
        <h2>Planos Individuais</h2>
        <div class="section-header__rule"></div>
        <p><strong>Compre o seu código, ligue-se e navegue sem limites!</strong></p>
        <p style="margin-top:.65rem;">Adquira o seu voucher AngolaWiFi, conecte-se a um dos nossos pontos de acesso e desfrute de uma experiência de internet rápida, estável e sem complicações.</p>
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
      <p>Compre o seu voucher, conecte-se e navegue sem limites!</p>
    </div>
    <div class="howto-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <h3>Conecte-se</h3>
        <p>Ligue o seu dispositivo à rede AngolaWiFi.</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <h3>Aceda à loja</h3>
        <p>Na página de login, clique em www.angolawifi.ao.</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <h3>Compre o voucher</h3>
        <p>Escolha o plano e efectue o pagamento de forma segura através do Gateway de Pagamentos Online da EMIS.</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <h3>Navegue</h3>
        <p>Receba o código, introduza-o na página de login e comece a navegar imediatamente e sem limites.</p>
      </div>
    </div>
  </div>
</section>

{{-- Agente Revendedor --}}
<section class="howto-section howto-section--reseller" id="revendedores">
  <div class="container">
    <div class="section-header">
      <div class="section-header__tag">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Parceiro de negócios
      </div>
      <h2>Agente Revendedor AngolaWiFi</h2>
      <div class="section-header__rule"></div>
      <p>Torne-se um Agente Revendedor em apenas 4 passos e comece a faturar com a venda de internet por vouchers. Também pode cadastrar a sua equipa de vendas para aumentar os seus rendimentos.</p>
    </div>
    <div class="howto-grid">
      <div class="step-card step-card--reseller">
        <div class="step-num step-num--dark">1</div>
        <h3>Torne-se Revendedor</h3>
        <p>Clique em <strong>"Torne-se Revendedor"</strong> e inicie o processo de candidatura.</p>
      </div>
      <div class="step-card step-card--reseller">
        <div class="step-num step-num--dark">2</div>
        <h3>Preencha o formulário</h3>
        <p>Preencha o formulário de inscrição com os seus dados e aguarde a aprovação.</p>
      </div>
      <div class="step-card step-card--reseller">
        <div class="step-num step-num--dark">3</div>
        <h3>Submeta o pedido</h3>
        <p>Submeta o pedido. A nossa equipa analisa e aprova em tempo útil.</p>
      </div>
      <div class="step-card step-card--reseller">
        <div class="step-num step-num--dark">4</div>
        <h3>Comece a vender</h3>
        <p>Receba as credenciais por e-mail, aceda ao Painel do Revendedor e comece a gerar receita imediatamente.</p>
      </div>
    </div>

    {{-- Destaque e CTA --}}
    <div class="reseller-cta-block">
      <div class="reseller-cta-text">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        <p>Transforme um ponto de internet numa fonte de rendimento e gere receitas todos os dias, com uma plataforma que faz a gestão automática das vendas e dos seus ganhos.</p>
      </div>
      <a href="{{ route('reseller.apply') }}" class="reseller-cta-btn">
        Torne-se Revendedor
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
      </a>
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
(function () {
  var fmt = new Intl.NumberFormat('pt-PT');

  function setNum(sel, val, suffix) {
    if (val === null || val === undefined) return;
    document.querySelectorAll(sel).forEach(function(el) {
      el.dataset.countTo     = val;
      el.dataset.countSuffix = suffix || '';
      el.removeAttribute('data-count-static');
      el.textContent = fmt.format(val) + (suffix || '');
    });
    if (window.initCounters) window.initCounters();
  }

  function setUpdated() {
    var time = 'Actualizado às ' + new Date().toLocaleTimeString('pt-PT', {hour:'2-digit', minute:'2-digit', second:'2-digit'});
    document.querySelectorAll('.js-live-updated').forEach(function(el) { el.textContent = time; });
  }

  function renderChart(countries) {
    var el = document.querySelector('.js-visitor-chart');
    if (!el || !countries) return;
    var entries = Object.entries(countries);
    if (!entries.length) {
      el.innerHTML = '<div class="visitor-chart__empty">Sem dados ainda</div>';
      return;
    }
    var max = Math.max.apply(null, entries.map(function(e){ return e[1]; }));
    var html = entries.map(function(e) {
      var pct = max > 0 ? Math.round((e[1] / max) * 100) : 0;
      return '<div class="vchart-row">'
        + '<span class="vchart-lbl">' + e[0] + '</span>'
        + '<div class="vchart-bar-wrap">'
        +   '<div class="vchart-bar" style="width:' + pct + '%"></div>'
        + '</div>'
        + '<span class="vchart-val">' + e[1] + '</span>'
        + '</div>';
    }).join('');
    el.innerHTML = html;
  }

  function renderCountryTotals(totals, grandTotal) {
    var el = document.querySelector('.js-country-totals');
    if (!el || !totals) return;
    var entries = Object.entries(totals);
    if (!entries.length) {
      el.innerHTML = '<div class="vhist-country-loading">Sem dados ainda</div>';
      return;
    }
    var base = grandTotal > 0 ? grandTotal : Math.max.apply(null, entries.map(function(e){ return e[1]; })) || 1;
    el.innerHTML = entries.map(function(e) {
      var pct  = Math.min(100, Math.round((e[1] / base) * 100));
      return '<div class="vhist-crow">'
        + '<span class="vhist-cname">' + e[0] + '</span>'
        + '<span class="vhist-ccount">' + fmt.format(e[1]) + ' <span style="opacity:.6;font-size:.72rem;">(' + pct + '%)</span></span>'
        + '<div class="vhist-cbar-wrap"><div class="vhist-cbar" style="width:' + pct + '%"></div></div>'
        + '</div>';
    }).join('');
  }

  function fetchStats() {
    fetch('/store/live-stats')
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(data){
        if (!data) return;
        setNum('.js-live-visitors', data.visitors_now,    '');
        setNum('.js-live-active',   data.active_clients,  '');
        setNum('.js-live-today',    data.vouchers_today,  '');
        setNum('.js-live-total',    data.total_delivered, '+');
        setNum('.js-hist-today',    data.visitors_today,  '');
        setNum('.js-hist-week',     data.visitors_week,   '');
        setNum('.js-hist-month',    data.visitors_month,  '');
        setNum('.js-hist-total',    data.visitors_total,  '');
        renderChart(data.top_countries);
        renderCountryTotals(data.country_totals, data.visitors_total);
        setUpdated();
      })
      .catch(function(){});
  }

  // Carregamento inicial assíncrono
  fetchStats();
  // Polling a cada 60 segundos
  setInterval(fetchStats, 60000);
})();
</script>
@endpush

