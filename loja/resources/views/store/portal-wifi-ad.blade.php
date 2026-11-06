<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="theme-color" content="#f7b500">
  <title>{{ $campaign ? 'Publicidade AngolaWiFi' : 'Planos WiFi AngolaWiFi' }}</title>
  <style>
    @font-face {
      font-family: 'Inter';
      font-style: normal;
      font-display: optional;
      font-weight: 100 900;
      src: url('{{ asset('fonts/inter-latin-wght-normal.woff2') }}') format('woff2-variations');
    }

    :root {
      color-scheme: light;
      font-family: 'Inter', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
      color: #0F172A;
      background: #F8FAFC;
      font-synthesis: none;
      text-rendering: optimizeLegibility;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      --brand: #f4b400;
      --brand-dark: #d99a00;
      --text-dark: #101828;
      --text-muted: #526071;
      --text-light: #758195;
      --surface: #FFFFFF;
      --surface-alt: #f4f6f9;
      --muted-border: #e4e8ee;
      --radius-card: 1rem;
      --shadow-sm: 0 8px 24px rgba(16,24,40,.07);
    }

    * { box-sizing: border-box; }

    html { scroll-behavior: smooth; }

    body {
      min-width: 280px;
      min-height: 100vh;
      margin: 0;
      padding: 18px clamp(14px, 3vw, 28px) 28px;
      background: #f5f7fa;
      line-height: 1.5;
    }

    a { color: inherit; }

    a:focus-visible,
    button:focus-visible {
      outline: 3px solid #2563EB;
      outline-offset: 3px;
    }

    .page { width: min(100%, 760px); margin: 0 auto; }

    .masthead {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin: 0 0 14px;
    }

    .brand { display: flex; align-items: center; gap: 11px; min-width: 0; }

    .brand__mark {
      display: grid;
      width: 40px;
      height: 40px;
      flex: 0 0 40px;
      place-items: center;
      overflow: hidden;
      border: 1px solid var(--muted-border);
      border-radius: 10px;
      background: var(--surface);
    }

    .brand__mark img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .brand__name { margin: 0; color: var(--text-dark); font-size: .92rem; font-weight: 750; letter-spacing: -.025em; }
    .brand__tagline { margin: 2px 0 0; color: var(--text-light); font-size: .7rem; }

    .store-button {
      min-height: 38px;
      padding: 0 13px;
      border: 1px solid var(--muted-border);
      border-radius: 9px;
      color: #344054;
      background: var(--surface);
      cursor: pointer;
      font: inherit;
      font-size: .75rem;
      font-weight: 700;
      white-space: nowrap;
    }

    .store-button:hover { border-color: var(--brand-dark); color: var(--text-dark); }

    .store-fallback {
      display: block;
      margin-top: 6px;
      color: var(--text-muted);
      font-size: .68rem;
      text-align: center;
      text-decoration: none;
    }

    .motion-ready .plans-heading { animation: hero-enter .5s cubic-bezier(.2,.7,.25,1) both; }
    .motion-ready .reveal-on-scroll {
      opacity: 0;
      transform: translateY(14px);
      transition: opacity .45s ease, transform .45s cubic-bezier(.2,.7,.25,1);
    }
    .motion-ready .reveal-on-scroll.is-visible { opacity: 1; transform: translateY(0); }
    .motion-ready .plan:nth-child(2) { transition-delay: 70ms; }
    .motion-ready .plan:nth-child(3) { transition-delay: 140ms; }
    .motion-ready .plan:nth-child(4) { transition-delay: 210ms; }

    @keyframes hero-enter {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .campaign,
    .plan {
      overflow: hidden;
      border: 1px solid var(--muted-border);
      border-radius: var(--radius-card);
      background: var(--surface);
      box-shadow: var(--shadow-sm);
    }

    .campaign-section { margin: 0 0 18px; }
    .campaign-section__heading { display: flex; align-items: center; gap: 7px; margin: 0 0 7px; }
    .campaign-section__heading p { margin: 0; color: var(--text-light); font-size: .62rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .campaign-section__heading p::before { display: inline-block; width: 6px; height: 6px; margin-right: 6px; border-radius: 50%; background: var(--brand); content: ""; vertical-align: 1px; }
    .campaign-section__heading h2 { margin: 0; color: var(--text-muted); font-size: .72rem; font-weight: 600; }
    .campaign { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); margin: 0; }
    .campaign__media {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      min-width: 0;
      min-height: 135px;
      height: min(24vw, 190px);
      max-height: 190px;
      place-items: center;
      overflow: hidden;
      padding: 8px;
      background: #eef1f5;
    }

    .campaign__media img { display: block; width: 100%; max-width: 100%; min-width: 0; height: 100%; object-fit: contain; }
    .campaign__content { display: flex; flex-direction: column; align-items: flex-start; justify-content: center; padding: clamp(12px, 2.5vw, 20px); }
    .campaign__label { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin: 0 0 8px; color: #79550a; font-size: .66rem; font-weight: 750; letter-spacing: .08em; text-transform: uppercase; }
    .campaign__label::before { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); content: ""; }
    .campaign__advertiser { color: var(--text-light); font-weight: 700; letter-spacing: .02em; }
    .campaign__title { max-width: 24ch; margin: 0; color: var(--text-dark); font-size: clamp(1.3rem, 4vw, 2rem); line-height: 1.12; letter-spacing: -.035em; overflow-wrap: anywhere; }
    .campaign__description { max-width: 62ch; margin: 8px 0 0; color: var(--text-muted); font-size: .88rem; line-height: 1.55; overflow-wrap: anywhere; }
    .campaign__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; margin-top: 11px; }

    .button {
      display: inline-flex;
      min-height: 44px;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 0 18px;
      border: 0;
      border-radius: 9px;
      color: #1d2735;
      background: var(--brand);
      box-shadow: 0 4px 12px rgba(16,24,40,.12);
      font-size: .82rem;
      font-weight: 750;
      line-height: 1.2;
      text-align: center;
      text-decoration: none;
      transition: background .18s ease, transform .18s ease;
    }

    .button:hover { transform: translateY(-1px); background: #e6a900; }
    .button__arrow { font-size: 1.15rem; line-height: 1; }
    .campaign__fallback { color: var(--text-muted); font-size: .83rem; font-weight: 650; text-decoration-thickness: 1px; text-underline-offset: 4px; }

    .plans-section { margin-top: 8px; scroll-margin-top: 14px; }
    .plans-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin: 0 0 14px; }
    .plans-heading__copy { min-width: 0; }
    .plans-heading__price { flex: 0 0 auto; padding: 8px 10px; border: 1px solid #f0dfae; border-radius: 9px; color: #71520b; background: #fff8e6; font-size: .66rem; font-weight: 700; white-space: nowrap; }
    .plans-heading h1,
    .plans-heading h2 { margin: 0; color: var(--text-dark); font-size: clamp(1.25rem, 4vw, 1.7rem); font-weight: 750; line-height: 1.15; letter-spacing: -.035em; }
    .plans-heading p { margin: 4px 0 0; color: var(--text-muted); font-size: .78rem; line-height: 1.4; }
    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 225px), 1fr)); gap: 11px; }

    .plan { position: relative; display: flex; min-width: 0; flex-direction: column; padding: 16px; border-radius: 13px; }
    .plan--popular { border-color: #d5a11a; box-shadow: 0 8px 24px rgba(16,24,40,.09); }
    .plan__topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .plan__name { margin: 0; color: var(--text-dark); font-size: .95rem; font-weight: 700; line-height: 1.3; overflow-wrap: anywhere; }
    .plan__badge { flex: 0 0 auto; padding: 4px 7px; border-radius: 5px; color: #72510c; background: #fff4d5; font-size: .62rem; font-weight: 700; }
    .plan__details { display: flex; flex-wrap: wrap; gap: 6px; margin: 11px 0 0; }
    .plan__detail { padding: 5px 7px; border: 1px solid #e8ebf0; border-radius: 5px; color: #475467; background: #f7f8fa; font-size: .68rem; line-height: 1.2; }
    .plan__price { margin: 13px 0 12px; color: var(--text-dark); font-size: 1.55rem; font-weight: 800; letter-spacing: -.04em; line-height: 1; }
    .plan__price span { color: var(--text-muted); font-size: .72rem; font-weight: 650; letter-spacing: 0; }
    .plan__button { width: 100%; min-height: 42px; margin-top: auto; }

    .empty-state { padding: 22px; border: 1px solid var(--muted-border); border-radius: var(--radius-card); background: var(--surface); }
    .empty-state h1 { margin: 0; color: var(--text-dark); font-size: 1.05rem; }
    .empty-state p { margin: 7px 0 0; color: var(--text-muted); font-size: .88rem; line-height: 1.5; }

    .page-note { margin: 16px 0 0; color: var(--text-light); font-size: .67rem; line-height: 1.4; text-align: center; }

    @media (max-width: 420px) {
      body { padding: 12px 12px 20px; }
      .masthead { gap: 8px; margin-bottom: 11px; }
      .brand { gap: 8px; }
      .brand__mark { width: 36px; height: 36px; flex-basis: 36px; }
      .brand__name { font-size: .82rem; }
      .brand__tagline { font-size: .65rem; }
      .store-button { min-height: 36px; padding: 0 10px; font-size: .68rem; }
      .campaign { grid-template-columns: 1fr; }
      .campaign__media { min-height: 82px; height: 27vw; max-height: 110px; padding: 5px; }
      .campaign__content { padding: 12px; }
      .campaign__actions { width: 100%; flex-direction: row; gap: 8px 12px; margin-top: 9px; }
      .campaign__actions .button { min-height: 38px; }
      .campaign__fallback { align-self: center; padding: 4px 0; }
      .plans-grid { grid-template-columns: 1fr; }
      .plans-section { margin-top: 18px; }
      .plan { padding: 14px; }
    }

    @media (max-width: 430px) and (max-height: 680px) {
      body { padding: 8px; line-height: 1.4; }
      .masthead { margin-bottom: 8px; }
      .brand__mark { width: 32px; height: 32px; flex-basis: 32px; }
      .brand__tagline { display: none; }
      .store-button { min-height: 32px; padding: 0 9px; font-size: .64rem; }
      .store-fallback { margin-top: 2px; font-size: .6rem; }
      .plans-heading { align-items: flex-start; gap: 7px; margin-bottom: 10px; }
      .plans-heading h1 { font-size: 1.18rem; }
      .plans-heading p { font-size: .69rem; }
      .plans-heading__price { padding: 6px 7px; font-size: .58rem; }
      .campaign-section { margin-bottom: 13px; }
      .campaign__media { min-height: 68px; height: 68px; max-height: 68px; padding: 3px; }
      .campaign__content { padding: 9px 11px; }
      .campaign__label { gap: 5px; margin-bottom: 5px; font-size: .58rem; }
      .campaign__title { font-size: 1.1rem; line-height: 1.12; }
      .campaign__description { margin-top: 6px; font-size: .75rem; line-height: 1.35; }
      .campaign__actions { gap: 5px 9px; margin-top: 7px; }
      .campaign__actions .button { width: auto; min-height: 34px; padding: 0 10px; font-size: .68rem; }
      .campaign__fallback { padding: 2px 0; font-size: .64rem; }
      .plans-section { margin-top: 0; }
      .plans-heading { margin-bottom: 9px; }
      .plans-heading h1, .plans-heading h2 { font-size: 1.2rem; }
      .plans-heading p { font-size: .72rem; }
      .plan { padding: 12px; }
      .plan__details { margin-top: 8px; gap: 5px; }
      .plan__detail { padding: 4px 6px; font-size: .62rem; }
      .plan__price { margin: 10px 0; font-size: 1.4rem; }
      .plan__button { min-height: 38px; }
      .page-note { margin-top: 6px; font-size: .62rem; }
    }

    @media (min-width: 421px) and (max-width: 820px) and (min-height: 681px) and (max-height: 820px) {
      .campaign__media { height: min(38vw, 300px); max-height: 300px; }
    }

    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      *, *::before, *::after { animation-duration: .01ms !important; transition-duration: .01ms !important; }
    }
  </style>
</head>
<body>
<main class="page">
  <header class="masthead">
    <div class="brand">
      <span class="brand__mark">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="" width="44" height="44">
      </span>
      <div>
        <p class="brand__name">AngolaWiFi</p>
        <p class="brand__tagline">Internet WiFi em Angola</p>
      </div>
    </div>
    <div>
      <button class="store-button" id="open-store" type="button">Abrir loja</button>
      <a class="store-fallback" id="store-fallback-link" href="https://angolawifi.ao/" target="_top">
        Abrir nesta janela
      </a>
    </div>
  </header>

  @if($campaign)
    <section class="campaign-section" aria-labelledby="campaign-section-title">
      <div class="campaign-section__heading">
        <p>Em destaque</p>
        <h2 id="campaign-section-title">Uma oferta para si</h2>
      </div>
      <article class="campaign" data-campaign-id="{{ $campaign->id }}" data-impression-url="{{ $impressionUrl }}">
        <div class="campaign__media">
          <img
            src="{{ route('ads.sol-image', $campaign->id) }}"
            alt="{{ $campaign->title }}"
            width="760"
            loading="lazy"
          >
        </div>
        <div class="campaign__content">
          <p class="campaign__label">
            Publicidade
            @if($campaign->advertiser_name)
              <span class="campaign__advertiser">{{ $campaign->advertiser_name }}</span>
            @endif
          </p>
          <h2 class="campaign__title" id="campaign-title">{{ $campaign->title }}</h2>
          @if($campaign->description)
            <p class="campaign__description">{{ $campaign->description }}</p>
          @endif
          <div class="campaign__actions">
            <a class="button" href="{{ $clickUrl }}" target="_blank" rel="noopener noreferrer">
              {{ $campaign->button_text }}
              <span class="button__arrow" aria-hidden="true">→</span>
            </a>
            <a class="campaign__fallback" href="{{ $clickUrl }}" target="_top">Abrir nesta janela</a>
          </div>
        </div>
      </article>
    </section>
  @endif

  @if($plans->isNotEmpty())
    <section class="plans-section" id="planos" aria-labelledby="plans-title">
      <div class="plans-heading">
        <div class="plans-heading__copy">
          <p class="eyebrow">AngolaWiFi · Internet à sua medida</p>
          <h1 id="plans-title">Escolha o seu plano WiFi</h1>
          <p>Escolha uma opção e comece a navegar.</p>
        </div>
        @php($startingPlan = $plans->sortBy('price_public_aoa')->first())
        @if($startingPlan)
          <span class="plans-heading__price">Desde {{ number_format($startingPlan->price_public_aoa, 0, ',', '.') }} Kz</span>
        @endif
      </div>
      <div class="plans-grid">
        @foreach($plans as $plan)
          <article class="plan{{ $plan->slug === 'semanal' ? ' plan--popular' : '' }}">
            <div class="plan__topline">
              <h2 class="plan__name">{{ $plan->name }}</h2>
              @if($plan->slug === 'semanal')
                <span class="plan__badge">Mais popular</span>
              @endif
            </div>
            <div class="plan__details" aria-label="Detalhes do plano">
              @if($plan->validity_label)
                <span class="plan__detail">Duração · {{ $plan->validity_label }}</span>
              @endif
              @if($plan->speed_label)
                <span class="plan__detail">Velocidade · {{ $plan->speed_label }}</span>
              @endif
            </div>
            <p class="plan__price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} <span>Kz</span></p>
            <a class="button plan__button" href="{{ route('store.checkout', ['plan' => $plan->slug]) }}" target="_top">
              Comprar Agora
              <span class="button__arrow" aria-hidden="true">→</span>
            </a>
          </article>
        @endforeach
      </div>
    </section>
  @else
    <section class="empty-state" aria-labelledby="empty-title">
      <h1 id="empty-title">Planos temporariamente indisponíveis</h1>
      <p>Aceda à loja AngolaWiFi para consultar as opções disponíveis.</p>
    </section>
  @endif

  <p class="page-note">AngolaWiFi</p>
</main>
<script src="{{ asset('js/sol-ads.js') }}" defer></script>
</body>
</html>
