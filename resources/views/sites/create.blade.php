@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Novo Site</h1>
    <form action="{{ route('sites.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="localizacao" class="form-label">Localização</label>
            <input type="text" name="localizacao" id="localizacao" class="form-control">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="ativo">Ativo</option>
                <option value="inativo">Inativo</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="capacidade" class="form-label">Capacidade</label>
            <input type="text" name="capacidade" id="capacidade" class="form-control">
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
