{{--
    CHECKOUT — PLANOS FAMILIARES & EMPRESARIAIS
    ═══════════════════════════════════════════
    Este formulário é EXCLUSIVO para planos familiares e empresariais carregados
    do Sistema de Gestão (SG) via /sg/plan-templates.

    NÃO confundir com store/checkout.blade.php (planos individuais):
     - Aqui: identificação obrigatória (nome, e-mail, telefone)
     - Aqui: activação feita pelo admin no SG após confirmação do pagamento
     - Checkout individual: sem dados pessoais, código WiFi imediato
--}}
@extends('layouts.app')

@section('title', 'Aderir ao Plano — AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Aderir ao Plano</h1>
  <p class="checkout-subtitle">Preencha os seus dados para solicitar a adesão ao plano familiar ou empresarial.</p>

  <div class="checkout-layout">

    {{-- Resumo do plano --}}
    <section class="checkout-summary-card">
      <h2>Plano Selecionado</h2>
      <p><span class="label">Plano:</span> {{ $plan['name'] }}</p>
      @if(!empty($plan['preco']))
        <p class="total">Valor: {{ number_format($plan['preco'], 0, ',', '.') }} AOA<span style="font-size:0.8rem;font-weight:400;">/mês</span></p>
      @else
        <p><span class="label">Valor:</span> Sob consulta</p>
      @endif
      @if(!empty($plan['ciclo']))
        <p><span class="label">Duração:</span> {{ $plan['ciclo'] }} dias</p>
      @endif

      <div style="margin-top:1rem;padding:0.75rem;background:#fffaf0;border-radius:8px;border:1px solid rgba(247,181,0,0.3);font-size:0.82rem;color:#64748b;">
        <strong style="color:#1e293b;">Como funciona?</strong><br>
        Após o pagamento, o seu plano é activado automaticamente. Não precisa de aguardar
        nenhuma confirmação manual — receberá uma notificação assim que o acesso estiver disponível.
      </div>
    </section>

    {{-- Formulário de identificação + pagamento --}}
    <section class="checkout-form-card">
      <h2>Os seus dados</h2>

      @if ($errors->any())
        <div class="checkout-errors">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('family.request.store') }}" class="checkout-form">
        @csrf

        {{-- Dados do plano (hidden) --}}
        <input type="hidden" name="plan_id"    value="{{ $plan['id'] }}">
        <input type="hidden" name="plan_name"  value="{{ $plan['name'] }}">
        <input type="hidden" name="plan_preco" value="{{ $plan['preco'] ?? '' }}">
        <input type="hidden" name="plan_ciclo" value="{{ $plan['ciclo'] ?? '' }}">

        {{-- Telefone — primeiro campo, serve também de lookup --}}
        <div class="checkout-form-row">
          <label for="customer_phone">Telefone / WhatsApp *</label>
          <div class="checkout-phone-group">
            <input type="tel" id="customer_phone" name="customer_phone"
              value="{{ old('customer_phone') }}" required
              placeholder="9XX XXX XXX" autocomplete="tel">
            <button type="button" id="lookup-btn" class="checkout-lookup-btn" title="Preencher dados automaticamente">
              Já sou cliente
            </button>
          </div>
          <small id="lookup-feedback" class="checkout-lookup-feedback"></small>
        </div>

        {{-- Nome --}}
        <div class="checkout-form-row">
          <label for="customer_name">Nome completo *</label>
          <input type="text" id="customer_name" name="customer_name"
            value="{{ old('customer_name') }}" required
            placeholder="Ex: João Silva" autocomplete="name">
        </div>

        {{-- E-mail --}}
        <div class="checkout-form-row">
          <label for="customer_email">
            E-mail
            <span style="font-weight:400;color:#94a3b8;">(opcional — para notificações)</span>
          </label>
          <input type="email" id="customer_email" name="customer_email"
            value="{{ old('customer_email') }}"
            placeholder="exemplo@gmail.com" autocomplete="email">
        </div>

        {{-- NIF (opcional) --}}
        <div class="checkout-form-row">
          <label for="customer_nif">NIF <span style="font-weight:400;color:#94a3b8;">(opcional — para emissão de fatura)</span></label>
          <input type="text" id="customer_nif" name="customer_nif"
            value="{{ old('customer_nif') }}"
            placeholder="Ex: 5417623LA041">
        </div>

        {{-- Método de pagamento --}}
        <div class="checkout-payment">
          <p class="checkout-payment-title">Método de pagamento *</p>
          <div class="checkout-payment-options">
            <label>
              <input type="radio" name="payment_method" value="multicaixa_express"
                {{ old('payment_method', 'multicaixa_express') === 'multicaixa_express' ? 'checked' : '' }}>
              <span>Multicaixa Express</span>
            </label>
            <label>
              <input type="radio" name="payment_method" value="paypal"
                {{ old('payment_method') === 'paypal' ? 'checked' : '' }}>
              <span>PayPal</span>
            </label>
          </div>
        </div>

        <p class="checkout-note">* Campos obrigatórios. Contactamos pelo telefone indicado em caso de dúvida.</p>

        <div class="checkout-actions">
          <button type="submit" class="btn-primary">Avançar para Pagamento →</button>
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
  const nameInput     = document.getElementById('customer_name');
  const emailInput    = document.getElementById('customer_email');
  const nifInput      = document.getElementById('customer_nif');
  const lookupBtn     = document.getElementById('lookup-btn');
  const feedback      = document.getElementById('lookup-feedback');
  const lookupUrl     = '{{ route('family.request.lookup') }}';

  function setFeedback(msg, type) {
    feedback.textContent = msg;
    feedback.className = 'checkout-lookup-feedback checkout-lookup-feedback--' + type;
    feedback.style.display = msg ? 'block' : 'none';
  }

  function markAutoFilled(input) {
    input.classList.add('checkout-autofilled');
  }

  async function doLookup() {
    const phone = phoneInput.value.replace(/[\s\-().]/g, '');
    if (phone.length < 7) {
      setFeedback('Introduza o número completo.', 'warn');
      return;
    }

    lookupBtn.disabled = true;
    lookupBtn.textContent = '…';
    setFeedback('', '');

    try {
      const res  = await fetch(lookupUrl + '?phone=' + encodeURIComponent(phone));
      const data = await res.json();

      if (data.found) {
        nameInput.value  = data.name  || '';
        emailInput.value = data.email || '';
        nifInput.value   = data.nif   || '';
        [nameInput, emailInput, nifInput].forEach(markAutoFilled);
        setFeedback('✓ Cliente encontrado — dados preenchidos. Confirme antes de avançar.', 'ok');
      } else {
        setFeedback('Número não encontrado — preencha os seus dados abaixo.', 'info');
      }
    } catch (e) {
      setFeedback('Não foi possível verificar. Preencha os dados manualmente.', 'warn');
    } finally {
      lookupBtn.disabled = false;
      lookupBtn.textContent = 'Já sou cliente';
    }
  }

  lookupBtn.addEventListener('click', doLookup);

  // Auto-dispara quando o campo de telefone fica com tamanho suficiente e o utilizador sai do campo
  phoneInput.addEventListener('blur', function () {
    const phone = this.value.replace(/[\s\-().]/g, '');
    if (phone.length >= 9 && !nameInput.value.trim()) {
      doLookup();
    }
  });

  // Remove marca de auto-preenchido se o utilizador editar o campo manualmente
  [nameInput, emailInput, nifInput].forEach(function (input) {
    input.addEventListener('input', function () {
      this.classList.remove('checkout-autofilled');
    });
  });
})();
</script>
@endpush

@push('styles')
<style>
.checkout-phone-group {
  display: flex;
  gap: 0.5rem;
  align-items: stretch;
}
.checkout-phone-group input {
  flex: 1;
  min-width: 0;
}
.checkout-lookup-btn {
  flex-shrink: 0;
  padding: 0 1rem;
  background: #0ea5e9;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  white-space: nowrap;
  transition: background 0.15s;
}
.checkout-lookup-btn:hover  { background: #0284c7; }
.checkout-lookup-btn:disabled { background: #94a3b8; cursor: default; }

.checkout-lookup-feedback {
  display: none;
  margin-top: 0.4rem;
  font-size: 0.8rem;
  border-radius: 5px;
  padding: 0.3rem 0.6rem;
}
.checkout-lookup-feedback--ok   { background: #f0fdf4; color: #166534; }
.checkout-lookup-feedback--info { background: #f0f9ff; color: #0369a1; }
.checkout-lookup-feedback--warn { background: #fefce8; color: #854d0e; }

.checkout-autofilled {
  border-color: #86efac !important;
  background: #f0fdf4 !important;
}
</style>
@endpush
