@extends('layouts.app')

@section('content')
<div style="max-width:880px;margin:48px auto;padding:28px;background:#fff;border-radius:12px;box-shadow:0 6px 30px rgba(0,0,0,0.06);text-align:center;">
    <h1 style="font-size:28px;margin-bottom:10px;color:#c0392b;">Acesso negado</h1>
    <p style="font-size:16px;color:#333;margin-bottom:18px;">{{ $message ?? 'Você não tem permissão para acessar esta página ou executar esta ação.' }}</p>
    <p style="color:#666;margin-bottom:8px;">Se acredita que deveria ter acesso, contacte um administrador ou o responsável pelo sistema.</p>
    @php
        $adminEmail = config('mail.from.address', 'admin@isp.example');
    @endphp
    <p style="color:#666;margin-bottom:18px;">
        <a href="mailto:{{ $adminEmail }}?subject=Pedido%20de%20acesso%20-%20Acesso%20negado" class="btn btn-link" style="padding:8px 10px;border-radius:8px;border:1px solid #e6e6e6;text-decoration:none;color:#0b5ed7;">Contactar administrador</a>
        &nbsp;ou&nbsp;
        <a href="{{ route('dashboard') }}" class="btn" style="padding:10px 14px;">Voltar ao Painel</a>
        &nbsp;
        {{-- previous-link removed from header/content area to avoid back button in header --}}
    </p>
</div>
@endsection
