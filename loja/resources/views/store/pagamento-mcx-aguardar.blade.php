@extends('layouts.app')

@section('title', 'A aguardar pagamento – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">

  {{-- Cabeçalho dinâmico --}}
  <div class="checkout-success-header" id="headerProcessando">
    <span class="checkout-success-icon" aria-hidden="true" id="iconSpinner">⏳</span>
    <h1 class="checkout-title">A aguardar autorização</h1>
    <p class="checkout-subtitle">Verifique a app <strong>Multicaixa Express</strong> e autorize o pagamento.</p>
  </div>
  <div class="checkout-success-header" id="headerAprovado" style="display:none;">
    <span class="checkout-success-icon" aria-hidden="true">✅</span>
    <h1 class="checkout-title">Pagamento Aprovado!</h1>
    <p class="checkout-subtitle">O seu código WiFi está pronto.</p>
  </div>
  <div class="checkout-success-header" id="headerRecusado" style="display:none;">
    <span class="checkout-success-icon" aria-hidden="true">❌</span>
    <h1 class="checkout-title">Pagamento Recusado</h1>
    <p class="checkout-subtitle">O pagamento não foi autorizado.</p>
  </div>

  <div class="checkout-layout">
    <section class="checkout-summary-card">
      <h2>Detalhes da Transação</h2>
      <p><span class="label">Plano:</span> {{ $order->plan_name }}</p>
      <p><span class="label">Referência:</span> {{ $order->payment_reference ?? '—' }}</p>
      <p><span class="label">Telemóvel:</span> +{{ ltrim($order->customer_phone ?? '', '0') }}</p>
      <p class="total">Total: {{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</p>

      {{-- Estado dinâmico --}}
      <p style="margin-top:1rem;">
        <span class="label">Estado: </span>
        <strong id="statusTexto">{{ match(strtolower($order->status ?? '')) {
            'awaiting_payment', 'pending', 'pendente' => 'A aguardar pagamento',
            'paid', 'aprovado', 'approved'            => 'Pago',
            'failed', 'erro'                          => 'Falhou',
            'cancelled', 'recusado', 'rejected'       => 'Recusado',
            'expired'                                 => 'Expirado',
            default                                   => ucfirst($order->status ?? 'Pendente'),
        } }}</strong>
      </p>

      {{-- Código WiFi (aparece após aprovação) --}}
      <div id="wifiCodeSection" style="display:none; margin-top:1rem;">
        <hr class="checkout-divider">
        <p class="label">O Seu Código WiFi:</p>
        <div class="wifi-code-box">
          <code class="wifi-code-value" id="wifiCodeValue"></code>
          <button type="button" class="btn-copy" onclick="copyCodigo()" title="Copiar código">📋 Copiar</button>
        </div>
        <p class="checkout-note">Guarde este código. A AngolaWiFi <strong>não armazena</strong> dados pessoais para planos individuais.</p>
      </div>
    </section>

    <section class="checkout-form-card">

      {{-- Instruções --}}
      <div id="mensagemAguardar">
        <h2>Como autorizar</h2>
        <ol style="padding-left:1.2rem; line-height:2;">
          <li>Verifique as <strong>notificações do telemóvel</strong> — a app Multicaixa Express envia um alerta de pagamento.</li>
          <li>Toque na notificação para abrir o pedido de <strong>{{ number_format($order->amount_aoa, 0, ',', '.') }} AOA</strong>.</li>
          <li>Se não recebeu notificação, abra a app e toque em <strong>Pagamentos</strong> → <strong>GPO</strong>.</li>
          <li>Introduza o seu <strong>PIN</strong> para autorizar.</li>
        </ol>
        <p class="checkout-note">Esta página atualiza automaticamente. Não feche o navegador.</p>
      </div>

      {{-- Mensagem de sucesso --}}
      <div id="mensagemAprovado" style="display:none;">
        <div class="checkout-errors" style="background:#d1fae5; border-color:#6ee7b7; color:#065f46;">
          ✅ <strong>Pagamento confirmado!</strong> O seu código WiFi foi gerado com sucesso.
        </div>
        <a href="#" id="linkConfirmacao" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Ver o meu código WiFi
        </a>
      </div>

      {{-- Mensagem de erro --}}
      <div id="mensagemRecusado" style="display:none;">
        <div class="checkout-errors">
          ❌ <strong>Pagamento recusado.</strong> <span id="motivoRecusa"></span>
        </div>
        <a href="{{ route('pay4all.iniciar', $order) }}" class="btn-primary" style="margin-top:1rem; display:block; text-align:center;">
          Tentar novamente
        </a>
      </div>

    </section>
  </div>

  <div style="text-align:center; margin-top:1rem;">
    <small class="checkout-note">Pagamento processado por <strong>Pay4All · Multicaixa Express</strong></small>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
  const statusUrl = '{{ route("pay4all.status", $order) }}';
  let tentativas = 0;
  const maxTentativas = 36; // ~3 minutos

  function poll() {
    fetch(statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        document.getElementById('statusTexto').textContent =
          data.status_pt || data.status;

        if (data.is_paid) {
          mostrarAprovado(data.wifi_code, data.redirect_url);
          return;
        }

        if (data.status === 'failed' || data.status === 'cancelled' || data.status === 'expired') {
          mostrarRecusado('');
          return;
        }

        tentativas++;
        if (tentativas < maxTentativas) {
          setTimeout(poll, 5000);
        } else {
          document.getElementById('mensagemAguardar').innerHTML =
            '<p class="checkout-note"><strong>Tempo esgotado.</strong> Se já autorizou na app MCX, aguarde alguns minutos e recarregue esta página.</p>';
        }
      })
      .catch(() => {
        tentativas++;
        if (tentativas < maxTentativas) setTimeout(poll, 5000);
      });
  }

  function mostrarAprovado(code, redirectUrl) {
    document.getElementById('headerProcessando').style.display = 'none';
    document.getElementById('headerAprovado').style.display    = 'block';
    document.getElementById('mensagemAguardar').style.display  = 'none';
    document.getElementById('mensagemAprovado').style.display  = 'block';
    if (code) {
      document.getElementById('wifiCodeSection').style.display = 'block';
      document.getElementById('wifiCodeValue').textContent     = code;
    }
    if (redirectUrl) {
      document.getElementById('linkConfirmacao').href = redirectUrl;
    }
  }

  function mostrarRecusado(motivo) {
    document.getElementById('headerProcessando').style.display = 'none';
    document.getElementById('headerRecusado').style.display    = 'block';
    document.getElementById('mensagemAguardar').style.display  = 'none';
    document.getElementById('mensagemRecusado').style.display  = 'block';
    if (motivo) document.getElementById('motivoRecusa').textContent = motivo;
  }

  function copyCodigo() {
    const code = document.getElementById('wifiCodeValue').textContent;
    navigator.clipboard.writeText(code).catch(() => {});
  }
  window.copyCodigo = copyCodigo;

  @if($order->isPaid())
    mostrarAprovado('{{ $order->wifi_code }}', '{{ route("store.checkout.confirm", $order->id) }}');
  @elseif($order->status === 'failed' || $order->status === 'cancelled')
    mostrarRecusado('');
  @else
    setTimeout(poll, 5000);
  @endif
})();
</script>
@endpush
