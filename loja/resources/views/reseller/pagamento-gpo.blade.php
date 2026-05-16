@extends('layouts.app')

@section('title', 'Pagamento Seguro – Painel Revendedor')

@section('content')
<div class="container--720 checkout-page">
  <h1 class="checkout-title">Pagamento Seguro</h1>
  <p class="checkout-subtitle">Complete o pagamento em segurança no ecrã abaixo (fornecido pela <strong>EMIS</strong>).</p>

  <div class="checkout-layout">

    {{-- Resumo do pedido --}}
    <section class="checkout-summary-card">
      <h2>Resumo da Compra</h2>

      @foreach($purchases as $p)
        <div style="display:flex; justify-content:space-between; font-size:0.88rem; margin-bottom:0.4rem;">
          <span style="color:#6b7280;">{{ $p->plan_name }} × {{ $p->quantity }}</span>
          <span style="font-weight:600;">{{ number_format($p->net_amount_aoa, 0, ',', '.') }} Kz</span>
        </div>
      @endforeach

      <div style="height:1px; background:#e5e7eb; margin:0.75rem 0;"></div>
      <div style="display:flex; justify-content:space-between; font-size:1rem;">
        <span style="font-weight:700;">Total a pagar</span>
        <span style="font-weight:800; color:#1a1a1a;">{{ number_format($total, 0, ',', '.') }} Kz</span>
      </div>

      <p style="margin-top:1rem; font-size:0.82rem;">
        <span class="label">Referência: </span>
        <strong>{{ $reference }}</strong>
      </p>

      <p style="margin-top:0.5rem; font-size:0.82rem;">
        <span class="label">Estado: </span>
        <strong id="statusTexto">A aguardar pagamento</strong>
      </p>

      {{-- Confirmação de sucesso (aparece após aprovação) --}}
      <div id="successSection" style="display:none; margin-top:1rem;">
        <hr class="checkout-divider">
        <div style="background:#d1fae5; border:1.5px solid #6ee7b7; border-radius:0.6rem; padding:0.8rem 1rem; font-size:0.85rem; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> Os vouchers foram transferidos para a sua conta.
        </div>
        <a href="#" id="linkConfirmacao" class="btn-primary" style="display:block; text-align:center; margin-top:0.75rem;">
          Ver os meus vouchers
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

      {{-- Mensagem de sucesso --}}
      <div id="painelAprovado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors" style="background:#d1fae5; border-color:#6ee7b7; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> Os seus vouchers estão prontos para download.
        </div>
        <a href="#" id="linkAprovado" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Ir para o painel →
        </a>
      </div>

      {{-- Mensagem de erro --}}
      <div id="painelRecusado" style="display:none; padding:1.5rem;">
        <div class="checkout-errors">
          ❌ <strong>Pagamento não concluído.</strong>
        </div>
        <form method="POST" action="{{ route('reseller.panel.payment.cancel') }}" style="margin-top:1rem;">
          @csrf
          <button type="submit" class="btn-secondary" style="width:100%;">Cancelar e recomeçar</button>
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
  const statusUrl     = '{{ route("reseller.panel.payment.gpo.status") }}';
  const gpoOrigin     = 'https://pagamentonline.emis.co.ao';
  let tentativas      = 0;
  const maxTentativas = 36; // ~3 minutos

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const el = document.getElementById('statusTexto');
        if (el) el.textContent = data.status_pt || data.status;

        if (data.is_paid) {
          mostrarAprovado(data.redirect_url);
          return;
        }
        if (data.status === 'failed') {
          mostrarRecusado();
          return;
        }
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
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

    const url = redirectUrl || '{{ route("reseller.panel.payment.gpo.confirm") }}';
    document.getElementById('linkAprovado').href   = url;
    document.getElementById('linkConfirmacao').href = url;
  }

  function mostrarRecusado() {
    document.getElementById('painelIframe').style.display    = 'none';
    document.getElementById('painelAprovado').style.display  = 'none';
    document.getElementById('painelRecusado').style.display  = 'block';
    document.getElementById('statusTexto').textContent       = 'Não concluído';
  }

  setTimeout(poll, 6000);
})();
</script>
@endpush
