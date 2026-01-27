@extends('layouts.app')

@section('content')
    <div class="clientes-container">
        <img src="{{ asset('img/logo.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Clientes</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
        <form id="formCliente" class="form-cadastro">
            <input type="text" id="nomeCliente" placeholder="Nome completo">
            <input type="email" id="emailCliente" placeholder="Email">
            <input type="text" id="contatoCliente" placeholder="Contacto">
            <button type="submit">Cadastrar Cliente</button>
        </form>
        <h2 style="margin-top:32px;">Lista de Clientes</h2>
        <div class="clientes-lista" id="clientesLista">
            <p>Nenhum cliente cadastrado ainda.</p>
        </div>
    </div>
@endsection
