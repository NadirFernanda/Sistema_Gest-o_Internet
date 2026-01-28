@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px; margin: 40px auto;">
    <h2>Estoque de Equipamentos</h2>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Voltar ao Dashboard</a>
    <a href="{{ route('estoque_equipamentos.create') }}" class="btn btn-primary" style="margin-left: 10px;">Cadastrar Novo Equipamento</a>
    <hr>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Modelo</th>
                <th>Nº Série</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipamentos as $equipamento)
                <tr>
                    <td>{{ $equipamento->nome }}</td>
                    <td>{{ $equipamento->descricao }}</td>
                    <td>{{ $equipamento->modelo }}</td>
                    <td>{{ $equipamento->numero_serie }}</td>
                    <td>{{ $equipamento->quantidade }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhum equipamento cadastrado no estoque.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
