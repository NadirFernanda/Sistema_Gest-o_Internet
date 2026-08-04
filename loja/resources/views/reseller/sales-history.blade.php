@extends('layouts.app')

@push('styles')
<style>
:root {
  --sl-bg:     #f4f6f9;
  --sl-surf:   #ffffff;
  --sl-border: #dde2ea;
  --sl-text:   #1a202c;
  --sl-muted:  #64748b;
  --sl-green:  #16a34a;
  --sl-amber:  #d97706;
  --sl-red:    #dc2626;
  --sl-yellow: #f7b500;
}
.sl-page  { font-family: Inter, system-ui, sans-serif; background: var(--sl-bg); min-height: 80vh; padding: 2rem 1rem 4rem; color: var(--sl-text); }
.sl-wrap  { max-width: 1000px; margin: 0 auto; }
.sl-topbar { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.75rem; }
.sl-topbar h1 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .2rem; }
.sl-topbar .sl-sub { font-size: .82rem; color: var(--sl-muted); }
.sl-back  { font-size: .82rem; font-weight: 600; color: var(--sl-muted); text-decoration: none; padding: .4rem .85rem; border: 1px solid var(--sl-border); border-radius: 7px; background: var(--sl-surf); transition: background .15s; white-space: nowrap; }
.sl-back:hover { background: var(--sl-border); color: var(--sl-text); }
.sl-ok   { background: #f0fdf4; border: 1px solid #86efac; border-left: 4px solid var(--sl-green); color: #166534; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.sl-warn { background: #fffbeb; border: 1px solid #fcd34d; border-left: 4px solid var(--sl-amber); color: #92400e; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }
.sl-err  { background: #fef2f2; border: 1px solid #fecaca; border-left: 4px solid var(--sl-red);   color: #7f1d1d; padding: .75rem 1rem; border-radius: 8px; font-size: .875rem; margin-bottom: 1rem; }

/* Sale card */
.sale-card { background: var(--sl-surf); border: 1px solid var(--sl-border); border-radius: 12px; margin-bottom: 1.25rem; overflow: hidden; }
.sale-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .75rem; padding: .9rem 1.1rem; background: #f8fafc; border-bottom: 1px solid var(--sl-border); cursor: pointer; }
.sale-header:hover { background: #f1f5f9; }
.sale-date   { font-size: .95rem; font-weight: 700; color: var(--sl-text); }
.sale-meta   { font-size: .78rem; color: var(--sl-muted); margin-top: .15rem; }
.sale-badge  { display: inline-flex; align-items: center; gap: .35rem; background: #dcfce7; color: #166534; font-size: .78rem; font-weight: 700; padding: .25rem .75rem; border-radius: 20px; }
.sale-actions { display: flex; gap: .5rem; align-items: center; }
.btn-pdf { display: inline-flex; align-items: center; gap: .35rem; padding: .4rem .9rem; background: #f7b500; color: #1a202c; border: none; border-radius: 7px; font-size: .8rem; font-weight: 700; text-decoration: none; cursor: pointer; transition: background .15s; }
.btn-pdf:hover { background: #e0a500; }

/* Codes table inside each card */
.sale-body { display: none; padding: 1rem 1.1rem; }
.sale-body.open { display: block; }
.codes-table { width: 100%; border-collapse: collapse; font-size: .84rem; margin-top: .5rem; }
.codes-table th { text-align: left; padding: .4rem .6rem; color: var(--sl-muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; border-bottom: 2px solid var(--sl-border); }
.codes-table td { padding: .5rem .6rem; border-bottom: 1px solid #f1f5f9; font-family: monospace; }
.codes-table tr:last-child td { border-bottom: none; }
.plan-chip { display: inline-block; background: #eff6ff; color: #1d4ed8; padding: .15rem .55rem; border-radius: 5px; font-size: .75rem; font-weight: 600; font-family: inherit; }

.empty-state { text-align: center; padding: 3rem 1rem; color: var(--sl-muted); }
.empty-state svg { width: 48px; height: 48px; opacity: .35; margin-bottom: 1rem; }
.empty-state h3 { font-size: 1rem; font-weight: 700; margin-bottom: .4rem; color: var(--sl-text); }
</style>
@endpush

@section('content')
<div class="sl-page">
<div class="sl-wrap">

  {{-- Topbar --}}
  <div class="sl-topbar">
    <div>
      <h1>Histórico de Vendas</h1>
      <div class="sl-sub">Todos os vouchers que entregou a clientes — clique numa linha para ver os códigos</div>
    </div>
    <a href="{{ route('reseller.sell') }}" class="sl-back">← Vender</a>
  </div>

  {{-- Alerts --}}
  @if(session('warning'))
    <div class="sl-warn">⚠ {{ session('warning') }}</div>
  @endif
  @if(session('status'))
    <div class="sl-ok">✓ {{ session('status') }}</div>
  @endif
  @if(session('error'))
    <div class="sl-err">{{ session('error') }}</div>
  @endif

  {{-- Lista de vendas --}}
  @if($sales->isEmpty())
    <div class="empty-state">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      <h3>Ainda não fez nenhuma venda</h3>
      <p style="font-size:.85rem;">Quando entregar vouchers a clientes, aparecem aqui com os códigos e opção de re-download do PDF.</p>
    </div>
  @else
    <p style="font-size:.82rem; color:var(--sl-muted); margin-bottom:1rem;">{{ $sales->count() }} venda(s) registada(s)</p>

    @foreach($sales as $i => $sale)
    <div class="sale-card">
      <div class="sale-header" onclick="toggleSale({{ $i }})">
        <div>
          <div class="sale-date">
            {{ $sale['date'] ? $sale['date']->format('d/m/Y H:i') : '—' }}
          </div>
          <div class="sale-meta">
            @if($sale['customer_ref'])
              Cliente: <strong>{{ $sale['customer_ref'] }}</strong> &nbsp;·&nbsp;
            @endif
            {{ $sale['total'] }} voucher(s)
            @if($sale['is_legacy'])
              &nbsp;<span style="background:#fef3c7;color:#92400e;padding:.1rem .4rem;border-radius:4px;font-size:.7rem;font-weight:700;">venda antiga</span>
            @endif
          </div>
        </div>
        <div class="sale-actions">
          <span class="sale-badge">✓ {{ $sale['total'] }} código(s)</span>
          @if($sale['sale_group'])
            <a href="{{ route('reseller.sales.pdf', $sale['sale_group']) }}"
               class="btn-pdf"
               onclick="event.stopPropagation();">
              ⬇ PDF
            </a>
          @endif
          <span style="font-size:.75rem; color:var(--sl-muted);" id="arrow-{{ $i }}">▼</span>
        </div>
      </div>
      <div class="sale-body" id="body-{{ $i }}">
        <table class="codes-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Plano</th>
              <th>Código do Voucher</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sale['codes'] as $j => $code)
            <tr>
              <td style="color:var(--sl-muted);">{{ $j + 1 }}</td>
              <td><span class="plan-chip">{{ $code->plan_id }}</span></td>
              <td><strong>{{ $code->code }}</strong></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
    @endforeach
  @endif

</div>
</div>
@endsection

@push('scripts')
<script>
function toggleSale(i) {
  var body  = document.getElementById('body-' + i);
  var arrow = document.getElementById('arrow-' + i);
  var open  = body.classList.toggle('open');
  arrow.textContent = open ? '▲' : '▼';
}
</script>
@endpush
