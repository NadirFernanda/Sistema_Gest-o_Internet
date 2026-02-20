@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos — Cadastrar',
            'subtitle' => ''
        ])

        {{-- botão-ícone centralizado sobre o formulário --}}
        <div style="position:relative;">
            <a href="{{ route('planos.index') }}" class="btn-icon btn-ghost" title="Voltar" aria-label="Voltar" 
               style="position:absolute; left:50%; transform:translateX(-50%); top:-28px; z-index:20; box-shadow:0 6px 18px rgba(0,0,0,0.06);">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>

            @include('planos._form')
        </div>
    </div>
@endsection
