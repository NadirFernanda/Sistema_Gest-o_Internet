@extends('layouts.error')

@section('content')
<div class="code">419</div>
<p class="title">Sessão expirada</p>
<p class="msg">A sua sessão expirou. Por favor, recarregue a página e tente novamente.</p>
<a href="/login" class="btn">Ir para o Login</a>
@endsection
