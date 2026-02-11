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
              <a href="{{ route('plan-templates.edit', $t) }}" class="btn">Editar</a>
              <form action="{{ route('plan-templates.destroy', $t) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Remover este modelo?');">
                @csrf
                @method('DELETE')
                <button class="btn" type="submit">Remover</button>
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
