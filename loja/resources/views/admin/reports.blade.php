@extends('layouts.app')

@section('content')
<style>
:root{--a-bg:#f4f6f9;--a-surf:#fff;--a-border:#dde2ea;--a-text:#1a202c;--a-muted:#64748b;--a-faint:#9aa5b4;--a-brand:#f7b500;--a-green:#16a34a;--a-amber:#d97706;--a-red:#dc2626;--a-blue:#2563eb;--a-purple:#7c3aed;}
.ap{font-family:Inter,system-ui,sans-serif;background:var(--a-bg);min-height:60vh;padding:2rem 0 4rem;color:var(--a-text);}
.ap-wrap{max-width:1200px;margin:0 auto;padding:0 1.5rem;}
.ap-topbar{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1.75rem;}
.ap-topbar h1{font-size:1.35rem;font-weight:800;margin:0 0 .15rem;letter-spacing:-.02em;}
.ap-sub{font-size:.78rem;color:var(--a-faint);}
.ap-back{font-size:.82rem;font-weight:600;color:var(--a-muted);text-decoration:none;padding:.4rem .85rem;border:1px solid var(--a-border);border-radius:7px;background:var(--a-surf);transition:background .15s;}
.ap-back:hover{background:var(--a-border);}
/* KPI grand */
.ap-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem;margin-bottom:2rem;}
.ap-kpi{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;padding:1.2rem 1.4rem;}
.ap-kpi-label{font-size:.75rem;font-weight:600;color:var(--a-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem;}
.ap-kpi-val{font-size:1.6rem;font-weight:800;line-height:1;color:var(--a-text);}
.ap-kpi-sub{font-size:.75rem;color:var(--a-faint);margin-top:.3rem;}
.ap-kpi--red .ap-kpi-val{color:var(--a-red);}
.ap-kpi--green .ap-kpi-val{color:var(--a-green);}
/* Sections */
.ap-section{margin-bottom:2.5rem;}
.ap-section-title{font-size:1.05rem;font-weight:800;color:var(--a-text);border-left:4px solid var(--a-brand);padding-left:.7rem;margin-bottom:1rem;}
.ap-section-title.blue{border-color:var(--a-blue);}
.ap-section-title.purple{border-color:var(--a-purple);}
/* Sub-KPIs */
.ap-subkpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:.75rem;margin-bottom:1.25rem;}
.ap-subkpi{background:var(--a-surf);border:1px solid var(--a-border);border-radius:9px;padding:.9rem 1.1rem;}
.ap-subkpi-label{font-size:.7rem;font-weight:600;color:var(--a-muted);text-transform:uppercase;letter-spacing:.04em;margin-bottom:.25rem;}
.ap-subkpi-val{font-size:1.15rem;font-weight:800;color:var(--a-text);}
/* Tables */
.ap-tcard{background:var(--a-surf);border:1px solid var(--a-border);border-radius:11px;overflow:hidden;margin-bottom:1rem;}
.ap-tcard-hdr{padding:.75rem 1.1rem;background:#f8fafc;border-bottom:1px solid var(--a-border);font-size:.82rem;font-weight:700;color:var(--a-muted);text-transform:uppercase;letter-spacing:.04em;}
.ap-table{width:100%;border-collapse:collapse;font-size:.85rem;}
.ap-table th{padding:.6rem 1rem;text-align:left;color:var(--a-muted);font-weight:600;font-size:.78rem;text-transform:uppercase;background:#f8fafc;border-bottom:1px solid var(--a-border);}
.ap-table td{padding:.6rem 1rem;border-bottom:1px solid #f1f5f9;color:var(--a-text);}
.ap-table tr:last-child td{border-bottom:none;}
.ap-table tr:hover td{background:#fafbfc;}
.ap-table tfoot td{font-weight:700;background:#f8fafc;border-top:2px solid var(--a-border);padding:.7rem 1rem;}
.ap-two-col{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:768px){.ap-two-col{grid-template-columns:1fr;}}
.badge{display:inline-block;padding:.2rem .6rem;border-radius:999px;font-size:.73rem;font-weight:700;white-space:nowrap;}
.bg-amber{background:#fef3c7;color:#b45309;}.bg-green{background:#dcfce7;color:#15803d;}.bg-gray{background:#f1f5f9;color:#475569;}.bg-red{background:#fee2e2;color:#b91c1c;}.bg-blue{background:#dbeafe;color:#1d4ed8;}.bg-purple{background:#ede9fe;color:#6d28d9;}
.ap-agt{background:#fff7ed;border:1px solid #fed7aa;border-left:4px solid #ea580c;padding:1rem 1.25rem;border-radius:10px;margin-top:1.5rem;}
.ap-agt-title{font-size:.95rem;font-weight:800;color:#c2410c;margin-bottom:.4rem;}
.ap-agt-val{font-size:1.5rem;font-weight:800;color:#9a3412;}
.ap-agt-note{font-size:.78rem;color:#92400e;margin-top:.4rem;line-height:1.55;}
.ap-empty{color:var(--a-faint);font-size:.85rem;padding:1.1rem;}
</style>

<div class="ap"><div class="ap-wrap">

  {{-- TOP BAR --}}
  <div class="ap-topbar">
    <div>
      <h1>Relat&oacute;rios Detalhados</h1>
      <p class="ap-sub">Admin &rsaquo; Autovenda + Canal Revendedor &mdash; totais acumulados</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="ap-back">&larr; Dashboard</a>
  </div>

  {{-- ─── GRAND TOTALS ─── --}}
  <div class="ap-kpis">
    <div class="ap-kpi ap-kpi--green">
      <div class="ap-kpi-label">Receita Total (todos os canais)</div>
      <div class="ap-kpi-val">{{ number_format($grandTotalRevenue, 0, ',', '.') }}<span style="font-size:.9rem;font-weight:600;"> Kz</span></div>
      <div class="ap-kpi-sub">Autovenda + Canal Revendedor</div>
    </div>
    <div class="ap-kpi">
      <div class="ap-kpi-label">Operações Concluídas</div>
      <div class="ap-kpi-val">{{ number_format($grandTotalOrders, 0, ',', '.') }}</div>
      <div class="ap-kpi-sub">Ordens pagas + compras revendedor</div>
    </div>
    <div class="ap-kpi ap-kpi--red">
      <div class="ap-kpi-label">Imposto Retido Total (AGT)</div>
      <div class="ap-kpi-val">{{ number_format($grandTotalTax, 0, ',', '.') }}<span style="font-size:.9rem;font-weight:600;"> Kz</span></div>
      <div class="ap-kpi-sub">6,5% Imposto Industrial &mdash; canal revendedor</div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════
       SECÇÃO 1 — AUTOVENDA (clientes directo online)
  ════════════════════════════════════════════════ --}}
  <div class="ap-section">
    <div class="ap-section-title blue">&#9889; Autovenda &mdash; Recargas Wi-Fi Online</div>

    {{-- Sub-KPIs autovenda --}}
    <div class="ap-subkpis">
      <div class="ap-subkpi">
        <div class="ap-subkpi-label">Total de Ordens</div>
        <div class="ap-subkpi-val">{{ number_format($autoTotals['total_orders'], 0, ',', '.') }}</div>
      </div>
      <div class="ap-subkpi" style="border-color:#bbf7d0;">
        <div class="ap-subkpi-label">Ordens Pagas</div>
        <div class="ap-subkpi-val" style="color:var(--a-green);">{{ number_format($autoTotals['paid_orders'], 0, ',', '.') }}</div>
      </div>
      <div class="ap-subkpi">
        <div class="ap-subkpi-label">Receita (Pago)</div>
        <div class="ap-subkpi-val">{{ number_format($autoTotals['revenue_aoa'], 0, ',', '.') }} Kz</div>
      </div>
      <div class="ap-subkpi" style="border-color:#fde68a;">
        <div class="ap-subkpi-label">Aguarda Pagamento</div>
        <div class="ap-subkpi-val" style="color:var(--a-amber);">{{ number_format($autoTotals['pending_orders'], 0, ',', '.') }}</div>
      </div>
    </div>

    <div class="ap-two-col">

      {{-- Por estado --}}
      <div class="ap-tcard">
        <div class="ap-tcard-hdr">Por Estado</div>
        @if($autoByStatus->isEmpty())
          <p class="ap-empty">Nenhuma ordem registada.</p>
        @else
          <table class="ap-table">
            <thead><tr><th>Estado</th><th>Ordens</th><th>Total (Kz)</th></tr></thead>
            <tbody>
              @foreach($autoByStatus as $row)
                <tr>
                  <td>
                    @if($row->status === 'paid')
                      <span class="badge bg-green">Pago</span>
                    @elseif($row->status === 'awaiting_payment')
                      <span class="badge bg-amber">Aguarda</span>
                    @elseif($row->status === 'failed')
                      <span class="badge bg-red">Falhou</span>
                    @elseif($row->status === 'cancelled')
                      <span class="badge bg-gray">Cancelado</span>
                    @else
                      <span class="badge bg-gray">{{ $row->status }}</span>
                    @endif
                  </td>
                  <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                  <td><strong>{{ number_format($row->total_amount ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>

      {{-- Por plano (pago) --}}
      <div class="ap-tcard">
        <div class="ap-tcard-hdr">Por Plano (Ordens Pagas)</div>
        @if($autoByPlan->isEmpty())
          <p class="ap-empty">Nenhum plano vendido.</p>
        @else
          <table class="ap-table">
            <thead><tr><th>Plano</th><th>Ordens</th><th>Receita (Kz)</th></tr></thead>
            <tbody>
              @foreach($autoByPlan as $row)
                <tr>
                  <td><span class="badge bg-blue">{{ $row->plan_name ?? $row->plan_id }}</span></td>
                  <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                  <td><strong>{{ number_format($row->total_amount ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td>Total</td>
                <td>{{ number_format($autoByPlan->sum('total'), 0, ',', '.') }}</td>
                <td>{{ number_format($autoByPlan->sum('total_amount'), 0, ',', '.') }} Kz</td>
              </tr>
            </tfoot>
          </table>
        @endif
      </div>

    </div>

    {{-- Últimos 30 dias --}}
    <div class="ap-tcard">
      <div class="ap-tcard-hdr">Actividade &mdash; &Uacute;ltimos 30 Dias (todas as ordens)</div>
      @if($autoLatestDays->isEmpty())
        <p class="ap-empty">Nenhuma actividade recente.</p>
      @else
        <table class="ap-table">
          <thead><tr><th>Data</th><th>Ordens</th><th>Montante (Kz)</th></tr></thead>
          <tbody>
            @foreach($autoLatestDays as $row)
              <tr>
                <td><strong>{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</strong></td>
                <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                <td>{{ number_format($row->total_amount ?? 0, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td>Total (30 dias)</td>
              <td>{{ number_format($autoLatestDays->sum('total'), 0, ',', '.') }}</td>
              <td>{{ number_format($autoLatestDays->sum('total_amount'), 0, ',', '.') }} Kz</td>
            </tr>
          </tfoot>
        </table>
      @endif
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════
       SECÇÃO 2 — CANAL REVENDEDOR
  ════════════════════════════════════════════════ --}}
  <div class="ap-section">
    <div class="ap-section-title purple">&#128722; Canal Revendedor &mdash; Compras + Venda Manual Admin</div>

    {{-- Sub-KPIs revendedor --}}
    <div class="ap-subkpis">
      <div class="ap-subkpi">
        <div class="ap-subkpi-label">Compras Concluídas</div>
        <div class="ap-subkpi-val">{{ number_format($resellerTotals->total_purchases ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="ap-subkpi">
        <div class="ap-subkpi-label">Códigos Alocados</div>
        <div class="ap-subkpi-val">{{ number_format($resellerTotals->total_codes ?? 0, 0, ',', '.') }}</div>
      </div>
      <div class="ap-subkpi" style="border-color:#bbf7d0;">
        <div class="ap-subkpi-label">Recebimentos (revendedor)</div>
        <div class="ap-subkpi-val" style="color:var(--a-green);">{{ number_format($resellerTotals->total_paid ?? 0, 0, ',', '.') }} Kz</div>
      </div>
      <div class="ap-subkpi" style="border-color:#d8b4fe;">
        <div class="ap-subkpi-label">Descontos (revendedor)</div>
        <div class="ap-subkpi-val" style="color:var(--a-purple);">{{ number_format($resellerTotals->total_profit ?? 0, 0, ',', '.') }} Kz</div>
      </div>
      <div class="ap-subkpi" style="border-color:#fecaca;">
        <div class="ap-subkpi-label">Imposto Retido</div>
        <div class="ap-subkpi-val" style="color:var(--a-red);">{{ number_format($resellerTotals->total_tax ?? 0, 0, ',', '.') }} Kz</div>
      </div>
    </div>

    <div class="ap-two-col">

      {{-- Por plano --}}
      <div class="ap-tcard">
        <div class="ap-tcard-hdr">Por Plano (Concluídas)</div>
        @if($resellerByPlan->isEmpty())
          <p class="ap-empty">Nenhuma compra registada.</p>
        @else
          <table class="ap-table">
            <thead><tr><th>Plano</th><th>Cód.</th><th>Recebimentos (Kz)</th><th>Descontos (Kz)</th><th>Imposto (Kz)</th></tr></thead>
            <tbody>
              @foreach($resellerByPlan as $row)
                <tr>
                  <td><span class="badge bg-purple">{{ $row->plan_name ?? $row->plan_slug }}</span></td>
                  <td>{{ number_format($row->total_codes, 0, ',', '.') }}</td>
                  <td>{{ number_format($row->total_paid ?? 0, 0, ',', '.') }}</td>
                  <td style="color:var(--a-green);">{{ number_format($row->total_profit ?? 0, 0, ',', '.') }}</td>
                  <td style="color:var(--a-red);">{{ number_format($row->total_tax ?? 0, 0, ',', '.') }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td>Total</td>
                <td>{{ number_format($resellerByPlan->sum('total_codes'), 0, ',', '.') }}</td>
                <td>{{ number_format($resellerByPlan->sum('total_paid'), 0, ',', '.') }} Kz</td>
                <td>{{ number_format($resellerByPlan->sum('total_profit'), 0, ',', '.') }} Kz</td>
                <td style="color:var(--a-red);">{{ number_format($resellerByPlan->sum('total_tax'), 0, ',', '.') }} Kz</td>
              </tr>
            </tfoot>
          </table>
        @endif
      </div>

      {{-- Por método de pagamento --}}
      <div class="ap-tcard">
        <div class="ap-tcard-hdr">Por M&eacute;todo de Pagamento</div>
        @if($resellerByMethod->isEmpty())
          <p class="ap-empty">Nenhuma compra registada.</p>
        @else
          <table class="ap-table">
            <thead><tr><th>Método</th><th>Compras</th><th>Códigos</th><th>Total (Kz)</th></tr></thead>
            <tbody>
              @foreach($resellerByMethod as $row)
                <tr>
                  <td>
                    @if($row->payment_method === 'manual_admin')
                      <span class="badge bg-amber">Venda Manual Admin</span>
                    @elseif($row->payment_method === 'transferencia')
                      <span class="badge bg-blue">Transferência</span>
                    @elseif($row->payment_method === 'multicaixa')
                      <span class="badge bg-green">Multicaixa</span>
                    @else
                      <span class="badge bg-gray">{{ $row->payment_method }}</span>
                    @endif
                  </td>
                  <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                  <td>{{ number_format($row->total_codes ?? 0, 0, ',', '.') }}</td>
                  <td><strong>{{ number_format($row->total_paid ?? 0, 0, ',', '.') }}</strong></td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <td>Total</td>
                <td>{{ number_format($resellerByMethod->sum('total'), 0, ',', '.') }}</td>
                <td>{{ number_format($resellerByMethod->sum('total_codes'), 0, ',', '.') }}</td>
                <td>{{ number_format($resellerByMethod->sum('total_paid'), 0, ',', '.') }} Kz</td>
              </tr>
            </tfoot>
          </table>
        @endif
      </div>

    </div>

    {{-- Últimos 30 dias revendedor --}}
    <div class="ap-tcard">
      <div class="ap-tcard-hdr">Actividade Revendedor &mdash; &Uacute;ltimos 30 Dias (pagas)</div>
      @if($resellerLatestDays->isEmpty())
        <p class="ap-empty">Nenhuma actividade recente.</p>
      @else
        <table class="ap-table">
          <thead><tr><th>Data</th><th>Compras</th><th>Códigos</th><th>Recebimentos (Kz)</th><th>Descontos (Kz)</th><th>Imposto (Kz)</th></tr></thead>
          <tbody>
            @foreach($resellerLatestDays as $row)
              <tr>
                <td><strong>{{ \Carbon\Carbon::parse($row->day)->format('d/m/Y') }}</strong></td>
                <td>{{ number_format($row->total, 0, ',', '.') }}</td>
                <td>{{ number_format($row->total_codes ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($row->total_paid ?? 0, 0, ',', '.') }}</td>
                <td style="color:var(--a-green);">{{ number_format($row->total_profit ?? 0, 0, ',', '.') }}</td>
                <td style="color:var(--a-red);">{{ number_format($row->total_tax ?? 0, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td>Total (30 dias)</td>
              <td>{{ number_format($resellerLatestDays->sum('total'), 0, ',', '.') }}</td>
              <td>{{ number_format($resellerLatestDays->sum('total_codes'), 0, ',', '.') }}</td>
              <td>{{ number_format($resellerLatestDays->sum('total_paid'), 0, ',', '.') }} Kz</td>
              <td style="color:var(--a-green);">{{ number_format($resellerLatestDays->sum('total_profit'), 0, ',', '.') }} Kz</td>
              <td style="color:var(--a-red);">{{ number_format($resellerLatestDays->sum('total_tax'), 0, ',', '.') }} Kz</td>
            </tr>
          </tfoot>
        </table>
      @endif
    </div>

    {{-- Top 10 Revendedores --}}
    <div class="ap-tcard">
      <div class="ap-tcard-hdr">Top 10 Revendedores (por volume pago)</div>
      @if($topResellers->isEmpty())
        <p class="ap-empty">Nenhum revendedor com compras concluídas.</p>
      @else
        <table class="ap-table">
          <thead><tr><th>#</th><th>Revendedor</th><th>Compras</th><th>Códigos</th><th>Recebimentos (Kz)</th><th>Descontos (Kz)</th><th>Imposto (Kz)</th></tr></thead>
          <tbody>
            @foreach($topResellers as $i => $row)
              <tr>
                <td style="color:var(--a-faint);font-weight:700;">{{ $i + 1 }}</td>
                <td>
                  <strong>{{ $row->application->business_name ?? $row->application->full_name ?? '—' }}</strong><br>
                  <span style="font-size:.75rem;color:var(--a-muted);">{{ $row->application->phone ?? '' }}</span>
                </td>
                <td>{{ number_format($row->total_purchases, 0, ',', '.') }}</td>
                <td>{{ number_format($row->total_codes ?? 0, 0, ',', '.') }}</td>
                <td><strong>{{ number_format($row->total_paid ?? 0, 0, ',', '.') }}</strong></td>
                <td style="color:var(--a-green);">{{ number_format($row->total_profit ?? 0, 0, ',', '.') }}</td>
                <td style="color:var(--a-red);">{{ number_format($row->total_tax ?? 0, 0, ',', '.') }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2">Total Top 10</td>
              <td>{{ number_format($topResellers->sum('total_purchases'), 0, ',', '.') }}</td>
              <td>{{ number_format($topResellers->sum('total_codes'), 0, ',', '.') }}</td>
              <td>{{ number_format($topResellers->sum('total_paid'), 0, ',', '.') }} Kz</td>
              <td>{{ number_format($topResellers->sum('total_profit'), 0, ',', '.') }} Kz</td>
              <td style="color:var(--a-red);">{{ number_format($topResellers->sum('total_tax'), 0, ',', '.') }} Kz</td>
            </tr>
          </tfoot>
        </table>
      @endif
    </div>

  </div>

  {{-- ─── CAIXA AGT ─── --}}
  <div class="ap-agt">
    <div class="ap-agt-title">&#9888; Imposto Industrial retido (6,5%) a entregar &agrave; AGT</div>
    <div class="ap-agt-val">{{ number_format($grandTotalTax, 0, ',', '.') }} Kz</div>
    <div class="ap-agt-note">
      Este valor corresponde ao total acumulado do imposto industrial retido sobre o lucro bruto dos revendedores (taxa de 6,5%), apurado sobre todas as compras concluídas desde o início.
      Deve ser declarado e entregue mensalmente à Administração Geral Tributária (AGT).<br>
      <strong>Nota:</strong> As vendas do canal online (Autovenda) não estão sujeitas a esta retenção.
    </div>
  </div>

</div></div>
@endsection
