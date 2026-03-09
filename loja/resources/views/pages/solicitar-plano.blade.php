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
        Após submeter este formulário, a nossa equipa recebe uma notificação, confirma o
        seu pagamento e activa o plano no sistema. Receberá uma confirmação por e-mail.
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

        {{-- Nome --}}
        <div class="checkout-form-row">
          <label for="customer_name">Nome completo *</label>
          <input type="text" id="customer_name" name="customer_name"
            value="{{ old('customer_name') }}" required
            placeholder="Ex: João Silva" autocomplete="name">
        </div>

        {{-- E-mail --}}
        <div class="checkout-form-row">
          <label for="customer_email">E-mail *</label>
          <input type="email" id="customer_email" name="customer_email"
            value="{{ old('customer_email') }}" required
            placeholder="exemplo@gmail.com" autocomplete="email">
        </div>

        {{-- Telefone --}}
        <div class="checkout-form-row">
          <label for="customer_phone">Telefone / WhatsApp *</label>
          <input type="tel" id="customer_phone" name="customer_phone"
            value="{{ old('customer_phone') }}" required
            placeholder="9XX XXX XXX" autocomplete="tel">
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

        <p class="checkout-note">* Campos obrigatórios. A equipa AngolaWiFi entrará em contacto pelo telefone/e-mail fornecidos.</p>

        <div class="checkout-actions">
          <button type="submit" class="btn-primary">Enviar Pedido de Adesão</button>
        </div>
      </form>
    </section>

  </div>
</div>
@endsection
