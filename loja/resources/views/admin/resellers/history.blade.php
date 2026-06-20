@extends('layouts.app')

@section('title', 'Histórico Compras vs Vendas — Admin')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-purple:#7c3aed;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 5rem;color:var(--a-text);}
.ap-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.5rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-topbar .ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);}
.ap-back:hover{background:var(--a-border);}

/* KPIs */
.ap-kpis{display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:.75rem;margin-bottom:1.75rem;}
.ap-kpi{background:var(--a-surf);border:1px solid var(--a-border);border-radius:10px;padding:1.1rem 1.2rem;border-top:3px solid var(--a-border);}
.ap-kpi.k-brand  {border-top-color:var(--a-brand);}
.ap-kpi.k-green  {border-top-color:var(--a-green);}
.ap-kpi.k-amber  {border-top-color:var(--a-amber);}
.ap-kpi.k-purple {border-top-color:var(--a-purple);}
.ap-kpi.k-red    {border-top-color:var(--a-red);}
.ap-kpi-val{font-size:1.55rem;font-weight:800;line-height:1;margin-bottom:.2rem;}
.ap-kpi-lbl{font-size:.74rem;font-weight:600;color:var(--a-muted);text-transform:uppercase;letter-spacing:.04em;}
.ap-kpi-sub{font-size:.72rem;color:var(--a-faint);margin-top:.3rem;padding-top:.3rem;border-top:1px solid var(--a-border);}

