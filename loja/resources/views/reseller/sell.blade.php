@extends('layouts.app')

@push('styles')
<style>
:root {
  --sl-bg:     #f4f6f9;
  --sl-surf:   #ffffff;
  --sl-border: #dde2ea;
  --sl-text:   #1a202c;
  --sl-muted:  #64748b;
  --sl-faint:  #9aa5b4;
  --sl-green:  #16a34a;
  --sl-amber:  #d97706;
  --sl-red:    #dc2626;
  --sl-yellow: #f7b500;
}

.sl-page { font-family: Inter, system-ui, sans-serif; background: var(--sl-bg); min-height: 80vh; padding: 2rem 1rem 4rem; color: var(--sl-text); }
.sl-wrap  { max-width: 1100px; margin: 0 auto; }

/* Header */
.sl-topbar { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.sl-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .2rem; }
.sl-topbar .sl-sub { font-size: .82rem; color: var(--sl-muted); line-height: 1.5; }
.sl-back { font-size: .82rem; font-weight: 600; color: var(--sl-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--sl-border); border-radius: 7px; background: var(--sl-surf); transition: background .15s; white-space: nowrap; }
.sl-back:hover { background: var(--sl-border); color: var(--sl-text); }

/* Alerts */
.sl-ok  { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--sl-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.sl-err { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--sl-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.sl-info { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid var(--sl-amber); color: #78350f; padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 1.25rem; line-height: 1.55; }

/* Layout */
.sl-layout { display: grid; grid-template-columns: 1fr 360px; gap: 1.25rem; align-items: start; }
@media (max-width: 860px) { .sl-layout { grid-template-columns: 1fr; } }

/* Summary cards */
.sl-summary { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: .75rem; margin-bottom: 1.5rem; }
.sl-card { background: var(--sl-surf); border: 1px solid var(--sl-border); border-radius: 10px; padding: .9rem 1rem; }
.sl-card-val { font-size: 1.8rem; font-weight: 800; line-height: 1; margin: 0 0 .2rem; }
.sl-card-lbl { font-size: .74rem; color: var(--sl-muted); font-weight: 500; }

/* Panel */
.sl-panel { background: var(--sl-surf); border: 1px solid var(--sl-border); border-radius: 10px; padding: 1.4rem; margin-bottom: 1.1rem; }
.sl-panel-title { font-size: 1rem; font-weight: 800; color: var(--sl-text); margin: 0 0 1rem; display: flex; align-items: center; gap: .45rem; }

/* Plan cards — catalog */
.sl-plans-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: .85rem; }
.sl-plan-card {
  background: var(--sl-surf);
  border: 2px solid var(--sl-border);
  border-radius: 12px;
  padding: 1.1rem;
  display: flex;
  flex-direction: column;
  gap: .7rem;
  transition: border-color .18s, box-shadow .18s;
}
.sl-plan-card.has-stock:hover { border-color: var(--sl-yellow); box-shadow: 0 4px 18px rgba(247,181,0,.18); }
.sl-plan-card.no-stock { opacity: .55; }
.sl-plan-card-header { display: flex; justify-content: space-between; align-items: flex-start; gap: .5rem; }
.sl-plan-name { font-size: 1rem; font-weight: 800; color: var(--sl-text); }
.sl-plan-price { font-size: 1.15rem; font-weight: 800; color: var(--sl-yellow); white-space: nowrap; }
.sl-plan-meta { font-size: .78rem; color: var(--sl-muted); line-height: 1.5; }
.sl-plan-badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .6rem; border-radius: 999px; font-size: .7rem; font-weight: 700; }
.sl-badge-green  { background: #d1fae5; color: #065f46; }
.sl-badge-gray   { background: #f1f5f9; color: #475569; }
.sl-badge-yellow { background: #fef9c3; color: #854d0e; }

/* Add to cart form inline */
.sl-add-form { display: flex; gap: .5rem; align-items: center; }
.sl-qty-input { width: 72px; padding: .4rem .6rem; border: 1.5px solid var(--sl-border); border-radius: 7px; font-size: .88rem; text-align: center; font-family: inherit; color: var(--sl-text); }
.sl-qty-input:focus { outline: none; border-color: var(--sl-yellow); }
.sl-add-btn { flex: 1; padding: .45rem .8rem; background: var(--sl-yellow); color: #000; font-weight: 700; font-size: .83rem; border: none; border-radius: 7px; cursor: pointer; font-family: inherit; transition: background .14s; white-space: nowrap; }
.sl-add-btn:hover { background: #e0a400; }

/* Cart (right column) */
.sl-cart-sticky { position: sticky; top: 1.5rem; }
.sl-cart-empty { text-align: center; padding: 1.5rem .5rem; color: var(--sl-faint); font-size: .85rem; }

/* Cart items */
.sl-cart-item { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .6rem 0; border-bottom: 1px solid var(--sl-border); }
.sl-cart-item:last-child { border-bottom: none; }
.sl-cart-item-name { font-size: .88rem; font-weight: 700; color: var(--sl-text); }
.sl-cart-item-meta { font-size: .75rem; color: var(--sl-muted); }
.sl-cart-item-price { font-size: .9rem; font-weight: 700; color: var(--sl-text); white-space: nowrap; }
.sl-cart-remove { background: none; border: none; color: var(--sl-faint); cursor: pointer; font-size: 1rem; padding: .15rem .35rem; border-radius: 4px; transition: color .12s, background .12s; }
.sl-cart-remove:hover { color: var(--sl-red); background: #fef2f2; }

/* Cart total */
.sl-cart-total { display: flex; justify-content: space-between; align-items: center; padding: .75rem 0 .5rem; border-top: 2px solid var(--sl-border); margin-top: .25rem; }
.sl-cart-total-lbl { font-size: .82rem; font-weight: 700; color: var(--sl-muted); }
.sl-cart-total-val { font-size: 1.2rem; font-weight: 800; color: var(--sl-yellow); }

/* Customer input */
.sl-customer-wrap { padding: .7rem 0 .75rem; }
.sl-field-label { font-size: .77rem; font-weight: 600; color: var(--sl-muted); margin-bottom: .3rem; }
.sl-customer-input { width: 100%; padding: .5rem .75rem; border: 1.5px solid var(--sl-border); border-radius: 8px; font-size: .87rem; color: var(--sl-text); font-family: inherit; box-sizing: border-box; }
.sl-customer-input:focus { outline: none; border-color: var(--sl-yellow); }

/* Sell button */
.sl-sell-btn { display: block; width: 100%; padding: .75rem 1rem; background: var(--sl-green); color: #fff; font-weight: 700; font-size: .95rem; border: none; border-radius: 9px; cursor: pointer; font-family: inherit; transition: background .14s; text-align: center; }
.sl-sell-btn:hover { background: #15803d; }
.sl-clear-btn { display: block; width: 100%; margin-top: .5rem; padding: .5rem 1rem; background: none; color: var(--sl-muted); font-size: .8rem; border: 1px solid var(--sl-border); border-radius: 7px; cursor: pointer; font-family: inherit; transition: all .13s; text-align: center; text-decoration: none; }
.sl-clear-btn:hover { border-color: var(--sl-red); color: var(--sl-red); background: #fef2f2; }

/* Recent sales */
.sl-recent-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
.sl-recent-table th { text-align: left; padding: .45rem .7rem; font-size: .69rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--sl-faint); border-bottom: 1px solid var(--sl-border); }
.sl-recent-table td { padding: .45rem .7rem; border-bottom: 1px solid #f4f6f9; vertical-align: middle; }
.sl-recent-table .mono { font-family: 'Courier New', monospace; font-weight: 700; font-size: .83rem; }
.sl-recent-table .dim  { color: var(--sl-faint); font-size: .78rem; }

/* Empty state */
.sl-empty { text-align: center; padding: 3rem 1rem; color: var(--sl-faint); }
.sl-empty-icon { font-size: 2.5rem; margin-bottom: .5rem; }
.sl-empty-title { font-size: 1rem; font-weight: 700; color: var(--sl-muted); margin-bottom: .3rem; }

@media (max-width: 640px) {
  .sl-plans-grid { grid-template-columns: 1fr; }
  .sl-summary { grid-template-columns: 1fr 1fr; }
  .sl-recent-table th, .sl-recent-table td { padding: .4rem .45rem; font-size: .76rem; }
}
</style>
@endpush

@section('content')
<div class="sl-page">
<div class="sl-wrap">

  {{-- Flash messages --}}
  @if(session('status'))
    <div class="sl-ok">&#10003; {{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="sl-err">&#10007; {{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="sl-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif

  {{-- Header --}}
  <div class="sl-topbar">
    <div>
      <h1>&#127991; Vender ao Cliente Final</h1>
      <p class="sl-sub">
        Escolha os planos e quantidades que o cliente pediu.<br>
        Após confirmar, o sistema entrega os vouchers automaticamente e gera o PDF para o cliente.
      </p>
    </div>
    <a href="{{ route('reseller.panel') }}" class="sl-back">&larr; Painel</a>
  </div>

  {{-- Summary cards --}}
  <div class="sl-summary">
    <div class="sl-card">
      <p class="sl-card-val" style="color:var(--sl-green)">{{ number_format($totalInStock, 0, ',', '.') }}</p>
      <p class="sl-card-lbl">Vouchers em stock</p>
    </div>
    <div class="sl-card">
      <p class="sl-card-val" style="color:var(--sl-muted)">{{ number_format($totalSold, 0, ',', '.') }}</p>
      <p class="sl-card-lbl">Total vendidos</p>
    </div>
    @foreach($stockByPlan as $slug => $qty)
      @php $plan = $voucherPlans->get($slug); @endphp
      <div class="sl-card">
        <p class="sl-card-lbl" style="font-weight:700;color:var(--sl-text);margin-bottom:.35rem;">{{ $plan ? $plan->name : $slug }}</p>
        <p class="sl-card-val" style="font-size:1.5rem;color:var(--sl-green)">{{ number_format($qty, 0, ',', '.') }}</p>
        <p class="sl-card-lbl">disponíveis</p>
      </div>
    @endforeach
  </div>

  @if($totalInStock === 0)
    @if($pendingPurchases->count() > 0)
      <div class="sl-info">
        <strong>&#9203; Tem {{ $pendingPurchases->count() }} compra(s) pendente(s) de pagamento.</strong><br>
        Os vouchers estão reservados mas só ficam disponíveis para vender após confirmar o pagamento.
        <a href="{{ route('reseller.panel.resume.payment', $pendingPurchases->first()) }}"
           style="display:inline-block;margin-top:.5rem;padding:.4rem .9rem;background:#d97706;color:#fff;border-radius:6px;font-weight:700;text-decoration:none;font-size:.82rem;">
          &#128179; Retomar pagamento &rarr;
        </a>
      </div>
    @endif
    <div class="sl-empty">
      <div class="sl-empty-icon">&#128205;</div>
      <p class="sl-empty-title">Sem vouchers para vender</p>
      <p>Compre vouchers no <a href="{{ route('reseller.panel') }}" style="color:#3b82f6;font-weight:700;">painel principal</a> para começar a revender.</p>
    </div>
  @else

  {{-- Main layout: catalog + cart --}}
  <div class="sl-layout">

    {{-- LEFT: Catálogo de planos disponíveis --}}
    <div>
      <div class="sl-panel">
        <div class="sl-panel-title">&#128230; Planos disponíveis &mdash; escolha a quantidade</div>

        <div class="sl-plans-grid">
          @foreach($stockByPlan as $planSlug => $availableQty)
            @php
              $plan = $voucherPlans->get($planSlug);
              $inCart = 0;
              foreach($sellItems as $item) {
                if ($item['plan']->slug === $planSlug) { $inCart = $item['qty']; break; }
              }
            @endphp
            <div class="sl-plan-card {{ $availableQty > 0 ? 'has-stock' : 'no-stock' }}">
              <div class="sl-plan-card-header">
                <div>
                  <div class="sl-plan-name">{{ $plan ? $plan->name : $planSlug }}</div>
                  @if($plan)
                    <div class="sl-plan-meta">
                      {{ $plan->validity_label }}{{ $plan->speed_label ? ' &middot; ' . $plan->speed_label : '' }}
                    </div>
                  @endif
                </div>
                <div>
                  @if($plan)
                    <div class="sl-plan-price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} Kz</div>
                  @endif
                </div>
              </div>

              <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;">
                <span class="sl-plan-badge {{ $availableQty > 0 ? 'sl-badge-green' : 'sl-badge-gray' }}">
                  {{ $availableQty > 0 ? $availableQty . ' em stock' : 'Sem stock' }}
                </span>
                @if($inCart > 0)
                  <span class="sl-plan-badge sl-badge-yellow">{{ $inCart }} no carrinho</span>
                @endif
              </div>

              @if($availableQty > 0)
                <form action="{{ route('reseller.sell.cart.add') }}" method="POST" class="sl-add-form">
                  @csrf
                  <input type="hidden" name="plan_slug" value="{{ $planSlug }}">
                  <input type="number" name="quantity" value="1" min="1" max="{{ $availableQty }}"
                         class="sl-qty-input" required>
                  <button type="submit" class="sl-add-btn">+ Adicionar</button>
                </form>
              @endif
            </div>
          @endforeach
        </div>
      </div>

      {{-- Recent sales --}}
      @if($recentSales->count() > 0)
      <div class="sl-panel">
        <div class="sl-panel-title">&#128203; Vendas recentes</div>
        <div style="overflow-x:auto;">
          <table class="sl-recent-table">
            <thead>
              <tr>
                <th>Código</th>
                <th>Plano</th>
                <th>Cliente</th>
                <th>Vendido em</th>
              </tr>
            </thead>
            <tbody>
              @foreach($recentSales as $code)
                @php $plan = $voucherPlans->get($code->plan_id); @endphp
                <tr>
                  <td class="mono">{{ $code->code }}</td>
                  <td>{{ $plan ? $plan->name : $code->plan_id }}</td>
                  <td>{{ $code->reseller_customer_ref ?: '&mdash;' }}</td>
                  <td class="dim">{{ optional($code->reseller_distributed_at)->format('d/m/Y H:i') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif
    </div>

    {{-- RIGHT: Carrinho de venda --}}
    <div class="sl-cart-sticky">
      <div class="sl-panel">
        <div class="sl-panel-title">&#128722; Carrinho do cliente</div>

        @if(empty($sellItems))
          <div class="sl-cart-empty">
            <div style="font-size:1.75rem;margin-bottom:.4rem;">&#128717;</div>
            <p>Adicione planos ao carrinho para iniciar a venda.</p>
          </div>
        @else
          {{-- Cart items --}}
          <div>
            @foreach($sellItems as $item)
              <div class="sl-cart-item">
                <div>
                  <div class="sl-cart-item-name">{{ $item['plan']->name }}</div>
                  <div class="sl-cart-item-meta">{{ $item['qty'] }}&times; {{ number_format($item['plan']->price_public_aoa, 0, ',', '.') }} Kz</div>
                </div>
                <div style="display:flex;align-items:center;gap:.4rem;">
                  <div class="sl-cart-item-price">{{ number_format($item['subtotal'], 0, ',', '.') }} Kz</div>
                  <form action="{{ route('reseller.sell.cart.remove') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_slug" value="{{ $item['plan']->slug }}">
                    <button type="submit" class="sl-cart-remove" title="Remover">&times;</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>

          {{-- Total --}}
          <div class="sl-cart-total">
            <span class="sl-cart-total-lbl">Total a cobrar ao cliente</span>
            <span class="sl-cart-total-val">{{ number_format($sellTotal, 0, ',', '.') }} Kz</span>
          </div>

          {{-- Confirm sale form --}}
          <form action="{{ route('reseller.sell.process') }}" method="POST"
                onsubmit="return confirm('Confirma a venda? O sistema irá alocar os vouchers e gerar o PDF para o cliente.')">
            @csrf

            <div class="sl-customer-wrap">
              <div class="sl-field-label">Nome / telefone do cliente (opcional)</div>
              <input type="text" name="customer_ref" class="sl-customer-input"
                     placeholder="Ex: João Silva · 923 456 789"
                     maxlength="200" autocomplete="off"
                     value="{{ old('customer_ref') }}">
            </div>

            <button type="submit" class="sl-sell-btn">
              &#10003; Confirmar venda &amp; gerar PDF
            </button>
          </form>

          <form action="{{ route('reseller.sell.cart.clear') }}" method="POST">
            @csrf
            <button type="submit" class="sl-clear-btn">Limpar carrinho</button>
          </form>
        @endif
      </div>

      {{-- Help tip --}}
      <div style="background:#f8fafc;border:1px solid var(--sl-border);border-radius:9px;padding:.9rem 1rem;font-size:.77rem;color:var(--sl-muted);line-height:1.6;">
        <strong style="color:var(--sl-text);">&#128161; Como funciona</strong><br>
        1. Escolha os planos e quantidades que o cliente pediu.<br>
        2. Preencha o nome/telefone (opcional).<br>
        3. Clique <em>Confirmar venda</em> &mdash; os vouchers são alocados automaticamente e o PDF é gerado para entregar ao cliente.<br>
        <strong style="color:var(--sl-text);">O pagamento é gerido por si.</strong>
      </div>
    </div>

  </div>{{-- /.sl-layout --}}

  @endif

</div>
</div>
@endsection

