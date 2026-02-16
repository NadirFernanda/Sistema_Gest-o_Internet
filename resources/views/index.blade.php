@extends('layouts.app')

@section('content')
    <div class="index-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Bem-vindo ao Sistema de Gestão</h1>
        <form class="login-form" id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="password" name="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
    </div>
@endsection
