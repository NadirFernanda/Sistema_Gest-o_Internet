@extends('layouts.app')

@section('title', 'Pedido enviado')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Pedido enviado</h1>

  <div class="p-4 bg-white rounded shadow text-sm text-gray-800">
    <p class="mb-2">Recebemos o seu pedido para ser agente revendedor AngolaWiFi.</p>
    <p class="mb-2">A nossa equipa irá analisar os seus dados e entrar em contacto brevemente, juntamente com os requisitos para ser revendedor.</p>

    <a href="{{ url('/') }}" class="inline-block mt-4 px-4 py-2 bg-blue-600 text-white rounded">Voltar à página inicial</a>
  </div>
@endsection
