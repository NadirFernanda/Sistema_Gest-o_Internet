<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name', 'Loja') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Non-blocking font load: page renders immediately; Inter applies when ready --}}
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"></noscript>
    {{-- Carregar CSS de forma mutuamente exclusiva: Vite quando disponivel, fallback caso contrario. --}}
    @if (file_exists(public_path('build/manifest.json')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @else
      <link rel="stylesheet" href="{{ asset('css/fallback.css') }}?v={{ filemtime(public_path('css/fallback.css')) }}">
    @endif
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

