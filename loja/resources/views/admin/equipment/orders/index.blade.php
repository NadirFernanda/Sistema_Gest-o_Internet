@extends('layouts.app')

@section('title', 'Encomendas de Equipamentos &mdash; Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1140px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
.ap-ok{background:#f0fdf4;border:1px solid #86efac;border-left:4px solid var(--a-green);color:#166534;padding:.75rem 1rem;border-radius:8px;font-size:.875rem;margin-bottom:1rem;}
.ap-label{display:block;font-size:.77rem;font-weight:600;color:#374151;margin-bottom:.3rem;}
.ap-ctrl{width:100%;box-sizing:border-box;padding:.55rem .75rem;border:1.5px solid var(--a-border);border-radius:8px;font-size:.875rem;color:var(--a-text);background:#f8fafc;font-family:inherit;outline:none;transition:border-color .15s;}
.ap-ctrl:focus{border-color:var(--a-brand);background:#fff;}
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
.ap-table th.r,.ap-table td.r{text-align:right;}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fafbff;}
.ap-table .dim{color:var(--a-faint);font-size:.82rem;}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}
.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}.bg-orange{background:#ffedd5;color:#c2410c;}
.ap-btn{display:inline-flex;align-items:center;gap:.4rem;padding:.38rem .85rem;border-radius:7px;font-size:.8rem;font-weight:700;border:none;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;background:#f1f5f9;color:#374151;transition:background .15s;}
.ap-btn:hover{background:var(--a-border);}
.ap-pager{padding:.7rem 1rem;border-top:1px solid var(--a-border);background:#f8fafc;}
.ap-empty{padding:3rem 1rem;text-align:center;color:var(--a-faint);}
.ap-empty-t{font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;}
.ap-empty-s{font-size:.82rem;margin:0;}
.ap-note{background:#fffbeb;border:1px solid #fde68a;border-left:4px solid var(--a-brand);color:#78350f;padding:.75rem 1rem;border-radius:8px;font-size:.85rem;margin-bottom:1.25rem;line-height:1.55;}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Encomendas de Equipamentos</h1>
      <p class="ap-sub">Admin &rsaquo; Lista de todas as encomendas de produtos da loja</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  @if(session('success'))
    <div class="ap-ok">{{ session('success') }}</div>
  @endif

  <div class="ap-note">
    <strong>O que é esta página?</strong> Lista de todas as encomendas de equipamento físico (routers, cabos, antenas, acessórios) feitas pelos clientes na loja online.<br><br>
    <strong>Estados e o que fazer:</strong><br>
    &bull; <strong>Pendente</strong>: encomenda nova, ainda não processada. Confirme a disponibilidade do produto e use <strong>"Ver →"</strong> para processar.<br>
    &bull; <strong>Confirmado</strong>: encomenda aceite e em preparação para envio.<br>
    &bull; <strong>Enviado</strong>: produto despachado para o endereço do cliente. A aguardar recepção.<br>
    &bull; <strong>Entregue</strong>: cliente recebeu o equipamento. Encomenda concluída com sucesso.<br>
    &bull; <strong>Cancelado</strong>: encomenda cancelada. Nenhum produto foi enviado.<br><br>
    <strong>Como gerir uma encomenda:</strong> Clique em <strong>"Ver →"</strong> (coluna da direita) para abrir os detalhes completos &mdash; aí encontrará os dados do cliente, produtos encomendados, endereço de entrega e a opção de actualizar o estado do pedido.
  </div>

  @php
    $statusColors = [
      'pending'   => 'bg-amber',
      'confirmed' => 'bg-blue',
      'shipped'   => 'bg-orange',
      'delivered' => 'bg-green',
      'cancelled' => 'bg-red',
    ];
  @endphp

  {{-- Filtros --}}
  <form method="get" class="ap-filters">
    <div class="ap-fg">
      <label class="ap-label">Estado</label>
      <select name="status" class="ap-ctrl" style="min-width:160px;">
        <option value="">Todos os estados</option>
        @foreach(\App\Models\EquipmentOrder::statusLabels() as $value => $label)
          <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">M&eacute;todo</label>
      <select name="payment_method" class="ap-ctrl" style="min-width:175px;">
        <option value="">Todos os m&eacute;todos</option>
        <option value="multicaixa_express" @selected(request('payment_method') === 'multicaixa_express')>Multicaixa Express</option>
        <option value="paypal"             @selected(request('payment_method') === 'paypal')>PayPal</option>
        <option value="cash"               @selected(request('payment_method') === 'cash')>Dinheiro</option>
      </select>
    </div>
    <div class="ap-fg">
      <label class="ap-label">De</label>
      <input type="date" name="date_from" value="{{ request('date_from') }}" class="ap-ctrl" style="min-width:140px;">
    </div>
    <div class="ap-fg">
      <label class="ap-label">At&eacute;</label>
      <input type="date" name="date_to" value="{{ request('date_to') }}" class="ap-ctrl" style="min-width:140px;">
    </div>
    <div class="ap-fg grow">
      <label class="ap-label">Pesquisa</label>
      <input name="q" value="{{ request('q') }}" class="ap-ctrl" placeholder="ID, nome, telefone, e-mail...">
    </div>
    <button type="submit" class="ap-btn ap-btn-primary">Filtrar</button>
    @if(request()->hasAny(['status','payment_method','date_from','date_to','q']))
      <a href="{{ route('admin.equipment.orders.index') }}" class="ap-btn ap-btn-outline ap-btn-sm">Limpar</a>
    @endif
  </form>

  <div class="ap-tcard">
    <table class="ap-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>Telefone</th>
          <th class="r">Total (Kz)</th>
          <th>Estado</th>
          <th>Data</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $order)
          <tr>
            <td class="dim">#{{ $order->id }}</td>
            <td style="font-weight:600;">{{ $order->customer_name }}</td>
            <td>{{ $order->customer_phone }}</td>
            <td class="r" style="font-weight:700;">{{ number_format($order->total_aoa, 0, ',', '.') }}</td>
            <td>
              <span class="badge {{ $statusColors[$order->status] ?? 'bg-gray' }}">{{ $order->statusLabel() }}</span>
            </td>
            <td class="dim" style="white-space:nowrap;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            <td>
              <a href="{{ route('admin.equipment.orders.show', $order->id) }}" class="ap-btn">Ver &rarr;</a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7">
              <div class="ap-empty">
                <p class="ap-empty-t">Nenhuma encomenda encontrada</p>
                <p class="ap-empty-s">Sem resultados para o filtro seleccionado.</p>
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
