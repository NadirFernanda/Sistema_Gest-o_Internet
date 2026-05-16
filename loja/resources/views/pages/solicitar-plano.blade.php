{{--
    CHECKOUT — PLANOS FAMILIARES & EMPRESARIAIS
    ═══════════════════════════════════════════
    Fluxo simplificado: telefone → pesquisa no SG → confirmar → pagar (GPO/EMIS).
    O cliente apenas digita o telefone; os dados são preenchidos automaticamente.
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
      <p><span class="label">Plano:</span> {{ $plan['name'] }}</p>
      @if(!empty($plan['preco']))
        <p class="total">{{ number_format($plan['preco'], 0, ',', '.') }} Kz<span style="font-size:0.8rem;font-weight:400;">/mês</span></p>
      @else
        <p><span class="label">Valor:</span> Sob consulta</p>
      @endif
      @if(!empty($plan['ciclo']))
        <p><span class="label">Duração:</span> {{ $plan['ciclo'] }} dias</p>
      @endif

      <div style="margin-top:1rem;padding:0.75rem;background:#f0fdf4;border-radius:8px;border:1px solid #bbf7d0;font-size:0.82rem;color:#166534;">
        ✅ <strong>Activação automática</strong><br>
        Após o pagamento o seu plano é activado de imediato — sem esperas.
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

        {{-- Dados do plano (hidden) --}}
        <input type="hidden" name="plan_id"         value="{{ $plan['id'] }}">
        <input type="hidden" name="plan_name"        value="{{ $plan['name'] }}">
        <input type="hidden" name="plan_preco"       value="{{ $plan['preco'] ?? '' }}">
        <input type="hidden" name="plan_ciclo"       value="{{ $plan['ciclo'] ?? '' }}">
        <input type="hidden" name="payment_method"   value="gpo">

        {{-- Dados do cliente (preenchidos pelo JS após lookup) --}}
        <input type="hidden" id="hCustomerName"  name="customer_name"  value="{{ old('customer_name') }}">
        <input type="hidden" id="hCustomerEmail" name="customer_email" value="{{ old('customer_email') }}">
        <input type="hidden" id="hCustomerNif"   name="customer_nif"   value="{{ old('customer_nif') }}">

        {{-- ── PASSO 1: Telefone ── --}}
        <div id="stepPhone">
          <label class="sp-label" for="customer_phone">O seu número de telefone</label>
          <div class="sp-phone-row">
            <input type="tel" id="customer_phone" name="customer_phone"
              value="{{ old('customer_phone') }}" required
              placeholder="9XX XXX XXX" autocomplete="tel"
              class="sp-phone-input">
            <button type="button" id="lookupBtn" class="sp-search-btn">
              <span id="lookupBtnText">Pesquisar</span>
              <svg id="lookupSpinner" style="display:none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
            </button>
          </div>
          <p class="sp-hint">Introduza o número registado na AngolaWiFi</p>
        </div>

        {{-- ── PASSO 2: Confirmação (oculto até ao lookup) ── --}}
        <div id="stepConfirm" style="display:none; margin-top:1.25rem;">
          <div class="sp-confirm-card">
            <div class="sp-confirm-check">✓</div>
            <div>
              <div class="sp-confirm-name" id="confirmName"></div>
              <div class="sp-confirm-sub">Número verificado — pronto para pagar</div>
            </div>
            <button type="button" class="sp-change-btn" id="changeBtn">Alterar</button>
          </div>

          <div style="margin-top:1.25rem; padding:0.75rem 1rem; background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:8px; font-size:0.85rem; color:#0369a1;">
            💳 Pagamento seguro via <strong>EMIS · GPO</strong>
          </div>

          <button type="submit" id="submitBtn" class="btn-primary" style="width:100%; margin-top:1.25rem; font-size:1.05rem; padding:0.85rem;">
            Pagar Agora →
          </button>
        </div>

        {{-- Banner: cliente não encontrado --}}
        <div id="notFoundBanner" style="display:none; margin-top:1rem; padding:0.85rem 1rem; background:#fff7ed; border:1.5px solid #fed7aa; border-radius:8px; font-size:0.85rem; color:#9a3412;">
          <strong>Número não registado.</strong><br>
          Os planos familiares e empresariais são exclusivos para clientes já instalados.
          Contacte-nos para agendar a sua instalação — depois pode gerir o plano aqui.
        </div>

      </form>
    </section>

  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const phoneInput    = document.getElementById('customer_phone');
  const lookupBtn     = document.getElementById('lookupBtn');
  const lookupText    = document.getElementById('lookupBtnText');
  const lookupSpinner = document.getElementById('lookupSpinner');
  const stepPhone     = document.getElementById('stepPhone');
  const stepConfirm   = document.getElementById('stepConfirm');
  const confirmName   = document.getElementById('confirmName');
  const changeBtn     = document.getElementById('changeBtn');
  const notFoundBanner = document.getElementById('notFoundBanner');
  const hName         = document.getElementById('hCustomerName');
  const hEmail        = document.getElementById('hCustomerEmail');
  const hNif          = document.getElementById('hCustomerNif');
  const lookupUrl     = '{{ route('family.request.lookup') }}';

  let verified = false;

  function setLoading(on) {
    lookupBtn.disabled = on;
    lookupText.style.display  = on ? 'none' : 'inline';
    lookupSpinner.style.display = on ? 'inline' : 'none';
    if (on) lookupSpinner.classList.add('sp-spin');
  }

  function showConfirm(name) {
    confirmName.textContent = name;
    stepConfirm.style.display  = 'block';
    notFoundBanner.style.display = 'none';
    verified = true;
  }

  function showNotFound() {
    stepConfirm.style.display  = 'none';
    notFoundBanner.style.display = 'block';
    hName.value = hEmail.value = hNif.value = '';
    verified = false;
  }

  function resetStep() {
    stepConfirm.style.display    = 'none';
    notFoundBanner.style.display = 'none';
    hName.value = hEmail.value = hNif.value = '';
    verified = false;
  }

  async function doLookup() {
    const phone = phoneInput.value.replace(/[\s\-().+]/g, '');
    if (phone.length < 9) {
      phoneInput.focus();
      return;
    }

    setLoading(true);
    resetStep();

    try {
      const res  = await fetch(lookupUrl + '?phone=' + encodeURIComponent(phone), {
        headers: { 'Accept': 'application/json' }
      });
      const data = await res.json();

      if (data.found) {
        hName.value  = data.name  || '';
        hEmail.value = data.email || '';
        hNif.value   = data.nif   || '';
        showConfirm(data.name || 'Cliente verificado');
      } else {
        showNotFound();
      }
    } catch (e) {
      // Falha de rede — deixa avançar, servidor valida
      notFoundBanner.innerHTML = '<strong>Não foi possível verificar.</strong> Verifique a ligação e tente novamente.';
      notFoundBanner.style.background = '#fefce8';
      notFoundBanner.style.borderColor = '#fde68a';
      notFoundBanner.style.color = '#854d0e';
      notFoundBanner.style.display = 'block';
    } finally {
      setLoading(false);
    }
  }

  lookupBtn.addEventListener('click', doLookup);

  // Auto-pesquisa quando o utilizador sai do campo com número completo
  phoneInput.addEventListener('blur', function () {
    const phone = this.value.replace(/[\s\-().+]/g, '');
    if (phone.length >= 9) doLookup();
  });

  // Reset ao editar o número
  phoneInput.addEventListener('input', function () {
    if (verified) resetStep();
  });

  // "Alterar" — volta ao campo de telefone
  changeBtn.addEventListener('click', function () {
    resetStep();
    phoneInput.value = '';
    phoneInput.focus();
  });

  // Guarda do form
  document.getElementById('planForm').addEventListener('submit', function (e) {
    if (!verified) {
      e.preventDefault();
      if (!hName.value) doLookup();
    }
  });

  // Se o telefone já veio preenchido (old() após erro), pesquisa automaticamente
  if (phoneInput.value.replace(/[\s\-().+]/g, '').length >= 9) {
    doLookup();
  }
})();
</script>
@endpush

