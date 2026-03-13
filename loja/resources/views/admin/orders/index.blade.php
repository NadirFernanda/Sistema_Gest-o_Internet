@extends('layouts.app')

@section('content')
<section class="planos-section" aria-label="Gestão de ordens de autovenda">
  <div class="container">
    <h2>Ordens de Autovenda</h2>
    <p class="lead">Lista de compras rápidas de códigos WiFi.</p>

    <form method="get" class="howto-hero" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:flex-end;">
      <div class="form-row" style="max-width:200px;">
        <label for="status">Estado</label>
        <select id="status" name="status" class="newsletter-input">
          <option value="">Todos</option>
          @foreach([\App\Models\AutovendaOrder::STATUS_PENDING => 'Pendente', \App\Models\AutovendaOrder::STATUS_AWAITING_PAYMENT => 'A aguardar pagamento', \App\Models\AutovendaOrder::STATUS_PAID => 'Pago', \App\Models\AutovendaOrder::STATUS_CANCELLED => 'Cancelado', \App\Models\AutovendaOrder::STATUS_FAILED => 'Falhou', \App\Models\AutovendaOrder::STATUS_EXPIRED => 'Expirado'] as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </div>

      <div class="form-row" style="max-width:200px;">
        <label for="payment_method">Método</label>
        <select id="payment_method" name="payment_method" class="newsletter-input">
          <option value="">Todos</option>
          <option value="{{ \App\Models\AutovendaOrder::METHOD_MULTICAIXA }}" @selected(request('payment_method') === \App\Models\AutovendaOrder::METHOD_MULTICAIXA)>Multicaixa Express</option>
          <option value="{{ \App\Models\AutovendaOrder::METHOD_PAYPAL }}" @selected(request('payment_method') === \App\Models\AutovendaOrder::METHOD_PAYPAL)>PayPal</option>
        </select>
      </div>

      <div class="form-row" style="flex:1;min-width:200px;">
        <label for="q">Pesquisa</label>
        <input id="q" name="q" value="{{ request('q') }}" class="newsletter-input" placeholder="ID, e-mail, telefone, referência ou código WiFi">
      </div>

      <div class="form-actions" style="margin-top:0;">
        <button type="submit" class="btn-primary">Filtrar</button>
      </div>
    </form>

    <div class="plans-table" style="margin-top:1rem;overflow-x:auto;">
      <table class="w-full text-sm" style="border-collapse:collapse;">
        <thead>
          <tr>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">#</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Plano</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Valor</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Estado</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Método</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Cliente</th>
            <th style="text-align:left;padding:.4rem;border-bottom:1px solid #e5e7eb;">Criada em</th>
          </tr>
        </thead>
        <tbody>
          @forelse($orders as $order)
            <tr>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;font-weight:700;color:var(--brand);">#{{ $order->id }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;font-weight:600;">{{ $order->plan_name ?? $order->plan_id }}</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;color:#1b1b18;">{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($order->status === 'paid')
                  <span class="badge bg-amber">Pago</span>
                @elseif($order->status === 'awaiting_payment')
                  <span class="badge bg-orange">Aguarda</span>
                @elseif($order->status === 'pending')
                  <span class="badge bg-gray">Pendente</span>
                @elseif($order->status === 'cancelled')
                  <span class="badge bg-gray">Cancelado</span>
                @elseif($order->status === 'failed')
                  <span class="badge bg-red">Falhou</span>
                @elseif($order->status === 'expired')
                  <span class="badge bg-gray">Expirado</span>
                @else
                  <span class="badge bg-gray">{{ $order->status }}</span>
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($order->payment_method === 'multicaixa')
                  <span class="badge bg-amber">Multicaixa Express</span>
                @elseif($order->payment_method === 'paypal')
                  <span class="badge bg-gray">PayPal</span>
                @else
                  <span class="badge bg-gray">—</span>
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;">
                @if($order->customer_email || $order->customer_phone)
                  {{ $order->customer_email }} @if($order->customer_phone) <span style="color:#706f6c;font-size:.92em;">/ {{ $order->customer_phone }}</span> @endif
                @else
                  <span class="muted">Sem dados (plano rápido)</span>
                @endif
              </td>
              <td style="padding:.4rem;border-bottom:1px solid #f3f4f6;color:#706f6c;">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="padding:.6rem;text-align:center;" class="muted">Nenhuma ordem encontrada para os filtros atuais.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:1rem;">
      {{ $orders->links() }}
    </div>
  </div>
</section>
@endsection
