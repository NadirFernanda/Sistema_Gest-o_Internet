@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos — Cadastrar',
            'subtitle' => '',
            'heroCtAs' => '<a href="' . route('planos.index') . '" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></a>'
        ])

        <div style="margin-bottom:16px;">
            <a href="{{ route('planos.index') }}" class="btn-back-circle btn-ghost" title="Voltar à Lista de Planos" aria-label="Voltar à Lista de Planos">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
        </div>

        @include('planos._form')
    </div>
@endsection
