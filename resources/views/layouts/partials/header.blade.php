<header>
    <h1>Sistema de Gestão</h1>

    @auth
    <div class="user-menu">
        <div class="user-actions">
            <a href="{{ route('password.change') }}">Alterar senha</a>
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit" class="link-button">Sair</button>
            </form>
        </div>
    </div>
    @endauth

</header>
