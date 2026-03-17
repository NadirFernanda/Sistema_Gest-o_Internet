@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v={{ filemtime(public_path('css/clientes.css')) }}">
@endpush

@section('content')
    <div class="planos-container">
        {{-- Página sem header: formulário isolado --}}
        <div style="max-width:1100px; margin:12px auto 0; text-align:center;">
            <a href="{{ route('planos.index') }}" class="btn btn-ghost" title="Voltar" aria-label="Voltar" style="display:inline-block; width:auto; margin:0 auto 12px;">Voltar</a>

            @include('planos._form')
        </div>
    </div>
@endsection
