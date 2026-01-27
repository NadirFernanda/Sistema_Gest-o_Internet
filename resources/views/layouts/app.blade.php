<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Gestão</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    @stack('styles')
</head>
<body>


    @auth
        <main style="min-height: 100vh;">
            @yield('content')
        </main>
    @else
        <main style="min-height: 100vh;">
            @yield('content')
        </main>
    @endauth

    <script src="{{ asset('js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>
