<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ config('app.name', 'Loja') }}</title>
    <!-- Always include fallback.css first to avoid layout breakage when Vite assets are unavailable -->
    <link rel="stylesheet" href="{{ asset('css/fallback.css') }}?v={{ file_exists(public_path('css/fallback.css')) ? filemtime(public_path('css/fallback.css')) : time() }}">
    @if (file_exists(public_path('build/manifest.json')))
      @vite(['resources/css/app.css','resources/js/app.js'])
    @endif
    <!-- Force header layout overrides (important to override app.css if present) -->
    <style>
      /* Ensure header inner uses the same centered container width as other sections */
      .store-header-inner{ display:flex !important; justify-content:space-between !important; padding-left:var(--page-gutter,1rem) !important; padding-right:var(--page-gutter,1rem) !important; margin:0 auto !important; max-width:var(--page-max-width,1100px) !important; width:100% !important; box-sizing:border-box !important; }
      .store-brand{ order:1 !important; }
      .store-right{ order:2 !important; margin-left:0 !important; }
      .store-nav{ order:1 !important; flex:0 0 auto !important; }
      .store-actions{ order:2 !important; margin-left:8px !important; }
      .search-wrapper{ flex:0 0 320px !important; max-width:360px !important; }
    </style>
  </head>
  <body class="bg-gray-50 text-gray-900 antialiased">
    @include('partials.header')
    <main class="container mx-auto py-8">
      @yield('content')
    </main>
    @include('partials.footer')
  </body>
</html>

