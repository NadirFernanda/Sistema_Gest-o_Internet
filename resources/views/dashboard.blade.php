<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGAW — Painel Principal</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        html,body{height:100%;overflow:hidden;}
        body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#f0f2f5;height:100vh;display:flex;overflow:hidden;}

        /* ── Sidebar ── */
        .sidebar{width:230px;height:100vh;background:#1a1e3a;display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:100;overflow:hidden;}
        .sb-logo{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px;}
        .sb-logo img{width:40px;height:40px;border-radius:8px;object-fit:cover;}
        .sb-logo-text .name{color:#F5A800;font-size:1.15em;font-weight:800;letter-spacing:-.3px;line-height:1.1;}
        .sb-logo-text .tag{color:rgba(255,255,255,.45);font-size:.7em;margin-top:2px;}
        .sb-nav{flex:1;padding:12px 0;}
        .nav-item{display:flex;align-items:center;gap:10px;padding:11px 20px;color:rgba(255,255,255,.6);text-decoration:none;font-size:.88em;transition:all .15s;border-right:3px solid transparent;}
        .nav-item:hover{background:rgba(255,255,255,.07);color:#fff;}
        .nav-item.active{background:rgba(245,168,0,.12);color:#F5A800;border-right-color:#F5A800;}
        .nav-item svg{width:18px;height:18px;flex-shrink:0;}
        .sb-bottom{border-top:1px solid rgba(255,255,255,.08);padding:14px 0 10px;}
        .sb-bottom-brand{padding:8px 20px 4px;color:#F5A800;font-weight:700;font-size:.9em;}
        .sb-bottom-tag{padding:0 20px 10px;color:rgba(255,255,255,.35);font-size:.7em;}

        /* ── Main ── */
        .main{margin-left:230px;flex:1;display:flex;flex-direction:column;height:100vh;overflow:hidden;min-width:0;}

        /* ── Topbar ── */
        .topbar{background:#fff;padding:14px 28px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 1px 4px rgba(0,0,0,.06);gap:16px;}
        .tb-left h1{font-size:1.18em;font-weight:700;color:#1a1e3a;}
        .tb-left p{font-size:.78em;color:#999;margin-top:2px;}
        .tb-date{display:flex;align-items:center;gap:7px;background:#f7f8fb;border:1px solid #ebebf0;border-radius:9px;padding:7px 13px;font-size:.8em;color:#666;white-space:nowrap;}
        .tb-right{display:flex;align-items:center;gap:10px;position:relative;flex-shrink:0;}
        .avatar{width:36px;height:36px;background:#F5A800;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.95em;flex-shrink:0;}
        .user-info .uname{font-size:.86em;font-weight:600;color:#222;line-height:1.2;}
        .user-info .ustatus{font-size:.73em;color:#2ecc71;display:flex;align-items:center;gap:3px;}
        .ustatus::before{content:'●';font-size:.65em;}
        .dd-btn{background:none;border:none;cursor:pointer;color:#aaa;display:flex;align-items:center;padding:2px;}
        .user-menu{position:absolute;top:46px;right:0;background:#fff;border:1px solid #e0e0e0;border-radius:10px;box-shadow:0 4px 16px rgba(0,0,0,.1);min-width:160px;z-index:200;display:none;overflow:hidden;}
        .user-menu a,.user-menu button{display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:.86em;color:#333;text-decoration:none;border:none;background:none;width:100%;text-align:left;cursor:pointer;transition:background .12s;}
        .user-menu a:hover,.user-menu button:hover{background:#f5f5f5;}
        .user-menu .sair{color:#e74c3c;}
        .user-menu hr{border:none;border-top:1px solid #f0f0f0;margin:2px 0;}

        /* ── Content — única área com scroll ── */
        .content{padding:22px 28px;flex:1;overflow-y:auto;min-height:0;}
        .grid2{display:grid;grid-template-columns:1fr 1fr;gap:20px;}

        /* ── Cards ── */
        .card{background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);}
        .card-hdr{display:flex;align-items:center;justify-content:space-between;padding:13px 20px;font-size:.78em;font-weight:700;letter-spacing:.06em;text-transform:uppercase;}
        .card-hdr.orange{background:#F5A800;color:#fff;}
        .card-hdr.dark{background:#1a1e3a;color:#fff;}
        .menu-row{display:flex;align-items:center;gap:14px;padding:13px 20px;border-bottom:1px solid #f4f4f4;text-decoration:none;color:inherit;transition:background .12s;cursor:pointer;}
        .menu-row:last-child{border-bottom:none;}
        .menu-row:hover{background:#fafafa;}
        .mi{width:40px;height:40px;background:#F5A800;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
        .mi svg{width:20px;height:20px;fill:#fff;}
        .mt{flex:1;}
        .mt-title{font-size:.9em;font-weight:600;color:#1a1e3a;}
        .mt-desc{font-size:.76em;color:#999;margin-top:2px;}
        .ma{color:#ccc;font-size:1.1em;line-height:1;}

        /* Submenu relatórios */
        .submenu{display:none;background:#fafafa;}
        .submenu .menu-row{padding-left:74px;border-bottom:1px solid #efefef;}

        footer{text-align:center;padding:14px;font-size:.75em;color:#bbb;}

        @media(max-width:900px){.grid2{grid-template-columns:1fr;}}
        @media(max-width:680px){.sidebar{display:none;}.main{margin-left:0;}.tb-date{display:none;}}

        /* ── Alerta de IP MikroTik ── */
        @keyframes sg-blink {
            0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(220,53,69,.45);}
            50%{opacity:.92;box-shadow:0 0 0 8px rgba(220,53,69,0);}
        }
        .sg-ip-alert {
            margin:0 28px 18px;
            background:#fff0f0;
            border:1.5px solid #e74c3c;
            border-radius:12px;
            padding:14px 18px;
            display:flex;
            align-items:flex-start;
            gap:14px;
            animation:sg-blink 1.6s ease-in-out infinite;
            cursor:pointer;
        }
        .sg-ip-alert__icon {
            width:36px;height:36px;background:#e74c3c;border-radius:50%;
            display:flex;align-items:center;justify-content:center;
            flex-shrink:0;color:#fff;font-size:1.2rem;font-weight:800;
        }
        .sg-ip-alert__body {flex:1;min-width:0;}
        .sg-ip-alert__title {font-size:.95em;font-weight:700;color:#c0392b;margin-bottom:6px;}
        .sg-ip-alert__list {list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:4px;}
        .sg-ip-alert__item {font-size:.82em;color:#555;display:flex;align-items:baseline;gap:6px;}
        .sg-ip-alert__site {font-weight:700;color:#c0392b;white-space:nowrap;}
        .sg-ip-alert__err {color:#888;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:420px;}
        .sg-ip-alert__link {margin-left:auto;font-size:.82em;font-weight:700;color:#e74c3c;text-decoration:none;white-space:nowrap;padding:6px 14px;border:1.5px solid #e74c3c;border-radius:8px;flex-shrink:0;align-self:center;}
        .sg-ip-alert__link:hover{background:#e74c3c;color:#fff;}
    </style>
</head>
<body>

@auth
@php
    $roleNames  = auth()->user()->getRoleNames();
    $activeRole = session('acting_as_role') ?: ($roleNames->isNotEmpty() ? $roleNames->implode(', ') : '—');
    $lojaToken  = config('services.sg.admin_token');
    $lojaBase   = rtrim(config('services.sg.loja_url', env('LOJA_URL','http://127.0.0.1:8001')), '/');
    $lojaUrl    = $lojaToken ? $lojaBase.'/admin?sg_sso='.urlencode($lojaToken) : $lojaBase.'/admin';
    $meses      = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    $semana     = ['Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'];
    $agora      = now();
    $dataFmt    = $agora->day.' de '.$meses[$agora->month].' de '.$agora->year;
    $diaSemana  = $semana[$agora->dayOfWeek];
    $hora       = $agora->format('H:i');
    $nome       = auth()->user()->name ?? 'Admin';
    $inicial    = strtoupper(mb_substr($nome, 0, 1));
@endphp

<!-- ── Sidebar ── -->
<aside class="sidebar">
    <div class="sb-logo">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logo">
        <div class="sb-logo-text">
            <div class="name">AngolaWiFi</div>
            <div class="tag">Conectarmos Angola</div>
        </div>
    </div>
    <nav class="sb-nav">
        <a href="{{ route('dashboard') }}" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
            Painel Principal
        </a>
        <a href="{{ route('dashboard') }}" class="nav-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
            Dashboard
        </a>
    </nav>
    <div class="sb-bottom">
        <a href="#" class="nav-item">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.5 2C6.81 2 3 5.81 3 10.5S6.81 19 11.5 19h.5v3c4.86-2.34 8-7 8-11.5C20 5.81 16.19 2 11.5 2zm1 14.5h-2v-2h2v2zm0-4h-2c0-3.25 3-3 3-5 0-1.1-.9-2-2-2s-2 .9-2 2h-2c0-2.21 1.79-4 4-4s4 1.79 4 4c0 2.5-3 2.75-3 5z"/></svg>
            Suporte
        </a>
        <div class="sb-bottom-brand">AngolaWiFi</div>
        <div class="sb-bottom-tag">Conectarmos Angola</div>
    </div>
</aside>

<!-- ── Main ── -->
<div class="main">

    <!-- Topbar -->
    <header class="topbar">
        <div class="tb-left">
            <h1>Bem-vindo ao SGAW</h1>
            <p>Sistema de Gestão AngolaWiFi</p>
        </div>

        <div class="tb-date">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="#888"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"/></svg>
            {{ $dataFmt }} &nbsp;·&nbsp; {{ $diaSemana }}, {{ $hora }}
        </div>

        <div class="tb-right">
            <div class="avatar">{{ $inicial }}</div>
            <div class="user-info">
                <div class="uname">{{ $activeRole }}</div>
                <div class="ustatus">Online</div>
            </div>
            <button class="dd-btn" onclick="toggleUserMenu(event)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
            </button>
            <div class="user-menu" id="user-menu">
                <a href="{{ route('password.change') }}">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    Alterar senha
                </a>
                <hr>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="sair">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
                        Sair
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Alerta de sites MikroTik com erro de ligação -->
    @if(isset($sitesComErro) && $sitesComErro->count() > 0)
    <div class="sg-ip-alert" onclick="window.location='{{ route('mikrotik.index') }}'">
        <div class="sg-ip-alert__icon">⚠</div>
        <div class="sg-ip-alert__body">
            <div class="sg-ip-alert__title">
                {{ $sitesComErro->count() }} site(s) MikroTik com erro de ligação — IP incorreto ou inacessível
            </div>
            <ul class="sg-ip-alert__list">
                @foreach($sitesComErro->take(4) as $siteErro)
                <li class="sg-ip-alert__item">
                    <span class="sg-ip-alert__site">{{ $siteErro->nome }}</span>
                    <span style="color:#ccc;">·</span>
                    <span class="sg-ip-alert__err" title="{{ $siteErro->api_last_error }}">{{ Str::limit($siteErro->api_last_error, 80) }}</span>
                </li>
                @endforeach
                @if($sitesComErro->count() > 4)
                <li class="sg-ip-alert__item" style="color:#aaa;font-style:italic;">
                    + {{ $sitesComErro->count() - 4 }} mais…
                </li>
                @endif
            </ul>
        </div>
        <a href="{{ route('mikrotik.index') }}" class="sg-ip-alert__link" onclick="event.stopPropagation()">Ver sites</a>
    </div>
    @endif

    <!-- Grid -->
    <main class="content">
        <div class="grid2">

            <!-- OPERAÇÕES -->
            <div class="card">
                <div class="card-hdr orange">
                    <span>Operações</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="rgba(255,255,255,.75)"><path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/></svg>
                </div>

                <a href="{{ route('clientes') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                    <div class="mt"><div class="mt-title">Clientes</div><div class="mt-desc">Gerir clientes e serviços</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ $lojaUrl }}" class="menu-row" target="_blank" rel="noopener">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 16.1 6.1 17 7.25 17H19v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63H15.5c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z"/></svg></div>
                    <div class="mt"><div class="mt-title">Loja</div><div class="mt-desc">Vendas de planos e produtos</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ route('estoque_equipamentos.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M20 7H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zm0 12H4V9h16v10zM12 4H4c-1.1 0-2 .9-2 2v2h2V6h8V4zm8 0h-4v2h4v2h2V6c0-1.1-.9-2-2-2z"/></svg></div>
                    <div class="mt"><div class="mt-title">Estoque de Equipamentos</div><div class="mt-desc">Gerir stock de equipamentos</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ route('mikrotik.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg></div>
                    <div class="mt"><div class="mt-title">MikroTik</div><div class="mt-desc">Gestão de dispositivos MikroTik</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ route('whatsapp-comunicado.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></div>
                    <div class="mt"><div class="mt-title">Comunicados WhatsApp</div><div class="mt-desc">Enviar comunicados via WhatsApp</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ route('email-settings.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg></div>
                    <div class="mt"><div class="mt-title">Configurar Email</div><div class="mt-desc">Configurações de envio de e-mails</div></div>
                    <span class="ma">›</span>
                </a>
            </div>

            <!-- INFORMAÇÕES / GESTÃO -->
            <div class="card">
                <div class="card-hdr dark">
                    <span>Informações / Gestão</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="rgba(255,255,255,.75)"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                </div>

                <a href="{{ app()->router->has('planos.index') ? route('planos.index') : url('/planos') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></div>
                    <div class="mt"><div class="mt-title">Planos</div><div class="mt-desc">Gerir planos e pacotes</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="{{ route('alertas') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg></div>
                    <div class="mt"><div class="mt-title">Alertas</div><div class="mt-desc">Ver alertas e notificações</div></div>
                    <span class="ma">›</span>
                </a>

                <div class="menu-row" onclick="toggleRelatorios()">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z"/></svg></div>
                    <div class="mt"><div class="mt-title">Relatórios</div><div class="mt-desc">Relatórios e estatísticas</div></div>
                    <span class="ma" id="rel-arrow">∨</span>
                </div>
                <div class="submenu" id="submenu-rel">
                    <a href="{{ route('cobrancas.index') }}" class="menu-row">
                        <div class="mt"><div class="mt-title">Cobrança</div></div><span class="ma">›</span>
                    </a>
                    <a href="{{ route('relatorios.gerais') }}" class="menu-row">
                        <div class="mt"><div class="mt-title">Geral</div></div><span class="ma">›</span>
                    </a>
                    @if(app()->router->has('admin.audit.index'))
                        <a href="{{ route('admin.audit.index') }}" class="menu-row">
                            <div class="mt"><div class="mt-title">Auditoria</div></div><span class="ma">›</span>
                        </a>
                    @else
                        <a href="{{ url('/admin/audit-logs') }}" class="menu-row">
                            <div class="mt"><div class="mt-title">Auditoria</div></div><span class="ma">›</span>
                        </a>
                    @endif
                </div>

                <a href="{{ route('saldo-whatsapp.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></div>
                    <div class="mt"><div class="mt-title">Saldo WhatsApp</div><div class="mt-desc">Consultar saldo da conta WhatsApp</div></div>
                    <span class="ma">›</span>
                </a>

                <a href="http://mx100.angoweb.net/webmail" class="menu-row" target="_blank" rel="noopener">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg></div>
                    <div class="mt"><div class="mt-title">Webmail</div><div class="mt-desc">Aceder ao webmail da empresa</div></div>
                    <span class="ma">›</span>
                </a>

                @can('users.view')
                <a href="{{ route('admin.users.index') }}" class="menu-row">
                    <div class="mi"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
                    <div class="mt"><div class="mt-title">Usuários</div><div class="mt-desc">Gerir usuários do sistema</div></div>
                    <span class="ma">›</span>
                </a>
                @endcan
            </div>

        </div>
    </main>

    <footer>© {{ date('Y') }} AngolaWiFi — Todos os direitos reservados.</footer>
</div>

@endauth

<script>
function toggleUserMenu(e) {
    e.stopPropagation();
    const m = document.getElementById('user-menu');
    m.style.display = m.style.display === 'block' ? 'none' : 'block';
}
document.addEventListener('click', () => { document.getElementById('user-menu').style.display = 'none'; });

function toggleRelatorios() {
    const sub = document.getElementById('submenu-rel');
    const arr = document.getElementById('rel-arrow');
    const open = sub.style.display === 'block';
    sub.style.display = open ? 'none' : 'block';
    arr.textContent  = open ? '∨' : '∧';
}
</script>
</body>
</html>
