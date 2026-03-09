{{--
    CONFIRMAÇÃO — PEDIDO DE PLANO FAMILIAR / EMPRESARIAL
    ═════════════════════════════════════════════════════
    Mostrada após FamilyPlanRequestController::store() com sucesso.
    Variável disponível: $familyRequest (instância de FamilyPlanRequest)
--}}
@extends('layouts.app')

@section('title', 'Pedido Registado — AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <div class="solicitar-confirmacao">

    @if($sgActivated)
      <div class="solicitar-confirmacao-icon">✅</div>
      <h1 class="solicitar-confirmacao-titulo">Plano Activado!</h1>
      <p class="solicitar-confirmacao-subtitulo">
        O seu plano <strong>{{ $familyRequest->plan_name }}</strong> foi activado com sucesso.
        Efectue o pagamento para garantir a continuidade do serviço.
      </p>
    @else
      <div class="solicitar-confirmacao-icon">📋</div>
      <h1 class="solicitar-confirmacao-titulo">Pedido Registado!</h1>
      <p class="solicitar-confirmacao-subtitulo">
        Recebemos o seu pedido para o plano <strong>{{ $familyRequest->plan_name }}</strong>.
        A nossa equipa irá contactá-lo(a) em breve para activar o acesso.
      </p>
    @endif

    <div class="solicitar-confirmacao-card">
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Referência</span>
        <span class="solicitar-confirmacao-value">#{{ $familyRequest->id }}</span>
      </div>
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Plano</span>
        <span class="solicitar-confirmacao-value">{{ $familyRequest->plan_name }}</span>
      </div>
      @if($familyRequest->plan_preco)
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Valor</span>
        <span class="solicitar-confirmacao-value">{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} AOA/mês</span>
      </div>
      @endif
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Nome</span>
        <span class="solicitar-confirmacao-value">{{ $familyRequest->customer_name }}</span>
      </div>
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Contacto</span>
        <span class="solicitar-confirmacao-value">{{ $familyRequest->customer_phone }}</span>
      </div>
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">E-mail</span>
        <span class="solicitar-confirmacao-value">{{ $familyRequest->customer_email }}</span>
      </div>
      <div class="solicitar-confirmacao-row">
        <span class="solicitar-confirmacao-label">Pagamento</span>
        <span class="solicitar-confirmacao-value">
          {{ $familyRequest->payment_method === 'multicaixa_express' ? 'Multicaixa Express' : 'PayPal' }}
        </span>
      </div>
    </div>

    <div class="solicitar-confirmacao-info">
      @if($sgActivated)
        <p>
          O seu acesso está <strong>activo</strong>. Foi enviado um e-mail de confirmação para
          <strong>{{ $familyRequest->customer_email }}</strong>.<br>
          Por favor efectue o pagamento via <strong>{{ $familyRequest->payment_method === 'multicaixa_express' ? 'Multicaixa Express' : 'PayPal' }}</strong>
          para garantir a continuidade do serviço.
        </p>
      @else
        <p>
          A nossa equipa irá <strong>contactá-lo(a) em breve</strong> pelo telefone
          <strong>{{ $familyRequest->customer_phone }}</strong> ou pelo e-mail
          <strong>{{ $familyRequest->customer_email }}</strong> para activar o acesso.
        </p>
      @endif
    </div>

    <a href="/" class="btn-primary" style="display:inline-block;margin-top:2rem;text-decoration:none;">
      Voltar à página inicial
    </a>

  </div>
</div>
@endsection
