@extends('layouts.app')

@section('title', 'Encomenda #{{ $order->id }} &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:760px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-bottom:1rem;}
@media(max-width:600px){.ap-grid-2{grid-template-columns:1fr}}
.ap-card{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.25rem;margin-bottom:1rem;}
.ap-card-title{font-size:.88rem;font-weight:700;margin:0 0 .85rem;padding-bottom:.6rem;border-bottom:1px solid var(--a-border);}
.ap-kv{display:flex;flex-direction:column;gap:.5rem;font-size:.875rem;}
.ap-kv dt{color:var(--a-muted);font-size:.78rem;font-weight:500;margin:0;}
.ap-kv dd{font-weight:600;margin:0;color:var(--a-text);}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}
.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}.bg-orange{background:#ffedd5;color:#c2410c;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
.ap-btn-primary{display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.2rem;border-radius:8px;font-size:.875rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;background:#f7b500;color:#1a202c;transition:filter .15s;}
.ap-btn-primary:hover{filter:brightness(.95);}
.ap-item{display:flex;justify-content:space-between;align-items:center;padding:.5rem 0;border-bottom:1px solid #f4f6f9;font-size:.88rem;}
.ap-item:last-child{border-bottom:none;}
.ap-item-total{display:flex;justify-content:space-between;align-items:center;padding:.6rem 0;font-size:1rem;font-weight:800;color:var(--a-text);border-top:2px solid var(--a-border);margin-top:.25rem;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Encomenda #{{ $order->id }}</h1>
      <p class="ap-sub">Admin &rsaquo; Encomendas de Equipamentos</p>
    </div>
    <a href="{{ route('admin.equipment.orders.index') }}" class="ap-back">&larr; Todas as encomendas</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  @php
    $statusColors = [
      'pending'   => 'bg-amber',
      'confirmed' => 'bg-blue',
      'shipped'   => 'bg-orange',
      'delivered' => 'bg-green',
      'cancelled' => 'bg-red',
    ];
  @endphp

  <div class="ap-grid-2">
    {{-- Dados do cliente --}}
    <div class="ap-card">
      <p class="ap-card-title">Dados do cliente</p>
      <dl class="ap-kv">
        <div><dt>Nome</dt><dd>{{ $order->customer_name }}</dd></div>
        <div><dt>Telefone</dt><dd>{{ $order->customer_phone }}</dd></div>
        @if($order->customer_email)
          <div><dt>E-mail</dt><dd>{{ $order->customer_email }}</dd></div>
        @endif
        @if($order->customer_address)
          <div><dt>Morada</dt><dd>{{ $order->customer_address }}</dd></div>
        @endif
        @if($order->notes)
          <div><dt>Observa&ccedil;&otilde;es</dt><dd>{{ $order->notes }}</dd></div>
        @endif
      </dl>
    </div>

    {{-- Dados da encomenda --}}
    <div class="ap-card">
      <p class="ap-card-title">Dados da encomenda</p>
      <dl class="ap-kv">
        <div>
          <dt>Estado</dt>
          <dd><span class="badge {{ $statusColors[$order->status] ?? 'bg-gray' }}">{{ $order->statusLabel() }}</span></dd>
        </div>
        <div>
          <dt>Pagamento</dt>
          <dd>
            @if($order->payment_method === 'multicaixa_express') Multicaixa Express
            @elseif($order->payment_method === 'paypal') PayPal
            @elseif($order->payment_method === 'cash') Pagamento na entrega
            @else {{ $order->payment_method ?? '&mdash;' }}
            @endif
          </dd>
        </div>
        <div><dt>Total</dt><dd style="font-size:1rem;font-weight:800;">{{ number_format($order->total_aoa, 0, ',', '.') }} Kz</dd></div>
        <div><dt>Data</dt><dd>{{ $order->created_at->format('d/m/Y H:i') }}</dd></div>
      </dl>
    </div>
  </div>

  {{-- Itens --}}
  <div class="ap-card">
    <p class="ap-card-title">Itens encomendados</p>
    @foreach($order->items as $item)
      <div class="ap-item">
        <span>{{ $item['product_name'] }} &times; {{ $item['quantity'] }}</span>
        <strong>{{ number_format($item['unit_price_aoa'] * $item['quantity'], 0, ',', '.') }} Kz</strong>
      </div>
    @endforeach
    <div class="ap-item-total">
      <span>Total</span>
      <span>{{ number_format($order->total_aoa, 0, ',', '.') }} Kz</span>
    </div>
  </div>

  {{-- Alterar estado --}}
  <div class="ap-card">
    <p class="ap-card-title">Alterar estado da encomenda</p>
    <form method="POST" action="{{ route('admin.equipment.orders.status', $order->id) }}"
          style="display:flex;gap:.75rem;align-items:flex-end;flex-wrap:wrap;">
      @csrf @method('PATCH')
      <div style="display:flex;flex-direction:column;gap:.25rem;">
        <label class="ap-label" for="status">Novo estado</label>
        <select id="status" name="status" class="ap-ctrl" style="width:auto;">
          @foreach(\App\Models\EquipmentOrder::statusLabels() as $st => $label)
            <option value="{{ $st }}" {{ $order->status === $st ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="ap-btn-primary">Guardar estado</button>
    </form>
  </div>

</div></div>
@endsection
