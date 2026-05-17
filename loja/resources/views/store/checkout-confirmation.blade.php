@extends('layouts.app')

@section('title', 'Compra Concluída – AngolaWiFi')

@push('styles')
<style>
/* ── Checkout Confirmation ── namespace .cc-* ─────────────────────── */
.cc-page {
  font-family: Inter, system-ui, sans-serif;
  background: #f4f6f9;
  min-height: 80vh;
  padding: 2.5rem 0 5rem;
  color: #1a202c;
}
.cc-wrap {
  max-width: 960px;
  margin: 0 auto;
  padding: 0 1.25rem;
}

/* ── Success header ─────────────────────────────────────────── */
.cc-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: .75rem;
  margin-bottom: 2.25rem;
}
.cc-check-icon {
  width: 64px;
  height: 64px;
  background: #16a34a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 20px rgba(22,163,74,.3);
}
.cc-check-icon svg { color: #fff; }
.cc-title {
  font-size: 1.7rem;
  font-weight: 800;
  letter-spacing: -.03em;
  margin: 0;
}
.cc-subtitle {
  font-size: .95rem;
  color: #64748b;
  margin: 0;
  max-width: 480px;
  line-height: 1.55;
}

/* ── Grid ───────────────────────────────────────────────────── */
.cc-grid {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 1.25rem;
  align-items: start;
}

/* ── Cards ──────────────────────────────────────────────────── */
.cc-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  overflow: hidden;
}

