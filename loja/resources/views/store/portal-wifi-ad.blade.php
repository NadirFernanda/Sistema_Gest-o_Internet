<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Planos WiFi AngolaWiFi</title>
  <style>
    :root { color-scheme: light; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 1rem; color: #172033; background: #f4f6f9; }
    main { width: min(100%, 760px); margin: 0 auto; }
    header, article { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; }
    header { display: flex; align-items: center; gap: .85rem; padding: 1rem; margin-bottom: 1rem; }
    header img { width: 48px; height: 48px; object-fit: cover; border-radius: 9px; }
    h1 { margin: 0; font-size: 1.2rem; }
    header p { margin: .2rem 0 0; color: #64748b; font-size: .9rem; }
    .store-action { margin: 1rem 0; text-align: center; }
    .store-action button { border: 0; cursor: pointer; font: inherit; }
    .store-fallback { display: block; margin-top: .55rem; color: #334155; font-size: .9rem; text-decoration: underline; }
    section { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 205px), 1fr)); gap: .75rem; }
    article { padding: 1rem; }
    h2 { margin: 0 0 .55rem; font-size: 1rem; }
    .detail { margin: .25rem 0; color: #475569; font-size: .9rem; }
    .price { margin: .75rem 0; font-size: 1.2rem; font-weight: 800; }
    a { display: inline-block; padding: .65rem .85rem; border-radius: 8px; color: #172033; background: #f7b500; font-weight: 700; text-decoration: none; }
    a:focus-visible { outline: 3px solid #2563eb; outline-offset: 2px; }
    footer { margin-top: 1rem; text-align: center; font-size: .9rem; }
    @media (max-width: 360px) { body { padding: .65rem; } }
  </style>
</head>
<body>
<main>
  <header>
    <img src="{{ asset('img/logo2.jpeg') }}" alt="" width="48" height="48">
    <div>
      <h1>Internet WiFi AngolaWiFi</h1>
      <p>Escolha um plano e continue na loja segura.</p>
    </div>
  </header>

  <div class="store-action">
    <button class="store-button" id="open-store" type="button">VER A LOJA</button>
    <a class="store-fallback" id="store-fallback-link" href="https://angolawifi.ao/" target="_top">
      Abrir nesta janela
    </a>
  </div>

  <section aria-label="Planos disponíveis">
    @forelse($plans as $plan)
      <article>
        <h2>{{ $plan->name }}</h2>
        @if($plan->validity_label)
          <p class="detail">Duração: {{ $plan->validity_label }}</p>
        @endif
        @if($plan->speed_label)
          <p class="detail">Velocidade: {{ $plan->speed_label }}</p>
        @endif
        <p class="price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} Kz</p>
        <a href="{{ route('store.checkout', ['plan' => $plan->slug]) }}" target="_top">
          Ver plano e comprar
        </a>
      </article>
    @empty
      <article>
        <h2>Planos temporariamente indisponíveis</h2>
        <p class="detail">Aceda à loja para consultar as opções disponíveis.</p>
      </article>
    @endforelse
  </section>

  <footer>
    <a href="{{ url('/') }}" target="_top">Abrir loja AngolaWiFi</a>
  </footer>
</main>
<script src="{{ asset('js/sol-ads.js') }}" defer></script>
</body>
</html>
