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
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Dashboard Administrativo</h1>
        <p>Bem-vindo ao painel de gestão de clientes e planos.</p>
        @auth
        @php $activeRole = session('acting_as_role') ?: (auth()->user()->getRoleNames()->first() ?? '—'); @endphp
        <div class="active-role-banner">Atuando como: <strong>{{ $activeRole }}</strong></div>
        @endauth
        <div class="dashboard-actions">
            <a href="{{ route('clientes') }}" class="btn">Clientes</a>
            <a href="{{ route('planos.index') }}" class="btn">Planos</a>
            <a href="{{ route('alertas') }}" class="btn">Alertas</a>
            <a href="{{ route('estoque_equipamentos.index') }}" class="btn">Estoque de Equipamentos</a>

            <div class="dropdown" style="display:block;position:relative;width:100%;">
                <button class="btn" id="relatoriosBtn" type="button" style="width:100%;">Relatórios ▼</button>
                <div id="relatoriosMenu" style="display:none;position:absolute;left:0;top:100%;background:#fff;border:1px solid #ccc;z-index:10;min-width:180px;box-shadow:0 2px 8px rgba(0,0,0,0.08);width:100%;">
                    <a href="{{ route('cobrancas.index') }}" class="btn" style="display:block;text-align:left;border-radius:0;padding:8px 12px;">Cobrança</a>
                </div>
            </div>

            @can('users.create')
                <a href="{{ route('admin.users.create') }}" class="btn btn-create-user">Criar utilizador</a>
            @endcan

            <!-- Button to change password -->
            <a href="{{ route('password.change') }}" class="btn btn-primary" style="width:100%;">Alterar senha</a>

            <form action="{{ route('logout') }}" method="POST" style="width:100%;margin:0;padding:0;">
                @csrf
                <button type="submit" class="btn btn-logout" style="width:100%;">Sair</button>
            </form>
        </div>
    </div>
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
    // Dropdown simples para relatórios
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('relatoriosBtn');
        var menu = document.getElementById('relatoriosMenu');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', function(e) {
            if (!btn.contains(e.target) && !menu.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>
