@extends('layouts.app')

@section('content')
<style>
    .form-modern input[type="text"],
    .form-modern input[type="number"] {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 10px 14px;
        font-size: 1rem;
        margin-bottom: 12px;
        width: 100%;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .form-modern input[type="text"]:focus,
    .form-modern input[type="number"]:focus {
        border-color: #f7b500;
        box-shadow: 0 2px 8px rgba(247,181,0,0.10);
    }
    .form-modern label { font-size: 0.98rem; color: #222; margin-bottom: 2px; font-weight: 500; }
    .form-modern .form-group { margin-bottom: 18px; text-align: left; }
    .form-modern .btn-primary { background: #f7b500; color: #fff; border: none; border-radius: 10px; font-size: 1.2rem; padding: 12px 0; width: 100%; margin-top: 10px; }
</style>
<div class="container" style="max-width: 500px; margin: 40px auto;">
    <div class="mb-3" style="text-align:left;">
        <a href="{{ route('estoque_equipamentos.index') }}" class="btn-back-circle btn-ghost" title="Voltar" aria-label="Voltar">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </a>
    </div>
    <h2 style="text-align:center;">Editar Equipamento</h2>
    <form action="{{ route('estoque_equipamentos.update', $equipamento->id) }}" method="POST" class="form-modern">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="nome">Nome <span class="text-danger">*</span></label>
            <input type="text" id="nome" name="nome" value="{{ old('nome', $equipamento->nome) }}">
            @if ($errors->has('nome'))
                <div class="text-danger small">{{ $errors->first('nome') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="descricao">Descrição <span class="text-danger">*</span></label>
            <input type="text" id="descricao" name="descricao" value="{{ old('descricao', $equipamento->descricao) }}">
            @if ($errors->has('descricao'))
                <div class="text-danger small">{{ $errors->first('descricao') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="modelo">Modelo <span class="text-danger">*</span></label>
            <input type="text" id="modelo" name="modelo" value="{{ old('modelo', $equipamento->modelo) }}">
            @if ($errors->has('modelo'))
                <div class="text-danger small">{{ $errors->first('modelo') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="numero_serie">Número de Série <span class="text-danger">*</span></label>
            <input type="text" id="numero_serie" name="numero_serie" value="{{ old('numero_serie', $equipamento->numero_serie) }}">
            @if ($errors->has('numero_serie'))
                <div class="text-danger small">{{ $errors->first('numero_serie') }}</div>
            @endif
        </div>
        <div class="form-group">
            <label for="quantidade">Quantidade <span class="text-danger">*</span></label>
            <input type="number" id="quantidade" name="quantidade" min="1" value="{{ old('quantidade', $equipamento->quantidade) }}">
            @if ($errors->has('quantidade'))
                <div class="text-danger small">{{ $errors->first('quantidade') }}</div>
            @endif
        </div>
        <button type="submit" class="btn btn-primary">Salvar alterações</button>
    </form>
</div>
@endsection
