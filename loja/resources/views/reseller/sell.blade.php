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
  --sl-blue:   #3b82f6;
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

/* Summary cards */
.sl-summary { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: .85rem; margin-bottom: 1.75rem; }
.sl-card { background: var(--sl-surf); border: 1px solid var(--sl-border); border-radius: 10px; padding: 1rem 1.1rem; }
.sl-card-val { font-size: 2rem; font-weight: 800; line-height: 1; margin: 0 0 .25rem; }
.sl-card-lbl { font-size: .75rem; color: var(--sl-muted); font-weight: 500; }

/* Info box */
.sl-info { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid var(--sl-amber); color: #78350f; padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 1.25rem; line-height: 1.55; }

/* Panel */
.sl-panel { background: var(--sl-surf); border: 1px solid var(--sl-border); border-radius: 10px; padding: 1.5rem; margin-bottom: 1.25rem; }
.sl-panel-title { font-size: 1.05rem; font-weight: 800; color: var(--sl-text); margin: 0 0 1rem; display: flex; align-items: center; gap: .5rem; }

/* Plan group */
.sl-plan-group { margin-bottom: 1.5rem; }
.sl-plan-group:last-child { margin-bottom: 0; }
.sl-plan-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; padding: .65rem 1rem; background: #f8fafc; border-radius: 8px; margin-bottom: .75rem; }
.sl-plan-name { font-size: .95rem; font-weight: 800; color: var(--sl-text); }
.sl-plan-meta { font-size: .78rem; color: var(--sl-muted); }
.sl-plan-count { font-size: .82rem; font-weight: 700; }
.sl-plan-count.ok  { color: var(--sl-green); }
.sl-plan-count.out { color: var(--sl-faint); }

/* Badges */
.sl-badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .65rem; border-radius: 999px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
.sl-badge-diario  { background: #dbeafe; color: #1d4ed8; }
.sl-badge-semanal { background: #ede9fe; color: #6d28d9; }
.sl-badge-mensal  { background: #fef3c7; color: #b45309; }
.sl-badge-default { background: #f1f5f9; color: #475569; }

/* Voucher grid */
.sl-voucher-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .5rem; }
.sl-voucher-item {
  display: flex; align-items: center; gap: .65rem;
  padding: .55rem .85rem;
  border: 1.5px solid var(--sl-border);
  border-radius: 8px;
  transition: border-color .15s, background .15s;
  cursor: pointer;
  user-select: none;
}
.sl-voucher-item:hover { border-color: var(--sl-yellow); background: #fffdf5; }
.sl-voucher-item.selected { border-color: var(--sl-green); background: #f0fdf4; }
.sl-voucher-item input[type=checkbox] { width: 18px; height: 18px; accent-color: var(--sl-green); cursor: pointer; flex-shrink: 0; }
.sl-voucher-code { font-family: 'Courier New', monospace; font-weight: 700; font-size: .9rem; letter-spacing: .04em; color: #0f172a; }
.sl-voucher-plan { font-size: .72rem; color: var(--sl-muted); }

/* Quick quantity selector */
.sl-qty-row { display: flex; align-items: center; gap: .5rem; margin-bottom: .65rem; flex-wrap: wrap; }
.sl-qty-label { font-size: .82rem; color: var(--sl-muted); font-weight: 600; }
.sl-qty-input { width: 80px; padding: .4rem .65rem; border: 1.5px solid var(--sl-border); border-radius: 7px; font-size: .88rem; text-align: center; font-family: inherit; color: var(--sl-text); }
.sl-qty-input:focus { outline: none; border-color: var(--sl-yellow); }
.sl-qty-btn { padding: .35rem .75rem; border: 1.5px solid var(--sl-border); border-radius: 7px; font-size: .78rem; font-weight: 700; cursor: pointer; background: var(--sl-surf); color: var(--sl-muted); transition: all .15s; font-family: inherit; }
.sl-qty-btn:hover { border-color: var(--sl-green); color: var(--sl-green); background: #f0fdf4; }
.sl-qty-btn.active { border-color: var(--sl-green); color: #fff; background: var(--sl-green); }

/* Sell bar (sticky bottom) */
.sl-sell-bar {
  position: sticky;
  bottom: 0;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-top: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  box-shadow: 0 -4px 24px rgba(0,0,0,.15);
  z-index: 50;
}
.sl-sell-bar .sl-sell-info { color: #fff; }
.sl-sell-bar .sl-sell-count { font-size: 1.1rem; font-weight: 800; color: var(--sl-yellow); }
.sl-sell-bar .sl-sell-sub { font-size: .82rem; color: #94a3b8; }
.sl-sell-bar .sl-sell-actions { display: flex; gap: .65rem; align-items: center; flex-wrap: wrap; }
.sl-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .6rem 1.25rem; border-radius: 8px; font-size: .9rem; font-weight: 700; border: none; cursor: pointer; font-family: inherit; text-decoration: none; white-space: nowrap; transition: all .15s; }
.sl-btn-sell { background: var(--sl-green); color: #fff; }
.sl-btn-sell:hover { background: #15803d; }
.sl-btn-sell:disabled { opacity: .5; cursor: not-allowed; }
.sl-btn-ghost { background: transparent; border: 1.5px solid rgba(255,255,255,.25); color: #fff; }
.sl-btn-ghost:hover { background: rgba(255,255,255,.1); }

/* Customer ref */
.sl-customer-row { display: flex; align-items: center; gap: .65rem; flex-wrap: wrap; }
.sl-customer-input { padding: .5rem .85rem; border: 1.5px solid rgba(255,255,255,.2); border-radius: 8px; font-size: .85rem; color: #fff; background: rgba(255,255,255,.08); font-family: inherit; width: 220px; }
.sl-customer-input::placeholder { color: rgba(255,255,255,.4); }
.sl-customer-input:focus { outline: none; border-color: var(--sl-yellow); }
.sl-customer-label { font-size: .78rem; color: #94a3b8; }

/* Recent sales */
.sl-recent-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.sl-recent-table th { text-align: left; padding: .5rem .75rem; font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--sl-faint); border-bottom: 1px solid var(--sl-border); }
.sl-recent-table td { padding: .5rem .75rem; border-bottom: 1px solid #f4f6f9; vertical-align: middle; }
.sl-recent-table .mono { font-family: 'Courier New', monospace; font-weight: 700; font-size: .85rem; color: #0f172a; }
.sl-recent-table .dim { color: var(--sl-faint); font-size: .8rem; }

/* Empty */
.sl-empty { text-align: center; padding: 3rem 1rem; color: var(--sl-faint); }
.sl-empty-icon { font-size: 2.5rem; margin-bottom: .5rem; }
.sl-empty-title { font-size: 1rem; font-weight: 700; color: var(--sl-muted); margin-bottom: .3rem; }

@media (max-width: 640px) {
  .sl-sell-bar { flex-direction: column; align-items: stretch; text-align: center; padding: .85rem 1rem; }
  .sl-sell-bar .sl-sell-actions { justify-content: center; flex-direction: column; }
  .sl-customer-input { width: 100%; }
  .sl-voucher-grid { grid-template-columns: 1fr; }
  .sl-qty-input { width: 60px; }
  .sl-qty-row { gap: .35rem; }
  .sl-summary { grid-template-columns: 1fr 1fr; }
  .sl-recent-table th, .sl-recent-table td { padding: .4rem .5rem; font-size: .78rem; }
  .sl-btn { padding: .55rem 1rem; font-size: .85rem; width: 100%; justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="sl-page">
<div class="sl-wrap">

  {{-- Flash messages --}}
  @if(session('status'))
    <div class="sl-ok">✓ {{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="sl-err">✗ {{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="sl-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif

  {{-- Header --}}
  <div class="sl-topbar">
    <div>
      <h1>🏷️ Vender Vouchers</h1>
      <p class="sl-sub">
        Seleccione os vouchers que pretende vender ao cliente final.<br>
        Após clicar em <strong>"Vender"</strong>, o sistema marca-os como vendidos e gera um PDF para entregar ao cliente.
      </p>
    </div>
    <a href="{{ route('reseller.panel') }}" class="sl-back">← Painel</a>
  </div>

  {{-- Info box --}}
  <div class="sl-info">
    <strong>Como funciona:</strong>
    1. Seleccione os vouchers que vai entregar ao cliente (pode misturar planos diferentes).
    2. Opcionalmente, preencha o nome/telefone do cliente.
    3. Clique em <strong>"Vender"</strong> — o sistema gera o PDF e desconta automaticamente os vouchers do seu stock.
    O pagamento é gerido por si — a loja não intervém no recebimento.
  </div>

  {{-- Summary cards --}}
  <div class="sl-summary">
    <div class="sl-card">
      <p class="sl-card-val" style="color:var(--sl-green)">{{ $totalInStock }}</p>
      <p class="sl-card-lbl">Total disponíveis</p>
    </div>
    <div class="sl-card">
      <p class="sl-card-val" style="color:var(--sl-muted)">{{ $totalSold }}</p>
      <p class="sl-card-lbl">Total vendidos</p>
    </div>
    @foreach($allPlanSlugs as $planSlug)
      @php
        $plan  = $voucherPlans->get($planSlug);
        $avail = isset($stockByPlan[$planSlug]) ? $stockByPlan[$planSlug]->count() : 0;
        $sold  = isset($soldByPlan[$planSlug])  ? $soldByPlan[$planSlug]->count()  : 0;
      @endphp
      <div class="sl-card">
        <p class="sl-card-lbl" style="font-weight:700;color:var(--sl-text);margin-bottom:.4rem;">
          {{ $plan ? $plan->name : $planSlug }}
        </p>
        <div style="display:flex;gap:1.1rem;">
          <div>
            <p class="sl-card-val" style="color:var(--sl-green);font-size:1.5rem;">{{ $avail }}</p>
            <p class="sl-card-lbl">disponíveis</p>
          </div>
          <div>
            <p class="sl-card-val" style="color:var(--sl-muted);font-size:1.5rem;">{{ $sold }}</p>
            <p class="sl-card-lbl">vendidos</p>
          </div>
        </div>
      </div>
    @endforeach
  </div>

  @if($totalInStock === 0)
    <div class="sl-empty">
      <div class="sl-empty-icon">📭</div>
      <p class="sl-empty-title">Sem vouchers para vender</p>
      <p>Compre vouchers no <a href="{{ route('reseller.panel') }}" style="color:var(--sl-blue);font-weight:700;">painel principal</a> para começar a revender.</p>
    </div>
  @else

  {{-- Sale form --}}
  <form id="sellForm" action="{{ route('reseller.sell.process') }}" method="POST">
    @csrf

    {{-- Voucher selection by plan --}}
    @foreach($stockByPlan as $planSlug => $codes)
      @php
        $plan = $voucherPlans->get($planSlug);
        $planName = $plan ? $plan->name : $planSlug;
        $validityLabel = $plan ? $plan->validity_label : '';
        $speedLabel = $plan ? $plan->speed_label : '';
        $badgeClass = match($planSlug) {
          'diario'  => 'sl-badge-diario',
          'semanal' => 'sl-badge-semanal',
          'mensal'  => 'sl-badge-mensal',
          default   => 'sl-badge-default',
        };
      @endphp

      <div class="sl-panel sl-plan-group" data-plan="{{ $planSlug }}">
        <div class="sl-plan-header">
          <div style="display:flex;align-items:center;gap:.65rem;">
            <span class="sl-badge {{ $badgeClass }}">{{ strtoupper($planSlug) }}</span>
            <span class="sl-plan-name">{{ $planName }}</span>
            @if($validityLabel)
              <span class="sl-plan-meta">{{ $validityLabel }}{{ $speedLabel ? ' · ' . $speedLabel : '' }}</span>
            @endif
          </div>
          <span class="sl-plan-count ok">{{ $codes->count() }} disponíveis</span>
        </div>

        {{-- Quick quantity selector --}}
        <div class="sl-qty-row">
          <span class="sl-qty-label">Seleccionar rápido:</span>
          <input type="number" min="0" max="{{ $codes->count() }}" value="0"
                 class="sl-qty-input" data-plan-qty="{{ $planSlug }}"
                 onchange="selectByQuantity('{{ $planSlug }}', this.value)">
          <button type="button" class="sl-qty-btn" onclick="selectByQuantity('{{ $planSlug }}', {{ $codes->count() }})">Todos ({{ $codes->count() }})</button>
          <button type="button" class="sl-qty-btn" onclick="selectByQuantity('{{ $planSlug }}', 0)">Nenhum</button>
        </div>

        {{-- Voucher grid --}}
        <div class="sl-voucher-grid" id="grid-{{ $planSlug }}">
          @foreach($codes as $code)
            <label class="sl-voucher-item" data-plan="{{ $planSlug }}" for="v-{{ $code->id }}">
              <input type="checkbox" name="voucher_ids[]" value="{{ $code->id }}"
                     id="v-{{ $code->id }}" class="voucher-cb" data-plan="{{ $planSlug }}"
                     onchange="updateSelection()">
              <div>
                <div class="sl-voucher-code">{{ $code->code }}</div>
                <div class="sl-voucher-plan">{{ $planName }}</div>
              </div>
            </label>
          @endforeach
        </div>
      </div>
    @endforeach

    {{-- Sticky sell bar --}}
    <div class="sl-sell-bar" id="sellBar">
      <div class="sl-sell-info">
        <div class="sl-sell-count">
          <span id="selectedCount">0</span> voucher(s) seleccionados
        </div>
        <div class="sl-sell-sub" id="selectedBreakdown">Seleccione vouchers acima</div>
      </div>
      <div class="sl-sell-actions">
        <div class="sl-customer-row">
          <span class="sl-customer-label">Cliente:</span>
          <input type="text" name="customer_ref" class="sl-customer-input"
                 placeholder="Nome / telefone (opcional)" maxlength="200" autocomplete="off">
        </div>
        <button type="submit" class="sl-btn sl-btn-sell" id="btnSell" disabled
                onclick="return confirm('Confirma a venda dos vouchers seleccionados? Esta acção gera o PDF e desconta os vouchers do seu stock.')">
          🏷️ Vender &amp; gerar PDF
        </button>
        <button type="button" class="sl-btn sl-btn-ghost" onclick="clearAll()">Limpar selecção</button>
      </div>
    </div>
  </form>

  @endif

  {{-- Recent sales --}}
  @if($recentSales->count() > 0)
  <div class="sl-panel" style="margin-top:1.5rem;">
    <div class="sl-panel-title">📋 Vendas recentes</div>
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
              <td>{{ $code->reseller_customer_ref ?: '—' }}</td>
              <td class="dim">{{ optional($code->reseller_distributed_at)->format('d/m/Y H:i') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

</div>
</div>

<script>
function updateSelection() {
  var checkboxes = document.querySelectorAll('.voucher-cb:checked');
  var count = checkboxes.length;
  document.getElementById('selectedCount').textContent = count;
  document.getElementById('btnSell').disabled = count === 0;

  // Update visual selection
  document.querySelectorAll('.sl-voucher-item').forEach(function(item) {
    var cb = item.querySelector('.voucher-cb');
    item.classList.toggle('selected', cb && cb.checked);
  });

  // Breakdown by plan
  var planCounts = {};
  checkboxes.forEach(function(cb) {
    var plan = cb.getAttribute('data-plan');
    planCounts[plan] = (planCounts[plan] || 0) + 1;
  });

  var parts = [];
  for (var plan in planCounts) {
    parts.push(planCounts[plan] + '× ' + plan);
  }
  document.getElementById('selectedBreakdown').textContent =
    count > 0 ? parts.join(' + ') : 'Seleccione vouchers acima';

  // Update qty inputs
  document.querySelectorAll('.sl-qty-input').forEach(function(input) {
    var planSlug = input.getAttribute('data-plan-qty');
    var checked = document.querySelectorAll('.voucher-cb[data-plan="' + planSlug + '"]:checked').length;
    input.value = checked;
  });
}

function selectByQuantity(planSlug, qty) {
  qty = parseInt(qty) || 0;
  var checkboxes = document.querySelectorAll('.voucher-cb[data-plan="' + planSlug + '"]');
  var count = 0;
  checkboxes.forEach(function(cb) {
    cb.checked = count < qty;
    count++;
  });
  // Update qty input
  var qtyInput = document.querySelector('[data-plan-qty="' + planSlug + '"]');
  if (qtyInput) qtyInput.value = qty;
  updateSelection();
}

function clearAll() {
  document.querySelectorAll('.voucher-cb').forEach(function(cb) {
    cb.checked = false;
  });
  updateSelection();
}
</script>
@endsection