/* Table card */
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;overflow:hidden;}
.ap-tcard-head{padding:.85rem 1.1rem;border-bottom:1px solid var(--a-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;}
.ap-tcard-head strong{font-size:.9rem;font-weight:700;}
.ap-table{width:100%;border-collapse:collapse;font-size:.845rem;}
.ap-table thead{background:#f8fafc;}
.ap-table th{text-align:left;padding:.6rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);border-bottom:1px solid var(--a-border);white-space:nowrap;}
.ap-table th.r,.ap-table td.r{text-align:right;}
.ap-table td{padding:.58rem 1rem;border-bottom:1px solid #f4f6f9;vertical-align:middle;color:#374151;}
.ap-table tbody tr:last-child td{border-bottom:none;}
.ap-table tbody tr:hover td{background:#fffdf5;}
.ap-table .dim{color:var(--a-faint);font-size:.8rem;}

/* VS bar */
.cv-bar-wrap{display:flex;height:7px;border-radius:999px;overflow:hidden;background:#f1f5f9;min-width:80px;}
.cv-bar-buy {background:var(--a-brand);}
.cv-bar-sell{background:var(--a-green);}

/* badges */
.badge{display:inline-block;padding:.18rem .55rem;border-radius:999px;font-size:.72rem;font-weight:700;white-space:nowrap;}
.b-green {background:#dcfce7;color:#15803d;}
.b-amber {background:#fef3c7;color:#b45309;}
.b-red   {background:#fee2e2;color:#b91c1c;}
.b-blue  {background:#dbeafe;color:#1d4ed8;}
.b-gray  {background:#f1f5f9;color:#475569;}

/* progress compare */
.cv-compare{display:flex;align-items:center;gap:.5rem;font-size:.8rem;}
.cv-pct{font-weight:700;min-width:36px;text-align:right;}

.ap-empty{padding:3.5rem 1rem;text-align:center;color:var(--a-faint);}
.ap-btn{display:inline-flex;align-items:center;gap:.35rem;padding:.38rem .85rem;border-radius:7px;font-size:.8rem;font-weight:700;border:1.5px solid var(--a-border);background:var(--a-surf);color:#374151;text-decoration:none;transition:background .13s,border-color .13s;}
.ap-btn:hover{background:#fffbeb;border-color:var(--a-brand);color:#92400e;}
.ap-btn.primary{background:var(--a-brand);border-color:var(--a-brand);color:#1a202c;}
.ap-btn.primary:hover{background:#e0a800;}

@media(max-width:900px){
  .ap-kpis{grid-template-columns:repeat(2,1fr);}
  .ap-table .hide-sm{display:none;}
}
@media(max-width:580px){
  .ap-kpis{grid-template-columns:1fr 1fr;}
  .ap-tcard-head{flex-direction:column;align-items:flex-start;}
}
</style>

<div class="ap"><div class="ap-wrap">

  <div class="ap-topbar">
    <div>
      <h1>Histórico Compras vs Vendas</h1>
      <p class="ap-sub">Admin › Revendedores › Visão comparativa por agente</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <a href="{{ route('admin.resellers.purchases.index') }}" class="ap-back">📋 Compras em Bloco</a>
      <a href="{{ route('admin.resellers.index') }}" class="ap-back">&larr; Candidaturas</a>
    </div>
  </div>

  {{-- KPIs globais --}}
  <div class="ap-kpis">
    <div class="ap-kpi k-brand">
      <div class="ap-kpi-val">{{ $stats->count() }}</div>
      <div class="ap-kpi-lbl">Agentes activos</div>
      <div class="ap-kpi-sub">com pelo menos 1 compra</div>
    </div>
    <div class="ap-kpi k-amber">
      <div class="ap-kpi-val">{{ number_format($globalTotals['spent'], 0, ',', '.') }}</div>
      <div class="ap-kpi-lbl">Total comprado (Kz)</div>
      <div class="ap-kpi-sub">{{ number_format($globalTotals['bought'], 0, ',', '.') }} vouchers adquiridos</div>
    </div>
    <div class="ap-kpi k-green">
      <div class="ap-kpi-val">{{ number_format($globalTotals['sales'], 0, ',', '.') }}</div>
      <div class="ap-kpi-lbl">Total vendido (Kz)</div>
      <div class="ap-kpi-sub">{{ number_format($globalTotals['sold'], 0, ',', '.') }} vouchers distribuídos</div>
    </div>
    <div class="ap-kpi k-purple">
      <div class="ap-kpi-val">{{ number_format($globalTotals['sales'] - $globalTotals['spent'], 0, ',', '.') }}</div>
      <div class="ap-kpi-lbl">Lucro total ARs (Kz)</div>
      <div class="ap-kpi-sub">vendas − custo de compra</div>
    </div>
    <div class="ap-kpi k-red">
      <div class="ap-kpi-val">{{ number_format($globalTotals['stock'], 0, ',', '.') }}</div>
      <div class="ap-kpi-lbl">Stock total em ARs</div>
      <div class="ap-kpi-sub">vouchers por distribuir</div>
    </div>
  </div>

  {{-- Tabela principal --}}
  <div class="ap-tcard">
    <div class="ap-tcard-head">
      <strong>Desempenho por Agente Revendedor</strong>
      <span class="ap-sub" style="font-size:.78rem;">ordenado por total comprado &darr;</span>
    </div>

    @if($stats->isEmpty())
      <div class="ap-empty">
        <p style="font-size:.95rem;font-weight:700;color:var(--a-muted);margin:0 0 .3rem;">Sem dados</p>
        <p style="font-size:.83rem;margin:0;">Nenhum revendedor aprovado efectuou compras ainda.</p>
      </div>
    @else
    <div style="overflow-x:auto;">
      <table class="ap-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Agente Revendedor</th>
            <th class="r">Compras (Kz)</th>
            <th class="r hide-sm">Vouchers comprados</th>
            <th class="r">Vendas (Kz)</th>
            <th class="r hide-sm">Vouchers vendidos</th>
            <th class="r hide-sm">Stock</th>
            <th class="r">Lucro AR (Kz)</th>
            <th class="hide-sm">% Escoado</th>
            <th class="r hide-sm">Última compra</th>
            <th class="r hide-sm">Última venda</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($stats as $i => $row)
            @php
              $escoado = $row->total_bought > 0 ? round($row->total_sold / $row->total_bought * 100) : 0;
              $medals  = ['🥇','🥈','🥉'];
            @endphp
            <tr>
              <td style="text-align:center;font-weight:700;color:{{ $i < 3 ? '#d97706' : '#9aa5b4' }};">
                {{ $medals[$i] ?? ($i + 1) }}
              </td>
              <td>
                <a href="{{ route('admin.resellers.show', $row->application) }}"
                   style="font-weight:700;color:#1a202c;text-decoration:none;">
                  {{ $row->application->full_name }}
                </a>
                <div class="dim" style="margin-top:.1rem;">{{ $row->application->phone }}</div>
                @if($row->application->reseller_mode === 'own')
                  <span class="badge b-blue" style="margin-top:.2rem;">Modo 1</span>
                @elseif($row->application->reseller_mode === 'angolawifi')
                  <span class="badge b-amber" style="margin-top:.2rem;">Modo 2</span>
                @endif
              </td>
              <td class="r" style="font-weight:700;">
                {{ number_format($row->total_spent, 0, ',', '.') }}
              </td>
              <td class="r hide-sm">
                <span style="font-weight:600;">{{ number_format($row->total_bought, 0, ',', '.') }}</span>
              </td>
              <td class="r" style="font-weight:700;color:{{ $row->total_sales_aoa > 0 ? 'var(--a-green)' : 'var(--a-faint)' }};">
                {{ $row->total_sales_aoa > 0 ? number_format($row->total_sales_aoa, 0, ',', '.') : '—' }}
              </td>
              <td class="r hide-sm">
                @if($row->total_sold > 0)
                  <span style="font-weight:600;color:var(--a-green);">{{ number_format($row->total_sold, 0, ',', '.') }}</span>
                @else
                  <span class="dim">0</span>
                @endif
              </td>
              <td class="r hide-sm">
                @if($row->stock > 0)
                  <span class="badge b-amber">{{ number_format($row->stock, 0, ',', '.') }}</span>
                @elseif($row->stock === 0 && $row->total_bought > 0)
                  <span class="badge b-green">0</span>
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td class="r" style="font-weight:700;color:{{ $row->profit_estimate >= 0 ? 'var(--a-green)' : 'var(--a-red)' }};">
                {{ $row->profit_estimate >= 0 ? '+' : '' }}{{ number_format($row->profit_estimate, 0, ',', '.') }}
              </td>
              <td class="hide-sm">
                @if($row->total_bought > 0)
                  <div style="display:flex;align-items:center;gap:.5rem;">
                    <div class="cv-bar-wrap" style="flex:1;">
                      <div class="cv-bar-buy" style="width:100%;"></div>
                    </div>
                    <div class="cv-bar-wrap" style="flex:1;">
                      <div class="cv-bar-sell" style="width:{{ $escoado }}%;"></div>
                    </div>
                    <span style="font-size:.75rem;font-weight:700;color:{{ $escoado >= 80 ? 'var(--a-green)' : ($escoado >= 40 ? 'var(--a-amber)' : 'var(--a-red)') }};min-width:32px;">
                      {{ $escoado }}%
                    </span>
                  </div>
                @else
                  <span class="dim">—</span>
                @endif
              </td>
              <td class="r dim hide-sm">
                {{ $row->last_purchase ? \Carbon\Carbon::parse($row->last_purchase)->format('d/m/Y') : '—' }}
              </td>
              <td class="r dim hide-sm">
                {{ $row->last_sale ? \Carbon\Carbon::parse($row->last_sale)->format('d/m/Y') : '—' }}
              </td>
              <td>
                <a href="{{ route('admin.resellers.show', $row->application) }}#cv-section" class="ap-btn">
                  Ver detalhe →
                </a>
              </td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr style="background:#f8fafc;border-top:2px solid var(--a-border);">
            <td colspan="2" style="font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--a-faint);padding:.65rem 1rem;">
              Totais
            </td>
            <td class="r" style="font-weight:800;">{{ number_format($globalTotals['spent'], 0, ',', '.') }}</td>
            <td class="r hide-sm" style="font-weight:800;">{{ number_format($globalTotals['bought'], 0, ',', '.') }}</td>
            <td class="r" style="font-weight:800;color:var(--a-green);">{{ number_format($globalTotals['sales'], 0, ',', '.') }}</td>
            <td class="r hide-sm" style="font-weight:800;color:var(--a-green);">{{ number_format($globalTotals['sold'], 0, ',', '.') }}</td>
            <td class="r hide-sm" style="font-weight:800;color:var(--a-amber);">{{ number_format($globalTotals['stock'], 0, ',', '.') }}</td>
            <td class="r" style="font-weight:800;color:var(--a-green);">
              +{{ number_format($globalTotals['sales'] - $globalTotals['spent'], 0, ',', '.') }}
            </td>
            <td colspan="4" class="hide-sm"></td>
          </tr>
        </tfoot>
      </table>
    </div>
    @endif
  </div>

  {{-- Legenda --}}
  <div style="margin-top:1.1rem;display:flex;flex-wrap:wrap;gap:1.25rem;font-size:.78rem;color:var(--a-muted);">
    <span><span style="display:inline-block;width:10px;height:10px;background:var(--a-brand);border-radius:2px;margin-right:.3rem;"></span>Compras = valor pago à AngolaWiFi</span>
    <span><span style="display:inline-block;width:10px;height:10px;background:var(--a-green);border-radius:2px;margin-right:.3rem;"></span>Vendas = valor cobrado ao cliente final (preço público)</span>
    <span><strong>% Escoado</strong> = vouchers distribuídos / vouchers comprados</span>
    <span><strong>Lucro AR</strong> = Vendas − Compras</span>
  </div>

</div></div>
@endsection
