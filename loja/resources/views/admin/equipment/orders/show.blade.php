@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Detalhe da encomenda #{{ $order->id }}">
  <div class="container" style="max-width:720px;">
    <a href="{{ route('admin.equipment.orders.index') }}" class="store-link" style="font-size:0.95rem;">&larr; Voltar às encomendas</a>

    <h2 style="margin-top:1rem;">Encomenda #{{ $order->id }}</h2>

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-top:1.5rem;">

      {{-- Dados do cliente --}}
      <div class="plan-card-modern" style="max-width:100%;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.75rem;">Dados do cliente</h3>
        <p><strong>Nome:</strong> {{ $order->customer_name }}</p>
        <p><strong>Telefone:</strong> {{ $order->customer_phone }}</p>
        @if ($order->customer_email)
          <p><strong>E-mail:</strong> {{ $order->customer_email }}</p>
        @endif
        @if ($order->customer_address)
          <p><strong>Morada:</strong> {{ $order->customer_address }}</p>
        @endif
        @if ($order->notes)
          <p style="margin-top:0.5rem;"><strong>Observações:</strong> {{ $order->notes }}</p>
        @endif
      </div>

      {{-- Dados da encomenda --}}
      <div class="plan-card-modern" style="max-width:100%;">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.75rem;">Dados da encomenda</h3>
        <p><strong>Estado:</strong>
          <span style="background:#f1f5f9;border-radius:0.4rem;padding:0.15rem 0.5rem;font-size:0.9rem;font-weight:700;">{{ $order->status }}</span>
        </p>
        <p><strong>Pagamento:</strong>
          @if ($order->payment_method === 'multicaixa_express') Multicaixa Express
          @elseif ($order->payment_method === 'paypal') PayPal
          @elseif ($order->payment_method === 'cash') Pagamento na entrega
          @else {{ $order->payment_method ?? '—' }}
          @endif
        </p>
        <p><strong>Total:</strong> {{ number_format($order->total_aoa, 0, ',', '.') }} Kz</p>
        <p><strong>Data:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
      </div>
    </div>

    {{-- Itens da encomenda --}}
    <div class="plan-card-modern" style="max-width:100%;margin-top:1.25rem;">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.75rem;">Itens encomendados</h3>
      @foreach ($order->items as $item)
        <div style="display:flex;justify-content:space-between;padding:0.45rem 0;border-bottom:1px solid #f1f5f9;font-size:0.96rem;">
          <span>{{ $item['product_name'] }} × {{ $item['quantity'] }}</span>
          <strong>{{ number_format($item['unit_price_aoa'] * $item['quantity'], 0, ',', '.') }} Kz</strong>
        </div>
      @endforeach
      <div style="display:flex;justify-content:space-between;padding:0.6rem 0;font-size:1.05rem;font-weight:800;color:#2563eb;">
        <span>Total</span>
        <span>{{ number_format($order->total_aoa, 0, ',', '.') }} Kz</span>
      </div>
    </div>

    {{-- Alterar estado --}}
    <div class="plan-card-modern" style="max-width:100%;margin-top:1.25rem;">
      <h3 style="font-size:1rem;font-weight:700;margin-bottom:0.75rem;">Alterar estado da encomenda</h3>
      <form method="POST" action="{{ route('admin.equipment.orders.status', $order->id) }}"
            style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
        @csrf @method('PATCH')
        <select name="status" style="padding:0.6rem;border:1.5px solid #e2e8f0;border-radius:0.5rem;font-size:1rem;">
          @foreach ($statuses as $st)
            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn-modern">Guardar estado</button>
      </form>
    </div>
  </div>
</section>
@endsection
