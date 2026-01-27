@extends('layouts.app')

@section('content')
<div class="login-bg">
    <div class="login-container">
        <img src="{{ asset('img/logo.jpeg') }}" alt="Logo">
        <h2 style="font-weight: 700; font-size: 1.7rem; margin-bottom: 16px; color: #222; text-align: center;">Entrar</h2>
        <form method="POST" action="{{ route('login') }}" style="width: 100%;">
            @csrf
            <div style="margin-bottom: 12px;">
                <label for="email" style="font-weight: 500; color: #444;">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="Digite seu email" style="width: 100%; padding: 10px 16px; border: 1px solid #ccc; border-radius: 6px; margin-top: 4px; font-size: 1rem; box-sizing: border-box; color: #222;" oninvalid="if (!this.value) { this.setCustomValidity('Por favor, preencha o campo de email.'); } else if (!/.+@.+\..+/.test(this.value)) { this.setCustomValidity('Por favor, digite um email válido.'); } else { this.setCustomValidity(''); }" oninput="this.setCustomValidity('')">
                @error('email')
                    <div style="color: #e74c3c; font-size: 0.95em; margin-top: 3px;">{{ $message }}</div>
                @enderror
            </div>
            <div style="margin-bottom: 12px;">
                <label for="password" style="font-weight: 500; color: #444;">Senha</label>
                <input type="password" name="password" id="password" required placeholder="Digite sua senha" style="width: 100%; padding: 10px 16px; border: 1px solid #ccc; border-radius: 6px; margin-top: 4px; font-size: 1rem; box-sizing: border-box; color: #222;" minlength="3" oninvalid="if (!this.value) { this.setCustomValidity('Por favor, preencha o campo de senha.'); } else if (this.value.length < 3) { this.setCustomValidity('A senha deve ter pelo menos 3 caracteres.'); } else { this.setCustomValidity(''); }" oninput="this.setCustomValidity('')">
                @error('password')
                    <div style="color: #e74c3c; font-size: 0.95em; margin-top: 3px;">{{ $message }}</div>
                @enderror
            </div>
            <div style="margin-bottom: 14px; display: flex; align-items: center;">
                <input type="checkbox" name="remember" id="remember" style="margin-right: 6px;">
                <label for="remember" style="font-size: 1rem; color: #444;">Lembrar-me</label>
            </div>
            <button type="submit" style="width: 100%; background: #f7b500; color: #fff; font-weight: 600; font-size: 1.1rem; border: none; border-radius: 8px; padding: 10px 0; cursor: pointer; transition: background 0.2s;">Entrar</button>
        </form>
    </div>
</div>
@endsection
