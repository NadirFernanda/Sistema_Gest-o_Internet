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
      background: linear-gradient(145deg, #1d2546 0%, #747b91 55%, #1d2546 100%);
      line-height: 1.5;
    }

    a { color: inherit; }

    a:focus-visible,
    button:focus-visible {
      outline: 3px solid #2563EB;
      outline-offset: 3px;
    }

    .page { width: min(100%, 760px); margin: 0 auto; padding: clamp(18px, 4vw, 34px); border-radius: 24px; background: #fff; box-shadow: 0 24px 55px rgba(5,20,65,.25); }

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
    .brand__name { margin: 0; color: #071955; font-size: 1.2rem; font-weight: 800; letter-spacing: -.035em; }
    .brand__tagline { margin: 2px 0 0; color: var(--text-light); font-size: .7rem; }

    .motion-ready .plans-heading { animation: hero-enter .5s cubic-bezier(.2,.7,.25,1) both; }
    .motion-ready .reveal-on-scroll {
      opacity: 0;
      transform: translateY(14px);
      transition: opacity .45s ease, transform .45s cubic-bezier(.2,.7,.25,1);
    }
    .motion-ready .reveal-on-scroll.is-visible { opacity: 1; transform: translateY(0); }

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
    .plans-heading h2 { margin: 0; color: #071955; font-size: clamp(1.9rem, 6vw, 3.4rem); font-weight: 850; line-height: 1.07; letter-spacing: -.055em; }
    .plans-heading p { margin: 4px 0 0; color: var(--text-muted); font-size: .78rem; line-height: 1.4; }
    .plans-grid { display: grid; grid-template-columns: 1fr; gap: 11px; }

    .plan { position: relative; display: flex; min-width: 0; flex-direction: column; padding: clamp(20px, 4vw, 34px); border: 0; border-radius: 22px; background: linear-gradient(150deg, #098ee9 0%, #0758c4 42%, #072e91 100%); box-shadow: 0 14px 28px rgba(0,68,181,.24); }
    .plan__topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .plan__name { margin: 0; color: #fff; font-size: clamp(1.35rem, 4vw, 2rem); font-weight: 800; line-height: 1.2; overflow-wrap: anywhere; }
    .plan__details { display: flex; flex-wrap: wrap; gap: 6px; margin: 11px 0 0; }
    .plan__detail { padding: 6px 9px; border: 1px solid rgba(255,255,255,.35); border-radius: 6px; color: #fff; background: rgba(255,255,255,.14); font-size: .72rem; line-height: 1.2; }
    .plan__price { margin: 15px 0 20px; color: #fff; font-size: clamp(3.5rem, 11vw, 6.5rem); font-weight: 850; letter-spacing: -.07em; line-height: 1; text-shadow: 0 4px 12px rgba(0,24,96,.25); }
    .plan__price span { color: #ffcf18; font-size: .5em; font-weight: 800; letter-spacing: 0; }
    .plan__button { width: 100%; min-height: 54px; margin-top: auto; border-radius: 12px; color: #071955; background: linear-gradient(180deg, #ffe528, #ffb800); font-size: 1rem; }
    .more-options { display: flex; width: 100%; min-height: 46px; align-items: center; justify-content: center; gap: 10px; margin-top: 12px; border: 2px solid #0755ba; border-radius: 10px; color: #0755ba; background: #fff; font-size: .84rem; font-weight: 750; text-align: center; text-decoration: none; }
    .more-options:hover { background: #eff6ff; }

    .empty-state { padding: 22px; border: 1px solid var(--muted-border); border-radius: var(--radius-card); background: var(--surface); }
    .empty-state h1 { margin: 0; color: var(--text-dark); font-size: 1.05rem; }
    .empty-state p { margin: 7px 0 0; color: var(--text-muted); font-size: .88rem; line-height: 1.5; }

    .page-note { margin: 25px -8px -8px; padding: 17px; border-radius: 0 0 18px 18px; color: #fff; background: linear-gradient(110deg, #049cea, #07399d); font-size: .84rem; font-style: italic; font-weight: 750; line-height: 1.4; text-align: right; }

    @media (max-width: 420px) {
      body { padding: 12px 12px 20px; }
      .page { padding: 15px; border-radius: 18px; }
      .masthead { gap: 8px; margin-bottom: 11px; }
      .brand { gap: 8px; }
      .brand__mark { width: 36px; height: 36px; flex-basis: 36px; }
      .brand__name { font-size: 1rem; }
      .brand__tagline { font-size: .65rem; }
      .campaign { grid-template-columns: 1fr; }
      .campaign__media { min-height: 82px; height: 27vw; max-height: 110px; padding: 5px; }
      .campaign__content { padding: 12px; }
      .campaign__actions { width: 100%; flex-direction: row; gap: 8px 12px; margin-top: 9px; }
      .campaign__actions .button { min-height: 38px; }
      .campaign__fallback { align-self: center; padding: 4px 0; }
      .plans-grid { grid-template-columns: 1fr; }
      .plans-section { margin-top: 18px; }
      .plan { padding: 20px; }
    }

    @media (max-width: 430px) and (max-height: 680px) {
      body { padding: 8px; line-height: 1.4; }
      .masthead { margin-bottom: 8px; }
      .brand__mark { width: 32px; height: 32px; flex-basis: 32px; }
      .brand__tagline { display: none; }
      .plans-heading { align-items: flex-start; gap: 7px; margin-bottom: 10px; }
      .plans-heading h1 { font-size: 1.8rem; }
      .plans-heading p { font-size: .69rem; }
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
      .plan { padding: 16px; }
      .plan__details { margin-top: 8px; gap: 5px; }
      .plan__detail { padding: 4px 6px; font-size: .62rem; }
      .plan__price { margin: 10px 0; font-size: 3.5rem; }
      .plan__button { min-height: 38px; }
      .more-options { min-height: 40px; margin-top: 8px; }
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

  @if($dailyPlan)
    <section class="plans-section" id="planos" aria-labelledby="plans-title">
      <div class="plans-heading">
        <div class="plans-heading__copy">
          <h1 id="plans-title">Navegue 24 horas sem limites</h1>
        </div>
      </div>
      <div class="plans-grid">
          <article class="plan">
            <div class="plan__topline">
              <h2 class="plan__name">{{ $dailyPlan->name }}</h2>
            </div>
            <div class="plan__details" aria-label="Detalhes do plano">
              @if($dailyPlan->validity_label)
                <span class="plan__detail">Duração · {{ $dailyPlan->validity_label }}</span>
              @endif
              @if($dailyPlan->speed_label)
                <span class="plan__detail">Velocidade · {{ $dailyPlan->speed_label }}</span>
              @endif
            </div>
            <p class="plan__price">{{ number_format($dailyPlan->price_public_aoa, 0, ',', '.') }} <span>Kz</span></p>
            <a class="button plan__button" href="{{ route('store.checkout', ['plan' => $dailyPlan->slug]) }}" target="_top">
              Comprar Agora
              <span class="button__arrow" aria-hidden="true">→</span>
            </a>
          </article>
      </div>
      <a class="more-options" href="{{ url('/').'#planos' }}" target="_top">Ver Mais Opções <span aria-hidden="true">⌄</span></a>
    </section>
  @else
    <section class="empty-state" aria-labelledby="empty-title">
      <h1 id="empty-title">Planos temporariamente indisponíveis</h1>
      <p>Aceda à loja AngolaWiFi para consultar as opções disponíveis.</p>
      <a class="more-options" href="{{ url('/').'#planos' }}" target="_top">Ver Mais Opções <span aria-hidden="true">⌄</span></a>
    </section>
  @endif

  <p class="page-note">AngolaWiFi – Sempre consigo!</p>
</main>
<script src="{{ asset('js/sol-ads.js') }}" defer></script>
</body>
</html>
