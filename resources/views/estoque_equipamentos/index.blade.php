@extends('layouts.app')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clientes.css') }}">
@endpush

<div class="estoque-container-moderna">
    @include('layouts.partials.clientes-hero', [
        'title' => 'Estoque de Equipamentos',
        'subtitle' => '',
        'stackLeft' => true,
        'heroCtAs' => '<a href="' . route('dashboard') . '" class="btn btn-secondary">Voltar ao Dashboard</a><a href="' . route('estoque_equipamentos.create') . '" class="btn btn-primary">Cadastrar Novo Equipamento</a><a href="' . route('estoque_equipamentos.export') . '" class="btn btn-success" style="color:#fff; min-width:180px;" target="_blank">Exportar Estoque Excel</a>'
    ])
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <form method="GET" action="{{ route('estoque_equipamentos.index') }}" class="estoque-busca-form">
        <input
            type="text"
            name="busca"
            value="{{ request('busca') }}"
            placeholder="Pesquisar por nome, modelo ou nº de série..."
        >
        <button type="submit">Pesquisar</button>
        @if(request('busca'))
            <a href="{{ route('estoque_equipamentos.index') }}" class="btn-limpar-busca">Limpar</a>
        @endif
    </form>
    <div class="estoque-tabela-moderna">
        <table class="tabela-estoque-moderna">
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
</div>
<style>
.estoque-container-moderna {
    max-width: 1100px;
    margin: 48px auto 0 auto;
    background: #fafafa;
    border-radius: 24px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10);
    padding: 38px 38px 38px 38px;
    min-width: 350px;
}
.estoque-cabecalho-moderna {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 18px;
    margin-bottom: 24px;
}
.estoque-cabecalho-moderna h1 {
    color: #111;
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 0;
}
.estoque-cabecalho-botoes {
    display: flex;
    gap: 24px;
    margin-top: 6px;
    flex-wrap: wrap;
    justify-content: center;
}
.estoque-busca-form {
    margin: 0 0 8px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    justify-content: flex-start;
}
.estoque-busca-form input[type="text"] {
    flex: 1;
    min-width: 220px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
}
.estoque-busca-form button[type="submit"] {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #f7b500;
    color: #fff;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-busca-form .btn-limpar-busca {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #aaa;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}
.estoque-tabela-moderna {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 8px #0001;
    padding: 18px 18px 8px 18px;
    margin-top: 18px;
    overflow-x: auto;
}
.tabela-estoque-moderna {
    width: 100%;
    min-width: 700px;
    font-size: 1.07em;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
}
.tabela-estoque-moderna th {
    background: #fffbe7;
    color: #f7b500;
    font-weight: bold;
    font-size: 1.09em;
    border-bottom: 2px solid #ffe6a0;
    padding: 14px 12px;
}
.tabela-estoque-moderna td {
    background: #fff;
    color: #222;
    font-size: 1em;
    padding: 13px 12px;
}
.tabela-estoque-moderna tr {
    border-bottom: 1px solid #f3e6b0;
}
</style>
@endsection
