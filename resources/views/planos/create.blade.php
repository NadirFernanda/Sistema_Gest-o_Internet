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

        {{-- back button removed from header/hero area --}}

        @include('planos._form')
    </div>
@endsection
