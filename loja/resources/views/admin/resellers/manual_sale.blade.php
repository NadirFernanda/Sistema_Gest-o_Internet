@extends('layouts.app')

@section('content')
<style>
:root {
  --a-bg:     #f4f6f9;
  --a-surf:   #ffffff;
  --a-border: #dde2ea;
  --a-text:   #1a202c;
  --a-muted:  #64748b;
  --a-faint:  #9aa5b4;
  --a-brand:  #f7b500;
  --a-green:  #16a34a;
  --a-amber:  #d97706;
  --a-red:    #dc2626;
}
.ms { font-family: Inter, system-ui, sans-serif; background: var(--a-bg); min-height: 60vh; padding: 2rem 0 4rem; color: var(--a-text); }
.ms-wrap { max-width: 860px; margin: 0 auto; padding: 0 1.5rem; }
.ms-topbar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.5rem; }
.ms-topbar h1 { font-size: 1.25rem; font-weight: 800; margin: 0; letter-spacing: -.02em; }
.ms-back { font-size: .82rem; font-weight: 600; padding: .38rem .9rem; border-radius: 7px; border: 1px solid var(--a-border); background: var(--a-surf); color: var(--a-muted); text-decoration: none; }
.ms-back:hover { background: rgba(247,181,0,.13); border-color: var(--a-brand); color: #7a4f00; }
.ms-card { background: var(--a-surf); border: 1px solid var(--a-border); border-radius: 14px; overflow: hidden; }
.ms-header { background: linear-gradient(135deg, #c8920a 0%, #f7b500 100%); padding: 1.5rem 1.75rem; color: #1a202c; }
.ms-header h2 { font-size: 1.05rem; font-weight: 800; margin: 0 0 .3rem; }
.ms-header p  { font-size: .82rem; opacity: .85; margin: 0; line-height: 1.5; }
.ms-body { padding: 1.75rem; }
.ms-alert-success { background: #dcfce7; border: 1px solid #86efac; color: #15803d; border-radius: 8px; padding: .9rem 1.1rem; font-size: .875rem; font-weight: 600; margin-bottom: 1.25rem; }
.ms-alert-error   { background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626; border-radius: 8px; padding: .9rem 1.1rem; font-size: .875rem; font-weight: 600; margin-bottom: 1.25rem; }
.ms-label { font-size: .8rem; font-weight: 700; color: var(--a-text); display: block; margin-bottom: .35rem; }
.ms-select, .ms-input, .ms-textarea {
  width: 100%; border: 1px solid var(--a-border); border-radius: 8px;
  padding: .55rem .85rem; font-size: .9rem; color: var(--a-text);
  background: var(--a-surf); font-family: inherit; box-sizing: border-box;
  transition: border-color .15s;
}
.ms-select:focus, .ms-input:focus, .ms-textarea:focus {
  outline: none; border-color: var(--a-brand); box-shadow: 0 0 0 3px rgba(247,181,0,.18);
}
.ms-field { margin-bottom: 1.25rem; }
.ms-divider { margin: 1.5rem 0; border: none; border-top: 1px solid var(--a-border); }
.ms-sec-label { font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: var(--a-faint); margin: 0 0 1rem; display: flex; align-items: center; gap: .6rem; }
.ms-sec-label::after { content:''; flex:1; height:1px; background:var(--a-border); }
.ms-items { display: flex; flex-direction: column; gap: .75rem; }
.ms-item { display: grid; grid-template-columns: 1fr 120px 36px; gap: .6rem; align-items: end; }
.ms-item-remove { width: 36px; height: 36px; border: 1px solid #fecaca; background: #fff1f2; color: var(--a-red); border-radius: 7px; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ms-item-remove:hover { background: #fecaca; }
.ms-add-btn { font-size: .82rem; font-weight: 600; padding: .42rem .9rem; border: 1px dashed var(--a-brand); border-radius: 7px; background: rgba(247,181,0,.1); color: #7a4f00; cursor: pointer; font-family: inherit; width: 100%; margin-top: .25rem; }
.ms-add-btn:hover { background: rgba(247,181,0,.2); }
.ms-submit { width: 100%; padding: .75rem 1rem; background: var(--a-brand); color: #1a202c; border: none; border-radius: 9px; font-weight: 800; font-size: 1rem; cursor: pointer; font-family: inherit; margin-top: .25rem; }
.ms-submit:hover { filter: brightness(.95); }
.ms-reseller-info { background: #f8fafc; border: 1px solid var(--a-border); border-radius: 8px; padding: .75rem 1rem; font-size: .82rem; color: var(--a-muted); display: none; margin-top: .4rem; }
.ms-reseller-info.show { display: block; }
.ms-stock-badge { font-size: .72rem; font-weight: 700; padding: .15rem .5rem; border-radius: 999px; }
.stock-ok  { background: #dcfce7; color: #15803d; }
.stock-low { background: #fef3c7; color: #b45309; }
.stock-out { background: #fef2f2; color: #dc2626; }
.ms-price-info { font-size: .75rem; color: var(--a-muted); margin-top: .3rem; }
.ms-summary { background: #f8fafc; border: 1px solid var(--a-border); border-radius: 10px; padding: 1rem 1.2rem; margin-top: 1.25rem; }
.ms-summary-row { display: flex; justify-content: space-between; align-items: center; font-size: .84rem; padding: .25rem 0; color: var(--a-muted); border-bottom: 1px solid var(--a-border); }
.ms-summary-row:last-child { border-bottom: none; font-weight: 800; color: var(--a-text); font-size: .92rem; padding-top: .5rem; margin-top: .2rem; }
.ms-summary-row span:last-child { font-weight: 700; color: var(--a-text); }
</style>

<div class="ms">
<div class="ms-wrap">

  <div class="ms-topbar">
    <h1>Vender Vouchers Manualmente</h1>
    <a href="{{ route('admin.dashboard') }}" class="ms-back">&larr; Dashboard</a>
  </div>

  <div class="ms-card">
    <div class="ms-header">
      <h2>Venda Manual para Revendedor</h2>
      <p>Seleccione o revendedor, os tipos de voucher e as quantidades.<br>Os vouchers ser&atilde;o atribu&iacute;dos directamente ao painel do revendedor sem necessidade de pagamento online.</p>
    </div>

    <div class="ms-body">

      @if(session('success'))
        <div class="ms-alert-success">&#10003; {{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="ms-alert-error">&#9888; {{ session('error') }}</div>
      @endif
      @if($errors->any())
        <div class="ms-alert-error">
          @foreach($errors->all() as $err)<div>&#9888; {{ $err }}</div>@endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('admin.manual_voucher_sale.store') }}" id="manualSaleForm">
        @csrf

        {{-- Selecção do revendedor --}}
        <div class="ms-field">
          <label class="ms-label" for="reseller_id">Agente Revendedor *</label>
          <select name="reseller_id" id="reseller_id" class="ms-select" required onchange="updateResellerInfo(this)">
            <option value="">— Seleccionar revendedor —</option>
            @foreach($resellers as $r)
              <option value="{{ $r->id }}"
                data-name="{{ $r->full_name }}"
                data-phone="{{ $r->phone }}"
                data-email="{{ $r->email }}"
                {{ old('reseller_id') == $r->id ? 'selected' : '' }}>
                {{ $r->full_name }} — {{ $r->phone }}
              </option>
            @endforeach
          </select>
          <div class="ms-reseller-info" id="resellerInfo">
            <strong id="riName"></strong> &nbsp;&middot;&nbsp;
            <span id="riPhone"></span> &nbsp;&middot;&nbsp;
            <span id="riEmail"></span>
          </div>
        </div>

        <hr class="ms-divider">

        {{-- Itens de voucher --}}
        <p class="ms-sec-label">Vouchers a enviar</p>

        <div class="ms-items" id="itemsContainer">
          @php
            $oldItems = old('items', [['plan' => '', 'qty' => 1]]);
          @endphp
          @foreach($oldItems as $idx => $oi)
          <div class="ms-item" id="item_{{ $idx }}">
            <div>
              <label class="ms-label">Tipo de Voucher</label>
              <select name="items[{{ $idx }}][plan]" class="ms-select plan-select" required onchange="updateSummary()">
                <option value="">— Seleccionar plano —</option>
                @foreach($voucherPlans as $vp)
                  <option value="{{ $vp->slug }}"
                    data-price="{{ $vp->price_reseller_aoa }}"
                    data-public="{{ $vp->price_public_aoa }}"
                    data-stock="{{ $vp->availableStock() }}"
                    data-name="{{ $vp->name }}"
                    {{ (old("items.{$idx}.plan", $oi['plan'] ?? '') === $vp->slug) ? 'selected' : '' }}>
                    {{ $vp->name }} — {{ number_format($vp->price_public_aoa, 0, ',', '.') }} Kz
                    (stock: {{ $vp->availableStock() }})
                  </option>
                @endforeach
              </select>
            </div>
            <div>
              <label class="ms-label">Qtd.</label>
              <input type="number" name="items[{{ $idx }}][qty]" class="ms-input qty-input"
                     value="{{ old("items.{$idx}.qty", $oi['qty'] ?? 1) }}"
                     min="1" max="9999" required oninput="updateSummary()">
            </div>
            <div>
              <button type="button" class="ms-item-remove" onclick="removeItem('item_{{ $idx }}')" title="Remover linha">✕</button>
            </div>
          </div>
          @endforeach
        </div>

        <button type="button" class="ms-add-btn" id="addItemBtn" onclick="addItem()">
          + Adicionar outro tipo de voucher
        </button>

        {{-- Resumo dinâmico --}}
        <div class="ms-summary" id="summaryBox" style="display:none;">
          <div style="font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--a-faint);margin-bottom:.6rem;">Resumo da Venda</div>
          <div id="summaryLines"></div>
          <div class="ms-summary-row">
            <span>Total de vouchers</span>
            <span id="summaryTotalVouchers">0</span>
          </div>
          <div class="ms-summary-row">
            <span>Valor total ao revendedor</span>
            <span id="summaryTotalAmt">0 Kz</span>
          </div>
        </div>

        <hr class="ms-divider">

        {{-- Observações opcionais --}}
        <div class="ms-field">
          <label class="ms-label" for="notes">Observa&ccedil;&otilde;es (opcional)</label>
          <textarea name="notes" id="notes" class="ms-textarea" rows="3"
                    placeholder="Ex: Pago em dinheiro — recibo emitido. Assistência presencial ao agente...">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="ms-submit" id="submitBtn">
          &#128722; Enviar Vouchers para o Painel do Revendedor
        </button>
      </form>

    </div>
  </div>

</div>
</div>

<script>
let itemIndex = {{ count(old('items', [['plan' => '', 'qty' => 1]])) }};

function addItem() {
  const container = document.getElementById('itemsContainer');
  const div = document.createElement('div');
  div.className = 'ms-item';
  div.id = 'item_' + itemIndex;

  const planOptions = `{!! implode('', $voucherPlans->map(fn($vp) => '<option value="' . e($vp->slug) . '" data-price="' . (int)$vp->price_reseller_aoa . '" data-public="' . (int)$vp->price_public_aoa . '" data-stock="' . (int)$vp->availableStock() . '" data-name="' . e($vp->name) . '">' . e($vp->name) . ' — ' . number_format($vp->price_public_aoa, 0, ',', '.') . ' Kz (stock: ' . (int)$vp->availableStock() . ')</option>')->toArray()) !!}`;

  div.innerHTML = `
    <div>
      <label class="ms-label">Tipo de Voucher</label>
      <select name="items[${itemIndex}][plan]" class="ms-select plan-select" required onchange="updateSummary()">
        <option value="">— Seleccionar plano —</option>
        ${planOptions}
      </select>
    </div>
    <div>
      <label class="ms-label">Qtd.</label>
      <input type="number" name="items[${itemIndex}][qty]" class="ms-input qty-input"
             value="1" min="1" max="9999" required oninput="updateSummary()">
    </div>
    <div>
      <button type="button" class="ms-item-remove" onclick="removeItem('item_${itemIndex}')" title="Remover linha">✕</button>
    </div>`;

  container.appendChild(div);
  itemIndex++;
  updateSummary();
}

function removeItem(id) {
  const el = document.getElementById(id);
  const container = document.getElementById('itemsContainer');
  if (el && container.children.length > 1) {
    el.remove();
    updateSummary();
  }
}

function updateResellerInfo(sel) {
  const info = document.getElementById('resellerInfo');
  const opt  = sel.options[sel.selectedIndex];
  if (sel.value) {
    document.getElementById('riName').textContent  = opt.dataset.name;
    document.getElementById('riPhone').textContent = opt.dataset.phone;
    document.getElementById('riEmail').textContent = opt.dataset.email;
    info.classList.add('show');
  } else {
    info.classList.remove('show');
  }
}

function updateSummary() {
  const items = document.querySelectorAll('#itemsContainer .ms-item');
  let totalVouchers = 0;
  let totalAmt      = 0;
  let lines         = '';
  let hasData       = false;

  items.forEach(item => {
    const planSel = item.querySelector('.plan-select');
    const qtyInp  = item.querySelector('.qty-input');
    if (!planSel || !qtyInp) return;
    const opt = planSel.options[planSel.selectedIndex];
    const qty = parseInt(qtyInp.value) || 0;
    if (!planSel.value || qty < 1) return;

    hasData = true;
    const price  = parseInt(opt.dataset.price) || 0;
    const stock  = parseInt(opt.dataset.stock) || 0;
    const name   = opt.dataset.name || planSel.value;
    const sub    = price * qty;
    totalVouchers += qty;
    totalAmt      += sub;

    const stockBadge = stock === 0
      ? `<span class="ms-stock-badge stock-out">sem stock</span>`
      : (stock < qty
        ? `<span class="ms-stock-badge stock-low">stock insuf. (${stock})</span>`
        : `<span class="ms-stock-badge stock-ok">stock ok (${stock})</span>`);

    lines += `<div class="ms-summary-row">
      <span>${name} × ${qty} ${stockBadge}</span>
      <span>${sub.toLocaleString('pt-PT')} Kz</span>
    </div>`;
  });

  const box = document.getElementById('summaryBox');
  if (hasData) {
    box.style.display = 'block';
    document.getElementById('summaryLines').innerHTML = lines;
    document.getElementById('summaryTotalVouchers').textContent = totalVouchers + ' voucher(s)';
    document.getElementById('summaryTotalAmt').textContent = totalAmt.toLocaleString('pt-PT') + ' Kz';
  } else {
    box.style.display = 'none';
  }
}

// Inicializar ao carregar a página
document.addEventListener('DOMContentLoaded', function () {
  updateSummary();
  const selEl = document.getElementById('reseller_id');
  if (selEl.value) updateResellerInfo(selEl);
});
</script>
@endsection
