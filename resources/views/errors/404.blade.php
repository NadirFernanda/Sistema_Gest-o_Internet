@extends('layouts.error')

@section('content')
<div class="code">404</div>
<p class="title">Página não encontrada</p>
<p class="msg">{{ $exception->getMessage() ?: 'A página que procura não existe ou foi removida.' }}</p>
<a href="/" class="btn">Voltar ao início</a>
@endsection
