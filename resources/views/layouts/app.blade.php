<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('page-title', 'SGAW') — AngolaWiFi</title>

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicons/favicon-16x16.png">
    <link rel="manifest" href="/favicons/site.webmanifest">

    @if (!request()->is('login'))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ config('app.asset_version', '1') }}">
    @else
        <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ config('app.asset_version', '1') }}">
    @endif

    @stack('styles')

    @if (!request()->is('login'))
    <style>
        /* ── Layout shell ── */
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        /* ── Sidebar ── */
        .sg-sidebar {
            width: 230px;
            height: 100vh;
            background: #1a1e3a;
            display: flex;
            flex-direction: column;
            position: fixed;
            left: 0; top: 0; bottom: 0;
            z-index: 200;
            overflow: hidden;
        }
        .sg-sb-logo {
            padding: 20px 18px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .sg-sb-logo img { width: 38px; height: 38px; border-radius: 7px; object-fit: cover; flex-shrink: 0; }
        .sg-sb-logo .sb-name { color: #F5A800; font-size: 1.08em; font-weight: 800; letter-spacing: -.3px; line-height: 1.1; }
        .sg-sb-logo .sb-tag  { color: rgba(255,255,255,.4); font-size: .68em; margin-top: 2px; }
        .sg-sb-nav { flex: 1; padding: 10px 0; overflow-y: auto; }
        .sg-nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            color: rgba(255,255,255,.58);
            text-decoration: none;
            font-size: .86em;
            transition: background .13s, color .13s;
            border-right: 3px solid transparent;
            white-space: nowrap;
        }
        .sg-nav-item:hover  { background: rgba(255,255,255,.07); color: #fff; }
        .sg-nav-item.active { background: rgba(245,168,0,.13); color: #F5A800; border-right-color: #F5A800; }
        .sg-nav-item svg    { width: 17px; height: 17px; flex-shrink: 0; fill: currentColor; }
        .sg-sb-foot {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 10px 0 8px;
        }
        .sg-sb-foot .sg-nav-item { font-size: .82em; }
        .sg-brand-foot { padding: 6px 18px 2px; color: #F5A800; font-weight: 700; font-size: .85em; }
        .sg-brand-tag  { padding: 0 18px 8px; color: rgba(255,255,255,.3); font-size: .68em; }

        /* ── Main wrapper ── */
        .sg-main {
            margin-left: 230px;
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            min-width: 0;
        }

        /* ── Topbar ── */
        .sg-topbar {
            background: #fff;
            padding: 11px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
            gap: 16px;
            flex-shrink: 0;
            z-index: 100;
        }
        .sg-tb-left { display: flex; align-items: center; gap: 14px; }
        .sg-tb-left a {
            color: #888;
            text-decoration: none;
            font-size: .82em;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color .13s;
        }
        .sg-tb-left a:hover { color: #F5A800; }
        .sg-tb-title { font-size: .95em; font-weight: 700; color: #1a1e3a; }
        .sg-tb-right { display: flex; align-items: center; gap: 10px; position: relative; flex-shrink: 0; }
        .sg-avatar {
            width: 34px; height: 34px;
            background: #F5A800;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: .9em; flex-shrink: 0;
        }
        .sg-uname  { font-size: .84em; font-weight: 600; color: #222; line-height: 1.2; white-space: nowrap; }
        .sg-ustatus { font-size: .71em; color: #2ecc71; display: flex; align-items: center; gap: 3px; }
        .sg-ustatus::before { content: '●'; font-size: .62em; }
        .sg-dd-btn { background: none; border: none; cursor: pointer; color: #aaa; display: flex; align-items: center; padding: 2px; }
        .sg-user-menu {
            position: absolute; top: 44px; right: 0;
            background: #fff; border: 1px solid #e0e0e0;
            border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,.1);
            min-width: 158px; z-index: 300; display: none; overflow: hidden;
        }
        .sg-user-menu a, .sg-user-menu button {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 14px; font-size: .84em; color: #333;
            text-decoration: none; border: none; background: none;
            width: 100%; text-align: left; cursor: pointer; transition: background .1s;
        }
        .sg-user-menu a:hover, .sg-user-menu button:hover { background: #f5f5f5; }
        .sg-user-menu .sg-sair { color: #e74c3c; }
        .sg-user-menu hr { border: none; border-top: 1px solid #f0f0f0; margin: 2px 0; }

        /* ── Page body — única área com scroll ── */
        .sg-page-body { flex: 1; overflow-y: auto; min-height: 0; padding: 24px 28px; box-sizing: border-box; }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .sg-sidebar { display: none; }
            .sg-main { margin-left: 0; }
        }
        /* ── Sem sidebar ── */
        body.sg-no-sidebar .sg-sidebar { display: none; }
        body.sg-no-sidebar .sg-main { margin-left: 0; }
        /* ── Badge piscante de erro ── */
        @keyframes sg-badge-blink {
            0%,100%{opacity:1;} 50%{opacity:.35;}
        }
    </style>
    @endif
</head>
<body class="{{ $hideSidebar ?? false ? 'sg-no-sidebar' : '' }}">

@if (!request()->is('login'))
@auth
@php
    $sgRoles    = auth()->user()->getRoleNames();
    $sgRole     = session('acting_as_role') ?: ($sgRoles->isNotEmpty() ? $sgRoles->implode(', ') : '—');
    $sgNome     = auth()->user()->name ?? 'Admin';
    $sgInicial  = strtoupper(mb_substr($sgNome, 0, 1));
    $sgCurrent  = request()->routeIs('dashboard') ? 'dashboard' : '';
@endphp

<!-- ── Sidebar ── -->
<aside class="sg-sidebar">
    <a href="{{ route('dashboard') }}" class="sg-sb-logo">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logo">
        <div>
            <div class="sb-name">AngolaWiFi</div>
            <div class="sb-tag">Conectarmos Angola</div>
        </div>
    </a>

    <nav class="sg-sb-nav">
        <a href="{{ route('dashboard') }}" class="sg-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            Painel Principal
        </a>
        <a href="{{ route('clientes') }}" class="sg-nav-item {{ request()->routeIs('clientes*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            Clientes
        </a>
        <a href="{{ route('estoque_equipamentos.index') }}" class="sg-nav-item {{ request()->routeIs('estoque_equipamentos*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M20 7H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 12H4V9h16v10zM12 4H4c-1.1 0-2 .9-2 2v2h2V6h8V4zm8 0h-4v2h4v2h2V6c0-1.1-.9-2-2-2z"/></svg>
            Estoque
        </a>
        @php $mikrotikErros = \App\Models\MikroTikSite::where('api_status','erro')->count(); @endphp
        <a href="{{ route('mikrotik.index') }}" class="sg-nav-item {{ request()->routeIs('mikrotik*') ? 'active' : '' }}" style="position:relative;">
            <svg viewBox="0 0 24 24"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg>
            MikroTik
            @if($mikrotikErros > 0)
            <span style="margin-left:auto;background:#e74c3c;color:#fff;font-size:.65em;font-weight:800;border-radius:12px;padding:1px 7px;animation:sg-badge-blink 1.4s ease-in-out infinite;">{{ $mikrotikErros }}</span>
            @endif
        </a>
        <a href="{{ route('alertas') }}" class="sg-nav-item {{ request()->routeIs('alertas*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
            Alertas
        </a>
        <a href="{{ route('saldo-whatsapp.index') }}" class="sg-nav-item {{ request()->routeIs('saldo-whatsapp*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            WhatsApp
        </a>
        <a href="{{ route('whatsapp-comunicado.index') }}" class="sg-nav-item {{ request()->routeIs('whatsapp-comunicado*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/></svg>
            Comunicados
        </a>
        <a href="{{ route('cobrancas.index') }}" class="sg-nav-item {{ request()->routeIs('cobrancas*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg>
            Relatórios
        </a>
    </nav>

    <div class="sg-sb-foot">
        <a href="{{ route('email-settings.index') }}" class="sg-nav-item {{ request()->routeIs('email-settings*') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
            Configurações
        </a>
        <div class="sg-brand-foot">AngolaWiFi</div>
        <div class="sg-brand-tag">Conectarmos Angola</div>
    </div>
</aside>

<!-- ── Main ── -->
<div class="sg-main">

    <!-- Topbar -->
    <header class="sg-topbar">
        <div class="sg-tb-left">
            @if (!request()->routeIs('dashboard'))
            <a href="{{ route('dashboard') }}">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/></svg>
                Painel
            </a>
            <span style="color:#ddd;">|</span>
            @endif
            <span class="sg-tb-title">@yield('page-title', 'Painel Principal')</span>
        </div>
        <div class="sg-tb-right">
            <div class="sg-avatar">{{ $sgInicial }}</div>
            <div>
                <div class="sg-uname">{{ $sgRole }}</div>
                <div class="sg-ustatus">Online</div>
            </div>
            <button class="sg-dd-btn" onclick="sgToggleMenu(event)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="sg-user-menu" id="sg-user-menu">
                <a href="{{ route('password.change') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    Alterar senha
                </a>
                <hr>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="sg-sair">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Page content -->
    <div class="sg-page-body">
        @yield('content')
    </div>

</div>

@endauth
@else
    {{-- Login page: sem sidebar --}}
    @yield('content')
@endif

@if (!request()->is('login'))
    <script src="{{ asset('js/main.js') }}"></script>
@endif

@stack('scripts')

@if (!request()->is('login'))
<script>
function sgToggleMenu(e) {
    e.stopPropagation();
    const m = document.getElementById('sg-user-menu');
    if (m) m.style.display = m.style.display === 'block' ? 'none' : 'block';
}
document.addEventListener('click', function() {
    const m = document.getElementById('sg-user-menu');
    if (m) m.style.display = 'none';
});
</script>
@endif

</body>
</html>
