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
      --brand: #f7b500;
      --brand-dark: #d4980a;
      --text-dark: #0F172A;
      --text-muted: #475569;
      --text-light: #64748b;
      --surface: #FFFFFF;
      --surface-alt: #f1f5f9;
      --muted-border: #E2E8F0;
      --radius-card: 1.125rem;
      --shadow-sm: 0 1px 3px rgba(15,23,42,.05), 0 6px 16px rgba(15,23,42,.05);
      --shadow-brand: 0 8px 32px rgba(247,181,0,.18);
    }

    * { box-sizing: border-box; }

    body {
      min-width: 280px;
      min-height: 100vh;
      margin: 0;
      padding: clamp(12px, 3vw, 28px);
      background:
        radial-gradient(ellipse at 50% -18%, rgba(247, 181, 0, .1), transparent 38rem),
        #F8FAFC;
      line-height: 1.7;
    }

    a { color: inherit; }

    a:focus-visible,
    button:focus-visible {
      outline: 3px solid #2563EB;
      outline-offset: 3px;
    }

    .page { width: min(100%, 820px); margin: 0 auto; }

    .masthead {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin: 0 0 18px;
    }

    .brand { display: flex; align-items: center; gap: 11px; min-width: 0; }

    .brand__mark {
      display: grid;
      width: 44px;
      height: 44px;
      flex: 0 0 44px;
      place-items: center;
      overflow: hidden;
      border: 1px solid rgba(226, 232, 240, .65);
      border-radius: 8px;
      background: var(--surface);
      box-shadow: var(--shadow-sm);
    }

    .brand__mark img { display: block; width: 100%; height: 100%; object-fit: cover; }
    .brand__name { margin: 0; color: var(--text-dark); font-size: .96rem; font-weight: 800; letter-spacing: -.025em; }
    .brand__tagline { margin: 3px 0 0; color: var(--text-light); font-size: .76rem; }

    .store-button {
      min-height: 42px;
      padding: 0 15px;
      border: 1px solid var(--muted-border);
      border-radius: 999px;
      color: var(--text-dark);
      background: var(--surface);
      box-shadow: var(--shadow-sm);
      cursor: pointer;
      font: inherit;
      font-size: .76rem;
      font-weight: 850;
      letter-spacing: .045em;
      white-space: nowrap;
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }

    .store-button:hover { transform: translateY(-1px); border-color: rgba(247, 181, 0, .5); box-shadow: var(--shadow-brand); }

    .store-fallback {
      display: block;
      margin-top: 6px;
      color: var(--text-muted);
      font-size: .72rem;
      text-align: center;
      text-decoration-thickness: 1px;
      text-underline-offset: 3px;
    }

    .intro { margin: 0 0 15px; }
    .eyebrow { margin: 0 0 6px; color: #78350f; font-size: .7rem; font-weight: 850; letter-spacing: .12em; text-transform: uppercase; }
    .intro h1 { margin: 0; color: var(--text-dark); font-size: clamp(1.35rem, 5vw, 2rem); line-height: 1.12; letter-spacing: -.045em; }
    .intro__copy { max-width: 58ch; margin: 7px 0 0; color: var(--text-muted); font-size: .91rem; line-height: 1.55; }

    .campaign,
    .plan {
      overflow: hidden;
      border: 1.5px solid rgba(226, 232, 240, .65);
      border-radius: var(--radius-card);
      background: var(--surface);
      box-shadow: var(--shadow-sm);
    }

    .campaign { border-top: 3px solid var(--brand); }
    .campaign__media {
      display: grid;
      grid-template-columns: minmax(0, 1fr);
      min-width: 0;
      min-height: 170px;
      height: min(48vw, 350px);
      max-height: 350px;
      place-items: center;
      overflow: hidden;
      padding: 8px;
      background: #f8fafc;
    }

    .campaign__media img { display: block; width: 100%; max-width: 100%; min-width: 0; height: 100%; object-fit: contain; }
    .campaign__content { padding: clamp(18px, 4vw, 30px); }
    .campaign__label { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin: 0 0 11px; color: #78350f; font-size: .7rem; font-weight: 850; letter-spacing: .1em; text-transform: uppercase; }
    .campaign__label::before { width: 7px; height: 7px; border-radius: 50%; background: var(--brand); content: ""; }
    .campaign__advertiser { color: var(--text-light); font-weight: 700; letter-spacing: .02em; }
    .campaign__title { max-width: 24ch; margin: 0; color: var(--text-dark); font-size: clamp(1.5rem, 5.4vw, 2.45rem); line-height: 1.08; letter-spacing: -.045em; overflow-wrap: anywhere; }
    .campaign__description { max-width: 62ch; margin: 12px 0 0; color: var(--text-muted); font-size: .97rem; line-height: 1.65; overflow-wrap: anywhere; }
    .campaign__actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px 18px; margin-top: 22px; }

    .button {
      display: inline-flex;
      min-height: 49px;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 0 21px;
      border: 0;
      border-radius: 8px;
      color: #111827;
      background: var(--brand);
      box-shadow: 0 4px 16px rgba(247,181,0,.28);
      font-size: .9rem;
      font-weight: 850;
      line-height: 1.2;
      text-align: center;
      text-decoration: none;
      transition: transform .18s ease, box-shadow .18s ease;
    }

    .button:hover { transform: translateY(-2px); background: #e0a800; box-shadow: 0 8px 24px rgba(247,181,0,.32); }
    .button__arrow { font-size: 1.15rem; line-height: 1; }
    .campaign__fallback { color: var(--text-muted); font-size: .83rem; font-weight: 650; text-decoration-thickness: 1px; text-underline-offset: 4px; }

    .plans-heading { margin: 18px 0 13px; }
    .plans-heading h1 { margin: 0; color: var(--text-dark); font-size: clamp(1.4rem, 5vw, 1.9rem); line-height: 1.15; letter-spacing: -.04em; }
    .plans-heading p { margin: 6px 0 0; color: var(--text-muted); font-size: .9rem; line-height: 1.5; }
    .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 220px), 1fr)); gap: 12px; }

    .plan { display: flex; min-width: 0; flex-direction: column; padding: 18px; border-radius: var(--radius-card); }
    .plan__topline { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
    .plan__name { margin: 0; color: var(--text-dark); font-size: 1.04rem; font-weight: 800; line-height: 1.3; overflow-wrap: anywhere; }
    .plan__badge { flex: 0 0 auto; padding: 5px 8px; border: 1px solid rgba(247,181,0,.3); border-radius: .35rem; color: #78350f; background: rgba(247,181,0,.1); font-size: .65rem; font-weight: 800; }
    .plan__details { display: flex; flex-wrap: wrap; gap: 7px; margin: 14px 0 0; }
    .plan__detail { padding: 7px 9px; border: 1px solid rgba(226,232,240,.55); border-radius: .35rem; color: var(--text-muted); background: #f1f5f9; font-size: .76rem; line-height: 1.2; }
    .plan__price { margin: 16px 0 14px; color: var(--text-dark); font-size: 1.58rem; font-weight: 900; letter-spacing: -.04em; }
    .plan__price span { color: var(--text-muted); font-size: .77rem; font-weight: 700; letter-spacing: 0; }
    .plan__button { width: 100%; min-height: 44px; margin-top: auto; }

    .empty-state { padding: 22px; border: 1px solid var(--muted-border); border-radius: var(--radius-card); background: var(--surface); }
    .empty-state h1 { margin: 0; color: var(--text-dark); font-size: 1.05rem; }
    .empty-state p { margin: 7px 0 0; color: var(--text-muted); font-size: .88rem; line-height: 1.5; }

    .page-note { margin: 15px 0 0; color: var(--text-light); font-size: .72rem; line-height: 1.45; text-align: center; }

    @media (max-width: 420px) {
      body { padding: 12px; }
      .masthead { gap: 8px; margin-bottom: 15px; }
      .brand { gap: 8px; }
      .brand__mark { width: 39px; height: 39px; flex-basis: 39px; border-radius: 8px; }
      .brand__name { font-size: .84rem; }
      .brand__tagline { font-size: .68rem; }
      .store-button { min-height: 39px; padding: 0 11px; font-size: .68rem; }
      .campaign__media { min-height: 150px; height: 52vw; }
      .campaign__actions { align-items: stretch; flex-direction: column; }
      .campaign__actions .button { width: 100%; }
      .campaign__fallback { align-self: center; padding: 4px; }
      .plans-grid { grid-template-columns: 1fr; }
      .plan { padding: 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
      *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
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
      <button class="store-button" id="open-store" type="button">VER A LOJA</button>
      <a class="store-fallback" id="store-fallback-link" href="https://angolawifi.ao/" target="_top">
        Abrir nesta janela
      </a>
    </div>
  </header>

  @if($campaign)
    <section aria-labelledby="campaign-title">
      <div class="intro">
        <p class="eyebrow">Descubra algo especial</p>
        <h1>Uma oportunidade para si</h1>
        <p class="intro__copy">Conheça esta oferta apresentada pela AngolaWiFi.</p>
      </div>

      <article class="campaign" data-campaign-id="{{ $campaign->id }}" data-impression-url="{{ $impressionUrl }}">
        <div class="campaign__media">
          <img
            src="{{ route('ads.sol-image', $campaign->id) }}"
            alt="{{ $campaign->title }}"
            width="760"
            loading="eager"
            fetchpriority="high"
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
  @elseif($plans->isNotEmpty())
    <section aria-labelledby="plans-title">
      <div class="plans-heading">
        <p class="eyebrow">Conecte-se à sua maneira</p>
        <h1 id="plans-title">Escolha o seu plano WiFi</h1>
        <p>Internet rápida e simples, com opções para o seu dia.</p>
      </div>
      <div class="plans-grid">
        @foreach($plans as $plan)
          <article class="plan">
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
