@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <div class="planos-header">
            <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
            <h1>Gestão de Planos — Cadastrar</h1>
        </div>
        <div style="margin-bottom:16px;">
            <a href="{{ route('planos') }}" class="btn btn-cta">Voltar à Lista de Planos</a>
        </div>

        @include('planos._form')
    </div>
@endsection
