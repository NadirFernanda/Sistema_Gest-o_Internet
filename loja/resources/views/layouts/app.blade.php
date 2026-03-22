<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name', 'Loja') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Carregamento não-bloqueante das fontes — evita ERR_SOCKET_NOT_CONNECTED bloquear o render --}}
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"></noscript>
    {{-- CSS: sempre do fallback.css (contém todo o CSS custom). O Tailwind do Vite não é carregado para não conflituar. --}}
    <link rel="stylesheet" href="{{ asset('css/fallback.css') }}?v={{ filemtime(public_path('css/fallback.css')) }}">
    {{-- JS via Vite (carrossel, dropdowns, etc.) --}}
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

