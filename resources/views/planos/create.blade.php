@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
    <div class="planos-container">
        {{-- Página sem header: formulário isolado --}}
        <div style="max-width:1100px; margin:12px auto 0; text-align:center;">
            <a href="{{ route('planos.index') }}" class="btn btn-ghost" title="Voltar" aria-label="Voltar" style="display:inline-block; margin:0 auto 12px;">Voltar</a>

            @include('planos._form')
        </div>
    </div>
@endsection
