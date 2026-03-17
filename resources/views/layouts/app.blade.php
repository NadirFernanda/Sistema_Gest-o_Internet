<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestão</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF token for JS (used by modal delete fallbacks and AJAX) --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    {{-- Vite-built assets (CSS/JS) — app.css includes project styles and Choices.js overrides --}}

    {{-- Carregar assets apenas se não for a tela de login --}}
    @if (!request()->is('login'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @php $styleVer = file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : 1; @endphp
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $styleVer }}">
    @else
        {{-- Apenas o mínimo necessário para o login --}}
        @php $styleVer = file_exists(public_path('css/style.css')) ? filemtime(public_path('css/style.css')) : 1; @endphp
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $styleVer }}">
    @endif

    @stack('styles')
</head>
<body>

    {{-- Header partial (shows user info, role badge, etc.) --}}
    @include('layouts.partials.header')

    @auth
        <main style="min-height: 100vh;">
            @yield('content')
        </main>
    @else
        <main style="min-height: 100vh;">
            @yield('content')
        </main>
    @endauth

    @if (!request()->is('login'))
        <script src="{{ asset('js/main.js') }}"></script>
    @endif

    @stack('scripts')
</body>
</html>
