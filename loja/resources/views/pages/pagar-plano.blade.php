{{--
    PAGAMENTO — PLANOS FAMILIARES & EMPRESARIAIS
    ═════════════════════════════════════════════
    Mostrada após FamilyPlanRequestController::store().
    Mostra instruções de pagamento (awaiting_payment) ou sucesso (activated / pending).
    Variável: $familyRequest (instância de FamilyPlanRequest)
--}}
@extends('layouts.app')

@php
    $isActivated = $familyRequest->status === \App\Models\FamilyPlanRequest::STATUS_ACTIVATED;
    $isPending   = $familyRequest->status === \App\Models\FamilyPlanRequest::STATUS_PENDING;
    $isAwaiting  = $familyRequest->status === \App\Models\FamilyPlanRequest::STATUS_AWAITING_PAYMENT;
    $isCancelled = $familyRequest->status === \App\Models\FamilyPlanRequest::STATUS_CANCELLED;
    $isMulticaixa = $familyRequest->payment_method === \App\Models\FamilyPlanRequest::METHOD_MULTICAIXA;
@endphp

@section('title',
    $isActivated ? 'Plano Activado — AngolaWiFi' :
    ($isPending   ? 'A Processar — AngolaWiFi' :
                    'Pagamento — AngolaWiFi')
)

@section('content')
<div class="container--720 checkout-page">
  <div class="solicitar-confirmacao">

    {{-- ── ESTADO: ACTIVADO ──────────────────────────────────────────────── --}}
    @if($isActivated)
      <div class="solicitar-confirmacao-icon">✅</div>
      <h1 class="solicitar-confirmacao-titulo">Plano Activado!</h1>
      <p class="solicitar-confirmacao-subtitulo">
        O seu plano <strong>{{ $familyRequest->plan_name }}</strong> foi activado com sucesso.
        Enviámos uma confirmação para <strong>{{ $familyRequest->customer_email }}</strong>.
      </p>
      <div class="pagar-plano-detalhe">
        <p><span class="label">Referência:</span> <strong>{{ $familyRequest->payment_reference }}</strong></p>
        <p><span class="label">Cliente:</span> {{ $familyRequest->customer_name }}</p>
        @if($familyRequest->plan_preco)
          <p><span class="label">Valor pago:</span> {{ number_format($familyRequest->plan_preco, 0, ',', '.') }} AOA</p>
        @endif
      </div>
      <a href="{{ url('/') }}" class="checkout-btn" style="display:inline-block;margin-top:1.5rem;text-decoration:none;">
        Voltar ao Início
      </a>

    {{-- ── ESTADO: PENDING (pagamento confirmado, SG falhou) ────────────── --}}
    @elseif($isPending)
      <div class="solicitar-confirmacao-icon">⏳</div>
      <h1 class="solicitar-confirmacao-titulo">A Processar…</h1>
      <p class="solicitar-confirmacao-subtitulo">
        O seu pagamento foi recebido. Estamos a activar o acesso no sistema.
        Se não receber confirmação por e-mail em breve, contacte o suporte.
      </p>
      <div class="pagar-plano-detalhe">
        <p><span class="label">Referência:</span> <strong>{{ $familyRequest->payment_reference }}</strong></p>
      </div>

    {{-- ── ESTADO: CANCELADO ─────────────────────────────────────────────── --}}
    @elseif($isCancelled)
      <div class="solicitar-confirmacao-icon">❌</div>
      <h1 class="solicitar-confirmacao-titulo">Pedido Cancelado</h1>
      <p class="solicitar-confirmacao-subtitulo">
        Este pedido foi cancelado. Se tiver alguma dúvida, contacte o suporte.
      </p>
      <a href="{{ url('/') }}" class="checkout-btn" style="display:inline-block;margin-top:1.5rem;text-decoration:none;">
        Voltar ao Início
      </a>

    {{-- ── ESTADO: AWAITING PAYMENT (estado normal — cliente acabou de submeter) ── --}}
    @else
      <div class="solicitar-confirmacao-icon">💳</div>
      <h1 class="solicitar-confirmacao-titulo">Como Pagar</h1>
      <p class="solicitar-confirmacao-subtitulo">
        O seu pedido foi registado. Complete o pagamento para activar o plano.
        <strong>A activação é automática após a confirmação do pagamento.</strong>
      </p>

      {{-- Detalhes do pedido --}}
      <div class="pagar-plano-detalhe">
        <p><span class="label">Plano:</span> <strong>{{ $familyRequest->plan_name }}</strong></p>
        @if($familyRequest->plan_preco)
          <p class="pagar-plano-valor">{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} AOA</p>
        @endif
        <p><span class="label">Referência do pedido:</span> <strong class="pagar-plano-ref">{{ $familyRequest->payment_reference }}</strong></p>
      </div>

      {{-- Multicaixa Express --}}
      @if($isMulticaixa)
        <div class="pagar-plano-instrucoes">
          <h2>💳 Pagar com Multicaixa Express</h2>
          <ol class="pagar-plano-steps">
            <li>Abra a sua aplicação bancária.</li>
            <li>Seleccione <strong>Multicaixa Express</strong> → <strong>Pagamentos</strong>.</li>
            <li>Introduza a referência: <strong class="pagar-plano-ref-inline">{{ $familyRequest->payment_reference }}</strong></li>
            @if($familyRequest->plan_preco)
              <li>Confirme o valor: <strong>{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} AOA</strong></li>
            @endif
            <li>Confirme o pagamento.</li>
          </ol>
          <div class="pagar-plano-aviso">
            <strong>⚡ Activação automática:</strong> assim que o pagamento for confirmado, o seu
            plano será activado instantaneamente e receberá um e-mail de confirmação.
          </div>
        </div>

      {{-- PayPal --}}
      @else
        <div class="pagar-plano-instrucoes">
          <h2>🅿 Pagar com PayPal</h2>
          <p>Clique no botão abaixo para ser redirecionado para o PayPal.</p>
          <p>Use a referência <strong>{{ $familyRequest->payment_reference }}</strong> no campo de nota do pagamento.</p>
          @if($familyRequest->plan_preco)
            <p>Valor: <strong>{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} AOA</strong></p>
          @endif
          <div class="pagar-plano-aviso">
            <strong>ℹ️ Nota:</strong> Após o pagamento, a activação pode demorar alguns minutos.
            Receberá um e-mail de confirmação em <strong>{{ $familyRequest->customer_email }}</strong>.
          </div>
        </div>
      @endif

      {{-- Botão de simulação (apenas em local/dev) --}}
      @if(app()->environment('local', 'testing'))
        <div style="margin-top:2rem;padding:1rem;background:#fef9c3;border:1px dashed #ca8a04;border-radius:8px;font-size:0.85rem;">
          <strong>🧪 Ambiente de Desenvolvimento</strong><br>
          Simule a confirmação do gateway de pagamento:
          <a href="{{ route('family.payment.simulate', $familyRequest->id) }}"
             class="checkout-btn"
             style="display:inline-block;margin-top:0.75rem;text-decoration:none;background:#ca8a04;">
            Simular Pagamento Confirmado
          </a>
        </div>
      @endif

    @endif

    {{-- Sessão de info genérica --}}
    @if(session('info'))
      <div style="margin-top:1rem;padding:0.75rem 1rem;background:#dbeafe;border-radius:8px;color:#1e40af;font-size:0.87rem;">
        {{ session('info') }}
      </div>
    @endif

    <div class="pagar-plano-contacto">
      <p>Dúvidas? Contacte-nos via WhatsApp ou e-mail de suporte.</p>
    </div>

  </div>
