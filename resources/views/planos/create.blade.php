@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}?v=bf3e0ef">
@endpush

@section('content')
    <div class="planos-container">
        {{-- Página sem header: formulário isolado --}}
        <div style="max-width:1100px; margin:12px auto 0; text-align:center;">
            <a href="{{ route('planos.index') }}" class="btn-icon btn-ghost" title="Voltar" aria-label="Voltar"
               style="display:inline-flex; margin:0 auto 12px; width:44px; height:44px; align-items:center; justify-content:center; border-radius:8px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>

            @include('planos._form')
        </div>
    </div>
@endsection
