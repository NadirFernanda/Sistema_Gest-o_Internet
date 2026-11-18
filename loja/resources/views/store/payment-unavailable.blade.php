@extends('layouts.app')

@section('title', 'Pagamento indisponível — AngolaWiFi')

@section('content')
<main style="max-width:650px;margin:4rem auto;padding:2rem;background:#fff;border-radius:16px;box-shadow:0 4px 24px #0001;">
  <h1 style="margin:0 0 1rem;">Pagamento temporariamente indisponível</h1>
  <p>Não é possível iniciar um novo pagamento neste momento. Se já pagou este pedido, não volte a pagar: contacte o suporte para verificarmos a transacção e entregarmos o voucher.</p>
  <p><strong>Pedido #{{ $order->id }}</strong>@if($order->payment_reference) · Referência {{ $order->payment_reference }}@endif</p>
  <a href="mailto:suporte@angolawifi.ao?subject={{ rawurlencode('Pagamento pendente - pedido #'.$order->id) }}">Contactar suporte</a>
</main>
@endsection