</div>
@endsection

@push('styles')
<style>
.pagar-plano-detalhe {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem 1.25rem;
    margin: 1.25rem 0;
    font-size: 0.92rem;
    color: #334155;
}
.pagar-plano-detalhe .label { color: #64748b; }
.pagar-plano-valor {
    font-size: 1.6rem;
    font-weight: 700;
    color: #0ea5e9;
    margin: 0.25rem 0;
}
.pagar-plano-ref {
    font-family: 'Courier New', monospace;
    background: #e0f2fe;
    color: #0369a1;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 1rem;
    letter-spacing: 0.05em;
}
.pagar-plano-ref-inline {
    font-family: 'Courier New', monospace;
    background: #e0f2fe;
    color: #0369a1;
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
}
.pagar-plano-instrucoes {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    margin: 1rem 0;
}
.pagar-plano-instrucoes h2 {
    font-size: 1rem;
    font-weight: 700;
    margin: 0 0 0.75rem;
    color: #1e293b;
}
.pagar-plano-steps {
    padding-left: 1.25rem;
    color: #334155;
    font-size: 0.92rem;
    line-height: 1.8;
}
.pagar-plano-aviso {
    margin-top: 1rem;
    padding: 0.75rem 1rem;
    background: #f0fdf4;
    border: 1px solid rgba(22,163,74,0.3);
    border-radius: 8px;
    font-size: 0.85rem;
    color: #166534;
}
.pagar-plano-contacto {
    margin-top: 2rem;
    font-size: 0.85rem;
    color: #94a3b8;
    text-align: center;
}

@media (max-width: 640px) {
    .pagar-plano-valor {
        font-size: 1.2rem;
    }
    .pagar-plano-ref {
        font-size: 0.85rem;
        word-break: break-all;
    }
    .pagar-plano-instrucoes {
        padding: 0.85rem 1rem;
    }
    .pagar-plano-instrucoes h2 {
        font-size: 0.9rem;
    }
    .pagar-plano-steps {
        font-size: 0.85rem;
        padding-left: 1rem;
    }
    .pagar-plano-detalhe {
        padding: 0.75rem 0.9rem;
    }
}
</style>
@endpush
