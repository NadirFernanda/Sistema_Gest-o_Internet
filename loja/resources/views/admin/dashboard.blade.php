@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Painel administrativo da loja">
  <div class="container">
    <h2>Visão geral da loja</h2>
    <p class="lead">Resumo rápido das vendas de autovenda e pedidos de revenda.</p>

    <div class="info-grid" style="margin-top:1.5rem;">
      <div class="info-card">
        <h3>Ordens de autovenda</h3>
        <p>Total de ordens: <strong>{{ $totalOrders }}</strong></p>
        <p>Pagas: <strong>{{ $paidOrders }}</strong></p>
        <p>A aguardar pagamento: <strong>{{ $awaitingPayment }}</strong></p>
        <p style="margin-top:.5rem;">Receita confirmada: <strong>{{ number_format($totalRevenueAoa, 0, ',', '.') }} AOA</strong></p>
      </div>

      <div class="info-card">
        <h3>Revenda</h3>
        <p>Pedidos pendentes: <strong>{{ $pendingResellers }}</strong></p>
        <p>Total de pedidos: <strong>{{ $totalResellers }}</strong></p>
        <p class="plan-note" style="margin-top:.5rem;">Use o menu Administração &rarr; Revendedores para ver os detalhes.</p>
      </div>
    </div>

    <div class="info-grid" style="margin-top:2rem;">
      <div class="info-card" style="grid-column:1 / -1;">
        <h3>Últimas ordens</h3>
        @if($recentOrders->isEmpty())
          <p>Nenhuma ordem registada ainda.</p>
        @else
          <div class="plans-table" style="overflow-x:auto;">
            <table class="w-full text-sm" style="border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">#</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Plano</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Valor</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Estado</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Método</th>
                  <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criada em</th>
                </tr>
              </thead>
              <tbody>
                @foreach($recentOrders as $order)
                  <tr>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">#{{ $order->id }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->plan_name ?? $order->plan_id }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->status }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ $order->payment_method ?? '—' }}</td>
                    <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
</section>
@endsection
