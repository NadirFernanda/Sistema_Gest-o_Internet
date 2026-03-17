@extends('layouts.error')

@section('content')
<div class="code">403</div>
<p class="title">Acesso negado</p>
<p class="msg">{{ $message ?? 'Você não tem permissão para acessar esta página ou executar esta ação.' }}<br><br>Se acredita que deveria ter acesso, contacte um administrador.</p>
@php $adminEmail = config('mail.from.address', 'admin@isp.example'); @endphp
<a href="mailto:{{ $adminEmail }}?subject=Pedido%20de%20acesso%20-%20Acesso%20negado" class="btn">Contactar administrador</a>
@endsection
