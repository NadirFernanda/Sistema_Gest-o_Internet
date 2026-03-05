@extends('layouts.app')

@section('title', 'Confirmação de Pedido')

@section('content')
  <h1 class="text-xl font-semibold mb-4">Confirmação de Pedido</h1>

  <div class="grid gap-6 md:grid-cols-5">
    <div class="md:col-span-2 p-4 bg-white rounded shadow">
      <h2 class="font-semibold mb-3">Resumo do Plano</h2>
      @isset($order)
        <p class="mb-1 text-sm text-gray-600">Ref. pedido: #{{ $order->id }}</p>
        @if(!empty($order->wifi_code))
          <p class="mb-1"><strong>Código WiFi:</strong> <code>{{ $order->wifi_code }}</code></p>
        @endif
      @endisset
      <p class="mb-1"><strong>Plano:</strong> {{ $plan['name'] }}</p>
      <p class="mb-1"><strong>Duração:</strong> {{ $plan['duration_label'] }}</p>
      <p class="mb-1"><strong>Velocidade:</strong> {{ $plan['speed'] }}</p>
      <p class="mb-1"><strong>Quantidade:</strong> 1 código</p>
      <p class="text-lg font-bold mt-2">Total: {{ number_format($plan['price_kwanza'], 0, ',', '.') }} AOA</p>
    </div>

    <div class="md:col-span-3 p-4 bg-white rounded shadow">
      <h2 class="font-semibold mb-3">Dados do Pedido</h2>

      @php
        $hasCustomerData = !empty($customer['nome']) || !empty($customer['email']) || !empty($customer['telefone']) || !empty($customer['nif'] ?? null);
      @endphp

      @if($hasCustomerData)
        <p class="mb-1"><strong>Nome:</strong> {{ $customer['nome'] }}</p>
        <p class="mb-1"><strong>E-mail:</strong> {{ $customer['email'] }}</p>
        <p class="mb-1"><strong>Telefone / WhatsApp:</strong> {{ $customer['telefone'] }}</p>
        @if(!empty($customer['nif']))
          <p class="mb-1"><strong>NIF:</strong> {{ $customer['nif'] }}</p>
        @endif
      @else
        <p class="mb-2 text-sm text-gray-700">
          Nenhum dado pessoal foi solicitado para esta compra. O seu código WiFi está disponível acima
          nesta página e pode ser utilizado imediatamente.
        </p>
      @endif

      @isset($order)
        <p class="mb-1"><strong>Método de pagamento:</strong>
          @if($order->payment_method === \App\Models\AutovendaOrder::METHOD_MULTICAIXA)
            Multicaixa Express
          @elseif($order->payment_method === \App\Models\AutovendaOrder::METHOD_PAYPAL)
            PayPal
          @else
            {{ $order->payment_method }}
          @endif
        </p>
        <p class="mb-1 text-sm text-gray-600"><strong>Estado interno:</strong> {{ $order->status }}</p>
      @endisset

      <a href="{{ url('/') }}" class="inline-block mt-4 px-4 py-2 btn-primary">
        Voltar à página inicial
      </a>
    </div>
  </div>
@endsection
