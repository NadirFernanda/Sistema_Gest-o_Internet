@extends('layouts.app')

@section('content')
  <div class="container">
    <h1>Modelos de Plano</h1>
    <a href="{{ route('plan-templates.create') }}" class="btn btn-primary">Novo Modelo</a>
    <table class="table" style="width:100%; margin-top:16px; border-collapse:collapse;">
      <thead>
        <tr style="text-align:left">
          <th>Nome</th>
          <th>Preço</th>
          <th>Ciclo (dias)</th>
          <th>Estado</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($templates as $t)
          <tr>
            <td>{{ $t->name }}</td>
            <td>{{ $t->preco ? 'Kz ' . number_format($t->preco, 2, ',', '.') : '-' }}</td>
            <td>{{ $t->ciclo ?? '-' }}</td>
            <td>{{ $t->estado ?? '-' }}</td>
            <td style="text-align:right">
              <a href="{{ route('plan-templates.edit', $t) }}" class="btn-icon btn-warning" title="Editar" aria-label="Editar modelo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
              </a>
              <form action="{{ route('plan-templates.destroy', $t) }}" method="POST" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Remover este modelo?');">
                @csrf
                @method('DELETE')
                <button class="btn-icon btn-danger" type="submit" title="Remover" aria-label="Remover modelo">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/></svg>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="5">Nenhum modelo cadastrado.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
