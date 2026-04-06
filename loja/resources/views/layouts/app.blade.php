<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    {{-- ── SEO: título, descrição e robots ─────────────────────────────── --}}
    @php
      $seoTitle  = trim(View::yieldContent('seo_title'))
                ?: trim(strip_tags(View::yieldContent('title')))
                ?: config('app.name', 'AngolaWiFi');
      $seoDesc   = trim(View::yieldContent('seo_description'))
                ?: 'AngolaWiFi — Internet WiFi em Angola. Planos residenciais, familiares e empresariais a partir de 200 Kz. Instalação em 48h, velocidades reais, suporte local.';
      $ogImage   = trim(View::yieldContent('og_image')) ?: asset('img/carrossel1.webp');
      $canonical = request()->url();
    @endphp

    <title>{{ $seoTitle }}</title>
    <meta name="description"          content="{{ $seoDesc }}">
    <link rel="canonical"             href="{{ $canonical }}">
    <meta name="robots"               content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type"          content="website">
    <meta property="og:site_name"     content="{{ config('app.name', 'AngolaWiFi') }}">
    <meta property="og:title"         content="{{ $seoTitle }}">
    <meta property="og:description"   content="{{ $seoDesc }}">
    <meta property="og:url"           content="{{ $canonical }}">
    <meta property="og:image"         content="{{ $ogImage }}">
    <meta property="og:locale"        content="pt_AO">

    {{-- Twitter Card --}}
    <meta name="twitter:card"         content="summary_large_image">
    <meta name="twitter:title"        content="{{ $seoTitle }}">
    <meta name="twitter:description"  content="{{ $seoDesc }}">
    <meta name="twitter:image"        content="{{ $ogImage }}">

    {{-- Dados estruturados e overrides por página --}}
    @stack('seo')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Carregamento não-bloqueante das fontes — evita ERR_SOCKET_NOT_CONNECTED bloquear o render --}}
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"></noscript>
    {{-- CSS principal — sempre carregado de public/css/fallback.css (fonte de verdade).
         O Vite trata apenas do JS. Desta forma um novo build nunca destrói o CSS. --}}
    <link rel="stylesheet" href="{{ asset('css/fallback.css') }}?v={{ filemtime(public_path('css/fallback.css')) }}">
    @if (file_exists(public_path('build/manifest.json')))
      @vite(['resources/js/app.js'])
    @endif
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    {{-- Preload da primeira imagem do carrossel (LCP) --}}
    <link rel="preload" as="image" href="/img/carrossel1.webp" type="image/webp" fetchpriority="high">
  </head>
  <body>
    @include('partials.header')
    <main>
      @yield('content')
    </main>
    @include('partials.footer')
    @stack('styles')
    @stack('scripts')
  </body>
</html>

