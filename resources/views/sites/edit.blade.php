@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Site</h1>
    <form action="{{ route('sites.update', $site) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nome" class="form-label">Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" value="{{ $site->nome }}" required>
        </div>
        <div class="mb-3">
            <label for="localizacao" class="form-label">Localização</label>
            <input type="text" name="localizacao" id="localizacao" class="form-control" value="{{ $site->localizacao }}">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control">
                <option value="ativo" @if($site->status=='ativo') selected @endif>Ativo</option>
                <option value="inativo" @if($site->status=='inativo') selected @endif>Inativo</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="capacidade" class="form-label">Capacidade</label>
            <input type="text" name="capacidade" id="capacidade" class="form-control" value="{{ $site->capacidade }}">
        </div>
        <div class="mb-3">
            <label for="observacoes" class="form-label">Observações</label>
            <textarea name="observacoes" id="observacoes" class="form-control">{{ $site->observacoes }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
