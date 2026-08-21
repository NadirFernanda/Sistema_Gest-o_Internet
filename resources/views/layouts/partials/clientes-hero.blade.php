@php
    $title      = $title ?? '';
    $subtitle   = $subtitle ?? '';
    $heroCtAs   = $heroCtAs ?? null;
    $heroSearch = $heroSearch ?? null;
@endphp

{{-- Define o título no topbar APENAS se a view não o definiu já --}}
@if($title && !$__env->hasSection('page-title'))
    @section('page-title', $title)
@endif

@if($subtitle || $heroCtAs || $heroSearch)
<div class="sg-page-subheader" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:14px 24px 0;">
    @if($subtitle)
        <p style="margin:0;font-size:0.81rem;color:#aaa;">{{ $subtitle }}</p>
    @endif
    @if($heroCtAs || $heroSearch)
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-left:auto;">
        @if($heroCtAs) {!! $heroCtAs !!} @endif
        @if($heroSearch) {!! $heroSearch !!} @endif
    </div>
    @endif
</div>
@endif
