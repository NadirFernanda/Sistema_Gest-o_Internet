@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Editar Modelo</h1>
    <form action="{{ route('plan-templates.update', $template) }}" method="POST">
      @csrf
      @method('PUT')
      <div>
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $template->name) }}" required>
      </div>
      <div>
        <label>Descrição</label>
        <textarea name="description">{{ old('description', $template->description) }}</textarea>
      </div>
      <div>
        <label>Preço (Kz)</label>
        <input type="number" step="0.01" name="preco" value="{{ old('preco', $template->preco) }}">
      </div>
      <div>
        <label>Ciclo (dias)</label>
        <input type="number" name="ciclo" value="{{ old('ciclo', $template->ciclo) }}">
      </div>
      <div>
        <label>Estado</label>
        <input type="text" name="estado" value="{{ old('estado', $template->estado) }}">
      </div>
      <div style="margin-top:12px">
        <button class="btn btn-primary" type="submit">Salvar</button>
        <a href="{{ route('plan-templates.index') }}" class="btn">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
