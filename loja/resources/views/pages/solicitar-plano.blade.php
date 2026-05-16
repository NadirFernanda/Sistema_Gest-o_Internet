{{--
    CHECKOUT — PLANOS FAMILIARES & EMPRESARIAIS
    ═══════════════════════════════════════════
    Fluxo: telefone → pesquisa no SG → confirmar → pagar (GPO/EMIS).
--}}
@extends('layouts.app')

@section('title', 'Aderir ao Plano — AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Aderir ao Plano</h1>

  <div class="checkout-layout">

    {{-- Resumo do plano --}}
    <section class="checkout-summary-card">
      <h2>Plano Selecionado</h2>
      <p><span class="label">{{ $plan['name'] }}</span></p>
      @if(!empty($plan['preco']))
        <p class="total">{{ number_format($plan['preco'], 0, ',', '.') }} Kz<span style="font-size:0.8rem;font-weight:400;"> / mês</span></p>
      @else
        <p><span class="label">Valor:</span> Sob consulta</p>
      @endif
      @if(!empty($plan['ciclo']))
        <p><span class="label">Duração:</span> {{ $plan['ciclo'] }} dias</p>
      @endif

      <div class="sp-info-box">
        <strong>Activação automática</strong><br>
        Após o pagamento o seu plano é activado de imediato — sem esperas.
      </div>

      <div class="sp-emis-badge">
        💳 Pagamento seguro via <strong>EMIS · GPO</strong>
      </div>
    </section>

    {{-- Formulário --}}
    <section class="checkout-form-card">

      @if ($errors->any())
        <div class="checkout-errors">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('family.request.store') }}" id="planForm">
        @csrf
        <input type="hidden" name="plan_id"       value="{{ $plan['id'] }}">
        <input type="hidden" name="plan_name"      value="{{ $plan['name'] }}">
        <input type="hidden" name="plan_preco"     value="{{ $plan['preco'] ?? '' }}">
        <input type="hidden" name="plan_ciclo"     value="{{ $plan['ciclo'] ?? '' }}">
        <input type="hidden" name="payment_method" value="gpo">
        <input type="hidden" id="hName"  name="customer_name"  value="{{ old('customer_name') }}">
        <input type="hidden" id="hEmail" name="customer_email" value="{{ old('customer_email') }}">
        <input type="hidden" id="hNif"   name="customer_nif"   value="{{ old('customer_nif') }}">

        {{-- Passo 1: Telefone --}}
        <div id="stepPhone">
          <label class="sp-label" for="customer_phone">O seu número de telefone</label>
          <input type="tel" id="customer_phone" name="customer_phone"
            value="{{ old('customer_phone') }}" required
            placeholder="9XX XXX XXX" autocomplete="tel"
            class="sp-phone-input">
          <button type="button" id="lookupBtn" class="sp-search-btn">
            <span id="lookupBtnText">Pesquisar</span>
            <svg id="lookupSpinner" style="display:none;flex-shrink:0" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          </button>
          <p class="sp-hint">Introduza o número com que se registou na AngolaWiFi</p>
        </div>

        {{-- Passo 2: Confirmação (oculto até lookup com sucesso) --}}
        <div id="stepConfirm" style="display:none; margin-top:1.25rem;">
          <div class="sp-confirm-card">
            <div class="sp-confirm-check">✓</div>
            <div style="flex:1; min-width:0;">
              <div class="sp-confirm-name" id="confirmName"></div>
              <div class="sp-confirm-sub">Número verificado — pronto para pagar</div>
            </div>
            <button type="button" class="sp-change-btn" id="changeBtn">Alterar</button>
          </div>

          <button type="submit" id="submitBtn" class="btn-primary sp-pay-btn">
            Pagar Agora →
          </button>
        </div>

        {{-- Número não registado --}}
        <div id="notFoundBanner" style="display:none; margin-top:1rem;" class="sp-banner sp-banner--warn">
          <strong>Número não registado.</strong><br>
          Os planos familiares e empresariais são exclusivos para clientes já instalados.
          Contacte-nos para agendar a sua instalação.
        </div>

        {{-- Erro de rede/servidor --}}
        <div id="errorBanner" style="display:none; margin-top:1rem;" class="sp-banner sp-banner--error">
          <strong>Não foi possível verificar o número.</strong><br>
          <span id="errorDetail"></span>
          <button type="button" id="retryBtn" class="sp-retry-btn" style="margin-top:0.5rem;">Tentar novamente</button>
        </div>

      </form>
    </section>

  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const phoneInput   = document.getElementById('customer_phone');
  const lookupBtn    = document.getElementById('lookupBtn');
  const lookupText   = document.getElementById('lookupBtnText');
  const lookupSpin   = document.getElementById('lookupSpinner');
  const stepConfirm  = document.getElementById('stepConfirm');
  const confirmName  = document.getElementById('confirmName');
  const changeBtn    = document.getElementById('changeBtn');
  const notFoundBanner = document.getElementById('notFoundBanner');
  const errorBanner  = document.getElementById('errorBanner');
  const errorDetail  = document.getElementById('errorDetail');
  const retryBtn     = document.getElementById('retryBtn');
  const hName        = document.getElementById('hName');
  const hEmail       = document.getElementById('hEmail');
  const hNif         = document.getElementById('hNif');
  const lookupUrl    = '{{ route('family.request.lookup') }}';

  let verified = false;

  function hideAll() {
    stepConfirm.style.display    = 'none';
    notFoundBanner.style.display = 'none';
    errorBanner.style.display    = 'none';
    hName.value = hEmail.value = hNif.value = '';
    verified = false;
  }

  function setLoading(on) {
    lookupBtn.disabled = on;
    lookupText.style.display = on ? 'none'   : 'inline';
    lookupSpin.style.display = on ? 'inline' : 'none';
    if (on) lookupSpin.classList.add('sp-spin');
    else    lookupSpin.classList.remove('sp-spin');
  }

  async function doLookup() {
    const phone = phoneInput.value.replace(/[\s\-().+]/g, '');
    if (phone.length < 9) { phoneInput.focus(); return; }

    setLoading(true);
    hideAll();

    let rawText = '';
    try {
      const res = await fetch(lookupUrl + '?phone=' + encodeURIComponent(phone), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      });

      rawText = await res.text();

      // Tenta fazer parse do JSON — se falhar, o servidor devolveu HTML (erro 500, etc.)
      let data;
      try {
        data = JSON.parse(rawText);
      } catch (_) {
        console.error('[AngolaWiFi lookup] resposta não-JSON (código ' + res.status + '):\n', rawText);
        errorDetail.textContent = 'Erro do servidor (código ' + res.status + '). Resposta: ' + rawText.substring(0, 120);
        errorBanner.style.display = 'block';
        return;
      }

      if (data.found) {
        hName.value  = data.name  || '';
        hEmail.value = data.email || '';
        hNif.value   = data.nif   || '';
        confirmName.textContent = data.name || 'Cliente verificado';
        stepConfirm.style.display = 'block';
        verified = true;
      } else {
        notFoundBanner.style.display = 'block';
      }

    } catch (netErr) {
      errorDetail.textContent = 'Sem ligação ao servidor. Verifique a sua rede.';
      errorBanner.style.display = 'block';
    } finally {
      setLoading(false);
    }
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

  // Se voltou com erros do servidor e telefone já preenchido, repesquisa
  if (phoneInput.value.replace(/[\s\-().+]/g, '').length >= 9) doLookup();
})();
</script>
@endpush

