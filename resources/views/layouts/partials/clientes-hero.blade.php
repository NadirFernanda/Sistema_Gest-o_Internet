@php
    $title = $title ?? '';
    $subtitle = $subtitle ?? '';
    $heroCtAs = $heroCtAs ?? null;
    $heroSearch = $heroSearch ?? null;
@endphp
@if($title || $heroCtAs || $heroSearch)
<div class="sg-page-hero" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;padding:20px 24px 0;margin-bottom:4px;">
    <div>
        @if($title)
            <h1 style="margin:0;font-size:1.25rem;font-weight:800;color:#1a1e3a;line-height:1.2;">{{ $title }}</h1>
        @endif
        @if($subtitle)
            <p style="margin:4px 0 0;font-size:0.82rem;color:#999;">{{ $subtitle }}</p>
        @endif
    </div>
    @if($heroCtAs || $heroSearch)
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        @if($heroCtAs)
            {!! $heroCtAs !!}
        @endif
        @if($heroSearch)
            {!! $heroSearch !!}
        @endif
    </div>
    @endif
</div>
@endif
