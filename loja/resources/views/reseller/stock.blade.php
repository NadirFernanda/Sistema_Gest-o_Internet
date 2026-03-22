@extends('layouts.app')

@push('styles')
<style>
:root {
  --s-bg:     #f4f6f9;
  --s-surf:   #ffffff;
  --s-border: #dde2ea;
  --s-text:   #1a202c;
  --s-muted:  #64748b;
  --s-faint:  #9aa5b4;
  --s-green:  #16a34a;
  --s-amber:  #d97706;
  --s-red:    #dc2626;
  --s-blue:   #3b82f6;
  --s-indigo: #4f46e5;
  --s-yellow: #f7b500;
}
.sp { font-family: Inter, system-ui, sans-serif; background: var(--s-bg); min-height: 80vh; padding: 2rem 1rem 4rem; color: var(--s-text); }
.sp-wrap { max-width: 980px; margin: 0 auto; }

/* Header */
.sp-topbar { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.sp-topbar h1 { font-size: 1.3rem; font-weight: 800; margin: 0 0 .2rem; }
.sp-topbar .sp-sub { font-size: .82rem; color: var(--s-muted); }
.sp-back { font-size: .82rem; font-weight: 600; color: var(--s-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--s-border); border-radius: 7px; background: var(--s-surf); transition: background .15s; white-space: nowrap; }
.sp-back:hover { background: var(--s-border); color: var(--s-text); }

/* Alerts */
.sp-ok  { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--s-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.sp-err { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--s-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }

/* Summary cards */
.sp-summary { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .85rem; margin-bottom: 1.75rem; }
.sp-card { background: var(--s-surf); border: 1px solid var(--s-border); border-radius: 10px; padding: 1rem 1.1rem; }
.sp-card-val { font-size: 2rem; font-weight: 800; line-height: 1; margin: 0 0 .25rem; }
.sp-card-lbl { font-size: .75rem; color: var(--s-muted); font-weight: 500; }

/* Filters */
.sp-filters { background: var(--s-surf); border: 1px solid var(--s-border); border-radius: 10px; padding: .85rem 1.1rem; display: flex; flex-wrap: wrap; gap: .65rem; align-items: flex-end; margin-bottom: 1.1rem; }
.sp-fg { display: flex; flex-direction: column; gap: .25rem; }
.sp-fg.grow { flex: 1; min-width: 170px; }
.sp-label { font-size: .77rem; font-weight: 600; color: #374151; }
.sp-ctrl { padding: .5rem .75rem; border: 1.5px solid var(--s-border); border-radius: 8px; font-size: .875rem; color: var(--s-text); background: #f8fafc; outline: none; font-family: inherit; }
.sp-ctrl:focus { border-color: var(--s-yellow); }
.sp-btn { display: inline-flex; align-items: center; gap: .35rem; padding: .5rem 1rem; border-radius: 8px; font-size: .845rem; font-weight: 700; border: none; cursor: pointer; font-family: inherit; text-decoration: none; white-space: nowrap; }
.sp-btn-primary { background: var(--s-indigo); color: #fff; }
.sp-btn-primary:hover { background: #4338ca; }
.sp-btn-outline { background: var(--s-surf); color: var(--s-muted); border: 1.5px solid var(--s-border); }
.sp-btn-outline:hover { background: var(--s-border); }
.sp-btn-sm { padding: .3rem .7rem; font-size: .78rem; }
.sp-btn-yellow { background: var(--s-yellow); color: #1a202c; }
.sp-btn-yellow:hover { background: #e0a800; }
.sp-btn-green { background: var(--s-green); color: #fff; }
.sp-btn-green:hover { background: #15803d; }
.sp-btn-ghost { background: none; border: 1.5px solid var(--s-border); color: var(--s-muted); }
.sp-btn-ghost:hover { background: #f1f5f9; color: var(--s-text); }

/* Table */
.sp-tcard { background: var(--s-surf); border: 1px solid var(--s-border); border-radius: 10px; overflow: hidden; }
.sp-table { width: 100%; border-collapse: collapse; font-size: .865rem; }
.sp-table thead { background: #f8fafc; }
.sp-table th { text-align: left; padding: .6rem 1rem; font-size: .69rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--s-faint); border-bottom: 1px solid var(--s-border); white-space: nowrap; }
.sp-table td { padding: .65rem 1rem; border-bottom: 1px solid #f4f6f9; vertical-align: middle; }
.sp-table tbody tr:last-child td { border-bottom: none; }
.sp-table tbody tr:hover td { background: #fafbff; }
.sp-table .mono { font-family: 'Courier New', monospace; font-weight: 700; font-size: .9rem; letter-spacing: .04em; color: #0f172a; }
.sp-table .dim  { color: var(--s-faint); font-size: .82rem; }

/* Badges */
.badge { display: inline-flex; align-items: center; gap: .25rem; padding: .2rem .65rem; border-radius: 999px; font-size: .73rem; font-weight: 700; white-space: nowrap; }
.badge-stock { background: #f0fdf4; color: #166534; border: 1px solid #86efac; }
.badge-sold  { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
.badge-plan-diario  { background: #dbeafe; color: #1d4ed8; }
.badge-plan-semanal { background: #ede9fe; color: #6d28d9; }
.badge-plan-mensal  { background: #fef3c7; color: #b45309; }

/* Inline distribute form */
.sp-dist-form { display: flex; gap: .4rem; align-items: center; flex-wrap: wrap; }
.sp-dist-input { padding: .35rem .65rem; border: 1.5px solid var(--s-border); border-radius: 7px; font-size: .8rem; width: 170px; background: #f8fafc; font-family: inherit; outline: none; color: var(--s-text); }
.sp-dist-input:focus { border-color: var(--s-green); }

/* Copy button */
.sp-copy-btn { background: none; border: none; cursor: pointer; font-size: .85rem; padding: .2rem .4rem; border-radius: 5px; color: var(--s-muted); transition: background .15s; }
.sp-copy-btn:hover { background: #f1f5f9; color: var(--s-text); }

/* Download bar */
.sp-dl-bar { display: flex; flex-wrap: wrap; gap: .65rem; background: var(--s-surf); border: 1px solid var(--s-border); border-radius: 10px; padding: .85rem 1.1rem; margin-bottom: 1rem; align-items: center; }
.sp-dl-title { font-size: .82rem; font-weight: 700; color: var(--s-muted); margin-right: .35rem; }

/* Empty state */
.sp-empty { padding: 3rem 1rem; text-align: center; color: var(--s-faint); }
.sp-empty-title { font-size: .95rem; font-weight: 700; color: var(--s-muted); margin: 0 0 .3rem; }

/* Info box */
.sp-info { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid var(--s-amber); color: #78350f; padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 1.25rem; line-height: 1.55; }

@media (max-width: 640px) {
  .sp-dist-form { flex-direction: column; align-items: flex-start; }
  .sp-dist-input { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="sp">
<div class="sp-wrap">

  {{-- Flash --}}
  @if(session('status'))
    <div class="sp-ok">&#10003; {{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="sp-err">&#10007; {{ session('error') }}</div>
  @endif
  @if($errors->any())
    <div class="sp-err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif

  {{-- Header --}}
  <div class="sp-topbar">
    <div>
      <h1>📦 Meus Vouchers &mdash; Compra #{{ $purchase->id }}</h1>
      <p class="sp-sub">
        Plano: <strong>{{ $purchase->plan_name ?? $purchase->plan_slug }}</strong>
        &middot; {{ optional($purchase->created_at)->format('d/m/Y H:i') }}
        &middot; {{ $totalCodes }} voucher(s) adquiridos
      </p>
    </div>
    <a href="{{ route('reseller.panel') }}" class="sp-back">&larr; Painel</a>
  </div>

  {{-- Info --}}
  <div class="sp-info">
    <strong>Como funciona:</strong> Os vouchers abaixo são seus para revender aos clientes finais (internautas).
    Para cada voucher que vender, clique em <strong>"Marcar como vendido"</strong> e registe o contacto do cliente (opcional, mas recomendado).
    Isso mantém o controlo do seu stock e das suas vendas.
  </div>

  {{-- Summary --}}
  <div class="sp-summary">
    <div class="sp-card">
      <p class="sp-card-val">{{ $totalCodes }}</p>
      <p class="sp-card-lbl">Total adquiridos</p>
    </div>
    <div class="sp-card">
      <p class="sp-card-val" style="color:var(--s-green)">{{ $inStock }}</p>
      <p class="sp-card-lbl">Em stock (por vender)</p>
    </div>
    <div class="sp-card">
      <p class="sp-card-val" style="color:var(--s-muted)">{{ $distributed }}</p>
      <p class="sp-card-lbl">Vendidos ao cliente</p>
    </div>
    @if($purchase->profit_aoa)
      <div class="sp-card">
        <p class="sp-card-val" style="color:var(--s-green);font-size:1.4rem;">{{ number_format($purchase->profit_aoa, 0, ',', '.') }} Kz</p>
        <p class="sp-card-lbl">Lucro potencial total</p>
      </div>
    @endif
  </div>

  {{-- Download bar --}}
  <div class="sp-dl-bar">
    <span class="sp-dl-title">Exportar:</span>
    <a href="{{ route('reseller.panel.purchase.vouchers', $purchase) }}" class="sp-btn sp-btn-outline sp-btn-sm">
      ⬇ CSV (todos)
    </a>
    <a href="{{ route('reseller.panel.purchase.pdf', $purchase) }}" class="sp-btn sp-btn-outline sp-btn-sm" style="border-color:#fecaca;color:#b91c1c;">
      📄 PDF
    </a>
    <a href="{{ route('reseller.panel.purchase.codes', ['purchase' => $purchase->id, 'filter' => 'stock']) }}"
       class="sp-btn sp-btn-outline sp-btn-sm">
      ⬇ Só por vender (CSV)
    </a>
    <button onclick="shareAllWhatsApp()" class="sp-btn sp-btn-sm" style="background:#25d366;color:#fff;border:none;cursor:pointer;">
      📲 Partilhar por WhatsApp
    </button>
  </div>

  {{-- Filter tabs --}}
  @php $filter = request('filter', 'all'); @endphp
  <div style="display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap;">
    <a href="{{ route('reseller.panel.purchase.codes', $purchase) }}"
       class="sp-btn sp-btn-sm {{ $filter === 'all'   ? 'sp-btn-yellow' : 'sp-btn-ghost' }}">Todos ({{ $totalCodes }})</a>
    <a href="{{ route('reseller.panel.purchase.codes', ['purchase' => $purchase->id, 'filter' => 'stock']) }}"
       class="sp-btn sp-btn-sm {{ $filter === 'stock' ? 'sp-btn-green'  : 'sp-btn-ghost' }}">Por vender ({{ $inStock }})</a>
    <a href="{{ route('reseller.panel.purchase.codes', ['purchase' => $purchase->id, 'filter' => 'sold']) }}"
       class="sp-btn sp-btn-sm {{ $filter === 'sold'  ? 'sp-btn-outline' : 'sp-btn-ghost' }}">Vendidos ({{ $distributed }})</a>
  </div>

  {{-- Table --}}
  @php
    $filtered = match($filter) {
      'stock' => $codes->filter(fn($c) => !$c->reseller_distributed_at),
      'sold'  => $codes->filter(fn($c) =>  $c->reseller_distributed_at),
      default => $codes,
    };
  @endphp

  <div class="sp-tcard">
    <div style="overflow-x:auto;">
      <table class="sp-table">
        <thead>
          <tr>
            <th>#</th>
            <th>C&oacute;digo</th>
            <th>Estado</th>
            <th>Cliente (ref.)</th>
            <th>Vendido em</th>
            <th>A&ccedil;&otilde;es</th>
          </tr>
        </thead>
        <tbody>
          @forelse($filtered as $code)
            <tr>
              <td class="dim">{{ $loop->iteration }}</td>
              <td>
                <span class="mono" id="code-{{ $code->id }}">{{ $code->code }}</span>
                <button class="sp-copy-btn" onclick="copyCode('{{ $code->code }}', this)" title="Copiar">&#128203;</button>
              </td>
              <td>
                @if($code->reseller_distributed_at)
                  <span class="badge badge-sold">&#10003; Vendido</span>
                @else
                  <span class="badge badge-stock">&#128230; Em stock</span>
                @endif
              </td>
              <td>
                @if($code->reseller_distributed_at)
                  <span style="font-size:.85rem;color:#374151;">{{ $code->reseller_customer_ref ?: '—' }}</span>
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td class="dim">
                {{ optional($code->reseller_distributed_at)->format('d/m/Y H:i') ?: '—' }}
              </td>
              <td>
                @if(!$code->reseller_distributed_at)
                  {{-- Mark as sold --}}
                  <form method="POST" action="{{ route('reseller.voucher.distribute', $code) }}"
                        class="sp-dist-form">
                    @csrf
                    <input name="customer_ref" class="sp-dist-input"
                           placeholder="Nome/telefone do cliente (opcional)"
                           maxlength="200" autocomplete="off">
                    <button type="submit" class="sp-btn sp-btn-sm sp-btn-green">
                      ✔ Marcar vendido
                    </button>
                    <button type="button"
                            onclick="shareVoucherWhatsApp('{{ $code->code }}', '{{ $purchase->plan_name }}', '{{ $purchase->plan_slug }}')"
                            class="sp-btn sp-btn-sm" style="background:#25d366;color:#fff;border:none;cursor:pointer;"
                            title="Enviar por WhatsApp">
                      📲
                    </button>
                  </form>
                @else
                  {{-- Undo distribution --}}
                  <form method="POST" action="{{ route('reseller.voucher.undistribute', $code) }}"
                        onsubmit="return confirm('Cancelar a marcação deste voucher como vendido?')">
                    @csrf
                    <button type="submit" class="sp-btn sp-btn-sm sp-btn-ghost">
                      ↩ Cancelar
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="sp-empty">
                  <p class="sp-empty-title">Nenhum voucher nesta categoria</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>
</div>

<script>
function copyCode(code, btn) {
  navigator.clipboard.writeText(code).then(function() {
    var orig = btn.innerHTML;
    btn.innerHTML = '&#10003;';
    btn.style.color = '#16a34a';
    setTimeout(function(){ btn.innerHTML = orig; btn.style.color = ''; }, 1500);
  }).catch(function(){
    // fallback — select text
    var el = document.getElementById('code-' + code.replace(/[^a-zA-Z0-9]/g,''));
    if (el) {
      var range = document.createRange();
      range.selectNode(el);
      window.getSelection().removeAllRanges();
      window.getSelection().addRange(range);
    }
  });
}

function shareVoucherWhatsApp(code, planName, planSlug) {
  var validityMap = { 'diario': '24 horas', 'semanal': '7 dias', 'mensal': '30 dias' };
  var validity = validityMap[planSlug] || planName;
  var msg = '🌐 *AngolaWiFi — Voucher WiFi*\n\n'
          + '📌 Plano: *' + planName + '*\n'
          + '⏱️ Validade: ' + validity + '\n'
          + '🔑 Código: *' + code + '*\n\n'
          + 'Para activar, ligue-se à rede AngolaWiFi e insira este código no portal de autenticação.\n\n'
          + '💬 Dúvidas? Contacte o seu revendedor.';
  var url = 'https://wa.me/?text=' + encodeURIComponent(msg);
  window.open(url, '_blank', 'noopener,noreferrer');
}

function shareAllWhatsApp() {
  // Collect all available (not sold) codes visible in stock tab
  var rows = document.querySelectorAll('.badge-stock');
  var codes = [];
  rows.forEach(function(badge) {
    var row = badge.closest('tr');
    if (row) {
      var monoEl = row.querySelector('.mono');
      if (monoEl) codes.push(monoEl.textContent.trim());
    }
  });

  if (codes.length === 0) {
    alert('Não há vouchers disponíveis (por vender) para partilhar.');
    return;
  }

  var planName = '{{ $purchase->plan_name }}';
  var planSlug = '{{ $purchase->plan_slug }}';
  var validityMap = { 'diario': '24 horas', 'semanal': '7 dias', 'mensal': '30 dias' };
  var validity = validityMap[planSlug] || planName;

  var msg = '🌐 *AngolaWiFi — Vouchers WiFi*\n'
          + '📌 Plano: *' + planName + '* (' + validity + ')\n'
          + '📦 Quantidade: ' + codes.length + ' voucher(s)\n\n';

  codes.forEach(function(c, i) {
    msg += (i + 1) + '. 🔑 *' + c + '*\n';
  });

  msg += '\nPara activar, ligue-se à rede AngolaWiFi e insira o código no portal.\n'
       + '💬 Dúvidas? Contacte o seu revendedor.';

  var url = 'https://wa.me/?text=' + encodeURIComponent(msg);
  window.open(url, '_blank', 'noopener,noreferrer');
}
</script>
@endsection
