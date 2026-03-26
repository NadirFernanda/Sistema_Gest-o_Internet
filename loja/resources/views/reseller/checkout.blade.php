@extends('layouts.app')

@push('styles')
<style>
/* ── Página de Pagamento — Revendedor ─────────────────── */
.rv-page {
  min-height: 80vh;
  background: #f8fafc;
  padding: 2.5rem 1rem 4rem;
}
.rv-pay-wrap {
  max-width: 780px;
  margin: 0 auto;
}
.rv-back-link {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  color: #64748b;
  font-size: .85rem;
  font-weight: 600;
  text-decoration: none;
  margin-bottom: 1.25rem;
}
.rv-back-link:hover { color: #0f172a; }
.rv-pay-header { margin-bottom: 1.5rem; }
.rv-pay-header h1 {
  font-size: 1.55rem;
  font-weight: 900;
  color: #0f172a;
  margin: 0 0 .2rem;
  letter-spacing: -.02em;
}
.rv-pay-header p { color: #64748b; font-size: .9rem; margin: 0; }

.rv-panel {
  background: #fff;
  border-radius: 1rem;
  box-shadow: 0 2px 12px rgba(0,0,0,.06);
  padding: 1.5rem;
  margin-bottom: 1.25rem;
}
.rv-panel-title {
  font-size: 1rem;
  font-weight: 800;
  color: #0f172a;
  margin: 0 0 1.1rem;
  display: flex;
  align-items: center;
  gap: .5rem;
}

/* ── Order summary table ──────────────────────────────── */
.rv-sum-table {
  width: 100%;
  border-collapse: collapse;
  font-size: .9rem;
}
.rv-sum-table th {
  text-align: left;
  padding: .45rem .6rem;
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #94a3b8;
  border-bottom: 1.5px solid #f1f5f9;
}
.rv-sum-table td {
  padding: .7rem .6rem;
  border-bottom: 1px solid #f8fafc;
  vertical-align: middle;
}
.rv-sum-table .r { text-align: right; }
.rv-sum-table tbody tr:hover { background: #fafafa; }
.rv-sum-plan-badge {
  display: inline-block;
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .05em;
  padding: .2rem .55rem;
  border-radius: 99px;
  background: #eff6ff;
  color: #1d4ed8;
}
.rv-sum-tfoot td {
  padding: .9rem .6rem;
  background: #f8fafc;
  font-weight: 800;
  font-size: 1rem;
  border-top: 2px solid #e2e8f0;
}
.rv-profit-col { color: #16a34a; font-weight: 600; }

/* ── Payment method cards ─────────────────────────────── */
.rv-methods-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: .85rem;
  margin-bottom: 1.25rem;
}
.rv-method-card {
  border: 2px solid #e2e8f0;
  border-radius: .85rem;
  padding: 1.15rem 1.25rem;
  cursor: pointer;
  transition: border-color .18s, background .18s, transform .12s;
  user-select: none;
}
.rv-method-card:hover { border-color: #f7b500; transform: translateY(-1px); }
.rv-method-card.active { border-color: #f7b500; background: #fffbeb; }
.rv-method-card input[type=radio] { display: none; }
.rv-method-icon { font-size: 1.6rem; margin-bottom: .45rem; }
.rv-method-name { font-size: .92rem; font-weight: 800; color: #0f172a; margin-bottom: .15rem; }
.rv-method-desc { font-size: .8rem; color: #64748b; line-height: 1.4; }

/* ── Reference / IBAN box ─────────────────────────────── */
.rv-detail-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: .85rem;
  padding: 1.25rem 1.4rem;
  margin-bottom: 1.1rem;
  transition: opacity .2s;
}
.rv-detail-box.hidden { display: none; }
.rv-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: .45rem 0;
  border-bottom: 1px solid #f1f5f9;
  font-size: .9rem;
}
.rv-detail-row:last-child { border-bottom: none; padding-bottom: 0; }
.rv-detail-label { color: #64748b; font-size: .8rem; font-weight: 600; }
.rv-detail-value { font-weight: 800; color: #0f172a; font-family: monospace; font-size: 1rem; letter-spacing: .04em; }
.rv-detail-value.big { font-size: 1.25rem; letter-spacing: .06em; }
.rv-detail-note {
  margin-top: .85rem;
  font-size: .8rem;
  color: #78716c;
  background: #fff7ed;
  border-radius: .55rem;
  padding: .6rem .85rem;
  line-height: 1.5;
  border: 1px solid #fed7aa;
}

/* ── Prototype / simulation notice ───────────────────── */
.rv-sim-notice {
  background: #fef9c3;
  border: 1px solid #fde047;
  border-left: 4px solid #eab308;
  border-radius: .6rem;
  padding: .75rem 1rem;
  font-size: .83rem;
  color: #713f12;
  margin-bottom: 1.1rem;
  line-height: 1.5;
}

/* ── Action buttons ───────────────────────────────────── */
.rv-btn-confirm {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  width: 100%;
  padding: .95rem 1.5rem;
  background: #16a34a;
  color: #fff;
  border: none;
  border-radius: .75rem;
  font-size: 1.05rem;
  font-weight: 800;
  cursor: pointer;
  transition: background .2s, transform .12s;
  margin-bottom: .65rem;
  font-family: inherit;
}
.rv-btn-confirm:hover { background: #15803d; transform: translateY(-1px); }
.rv-btn-confirm:active { transform: none; }
.rv-btn-cancel {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .4rem;
  width: 100%;
  padding: .75rem 1.5rem;
  background: transparent;
  color: #dc2626;
  border: 2px solid #fca5a5;
  border-radius: .75rem;
  font-size: .9rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .18s;
  font-family: inherit;
}
.rv-btn-cancel:hover { background: #fef2f2; }

/* ── Total highlight ──────────────────────────────────── */
.rv-total-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
  color: #fff;
  border-radius: .85rem;
  padding: 1rem 1.5rem;
  margin-bottom: 1.25rem;
}
.rv-total-bar .lbl { font-size: .8rem; font-weight: 600; opacity: .65; }
.rv-total-bar .amount { font-size: 1.6rem; font-weight: 900; letter-spacing: -.02em; color: #f7b500; }
.rv-total-bar .profit { font-size: .85rem; font-weight: 700; color: #86efac; margin-top: .1rem; }

@media (max-width: 640px) {
  .rv-pay-wrap { padding: 0; }
  .rv-methods-grid { grid-template-columns: 1fr; }
  .rv-total-bar {
    flex-direction: column;
    text-align: center;
    gap: .75rem;
    padding: 1rem;
  }
  .rv-total-bar div[style*="text-align:right"] { text-align: center !important; }
  .rv-total-bar .amount { font-size: 1.3rem; }
  .rv-detail-row {
    flex-direction: column;
    align-items: flex-start;
    gap: .15rem;
  }
  .rv-detail-value { font-size: .85rem; word-break: break-all; }
  .rv-detail-value.big { font-size: 1rem; }
  .rv-detail-box { padding: .85rem 1rem; }
  .rv-sum-table th { font-size: .65rem; padding: .35rem .4rem; }
  .rv-sum-table td { font-size: .8rem; padding: .5rem .4rem; }
  .rv-sum-tfoot td { font-size: .85rem; padding: .65rem .4rem; }
  .rv-panel { padding: 1rem; }
  .rv-btn-confirm { font-size: .92rem; padding: .85rem 1rem; }
}
</style>
@endpush

@section('content')
<div class="rv-page">
  <div class="rv-pay-wrap">

    {{-- Back link --}}
    <form action="{{ route('reseller.panel.payment.cancel') }}" method="POST" style="display:inline;">
      @csrf
      <button type="submit" class="rv-back-link" style="background:none;border:none;cursor:pointer;padding:0;" onclick="return confirm('Cancelar e voltar? Os vouchers reservados serão libertados.')">
        ← Voltar ao painel
      </button>
    </form>

    {{-- Header --}}
    <div class="rv-pay-header">
      <h1>💳 Pagamento</h1>
      <p>Reveja os detalhes da encomenda e escolha o meio de pagamento para desbloquear os seus vouchers.</p>
    </div>

    {{-- Flash error --}}
    @if(session('error'))
      <div style="background:#fef2f2;border:1.5px solid #fecaca;border-left:4px solid #dc2626;color:#7f1d1d;padding:.75rem 1rem;border-radius:.65rem;font-size:.875rem;margin-bottom:1rem;">
        <strong>Erro:</strong> {{ session('error') }}
      </div>
    @endif

    {{-- Prototype / simulation notice --}}
    <div class="rv-sim-notice">
      ⚠️ <strong>Modo Protótipo:</strong> O botão "Confirmar Pagamento" simula a confirmação de pagamento e transfere imediatamente os vouchers para a sua conta. Num ambiente de produção, os vouchers são transferidos apenas após o pagamento ser validado pelo sistema.
    </div>

    {{-- Total bar --}}
    <div class="rv-total-bar">
      <div>
        <div class="lbl">Total a pagar</div>
        <div class="amount">{{ number_format($total, 0, ',', '.') }} Kz</div>
      </div>
      <div style="text-align:right;">
        <div class="lbl">Lucro estimado</div>
        <div class="profit">+{{ number_format($purchases->sum('profit_aoa'), 0, ',', '.') }} Kz</div>
      </div>
    </div>

    {{-- Order summary --}}
    <div class="rv-panel">
      <div class="rv-panel-title">📦 Resumo da encomenda</div>
      <div style="overflow-x:auto;">
        <table class="rv-sum-table">
          <thead>
            <tr>
              <th>Plano</th>
              <th class="r">Qtd.</th>
              <th class="r">Preço unit.</th>
              <th class="r">Subtotal</th>
              <th class="r">Lucro est.</th>
            </tr>
          </thead>
          <tbody>
            @foreach($purchases as $purchase)
            <tr>
              <td>
                <span class="rv-sum-plan-badge">{{ strtoupper($purchase->plan_slug) }}</span>
                <span style="font-weight:600;margin-left:.5rem;">{{ $purchase->plan_name }}</span>
              </td>
              <td class="r">{{ number_format($purchase->quantity) }} vouchers</td>
              <td class="r">{{ number_format($purchase->unit_price_aoa, 0, ',', '.') }} Kz</td>
              <td class="r"><strong>{{ number_format($purchase->net_amount_aoa, 0, ',', '.') }} Kz</strong></td>
              <td class="r rv-profit-col">+{{ number_format($purchase->profit_aoa, 0, ',', '.') }} Kz</td>
            </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <td class="rv-sum-tfoot" colspan="3">Total</td>
              <td class="rv-sum-tfoot r">{{ number_format($total, 0, ',', '.') }} Kz</td>
              <td class="rv-sum-tfoot r rv-profit-col">+{{ number_format($purchases->sum('profit_aoa'), 0, ',', '.') }} Kz</td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Payment form --}}
    <div class="rv-panel">
      <div class="rv-panel-title">💳 Meio de pagamento</div>

      <form id="paymentForm" action="{{ route('reseller.panel.payment.confirm') }}" method="POST">
        @csrf
        <input type="hidden" name="payment_method"    id="inputMethod"    value="multicaixa">
        <input type="hidden" name="payment_reference" id="inputReference" value="{{ $mcxRef }}">

        {{-- Method selection --}}
        <div class="rv-methods-grid">

          {{-- Multicaixa / ATM --}}
          <label class="rv-method-card active" id="card-mcx">
            <input type="radio" name="_ui_method" value="multicaixa" checked>
            <div class="rv-method-icon">🏧</div>
            <div class="rv-method-name">Multicaixa / ATM</div>
            <div class="rv-method-desc">Pague em qualquer caixa ATM ou via banca online com a referência gerada.</div>
          </label>

        </div>

        {{-- ── Multicaixa / ATM reference ─────────────────── --}}
        <div class="rv-detail-box" id="detail-mcx">
          <div class="rv-detail-row">
            <span class="rv-detail-label">Entidade</span>
            <span class="rv-detail-value">{{ $mcxEntity }}</span>
          </div>
          <div class="rv-detail-row">
            <span class="rv-detail-label">Referência</span>
            <span class="rv-detail-value big">{{ wordwrap($mcxRef, 3, ' ', true) }}</span>
          </div>
          <div class="rv-detail-row">
            <span class="rv-detail-label">Montante</span>
            <span class="rv-detail-value">{{ number_format($total, 0, ',', '.') }} Kz</span>
          </div>
          <div class="rv-detail-note">
            ⏱ Esta referência reserva o stock por <strong>30 minutos</strong>. Após este prazo os vouchers são libertados automaticamente.
          </div>
        </div>


        {{-- Confirm button --}}
        <button type="submit" class="rv-btn-confirm">
          ✅ Confirmar Pagamento — {{ number_format($total, 0, ',', '.') }} Kz
        </button>

      </form>

      {{-- Cancel --}}
      <form action="{{ route('reseller.panel.payment.cancel') }}" method="POST">
        @csrf
        <button type="submit" class="rv-btn-cancel"
                onclick="return confirm('Cancelar a compra? Os vouchers reservados serão libertados e poderá voltar a encomendá-los.')">
          ❌ Cancelar e voltar ao painel
        </button>
      </form>

    </div>
  </div>
</div>

@endsection
