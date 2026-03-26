@extends('layouts.app')

@push('styles')
<style>
/* Reuse checkout page styles */
.rv-page { min-height: 80vh; background: #f8fafc; padding: 2.5rem 1rem 4rem; }
.rv-pay-wrap { max-width: 680px; margin: 0 auto; }
.rv-back-link { display: inline-flex; align-items: center; gap: .4rem; color: #64748b; font-size: .85rem; font-weight: 600; text-decoration: none; margin-bottom: 1.25rem; background: none; border: none; cursor: pointer; padding: 0; }
.rv-back-link:hover { color: #0f172a; }
.rv-pay-header { margin-bottom: 1.5rem; }
.rv-pay-header h1 { font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0 0 .2rem; letter-spacing: -.02em; }
.rv-pay-header p { color: #64748b; font-size: .9rem; margin: 0; }
.rv-panel { background: #fff; border-radius: 1rem; box-shadow: 0 2px 12px rgba(0,0,0,.06); padding: 1.5rem; margin-bottom: 1.25rem; }
.rv-panel-title { font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 1.1rem; display: flex; align-items: center; gap: .5rem; }
.rv-total-bar { display: flex; justify-content: space-between; align-items: center; background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%); color: #fff; border-radius: .85rem; padding: 1rem 1.5rem; margin-bottom: 1.25rem; }
.rv-total-bar .lbl { font-size: .8rem; font-weight: 600; opacity: .65; }
.rv-total-bar .amount { font-size: 1.6rem; font-weight: 900; letter-spacing: -.02em; color: #fca5a5; }
.rv-total-bar .sub { font-size: .82rem; color: #fca5a5; margin-top: .1rem; }
.rv-methods-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .85rem; margin-bottom: 1.25rem; }
.rv-method-card { border: 2px solid #e2e8f0; border-radius: .85rem; padding: 1.15rem 1.25rem; cursor: pointer; transition: border-color .18s, background .18s; user-select: none; }
.rv-method-card:hover { border-color: #f7b500; }
.rv-method-card.active { border-color: #dc2626; background: #fef2f2; }
.rv-method-card input[type=radio] { display: none; }
.rv-method-icon { font-size: 1.6rem; margin-bottom: .45rem; }
.rv-method-name { font-size: .92rem; font-weight: 800; color: #0f172a; margin-bottom: .15rem; }
.rv-method-desc { font-size: .8rem; color: #64748b; line-height: 1.4; }
.rv-detail-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .85rem; padding: 1.25rem 1.4rem; margin-bottom: 1.1rem; }
.rv-detail-box.hidden { display: none; }
.rv-detail-row { display: flex; justify-content: space-between; align-items: baseline; padding: .45rem 0; border-bottom: 1px solid #f1f5f9; font-size: .9rem; }
.rv-detail-row:last-child { border-bottom: none; }
.rv-detail-label { color: #64748b; font-size: .8rem; font-weight: 600; }
.rv-detail-value { font-weight: 800; color: #0f172a; font-family: monospace; font-size: 1rem; letter-spacing: .04em; }
.rv-detail-value.big { font-size: 1.25rem; letter-spacing: .06em; }
.rv-detail-note { margin-top: .85rem; font-size: .8rem; color: #78716c; background: #fff7ed; border-radius: .55rem; padding: .6rem .85rem; line-height: 1.5; border: 1px solid #fed7aa; }
.rv-btn-confirm { display: flex; align-items: center; justify-content: center; gap: .5rem; width: 100%; padding: .95rem 1.5rem; background: #dc2626; color: #fff; border: none; border-radius: .75rem; font-size: 1.05rem; font-weight: 800; cursor: pointer; transition: background .2s; margin-bottom: .65rem; font-family: inherit; }
.rv-btn-confirm:hover { background: #b91c1c; }
.rv-btn-back { display: flex; align-items: center; justify-content: center; gap: .4rem; width: 100%; padding: .75rem 1.5rem; background: transparent; color: #64748b; border: 2px solid #e2e8f0; border-radius: .75rem; font-size: .9rem; font-weight: 700; cursor: pointer; font-family: inherit; text-decoration: none; transition: background .18s; }
.rv-btn-back:hover { background: #f1f5f9; }
.rv-sim-notice { background: #fef9c3; border: 1px solid #fde047; border-left: 4px solid #eab308; border-radius: .6rem; padding: .75rem 1rem; font-size: .83rem; color: #713f12; margin-bottom: 1.1rem; line-height: 1.5; }
@media (max-width:640px) {
  .rv-total-bar { flex-direction: column; text-align: center; gap: .75rem; padding: 1rem; }
  .rv-methods-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="rv-page">
<div class="rv-pay-wrap">

  <a href="{{ route('reseller.panel') }}" class="rv-back-link">← Voltar ao painel</a>

  <div class="rv-pay-header">
    <h1>🔔 Taxa de Manutenção Mensal</h1>
    <p>Regularize a sua taxa de manutenção para continuar a utilizar o painel de revendedor sem restrições.</p>
  </div>

  {{-- Flash --}}
  @if(session('error'))
    <div style="background:#fef2f2;border:1.5px solid #fecaca;border-left:4px solid #dc2626;color:#7f1d1d;padding:.75rem 1rem;border-radius:.65rem;font-size:.875rem;margin-bottom:1rem;">
      <strong>Erro:</strong> {{ session('error') }}
    </div>
  @endif

  {{-- Total bar --}}
  <div class="rv-total-bar">
    <div>
      <div class="lbl">Revendedor</div>
      <div style="font-size:1rem;font-weight:700;color:#fff;">{{ $application->full_name }}</div>
    </div>
    <div style="text-align:right;">
      <div class="lbl">Valor a pagar</div>
      <div class="amount">{{ number_format($amount, 0, ',', '.') }} Kz</div>
      <div class="sub">Taxa de manutenção {{ now()->year }}</div>
    </div>
  </div>

  {{-- Info sobre a taxa --}}
  <div class="rv-panel">
    <div class="rv-panel-title">ℹ️ Detalhes da taxa</div>
    <div class="rv-detail-row">
      <span class="rv-detail-label">Tipo de taxa</span>
      <span class="rv-detail-value" style="font-family:inherit;font-size:.9rem;">
        Manutenção anual — revendedor
        {{ $application->reseller_mode === 'own' ? 'com internet própria' : 'AngolaWiFi' }}
      </span>
    </div>
    <div class="rv-detail-row">
      <span class="rv-detail-label">Ano de referência</span>
      <span class="rv-detail-value" style="font-family:inherit;">{{ now()->year }}</span>
    </div>
    <div class="rv-detail-row">
      <span class="rv-detail-label">Montante</span>
      <span class="rv-detail-value" style="font-family:inherit;color:#dc2626;">{{ number_format($amount, 0, ',', '.') }} Kz</span>
    </div>
  </div>

  {{-- Payment form --}}
  <div class="rv-panel">
    <div class="rv-panel-title">💳 Meio de pagamento</div>

    <form id="maintForm" action="{{ route('reseller.maintenance.payment.confirm') }}" method="POST">
      @csrf
      <input type="hidden" name="payment_method"    id="inputMethod"    value="multicaixa">
      <input type="hidden" name="payment_reference" id="inputReference" value="{{ $mcxRef }}">
      <input type="hidden" name="payment_token"     value="{{ $token }}">

      <div class="rv-methods-grid">
        <label class="rv-method-card active" id="card-mcx"
               onclick="selectMethod('multicaixa', '{{ $mcxRef }}', 'mcx')">
          <input type="radio" name="_ui_method" value="multicaixa" checked>
          <div class="rv-method-icon">🏧</div>
          <div class="rv-method-name">Multicaixa / ATM</div>
          <div class="rv-method-desc">Pague em qualquer caixa ATM com a referência gerada.</div>
        </label>

        <label class="rv-method-card" id="card-bank"
               onclick="selectMethod('transferencia', 'TRF-MAINT-{{ now()->year }}-{{ $application->id }}', 'bank')">
          <input type="radio" name="_ui_method" value="transferencia">
          <div class="rv-method-icon">🏦</div>
          <div class="rv-method-name">Transferência Bancária</div>
          <div class="rv-method-desc">Transfira para a conta da AngolaWiFi e envie o comprovativo.</div>
        </label>

        <label class="rv-method-card" id="card-mobile"
               onclick="selectMethod('multicaixa_express', 'MCX-MAINT-{{ now()->year }}-{{ $application->id }}', 'mobile')">
          <input type="radio" name="_ui_method" value="multicaixa_express">
          <div class="rv-method-icon">📱</div>
          <div class="rv-method-name">Multicaixa Express</div>
          <div class="rv-method-desc">Pague pelo telemóvel via mPay.</div>
        </label>
      </div>

      {{-- Multicaixa reference --}}
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
          <span class="rv-detail-value">{{ number_format($amount, 0, ',', '.') }} Kz</span>
        </div>
        <div class="rv-detail-note">
          Esta referência é válida para a taxa de manutenção de {{ now()->year }} do revendedor <strong>{{ $application->full_name }}</strong>.
        </div>
      </div>

      {{-- Bank transfer --}}
      <div class="rv-detail-box hidden" id="detail-bank">
        <div class="rv-detail-row">
          <span class="rv-detail-label">Banco</span>
          <span class="rv-detail-value">Banco BIC</span>
        </div>
        <div class="rv-detail-row">
          <span class="rv-detail-label">Titular</span>
          <span class="rv-detail-value">AngolaWiFi, Lda.</span>
        </div>
        <div class="rv-detail-row">
          <span class="rv-detail-label">IBAN</span>
          <span class="rv-detail-value" style="font-size:.88rem;">AO06 0040 0000 0000 0000 0001 5</span>
        </div>
        <div class="rv-detail-row">
          <span class="rv-detail-label">Montante</span>
          <span class="rv-detail-value">{{ number_format($amount, 0, ',', '.') }} Kz</span>
        </div>
        <div class="rv-detail-note">
          📎 Envie o comprovativo para <strong>financeiro@angolawifi.ao</strong> com o assunto
          <em>"Taxa Manutenção {{ now()->year }} — {{ $application->full_name }}"</em>.
        </div>
      </div>

      {{-- Multicaixa Express --}}
      <div class="rv-detail-box hidden" id="detail-mobile">
        <div class="rv-detail-row">
          <span class="rv-detail-label">Número a debitar</span>
          <span class="rv-detail-value">{{ $application->phone ?? '9XX XXX XXX' }}</span>
        </div>
        <div class="rv-detail-row">
          <span class="rv-detail-label">Montante</span>
          <span class="rv-detail-value">{{ number_format($amount, 0, ',', '.') }} Kz</span>
        </div>
        <div class="rv-detail-note">
          📲 Após confirmar, receberá uma notificação no telemóvel para autorizar o débito.
        </div>
      </div>


      <button type="submit" class="rv-btn-confirm"
              onclick="return confirm('Confirmar o pagamento de {{ number_format($amount, 0, ',', '.') }} Kz referente à taxa de manutenção de {{ now()->year }}?')">
        ✅ Confirmar Pagamento — {{ number_format($amount, 0, ',', '.') }} Kz
      </button>

    </form>

    <a href="{{ route('reseller.panel') }}" class="rv-btn-back">← Voltar ao painel sem pagar</a>
  </div>

</div>
</div>

<script>
const allCards   = ['card-mcx','card-bank','card-mobile'];
const allDetails = ['detail-mcx','detail-bank','detail-mobile'];

function selectMethod(method, ref, key) {
  document.getElementById('inputMethod').value    = method;
  document.getElementById('inputReference').value = ref;

  allCards.forEach(id => document.getElementById(id)?.classList.remove('active'));
  allDetails.forEach(id => document.getElementById(id)?.classList.add('hidden'));

  document.getElementById('card-' + key)?.classList.add('active');
  document.getElementById('detail-' + key)?.classList.remove('hidden');
}
</script>
@endsection
