<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LuandaWiFi</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="dashboard-container">
        <img src="{{ asset('img/logo.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Dashboard Administrativo</h1>
        <p>Bem-vindo ao painel de gestão de clientes e planos.</p>
        <div class="dashboard-actions">
            <a href="#" class="btn">Clientes</a>
            <a href="#" class="btn">Planos</a>
            <a href="#" class="btn">Alertas</a>
            <form action="{{ route('logout') }}" method="POST" style="width:100%;margin:0;padding:0;">
                @csrf
                <button type="submit" class="btn btn-logout" style="width:100%;">Sair</button>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/main.js') }}"></script>
</body>
</html>
