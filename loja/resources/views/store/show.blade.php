@extends('layouts.app')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Detalhe do Plano</h1>
  <div class="p-4 bg-white rounded shadow">
    <p>ID: {{ $id }}</p>
    <form method="GET" action="/checkout">
      <button class="mt-4 px-3 py-2 bg-green-600 text-white rounded">Comprar</button>
    </form>
  </div>
@endsection
