@extends('layouts.app')

@section('content')
    <div class="planos-container">
        <img src="{{ asset('img/logo.jpeg') }}" alt="LuandaWiFi Logo" class="logo">
        <h1>Gestão de Planos</h1>
        <a href="{{ route('dashboard') }}" class="btn">Voltar ao Dashboard</a>
        <form id="formPlano" class="form-cadastro">
            <select id="clientePlano" required>
                <option value="">Selecione o cliente</option>
            </select>
            <input type="text" id="nomePlano" placeholder="Nome do plano" required>
            <input type="text" id="descricaoPlano" placeholder="Descrição" required>
            <input type="number" id="precoPlano" placeholder="Preço (Kz)" min="0" required>
            <input type="number" id="cicloPlano" placeholder="Ciclo de serviço (dias)" min="1" required>
            <input type="date" id="dataAtivacaoPlano" placeholder="Data de ativação" required>
            <select id="estadoPlano" required>
                <option value="">Estado do plano</option>
                <option value="Ativo">Ativo</option>
                <option value="Em aviso">Em aviso</option>
                <option value="Suspenso">Suspenso</option>
                <option value="Cancelado">Cancelado</option>
            </select>
            <button type="submit">Cadastrar Plano</button>
        </form>
        <h2 style="margin-top:32px;">Lista de Planos</h2>
        <div class="busca-planos-form" style="margin:12px 0 4px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <input
                type="text"
                id="buscaPlanos"
                placeholder="Pesquisar por plano ou cliente..."
                style="flex:1; min-width:220px; padding:8px 10px; border-radius:8px; border:1px solid #ccc;"
            >
            <button
                type="button"
                id="btnBuscarPlanos"
                style="padding:8px 16px; border-radius:8px; border:none; background:#3498db; color:#fff; cursor:pointer; white-space:nowrap;"
            >
                Pesquisar
            </button>
        </div>
        <div class="planos-lista" id="planosLista">
            <p>Nenhum plano cadastrado ainda.</p>
        </div>
    </div>
@endsection