@push('styles')
<style>
.sp-label {
  display: block;
  font-weight: 600;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
  color: #1e293b;
}
.sp-phone-row {
  display: flex;
  gap: 0.5rem;
}
.sp-phone-input {
  flex: 1;
  font-size: 1.15rem;
  padding: 0.75rem 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  outline: none;
  transition: border-color 0.15s;
}
.sp-phone-input:focus { border-color: #0ea5e9; }
.sp-search-btn {
  flex-shrink: 0;
  padding: 0 1.25rem;
  background: #0ea5e9;
  color: #fff;
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.4rem;
  transition: background 0.15s;
  white-space: nowrap;
}
.sp-search-btn:hover    { background: #0284c7; }
.sp-search-btn:disabled { background: #94a3b8; cursor: default; }
.sp-hint {
  margin-top: 0.4rem;
  font-size: 0.78rem;
  color: #94a3b8;
}
.sp-confirm-card {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.85rem 1rem;
  background: #f0fdf4;
  border: 1.5px solid #86efac;
  border-radius: 10px;
}
.sp-confirm-check {
  width: 2.2rem;
  height: 2.2rem;
  background: #16a34a;
  color: #fff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  font-weight: 700;
  flex-shrink: 0;
}
.sp-confirm-name {
  font-size: 1rem;
  font-weight: 700;
  color: #166534;
}
.sp-confirm-sub {
  font-size: 0.78rem;
  color: #4ade80;
  color: #15803d;
}
.sp-change-btn {
  margin-left: auto;
  background: none;
  border: 1px solid #86efac;
  border-radius: 6px;
  color: #166534;
  font-size: 0.78rem;
  padding: 0.25rem 0.65rem;
  cursor: pointer;
  white-space: nowrap;
}
.sp-change-btn:hover { background: #dcfce7; }
@keyframes sp-spin { to { transform: rotate(360deg); } }
.sp-spin { animation: sp-spin 0.8s linear infinite; }

@media (max-width: 480px) {
  .sp-phone-row { flex-direction: column; }
  .sp-search-btn { padding: 0.7rem 1rem; justify-content: center; }
}
</style>
@endpush
