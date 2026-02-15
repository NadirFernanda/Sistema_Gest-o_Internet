
@extends('layouts.app')

@section('content')
<div class="login-bg">
    <div class="login-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="Logo" class="login-logo">
        <h2 class="login-title">Bem-vindo</h2>
        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="Digite seu email"
                    oninvalid="if (!this.value) { this.setCustomValidity('Por favor, preencha o campo de email.'); } else if (!/.+@.+\..+/.test(this.value)) { this.setCustomValidity('Por favor, digite um email válido.'); } else { this.setCustomValidity(''); }"
                    oninput="this.setCustomValidity('')">
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" name="password" id="password" required placeholder="Digite sua senha" minlength="3"
                    oninvalid="if (!this.value) { this.setCustomValidity('Por favor, preencha o campo de senha.'); } else if (this.value.length < 3) { this.setCustomValidity('A senha deve ter pelo menos 3 caracteres.'); } else { this.setCustomValidity(''); }"
                    oninput="this.setCustomValidity('')">
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group form-remember" style="display: flex; flex-direction: row; align-items: center; gap: 7px; margin-bottom: 8px; margin-top: 2px; justify-content: flex-start; width: 100%;">
                <input type="checkbox" name="remember" id="remember" style="margin: 0;">
                <label for="remember" style="margin: 0; font-size: 1rem; font-weight: 500; color: #444; cursor: pointer; white-space: nowrap;">Lembrar-me</label>
            </div>
            <button type="submit" class="login-btn">Entrar</button>
        </form>
    </div>
</div>
@endsection
