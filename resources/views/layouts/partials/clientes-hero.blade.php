@php
    $title      = $title ?? '';
    $subtitle   = $subtitle ?? '';
    $heroCtAs   = $heroCtAs ?? null;
    $heroSearch = $heroSearch ?? null;
@endphp

{{-- Define o título no topbar se a view ainda nao o definiu --}}
@if($title && !$__env->hasSection('page-title'))
    @section('page-title', $title)
@endif

{{-- Só renderiza algo visível se houver botões de acção ou pesquisa --}}
@if($heroCtAs || $heroSearch)
<div style="display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:10px;padding:12px 24px 0;">
    @if($heroCtAs) {!! $heroCtAs !!} @endif
    @if($heroSearch) {!! $heroSearch !!} @endif
</div>
@endif