@push('styles')
<style>
/* ── Campo de telefone ── */
.sp-label {
  display: block;
  font-weight: 700;
  font-size: 1rem;
  margin-bottom: 0.6rem;
  color: var(--text-dark);
}
.sp-phone-input {
  display: block;
  width: 100%;
  box-sizing: border-box;
  font-size: 1.15rem;
  padding: 0.8rem 1rem;
  border: 2px solid var(--muted-border);
  border-radius: 10px;
  outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: var(--font-sans);
  color: var(--text-dark);
}
.sp-phone-input:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px var(--brand-glow);
}
.sp-search-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  width: 100%;
  margin-top: 0.6rem;
  padding: 0.75rem 1rem;
  background: var(--brand);
  color: #111827;
  border: none;
  border-radius: 10px;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .15s, transform .15s;
  font-family: var(--font-sans);
  box-shadow: var(--shadow-brand);
}
.sp-search-btn:hover    { background: var(--brand-dark); transform: translateY(-1px); }
.sp-search-btn:disabled { background: #cbd5e1; color: #64748b; cursor: default; transform: none; box-shadow: none; }
.sp-hint {
  margin-top: 0.45rem;
  font-size: 0.78rem;
  color: var(--text-light);
}

/* ── Card de confirmação ── */
.sp-confirm-card {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.9rem 1rem;
  background: rgba(247,181,0,0.08);
  border: 1.5px solid var(--brand);
  border-radius: 10px;
}
.sp-confirm-check {
  width: 2.2rem;
  height: 2.2rem;
  background: var(--brand);
  color: #111827;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  font-weight: 900;
  flex-shrink: 0;
}
.sp-confirm-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-dark);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.sp-confirm-sub {
  font-size: 0.78rem;
  color: var(--brand-dark);
  font-weight: 500;
}
.sp-change-btn {
  margin-left: auto;
  background: none;
  border: 1px solid var(--brand);
  border-radius: 6px;
  color: var(--brand-dark);
  font-size: 0.78rem;
  font-weight: 600;
  padding: 0.28rem 0.65rem;
  cursor: pointer;
  white-space: nowrap;
  font-family: var(--font-sans);
  transition: background .15s;
}
.sp-change-btn:hover { background: rgba(247,181,0,0.12); }

