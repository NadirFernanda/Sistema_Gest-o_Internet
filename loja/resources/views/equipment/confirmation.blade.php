@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Confirmação de encomenda">
  <div class="container" style="max-width:640px;text-align:center;">

    <div class="plan-card-modern" style="max-width:100%;padding:2.5rem 1.5rem;">
      <div style="font-size:4rem;margin-bottom:1rem;">✅</div>
      <h2 style="font-size:1.7rem;color:#16a34a;margin-bottom:0.5rem;">Encomenda Confirmada!</h2>
      <p style="color:#64748b;font-size:1.05rem;margin-bottom:1.5rem;">
        Obrigado pela sua encomenda, <strong>{{ $order->customer_name }}</strong>!<br>
        O número da sua encomenda é <strong>#{{ $order->id }}</strong>.
      </p>

      <div style="background:#f8fafc;border-radius:0.75rem;padding:1.25rem;text-align:left;margin-bottom:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.75rem;">Detalhes da encomenda</h3>

        @foreach ($order->items as $item)
          <div style="display:flex;justify-content:space-between;padding:0.35rem 0;border-bottom:1px solid #e2e8f0;font-size:0.96rem;">
            <span>{{ $item['product_name'] }} × {{ $item['quantity'] }}</span>
            <strong>{{ number_format($item['unit_price_aoa'] * $item['quantity'], 0, ',', '.') }} Kz</strong>
          </div>
        @endforeach

        <div style="display:flex;justify-content:space-between;padding:0.6rem 0;font-size:1.05rem;font-weight:800;color:#2563eb;">
          <span>Total pago</span>
          <span>{{ number_format($order->total_aoa, 0, ',', '.') }} Kz</span>
        </div>

        <p style="font-size:0.93rem;color:#64748b;margin-top:0.5rem;">
          Método: <strong>
            @if ($order->payment_method === 'multicaixa_express') Multicaixa Express
            @elseif ($order->payment_method === 'paypal') PayPal
            @else Pagamento na entrega
            @endif
          </strong>
        </p>

        @if ($order->estimated_delivery_date)
          <p style="font-size:0.93rem;color:#15803d;margin-top:0.4rem;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Entrega estimada: <strong>{{ $order->estimated_delivery_date->format('d/m/Y') }}</strong>
          </p>
        @endif

        @if ($order->order_type === \App\Models\EquipmentOrder::TYPE_BACKORDER)
          <p style="font-size:0.88rem;color:#d97706;margin-top:0.35rem;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-2px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Encomenda por pedido — prazo de 2 a 30 dias úteis após confirmação do pagamento.
          </p>
        @endif

        @if ($order->customer_address)
          <p style="font-size:0.93rem;color:#64748b;">Entrega: {{ $order->customer_address }}</p>
        @endif
      </div>

      <p style="color:#64748b;font-size:0.97rem;margin-bottom:1.5rem;">
        A nossa equipa irá contactá-lo brevemente
        @if ($order->customer_phone)
          pelo número <strong>{{ $order->customer_phone }}</strong>
        @endif
        para confirmar os detalhes e a entrega.
      </p>

      <div style="display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('equipment.index') }}" class="btn-modern">Ver mais equipamentos</a>
        <a href="{{ url('/') }}" class="btn-modern" style="background:linear-gradient(90deg,#94a3b8,#64748b);">Voltar à loja</a>
      </div>
    </div>
  </div>
</section>
@endsection
