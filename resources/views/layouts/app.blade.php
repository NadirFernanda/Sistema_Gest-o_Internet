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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Cache-busting query to force browsers to fetch updated CSS after deploy (legacy styles) --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

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


    @stack('scripts')
</body>
</html>
