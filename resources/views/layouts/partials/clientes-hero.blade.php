@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? '';
    $heroCtAs = $heroCtAs ?? null;
    $heroSearch = $heroSearch ?? null;
@endphp
<header class="clientes-hero modern-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
            <div class="hero-titles">
                <h1>{{ $title }}</h1>
                @if($subtitle)
                    <p class="hero-sub">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        <div class="hero-right">
            <div class="hero-ctas">
                @if($heroCtAs)
                    {!! $heroCtAs !!}
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
                @endif
            </div>
            @if($heroSearch)
                {!! $heroSearch !!}
            @endif
        </div>
    </div>
</header>
