{{--
    CHECKOUT — PLANOS FAMILIARES & EMPRESARIAIS
    ═══════════════════════════════════════════
    Fluxo: telefone → pesquisa no SG → confirmar → pagar (GPO/EMIS).
--}}
@extends('layouts.app')

@section('title', '{{ $plan["name"] }} — Checkout AngolaWiFi')

@section('content')
@push('styles')
<style>
/* ══════════════════════════════════════════════
   CHECKOUT — PLANOS FAMILIARES / EMPRESARIAIS
   ══════════════════════════════════════════════ */
.co-page {
  min-height: 80vh;
  background: #f0f2f5;
  padding: 2.5rem 1rem 5rem;
  font-family: Inter, system-ui, -apple-system, sans-serif;
}

.co-wrap {
  max-width: 560px;
  margin: 0 auto;
}

/* Back link */
.co-back {
  display: inline-flex;
  align-items: center;
  gap: .4rem;
  font-size: .82rem;
  font-weight: 600;
  color: #64748b;
  text-decoration: none;
  margin-bottom: 1.5rem;
  transition: color .15s;
}
.co-back:hover { color: #1a202c; }
.co-back svg { flex-shrink: 0; }

/* ── Main card ── */
.co-card {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 4px 24px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
  overflow: hidden;
}

/* Plan header */
.co-plan-header {
  background: linear-gradient(135deg, #fffbea 0%, #fff8d6 100%);
  border-bottom: 1px solid rgba(247,181,0,.25);
  padding: 1.75rem 2rem 1.5rem;
  position: relative;
}
.co-plan-header::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, #f7b500, #ffd95a);
}
.co-plan-label {
  font-size: .72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: .1em;
  color: #b8860b;
  margin: 0 0 .4rem;
}
.co-plan-name {
  font-size: 1.35rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 1rem;
  letter-spacing: -.02em;
  line-height: 1.2;
}
.co-plan-price {
  display: flex;
  align-items: baseline;
  gap: .35rem;
  margin-bottom: 1.25rem;
}
.co-plan-price-val {
  font-size: 2.4rem;
  font-weight: 900;
  color: #1a202c;
  letter-spacing: -.03em;
  line-height: 1;
}
.co-plan-price-cur {
  font-size: 1rem;
  font-weight: 700;
  color: #64748b;
}
.co-plan-price-per {
  font-size: .82rem;
  color: #94a3b8;
  font-weight: 500;
}

.co-plan-pills {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  margin-bottom: 1.25rem;
}
.co-pill {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .3rem .75rem;
  background: rgba(247,181,0,.15);
  border: 1px solid rgba(247,181,0,.4);
  border-radius: 999px;
  font-size: .78rem;
  font-weight: 700;
  color: #7a4f00;
}

.co-trust-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: .75rem;
}
.co-trust-item {
  display: flex;
  align-items: center;
  gap: .35rem;
  font-size: .75rem;
  color: #64748b;
  font-weight: 500;
}
.co-trust-item svg { flex-shrink: 0; color: #16a34a; }
.co-trust-emis {
  margin-left: auto;
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: .3rem .75rem;
  display: flex;
  align-items: center;
  line-height: 1;
}

/* Form section */
.co-form-body {
  padding: 1.75rem 2rem 2rem;
}

.co-form-title {
  font-size: 1rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .3rem;
  letter-spacing: -.01em;
}
.co-form-sub {
  font-size: .82rem;
  color: #64748b;
  margin: 0 0 1.5rem;
}

/* Errors */
.co-errors {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  border-left: 4px solid #dc2626;
  border-radius: 10px;
  padding: .85rem 1rem;
  margin-bottom: 1.25rem;
  font-size: .84rem;
  color: #7f1d1d;
}
.co-errors ul { margin: .35rem 0 0 1.1rem; padding: 0; }
.co-errors li { margin: .15rem 0; }

/* Phone input group */
.co-phone-group {
  position: relative;
  margin-bottom: .5rem;
}
.co-phone-prefix {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  font-weight: 600;
  color: #94a3b8;
  pointer-events: none;
  user-select: none;
}
.co-phone-input {
  display: block;
  width: 100%;
  box-sizing: border-box;
  font-size: 1.15rem;
  font-weight: 600;
  padding: .85rem 1rem .85rem 3.2rem;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit;
  color: #1a202c;
  background: #f8fafc;
  letter-spacing: .04em;
}
.co-phone-input:focus {
  border-color: #f7b500;
  box-shadow: 0 0 0 4px rgba(247,181,0,.15);
  background: #fff;
}
.co-phone-hint {
  font-size: .76rem;
  color: #94a3b8;
  margin-bottom: 1rem;
  padding-left: .25rem;
}

/* Search button */
.co-search-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  width: 100%;
  padding: .85rem 1rem;
  background: #f7b500;
  color: #1a202c;
  border: none;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 800;
  cursor: pointer;
  transition: filter .15s, transform .12s;
  font-family: inherit;
  box-shadow: 0 2px 12px rgba(247,181,0,.35);
  letter-spacing: -.01em;
}
.co-search-btn:hover    { filter: brightness(.96); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(247,181,0,.4); }
.co-search-btn:active   { transform: translateY(0); filter: brightness(.93); }
.co-search-btn:disabled { background: #e2e8f0; color: #94a3b8; cursor: default; transform: none; box-shadow: none; filter: none; }

/* Confirm card */
.co-confirm-card {
  display: flex;
  align-items: center;
  gap: .9rem;
  padding: 1rem 1.1rem;
  background: #f0fdf4;
  border: 1.5px solid #86efac;
  border-radius: 12px;
  margin-top: 1rem;
}
.co-confirm-icon {
  width: 2.4rem;
  height: 2.4rem;
  background: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.co-confirm-icon svg { color: #fff; }
.co-confirm-name {
  font-size: .95rem;
  font-weight: 700;
  color: #14532d;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.co-confirm-sub {
  font-size: .75rem;
  color: #16a34a;
  font-weight: 500;
  margin-top: .1rem;
}
.co-change-btn {
  margin-left: auto;
  background: #fff;
  border: 1px solid #86efac;
  border-radius: 7px;
  color: #15803d;
  font-size: .75rem;
  font-weight: 600;
  padding: .3rem .7rem;
  cursor: pointer;
  white-space: nowrap;
  font-family: inherit;
  transition: background .15s;
  flex-shrink: 0;
}
.co-change-btn:hover { background: #dcfce7; }

/* Pay button */
.co-pay-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .6rem;
  width: 100%;
  margin-top: .85rem;
  padding: 1rem 1.5rem;
  background: #1a202c;
  color: #f7b500;
  border: none;
  border-radius: 12px;
  font-size: 1.05rem;
  font-weight: 800;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s, transform .12s;
  letter-spacing: -.01em;
  box-shadow: 0 2px 12px rgba(26,32,44,.2);
}
.co-pay-btn:hover  { background: #111827; transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,32,44,.28); }
.co-pay-btn:active { transform: translateY(0); }
.co-pay-btn svg { flex-shrink: 0; }

/* Banners */
.co-banner {
  border-radius: 12px;
  padding: .9rem 1.1rem;
  font-size: .84rem;
  line-height: 1.55;
  margin-top: 1rem;
}
.co-banner strong { display: block; margin-bottom: .2rem; }
.co-banner--warn {
  background: #fffbeb;
  border: 1.5px solid #fde68a;
  color: #78350f;
}
.co-banner--warn .co-banner-cta {
  display: inline-block;
  margin-top: .6rem;
  padding: .35rem .85rem;
  background: #f7b500;
  color: #1a202c;
  border-radius: 7px;
  font-size: .78rem;
  font-weight: 700;
  text-decoration: none;
  font-family: inherit;
}
.co-banner--error {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  color: #991b1b;
  display: flex;
  flex-direction: column;
  gap: .5rem;
}
.co-retry-btn {
  align-self: flex-start;
  padding: .35rem .9rem;
  background: #fff;
  color: #991b1b;
  border: 1px solid #fca5a5;
  border-radius: 7px;
  font-size: .8rem;
  font-weight: 700;
  cursor: pointer;
  font-family: inherit;
  transition: background .15s;
}
.co-retry-btn:hover { background: #fef2f2; }

/* Divider */
.co-divider {
  display: flex;
  align-items: center;
  gap: .75rem;
  margin: 1.5rem 0;
  color: #cbd5e1;
  font-size: .72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
}
.co-divider::before, .co-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: #e2e8f0;
}

/* Spinner */
@keyframes co-spin { to { transform: rotate(360deg); } }
.co-spinning { animation: co-spin .75s linear infinite; }

/* Mobile */
@media (max-width: 480px) {
  .co-page { padding: 1.25rem .75rem 4rem; }
  .co-plan-header, .co-form-body { padding-left: 1.25rem; padding-right: 1.25rem; }
  .co-plan-price-val { font-size: 2rem; }
  .co-trust-emis { display: none; }
}
</style>
@endpush

<div class="co-page">
<div class="co-wrap">

  <a href="/" class="co-back">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    Voltar à loja
  </a>

  <div class="co-card">

    {{-- Plan header ── ── ── ── ── ── ── ── ── ── --}}
    <div class="co-plan-header">
      <p class="co-plan-label">Plano selecionado</p>
      <h1 class="co-plan-name">{{ $plan['name'] }}</h1>

      @if(!empty($plan['preco']))
        <div class="co-plan-price">
          <span class="co-plan-price-val">{{ number_format((float)$plan['preco'], 0, ',', '.') }}</span>
          <span class="co-plan-price-cur">Kz</span>
          <span class="co-plan-price-per">/ mês</span>
        </div>
      @else
        <div class="co-plan-price">
          <span class="co-plan-price-val" style="font-size:1.4rem;">Sob consulta</span>
        </div>
      @endif

      <div class="co-plan-pills">
        @if(!empty($plan['ciclo']))
          <span class="co-pill">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            {{ $plan['ciclo'] }} dias
          </span>
        @endif
        <span class="co-pill">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          Activação imediata
        </span>
        <span class="co-pill">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Pagamento seguro
        </span>
      </div>

      <div class="co-trust-row">
        <span class="co-trust-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Sem fidelização
        </span>
        <span class="co-trust-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Suporte 24/7
        </span>
        <span class="co-trust-emis">
          <img src="/img/emis.png" alt="EMIS" height="26" style="display:block;">
        </span>
      </div>
    </div>

    {{-- Form ── ── ── ── ── ── ── ── ── ── ── ── --}}
    <div class="co-form-body">
      <h2 class="co-form-title">Verificar o seu número</h2>
      <p class="co-form-sub">Introduza o número com que se registou na AngolaWiFi para confirmar o seu acesso.</p>

      @if ($errors->any())
        <div class="co-errors">
          <strong>Por favor corrija os erros:</strong>
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('family.request.store') }}" id="planForm">
        @csrf
        <input type="hidden" name="plan_id"        value="{{ $plan['id'] }}">
        <input type="hidden" name="plan_name"       value="{{ $plan['name'] }}">
        <input type="hidden" name="plan_preco"      value="{{ $plan['preco'] ?? '' }}">
        <input type="hidden" name="plan_ciclo"      value="{{ $plan['ciclo'] ?? '' }}">
        <input type="hidden" name="payment_method"  value="gpo">
        <input type="hidden" id="hName"  name="customer_name"  value="{{ old('customer_name') }}">
        <input type="hidden" id="hEmail" name="customer_email" value="{{ old('customer_email') }}">
        <input type="hidden" id="hNif"   name="customer_nif"   value="{{ old('customer_nif') }}">

        {{-- Phone input --}}
        <div class="co-phone-group">
          <span class="co-phone-prefix">📱</span>
          <input type="tel" id="customer_phone" name="customer_phone"
            value="{{ old('customer_phone') }}" required
            placeholder="9XX XXX XXX" autocomplete="tel"
            class="co-phone-input">
        </div>
        <p class="co-phone-hint">Ex: 923 000 000 — mesmo número que usou para se registar</p>

        <button type="button" id="lookupBtn" class="co-search-btn">
          <svg id="lookupSpinner" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          <span id="lookupBtnText">Verificar número</span>
        </button>

        {{-- Confirmed --}}
        <div id="stepConfirm" style="display:none;">
          <div class="co-confirm-card">
            <div class="co-confirm-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div style="flex:1;min-width:0;">
              <div class="co-confirm-name" id="confirmName"></div>
              <div class="co-confirm-sub">Conta verificada com sucesso</div>
            </div>
            <button type="button" class="co-change-btn" id="changeBtn">Alterar</button>
          </div>

          <button type="submit" id="submitBtn" class="co-pay-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Pagar agora
          </button>
        </div>

        {{-- Not found --}}
        <div id="notFoundBanner" style="display:none;" class="co-banner co-banner--warn">
          <strong>Número não registado</strong>
          Os planos familiares e empresariais são exclusivos para clientes já instalados.
          <a href="/agendar-instalacao" class="co-banner-cta">Agendar instalação →</a>
        </div>

        {{-- Plan mismatch --}}
        <div id="mismatchBanner" style="display:none;" class="co-banner co-banner--error">
          <strong>Plano incompatível</strong>
          Este número está associado ao <span id="mismatchPlanName">seu plano actual</span>.
          Não é possível pagar um plano diferente do que tem contratado.
          <span style="display:block;margin-top:.25rem;font-size:.8rem;opacity:.85;">Para alterar o seu plano, contacte o suporte.</span>
        </div>

        {{-- Error --}}
        <div id="errorBanner" style="display:none;" class="co-banner co-banner--error">
          <strong>Não foi possível verificar o número</strong>
          <span id="errorDetail"></span>
          <button type="button" id="retryBtn" class="co-retry-btn">Tentar novamente</button>
        </div>

      </form>
    </div>

  </div>
</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  var phoneInput     = document.getElementById('customer_phone');
  var lookupBtn      = document.getElementById('lookupBtn');
  var lookupText     = document.getElementById('lookupBtnText');
  var lookupSpin     = document.getElementById('lookupSpinner');
  var stepConfirm    = document.getElementById('stepConfirm');
  var confirmName    = document.getElementById('confirmName');
  var changeBtn      = document.getElementById('changeBtn');
  var notFoundBanner = document.getElementById('notFoundBanner');
  var mismatchBanner = document.getElementById('mismatchBanner');
  var mismatchPlanName = document.getElementById('mismatchPlanName');
  var errorBanner    = document.getElementById('errorBanner');
  var errorDetail    = document.getElementById('errorDetail');
  var retryBtn       = document.getElementById('retryBtn');
  var hName          = document.getElementById('hName');
  var hEmail         = document.getElementById('hEmail');
  var hNif           = document.getElementById('hNif');
  var lookupUrl      = '{{ route('family.request.lookup') }}';
  var pagePlanId     = '{{ $plan["id"] }}';

  var verified = false;

  function hideAll() {
    stepConfirm.style.display    = 'none';
    notFoundBanner.style.display = 'none';
    mismatchBanner.style.display = 'none';
    errorBanner.style.display    = 'none';
    hName.value = hEmail.value = hNif.value = '';
    verified = false;
  }

  function setLoading(on) {
    lookupBtn.disabled = on;
    lookupText.style.display = on ? 'none'   : 'inline';
    lookupSpin.style.display = on ? 'inline' : 'none';
    if (on) lookupSpin.classList.add('co-spinning');
    else    lookupSpin.classList.remove('co-spinning');
  }

  function doLookup() {
    var phone = phoneInput.value.replace(/[\s\-().+]/g, '');
    if (phone.length < 9) { phoneInput.focus(); return; }

    setLoading(true);
    hideAll();

    fetch(lookupUrl + '?phone=' + encodeURIComponent(phone), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function (res) { return res.text(); })
    .then(function (rawText) {
      var data;
      try { data = JSON.parse(rawText); }
      catch (_) {
        errorDetail.textContent = 'Erro do servidor (código ' + status + ').';
        errorBanner.style.display = 'flex';
        return;
      }

      if (data.found) {
        // Verifica se o plano solicitado corresponde ao plano actual do cliente
        if (data.current_plan_id && String(data.current_plan_id) !== String(pagePlanId)) {
          var planLabel = data.current_plan_name ? '"' + data.current_plan_name + '"' : 'plano actual';
          mismatchPlanName.textContent = planLabel;
          mismatchBanner.style.display = 'block';
          return;
        }
        hName.value  = data.name  || '';
        hEmail.value = data.email || '';
        hNif.value   = data.nif   || '';
        confirmName.textContent = data.name || 'Cliente verificado';
        stepConfirm.style.display = 'block';
        verified = true;
      } else {
        notFoundBanner.style.display = 'block';
      }
    })
    .catch(function () {
      errorDetail.textContent = 'Sem ligação ao servidor. Verifique a sua rede.';
      errorBanner.style.display = 'flex';
    })
    .finally(function () { setLoading(false); });
  }

  lookupBtn.addEventListener('click', doLookup);
  retryBtn.addEventListener('click', doLookup);

  phoneInput.addEventListener('blur', function () {
    if (this.value.replace(/[\s\-().+]/g, '').length >= 9) doLookup();
  });

  phoneInput.addEventListener('input', function () {
    if (verified) hideAll();
  });

  changeBtn.addEventListener('click', function () {
    hideAll();
    phoneInput.value = '';
    phoneInput.focus();
  });

  document.getElementById('planForm').addEventListener('submit', function (e) {
    if (!verified) { e.preventDefault(); doLookup(); }
  });

  if (phoneInput.value.replace(/[\s\-().+]/g, '').length >= 9) doLookup();
})();
</script>
@endpush
