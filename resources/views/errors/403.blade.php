@extends('layouts.app')

@section('content')
<div style="max-width:880px;margin:48px auto;padding:28px;background:#fff;border-radius:12px;box-shadow:0 6px 30px rgba(0,0,0,0.06);text-align:center;">
    <h1 style="font-size:28px;margin-bottom:10px;color:#c0392b;">Acesso negado</h1>
    <p style="font-size:16px;color:#333;margin-bottom:18px;">{{ $message ?? 'Você não tem permissão para acessar esta página ou executar esta ação.' }}</p>
    <p style="color:#666;margin-bottom:22px;">Se acredita que deveria ter acesso, contacte um administrador ou o responsável pelo sistema.</p>
    <div style="display:flex;gap:12px;justify-content:center;">
        <a href="{{ route('dashboard') }}" class="btn" style="padding:10px 14px;">Voltar ao Painel</a>
        <a href="{{ url()->previous() }}" class="btn btn-ghost" style="padding:10px 14px;">Voltar</a>
    </div>
</div>
@endsection
