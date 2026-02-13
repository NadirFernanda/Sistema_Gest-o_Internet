@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
    <div class="planos-container">
        @include('layouts.partials.clientes-hero', [
            'title' => 'Gestão de Planos — Cadastrar',
            'subtitle' => '',
            'heroCtAs' => '<a href="' . route('planos.index') . '" class="btn btn-ghost">Voltar</a>'
        ])

        <div style="margin-bottom:16px;">
            <a href="{{ route('planos.index') }}" class="btn btn-cta">Voltar à Lista de Planos</a>
        </div>

        @include('planos._form')
    </div>
@endsection
