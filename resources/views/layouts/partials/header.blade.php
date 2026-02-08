<header>
    <h1>Sistema de Gestão</h1>

    @auth
    <div class="user-menu" style="float:right">
        <span>{{ auth()->user()->email }}</span>
        &nbsp;|&nbsp;
        <a href="{{ route('password.change') }}">Alterar senha</a>
        &nbsp;|&nbsp;
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:inherit;cursor:pointer;padding:0">Sair</button>
        </form>
    </div>
    @endauth

</header>
