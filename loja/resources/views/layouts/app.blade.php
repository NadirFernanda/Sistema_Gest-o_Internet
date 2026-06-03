<!DOCTYPE html>
<html lang="pt">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ── SEO: título, descrição, OG, Twitter ────────────────────────── --}}
    @if($__env->hasSection('seo_title'))
      <title>@yield('seo_title')</title>
    @elseif($__env->hasSection('title'))
      <title>@yield('title')</title>
    @else
      <title>{{ config('app.name', 'AngolaWiFi') }}</title>
    @endif

    @if($__env->hasSection('seo_description'))
      <meta name="description" content="@yield('seo_description')">
    @else
      <meta name="description" content="AngolaWiFi — Internet WiFi em Angola. Planos residenciais, familiares e empresariais a partir de 200 Kz. Instalação em 48h, velocidades reais, suporte local.">
    @endif

    <link rel="canonical" href="{{ request()->url() }}">
    <meta name="robots" content="index, follow">

    {{-- Open Graph --}}
    <meta property="og:type"      content="website">
    <meta property="og:site_name" content="{{ config('app.name', 'AngolaWiFi') }}">
    <meta property="og:url"       content="{{ request()->url() }}">
    <meta property="og:locale"    content="pt_AO">
    @if($__env->hasSection('seo_title'))
      <meta property="og:title"       content="@yield('seo_title')">
    @elseif($__env->hasSection('title'))
      <meta property="og:title"       content="@yield('title')">
    @else
      <meta property="og:title"       content="{{ config('app.name', 'AngolaWiFi') }}">
    @endif
    @if($__env->hasSection('seo_description'))
      <meta property="og:description" content="@yield('seo_description')">
    @else
      <meta property="og:description" content="AngolaWiFi — Internet WiFi em Angola. Planos residenciais, familiares e empresariais a partir de 200 Kz. Instalação em 48h, velocidades reais, suporte local.">
    @endif
    @if($__env->hasSection('og_image'))
      <meta property="og:image" content="@yield('og_image')">
    @else
      <meta property="og:image" content="{{ asset('img/carrossel1.webp') }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    @if($__env->hasSection('seo_title'))
      <meta name="twitter:title"       content="@yield('seo_title')">
    @elseif($__env->hasSection('title'))
      <meta name="twitter:title"       content="@yield('title')">
    @else
      <meta name="twitter:title"       content="{{ config('app.name', 'AngolaWiFi') }}">
    @endif
    @if($__env->hasSection('seo_description'))
      <meta name="twitter:description" content="@yield('seo_description')">
    @else
      <meta name="twitter:description" content="AngolaWiFi — Internet WiFi em Angola. Planos residenciais, familiares e empresariais a partir de 200 Kz. Instalação em 48h, velocidades reais, suporte local.">
    @endif
    @if($__env->hasSection('og_image'))
      <meta name="twitter:image" content="@yield('og_image')">
    @else
      <meta name="twitter:image" content="{{ asset('img/carrossel1.webp') }}">
    @endif

    {{-- Dados estruturados por página --}}
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
    @include('partials.cookie-consent')
    @stack('styles')
    @stack('scripts')
  </body>
</html>

