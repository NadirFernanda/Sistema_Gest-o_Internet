@extends('layouts.app')

@section('title', 'Pagamento Confirmado (Protótipo)')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Pagamento Confirmado (Protótipo)</h1>

  <div class="p-4 bg-white rounded shadow">
    <p class="mb-2">A ordem #{{ $order->id }} foi marcada como <strong>{{ $order->status }}</strong>.</p>
    <p class="mb-2"><strong>Código WiFi gerado:</strong> <code>{{ $order->wifi_code }}</code></p>
    <p class="mb-4 text-sm text-gray-700">Um e-mail com este código foi enviado para {{ $order->customer_email }} (em ambiente real).</p>

    <a href="{{ url('/') }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded">Ir para a página inicial</a>
  </div>
@endsection