/* ── Plan badge ─────────────────────────────────────────────── */
.cc-plan-badge {
  background: linear-gradient(135deg, #f7b500 0%, #e6a800 100%);
  padding: 1.5rem 1.75rem 1.25rem;
}
.cc-plan-label {
  font-size: .7rem;
  font-weight: 800;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: rgba(26,32,44,.55);
  margin: 0 0 .3rem;
}
.cc-plan-name {
  font-size: 1.3rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 .1rem;
}
.cc-plan-price {
  font-size: 1rem;
  font-weight: 700;
  color: #7a4f00;
  margin: 0;
}

/* ── Plan details list ──────────────────────────────────────── */
.cc-plan-details {
  padding: 1.25rem 1.75rem 1.5rem;
  display: flex;
  flex-direction: column;
  gap: .6rem;
}
.cc-detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: .85rem;
}
.cc-detail-lbl {
  color: #64748b;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: .45rem;
}
.cc-detail-lbl svg { flex-shrink: 0; color: #94a3b8; }
.cc-detail-val {
  font-weight: 700;
  color: #1a202c;
  text-align: right;
}
.cc-divider {
  border: none;
  border-top: 1px solid #f1f5f9;
  margin: .25rem 0;
}

/* ── Code section ───────────────────────────────────────────── */
.cc-code-section {
  padding: 1.5rem 1.75rem;
}
.cc-code-label {
  font-size: .72rem;
  font-weight: 800;
  letter-spacing: .1em;
  text-transform: uppercase;
  color: #64748b;
  margin: 0 0 .85rem;
}
.cc-code-box {
  background: #1a202c;
  border-radius: 14px;
  padding: 1.5rem 1.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.25rem;
}
.cc-code-value {
  font-family: 'JetBrains Mono', 'Courier New', monospace;
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: .18em;
  color: #f7b500;
  background: none;
  border: none;
  padding: 0;
  line-height: 1;
  user-select: all;
}
.cc-copy-btn {
  display: flex;
  align-items: center;
  gap: .4rem;
  padding: .55rem .9rem;
  background: rgba(247,181,0,.15);
  border: 1px solid rgba(247,181,0,.4);
  border-radius: 8px;
  color: #f7b500;
  font-size: .78rem;
  font-weight: 700;
  cursor: pointer;
  transition: background .15s;
  white-space: nowrap;
  flex-shrink: 0;
}
.cc-copy-btn:hover { background: rgba(247,181,0,.25); }
.cc-copy-btn svg { flex-shrink: 0; }

/* ── Action buttons ─────────────────────────────────────────── */
.cc-actions {
  display: flex;
  flex-direction: column;
  gap: .65rem;
}
.cc-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .55rem;
  padding: .9rem 1.25rem;
  border-radius: 12px;
  font-size: .92rem;
  font-weight: 800;
  text-decoration: none;
  border: none;
  cursor: pointer;
  transition: filter .15s, transform .12s;
  font-family: inherit;
  line-height: 1.2;
}
.cc-btn:hover { filter: brightness(.95); transform: translateY(-1px); }
.cc-btn--primary {
  background: #f7b500;
  color: #1a202c;
  box-shadow: 0 2px 12px rgba(247,181,0,.35);
}
.cc-btn--outline {
  background: #fff;
  color: #1a202c;
  border: 1.5px solid #e2e8f0;
}
.cc-btn--outline:hover { border-color: #f7b500; background: rgba(247,181,0,.06); }

/* ── Instructions card ──────────────────────────────────────── */
.cc-instructions {
  padding: 1.5rem 1.75rem;
}
.cc-instr-title {
  font-size: .88rem;
  font-weight: 800;
  color: #1a202c;
  margin: 0 0 1rem;
  letter-spacing: -.01em;
}
.cc-steps {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: .75rem;
}
.cc-step {
  display: flex;
  align-items: flex-start;
  gap: .75rem;
  font-size: .84rem;
  color: #374151;
  line-height: 1.5;
}
.cc-step-num {
  flex-shrink: 0;
  width: 22px;
  height: 22px;
  background: #f7b500;
  color: #1a202c;
  border-radius: 50%;
  font-size: .72rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: .15rem;
}
.cc-ref-note {
  margin-top: 1.25rem;
  padding-top: 1rem;
  border-top: 1px solid #f1f5f9;
  font-size: .78rem;
  color: #94a3b8;
  line-height: 1.6;
}
.cc-ref-note strong { color: #64748b; }

/* ── Account created banner ─────────────────────────────────── */
.cc-account-banner {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 10px;
  padding: .9rem 1.1rem;
  margin-top: 1rem;
  font-size: .83rem;
  color: #15803d;
  line-height: 1.5;
}
.cc-account-banner a { color: #16a34a; font-weight: 700; text-decoration: underline; }

/* ── Mobile ─────────────────────────────────────────────────── */
@media (max-width: 640px) {
  .cc-page { padding: 1.5rem 0 4rem; }
  .cc-grid { grid-template-columns: 1fr; }
  .cc-code-value { font-size: 1.6rem; }
  .cc-title { font-size: 1.35rem; }
}
</style>
@endpush

@section('content')
@php
  $waText = rawurlencode(
    "Olá! O seu código WiFi AngolaWiFi é: " . ($order->wifi_code ?? '') . "\n\n"
    . "Plano: " . ($order->plan_name ?? ($order->plan_id ?? '')) . "\n"
    . "Referência: #" . ($order->id ?? '') . "\n\n"
    . "Para usar: ligue-se à rede AngolaWiFi e introduza este código no portal de acesso.\n\n"
    . "Obrigado pela sua compra!"
  );
  $waPhone = !empty($order->customer_phone) ? preg_replace('/[^0-9]/', '', $order->customer_phone) : '';
@endphp

<div class="cc-page">
<div class="cc-wrap">

  {{-- Success header ─────────────────────────────────────── --}}
  <div class="cc-header">
    <div class="cc-check-icon">
      <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h1 class="cc-title">Compra Concluída!</h1>
    <p class="cc-subtitle">O seu código WiFi está pronto. Pode utilizá-lo imediatamente em qualquer ponto da rede AngolaWiFi.</p>
  </div>

  {{-- Grid ──────────────────────────────────────────────── --}}
  <div class="cc-grid">

    {{-- LEFT — plano + detalhes ───────────────────────── --}}
    <div class="cc-card">
      <div class="cc-plan-badge">
        <p class="cc-plan-label">Plano adquirido</p>
        <p class="cc-plan-name">{{ $plan?->name ?? ($order->plan_name ?? 'Plano WiFi') }}</p>
        @if($plan)
          <p class="cc-plan-price">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} Kz</p>
        @endif
      </div>
      <div class="cc-plan-details">
        @if($plan?->validity_label)
          <div class="cc-detail-row">
            <span class="cc-detail-lbl">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              Duração
            </span>
            <span class="cc-detail-val">{{ $plan->validity_label }}</span>
          </div>
        @endif
        @if($plan?->speed_label)
          <div class="cc-detail-row">
            <span class="cc-detail-lbl">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
              Velocidade
            </span>
            <span class="cc-detail-val">{{ $plan->speed_label }}</span>
          </div>
        @endif
        <hr class="cc-divider">
        <div class="cc-detail-row">
          <span class="cc-detail-lbl">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            Referência
          </span>
          <span class="cc-detail-val">#{{ $order->id }}</span>
        </div>
        <div class="cc-detail-row">
          <span class="cc-detail-lbl">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Método
          </span>
          <span class="cc-detail-val">EMIS · GPO</span>
        </div>
        @if($plan)
          <hr class="cc-divider">
          <div class="cc-detail-row">
            <span class="cc-detail-lbl" style="font-weight:700;color:#1a202c;">Total pago</span>
            <span class="cc-detail-val" style="font-size:1rem;color:#f7b500;">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} AOA</span>
          </div>
        @endif
      </div>
    </div>

    {{-- RIGHT — código + acções ───────────────────────── --}}
    <div class="cc-card">
      <div class="cc-code-section">
        <p class="cc-code-label">O seu código WiFi</p>

        @if(!empty($order->wifi_code))
          <div class="cc-code-box">
            <code class="cc-code-value" id="ccCode">{{ $order->wifi_code }}</code>
            <button type="button" class="cc-copy-btn" id="ccCopyBtn" onclick="ccCopy()">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
              <span id="ccCopyLbl">Copiar</span>
            </button>
          </div>

          @if(!empty($order->customer_email))
            <p style="font-size:.8rem;color:#64748b;margin:0 0 1.1rem;display:flex;align-items:center;gap:.4rem;">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              Código enviado para <strong>{{ $order->customer_email }}</strong>
            </p>
          @endif

          <div class="cc-actions">
            @if($waPhone)
              <a href="https://wa.me/{!! $waPhone !!}?text={!! $waText !!}"
                 target="_blank" rel="noopener"
                 class="cc-btn cc-btn--primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Enviar por WhatsApp
              </a>
            @endif
            <a href="{{ url('/') }}" class="cc-btn cc-btn--outline">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Comprar outro plano
            </a>
          </div>

        @else
          <p style="font-size:.88rem;color:#64748b;line-height:1.6;">O código está a ser processado. Se não aparecer em breve, contacte o suporte AngolaWiFi.</p>
        @endif

        @if(!empty($accountCreated))
          <div class="cc-account-banner">
            Conta criada com sucesso — aceda ao histórico das suas compras em
            <a href="{{ route('account.index') }}">Minha Conta</a>.
          </div>
        @endif
      </div>

      {{-- Instruções ────────────────────────────────── --}}
      <div class="cc-instructions" style="border-top:1px solid #f1f5f9;">
        <p class="cc-instr-title">Como utilizar o código</p>
        <ol class="cc-steps">
          <li class="cc-step"><span class="cc-step-num">1</span><span>Ligue-se à rede WiFi <strong>AngolaWiFi</strong> no seu dispositivo.</span></li>
          <li class="cc-step"><span class="cc-step-num">2</span><span>Abra o browser — será redirecionado para o portal de acesso.</span></li>
          <li class="cc-step"><span class="cc-step-num">3</span><span>Introduza o código acima e clique em <strong>Ligar</strong>.</span></li>
          <li class="cc-step"><span class="cc-step-num">4</span><span>Pronto — está a navegar!</span></li>
        </ol>
        <p class="cc-ref-note">Ref. pedido: <strong>#{{ $order->id }}</strong></p>
      </div>
    </div>

  </div>
</div>
</div>

@push('scripts')
<script>
function ccCopy() {
  var code = document.getElementById('ccCode');
  var lbl  = document.getElementById('ccCopyLbl');
  if (!code || !lbl) return;
  navigator.clipboard.writeText(code.textContent.trim()).then(function() {
    lbl.textContent = 'Copiado!';
    setTimeout(function(){ lbl.textContent = 'Copiar'; }, 2000);
  }).catch(function() {
    var range = document.createRange();
    range.selectNode(code);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);
  });
}
</script>
@endpush
@endsection
