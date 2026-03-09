@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Detalhes do Site</h1>
    <table class="table table-bordered">
        <tr><th>ID</th><td>{{ $site->id }}</td></tr>
        <tr><th>Nome</th><td>{{ $site->nome }}</td></tr>
        <tr><th>Localização</th><td>{{ $site->localizacao }}</td></tr>
        <tr><th>Status</th><td>{{ $site->status }}</td></tr>
        <tr><th>Capacidade</th><td>{{ $site->capacidade }}</td></tr>
        <tr><th>Observações</th><td>{{ $site->observacoes }}</td></tr>
    </table>
    <a href="{{ route('sites.edit', $site) }}" class="btn btn-warning">Editar</a>
    <a href="{{ route('sites.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
