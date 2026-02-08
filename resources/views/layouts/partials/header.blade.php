<header>
    <h1>Sistema de Gestão</h1>

    @auth
    <div class="user-menu">
        <div class="user-info">
            <span class="user-email">{{ auth()->user()->email }}</span>
            @php
                $activeRole = session('acting_as_role') ?: (auth()->user()->getRoleNames()->first() ?? null);
            @endphp
            @if($activeRole)
                <span class="role-active">Role: <span class="role-badge">{{ $activeRole }}</span></span>
            @endif
        </div>

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
