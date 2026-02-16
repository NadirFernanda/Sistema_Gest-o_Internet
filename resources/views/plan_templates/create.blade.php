@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Novo Plano</h1>
    <form action="{{ route('plan-templates.store') }}" method="POST">
      @csrf
      <div>
        <label>Nome</label>
        <input type="text" name="name" required>
      </div>
      <div>
        <label>Descrição</label>
        <textarea name="description"></textarea>
      </div>
      <div>
        <label>Preço (Kz)</label>
        <input type="number" step="0.01" name="preco">
      </div>
      <div>
        <label>Ciclo (dias)</label>
        <input type="number" name="ciclo">
      </div>
      <div>
        <label>Estado</label>
        <input type="text" name="estado">
      </div>
      <div style="margin-top:12px">
        <button class="btn btn-primary" type="submit">Salvar Plano</button>
        <a href="{{ route('plan-templates.index') }}" class="btn">Cancelar</a>
      </div>
    </form>
  </div>
@endsection
