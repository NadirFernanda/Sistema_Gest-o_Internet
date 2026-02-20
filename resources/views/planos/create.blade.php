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

        {{-- Restaurado: botão Voltar e formulário; toolbar removida por pedido do usuário --}}
        <div style="position:relative; max-width:1100px; margin:12px auto 0;">
            <a href="{{ route('planos.index') }}" class="btn btn-ghost" title="Voltar" aria-label="Voltar"
               style="display:inline-flex; margin:0 auto 12px; position:relative; left:50%; transform:translateX(-50%);">
                ← Voltar
            </a>

            @include('planos._form')
        </div>
    </div>
@endsection
