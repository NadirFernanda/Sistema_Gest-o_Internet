@extends('layouts.app')

@section('content')
    <div class="index-container">
        <img src="{{ asset('img/logo2.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Bem-vindo ao Sistema de Gestão</h1>
        <form class="login-form" id="loginForm">
            <input type="email" id="email" placeholder="Email" required>
            <input type="password" id="password" placeholder="Senha" required>
            <button type="submit">Entrar</button>
        </form>
        <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            var email = document.getElementById('email').value.trim();
            var password = document.getElementById('password').value.trim();
            if(!email || !password) {
                alert('Preencha todos os campos!');
                return;
            }
            try {
                const res = await fetch('/api/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password })
                });
                let data = null;
                try {
                    data = await res.json();
                } catch (jsonErr) {
                    console.error('Erro ao converter resposta para JSON:', jsonErr);
                }
                console.log('Status:', res.status);
                console.log('Response:', data);
                if(res.ok && data && data.token) {
                    localStorage.setItem('token', data.token);
                    window.location.href = '{{ route('dashboard') }}';
                } else {
                    alert((data && data.error) || 'Credenciais inválidas!');
                }
            } catch (err) {
                console.error('Erro na requisição:', err);
                alert('Erro ao conectar com o servidor.');
            }
        });
        </script>
    </div>
@endsection
