@extends('layouts.app')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-orange:#ea580c;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-err{background:#fef2f2;border:1px solid #fecaca;border-left:4px solid var(--a-red);color:#7f1d1d;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-stats{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:.75rem;margin-bottom:1.5rem;}
.ap-stat{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1rem 1.1rem;}
.ap-stat-val{font-size:1.7rem;font-weight:800;line-height:1;margin:0 0 .2rem;}
.ap-stat-lbl{font-size:.75rem;color:var(--a-muted);font-weight:500;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn{display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;transition:filter .15s;text-decoration:none;white-space:nowrap;}
.ap-btn-primary{background:#f7b500;color:#1a202c;}.ap-btn-primary:hover{filter:brightness(.95);}
.ap-btn-outline{background:var(--a-surf);color:var(--a-muted);border:1.5px solid var(--a-border);}.ap-btn-outline:hover{background:var(--a-border);}
.ap-btn-sm{padding:.35rem .75rem;font-size:.8rem;}
.ap-filters{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:.9rem 1.1rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:flex-end;margin-bottom:1.25rem;}
.ap-fg{display:flex;flex-direction:column;gap:.25rem;}
.ap-fg.grow{flex:1;min-width:170px;}
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;overflow:hidden;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.65rem 1rem;font-size:.69rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-orange{background:#ffedd5;color:#9a3412;}
.bg-green{background:#dcfce7;color:#15803d;}.bg-gray{background:#f1f5f9;color:#475569;}
.bg-red{background:#fee2e2;color:#b91c1c;}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Recargas WiFi</h1>
      <p class="ap-sub">Admin &rsaquo; Compras de planos individuais</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  <form method="get" class="ap-filters">
    <div class="ap-fg">
      <label class="ap-label">Estado</label>
      <select name="status" class="ap-ctrl" style="min-width:160px;">
        <option value="">Todos os estados</option>
        @foreach([\App\Models\AutovendaOrder::STATUS_PENDING => 'Pendente', \App\Models\AutovendaOrder::STATUS_AWAITING_PAYMENT => 'A aguardar pagamento', \App\Models\AutovendaOrder::STATUS_PAID => 'Pago', \App\Models\AutovendaOrder::STATUS_CANCELLED => 'Cancelado', \App\Models\AutovendaOrder::STATUS_FAILED => 'Falhou', \App\Models\AutovendaOrder::STATUS_EXPIRED => 'Expirado'] as $value => $label)
          <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">M&eacute;todo</label>
      <select name="payment_method" class="ap-ctrl" style="min-width:180px;">
        <option value="">Todos os m&eacute;todos</option>
        <option value="{{ \App\Models\AutovendaOrder::METHOD_MULTICAIXA }}" @selected(request('payment_method') === \App\Models\AutovendaOrder::METHOD_MULTICAIXA)>Multicaixa Express</option>
        <option value="{{ \App\Models\AutovendaOrder::METHOD_PAYPAL }}"     @selected(request('payment_method') === \App\Models\AutovendaOrder::METHOD_PAYPAL)>PayPal</option>
      </select>
    </div>
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="ID, e-mail, telefone, refer&ecirc;ncia...">
    </div>
    <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
    @if(request()->hasAny(['status','payment_method','q']))
      <a href="{{ route('admin.autovenda.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Plano</th>
          <th>Valor</th>
          <th>Estado</th>
          <th>M&eacute;todo</th>
          <th>Cliente</th>
          <th>Criada em</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td style="font-weight:700;color:var(--a-amber);">#{{ $order->id }}</td>
            <td style="font-weight:600;">{{ $order->plan_name ?? $order->plan_id }}</td>
            <td>{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</td>
            <td>
              @if($order->status === 'paid')
                <span class="badge bg-green">Pago</span>
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
            <td>
              @if($order->payment_method === \App\Models\AutovendaOrder::METHOD_MULTICAIXA)
                <span class="badge bg-amber">Multicaixa Express</span>
              @elseif($order->payment_method === \App\Models\AutovendaOrder::METHOD_PAYPAL)
                <span class="badge bg-gray">PayPal</span>
              @elseif($order->payment_method)
                <span class="badge bg-gray">{{ $order->payment_method }}</span>
              @else
                <span class="dim">&mdash;</span>
              @endif
            </td>
            <td>
              @if($order->customer_email || $order->customer_phone)
                {{ $order->customer_email }}
                @if($order->customer_phone)
                  <span class="dim"> / {{ $order->customer_phone }}</span>
                @endif
              @else
                <span class="dim">Sem dados</span>
              @endif
            </td>
            <td class="dim">{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhuma recarga encontrada</p>
                <p class="ap-empty-s">Ajuste os filtros ou aguarde novas compras.</p>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
    <div class="ap-pager">{{ $orders->links() }}</div>
  </div>

</div></div>
@endsection
