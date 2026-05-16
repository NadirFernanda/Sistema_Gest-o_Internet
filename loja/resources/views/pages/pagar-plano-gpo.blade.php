@extends('layouts.app')

@section('title', 'Pagamento Seguro — AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Pagamento Seguro</h1>
  <p class="checkout-subtitle">Complete o pagamento em segurança no ecrã abaixo (fornecido pela <strong>EMIS</strong>).</p>

  <div class="checkout-layout">

    {{-- Resumo do pedido --}}
    <section class="checkout-summary-card">
      <h2>Resumo do Pedido</h2>

      <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:0.4rem;">
        <span style="color:#6b7280;">{{ $familyRequest->plan_name }}</span>
        <span style="font-weight:600;">{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} Kz</span>
      </div>

      @if($familyRequest->plan_ciclo_dias)
      <div style="display:flex; justify-content:space-between; font-size:0.82rem; color:#9ca3af; margin-bottom:0.4rem;">
        <span>Duração</span>
        <span>{{ $familyRequest->plan_ciclo_dias }} dias</span>
      </div>
      @endif

      <div style="height:1px; background:#e5e7eb; margin:0.75rem 0;"></div>
      <div style="display:flex; justify-content:space-between; font-size:1rem;">
        <span style="font-weight:700;">Total a pagar</span>
        <span style="font-weight:800; color:#1a1a1a;">{{ number_format($familyRequest->plan_preco, 0, ',', '.') }} Kz</span>
      </div>

      <p style="margin-top:1rem; font-size:0.82rem;">
        <span style="color:#6b7280;">Referência: </span>
        <strong>{{ $reference }}</strong>
      </p>
      <p style="margin-top:0.3rem; font-size:0.82rem;">
        <span style="color:#6b7280;">Cliente: </span>
        {{ $familyRequest->customer_name }}
      </p>

      <p style="margin-top:0.6rem; font-size:0.82rem;">
        <span style="color:#6b7280;">Estado: </span>
        <strong id="statusTexto">A aguardar pagamento</strong>
      </p>

      {{-- Painel de sucesso (hidden) --}}
      <div id="successSection" style="display:none; margin-top:1rem;">
        <hr style="border:none; border-top:1px solid #e5e7eb; margin-bottom:1rem;">
        <div style="background:#d1fae5; border:1.5px solid #6ee7b7; border-radius:0.6rem; padding:0.8rem 1rem; font-size:0.85rem; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> O seu plano está a ser activado.
        </div>
        <a href="#" id="linkConfirmacao" class="btn-primary" style="display:block; text-align:center; margin-top:0.75rem;">
          Ver confirmação →
        </a>
      </div>
    </section>

    {{-- Painel principal --}}
    <section class="checkout-form-card" style="padding:0; overflow:hidden;">

      {{-- Iframe GPO --}}
      <div id="painelIframe">
        <iframe
          id="gpoFrame"
          src="{{ $iframeUrl }}"
          style="width:100%; min-height:520px; border:none; display:block;"
          title="Pagamento seguro EMIS GPO"
          allow="payment"
          sandbox="allow-scripts allow-forms allow-same-origin allow-popups"
        ></iframe>
        <p class="checkout-note" style="padding:0.75rem 1rem; margin:0; border-top:1px solid #e5e7eb;">
          Pagamento processado pela <strong>EMIS – Gateway de Pagamentos Online</strong>. Os dados do cartão nunca passam pelos nossos servidores.
        </p>
      </div>

      {{-- Sucesso --}}
      <div id="painelAprovado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors" style="background:#d1fae5; border-color:#6ee7b7; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> O seu plano está a ser activado. Receberá um e-mail em breve.
        </div>
        <a href="#" id="linkAprovado" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Ver confirmação →
        </a>
      </div>

      {{-- Falha --}}
      <div id="painelRecusado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors">
          ❌ <strong>Pagamento não concluído.</strong>
        </div>
        <form method="POST" action="{{ route('family.payment.gpo.cancel', $familyRequest->id) }}" style="margin-top:1rem;">
          @csrf
          <button type="submit" class="btn-secondary" style="width:100%;">Tentar outro método de pagamento</button>
        </form>
      </div>

    </section>
  </div>

  <div style="text-align:center; margin-top:1rem;">
    <small class="checkout-note">Pagamento processado por <strong>EMIS · Gateway de Pagamentos Online</strong></small>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  const statusUrl     = '{{ route("family.payment.gpo.status", $familyRequest->id) }}';
  const gpoOrigin     = 'https://pagamentonline.emis.co.ao';
  let tentativas      = 0;
  const maxTentativas = 36; // ~3 minutos

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('statusTexto');
        if (el) el.textContent = data.is_paid ? 'Pago ✅' : 'A aguardar pagamento';

        if (data.is_paid) {
          mostrarAprovado(data.redirect_url);
          return;
        }
        if (data.status === 'failed') {
          mostrarRecusado();
          return;
        }
        tentativas++;
        if (tentativas < maxTentativas) {
          setTimeout(poll, 5000);
        } else {
          mostrarTimeout();
        }
      })
      .catch(() => {
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      });
  }

  window.addEventListener('message', function(event) {
    if (event.origin !== gpoOrigin) return;
    if (event.data) { tentativas = 0; poll(); }
  }, false);

  function mostrarAprovado(redirectUrl) {
    document.getElementById('painelIframe').style.display    = 'none';
    document.getElementById('painelAprovado').style.display  = 'block';
    document.getElementById('painelRecusado').style.display  = 'none';
    document.getElementById('statusTexto').textContent       = 'Pago ✅';
    document.getElementById('successSection').style.display  = 'block';

    const url = redirectUrl || '{{ route("family.payment.gpo.confirm", $familyRequest->id) }}';
    document.getElementById('linkAprovado').href    = url;
    document.getElementById('linkConfirmacao').href = url;
  }

  function mostrarRecusado() {
    document.getElementById('painelIframe').style.display    = 'none';
    document.getElementById('painelAprovado').style.display  = 'none';
    document.getElementById('painelRecusado').style.display  = 'block';
    document.getElementById('statusTexto').textContent       = 'Não concluído';
  }

  function mostrarTimeout() {
    document.getElementById('statusTexto').textContent = 'Tempo esgotado';
    var recusado = document.getElementById('painelRecusado');
    recusado.style.display = 'block';
    recusado.querySelector('div').innerHTML = '⏱️ <strong>Tempo limite atingido.</strong> Se completou o pagamento aguarde a confirmação por e-mail. Caso contrário, tente outro método.';
  }

  setTimeout(poll, 6000);
})();
</script>
@endpush