/* ── Botão Pagar ── */
.sp-pay-btn {
  width: 100%;
  margin-top: 1.1rem;
  font-size: 1.05rem;
  padding: 0.9rem 1.5rem;
}

/* ── Banners ── */
.sp-banner {
  padding: 0.85rem 1rem;
  border-radius: 10px;
  font-size: 0.85rem;
  line-height: 1.5;
}
.sp-banner--warn {
  background: #fffbeb;
  border: 1.5px solid #fcd34d;
  color: #78350f;
}
.sp-banner--error {
  background: #fef2f2;
  border: 1.5px solid #fca5a5;
  color: #991b1b;
  display: flex;
  flex-direction: column;
}
.sp-retry-btn {
  align-self: flex-start;
  padding: 0.35rem 0.85rem;
  background: var(--brand);
  color: #111827;
  border: none;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  font-family: var(--font-sans);
  transition: background .15s;
}
.sp-retry-btn:hover { background: var(--brand-dark); }

/* ── Info boxes no resumo ── */
.sp-info-box {
  margin-top: 1rem;
  padding: 0.75rem 1rem;
  background: rgba(247,181,0,0.08);
  border-radius: 8px;
  border: 1px solid rgba(247,181,0,0.3);
  font-size: 0.82rem;
  color: var(--text-muted);
  line-height: 1.55;
}
.sp-info-box strong { color: var(--text-dark); }
.sp-emis-badge {
  margin-top: 0.75rem;
  padding: 0.6rem 0.85rem;
  background: var(--surface-2);
  border: 1px solid var(--muted-border);
  border-radius: 8px;
  font-size: 0.82rem;
  color: var(--text-muted);
}

/* ── Spinner ── */
@keyframes sp-spin { to { transform: rotate(360deg); } }
.sp-spin { animation: sp-spin 0.75s linear infinite; }

</style>
@endpush
