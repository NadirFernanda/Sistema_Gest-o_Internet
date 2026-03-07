@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Encomendas de equipamentos">
  <div class="container">
    <h2>Encomendas de Equipamentos</h2>
    <p class="lead">Lista de todas as encomendas de produtos da loja.</p>

    @if (session('success'))
      <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    {{-- Filtro de estado --}}
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;" role="group" aria-label="Filtrar por estado">
      <a href="{{ route('admin.equipment.orders.index') }}"
         class="plan-feature {{ !request('status') ? 'plan-feature--active' : '' }}">Todos</a>
      @foreach ($statuses as $st)
        <a href="{{ route('admin.equipment.orders.index', ['status' => $st]) }}"
           class="plan-feature {{ request('status') === $st ? 'plan-feature--active' : '' }}">
          {{ ucfirst($st) }}
        </a>
      @endforeach
    </div>

    @if ($orders->isEmpty())
      <p style="color:#64748b;">Nenhuma encomenda encontrada.</p>
    @else
      <div class="plans-table" style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;background:#fff;border-radius:0.75rem;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">
          <thead>
            <tr style="background:#f8fafc;">
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">#</th>
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Cliente</th>
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Telefone</th>
              <th style="text-align:right;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Total (Kz)</th>
              <th style="text-align:center;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Estado</th>
              <th style="text-align:left;padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;">Data</th>
              <th style="padding:0.75rem 1rem;border-bottom:1.5px solid #e2e8f0;"></th>
            </tr>
          </thead>
          <tbody>
            @foreach ($orders as $order)
              <tr>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;color:#94a3b8;">#{{ $order->id }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;font-weight:600;">{{ $order->customer_name }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;">{{ $order->customer_phone }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;text-align:right;font-weight:700;">{{ number_format($order->total_aoa, 0, ',', '.') }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;text-align:center;">
                  <span style="background:#f1f5f9;border-radius:0.4rem;padding:0.2rem 0.6rem;font-size:0.88rem;font-weight:600;">{{ $order->status }}</span>
                </td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;font-size:0.9rem;color:#64748b;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td style="padding:0.6rem 1rem;border-bottom:1px solid #f1f5f9;">
                  <a href="{{ route('admin.equipment.orders.show', $order->id) }}" class="btn-modern" style="font-size:0.88rem;padding:0.4rem 0.9rem;">Ver</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div style="margin-top:1.5rem;">
        {{ $orders->links() }}
      </div>
    @endif
  </div>
</section>
@endsection
