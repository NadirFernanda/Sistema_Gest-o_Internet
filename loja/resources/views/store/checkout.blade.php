@extends('layouts.app')

@section('title', 'Checkout – AngolaWiFi')

@section('content')
<div class="container--720 checkout-page">

  <div style="text-align:center; margin-bottom:2rem;">
    <h1 class="checkout-title">Finalizar Compra</h1>
    <p class="checkout-subtitle">Preencha os seus dados (opcional) e prossiga para o pagamento seguro.</p>
  </div>

  @if ($errors->any())
    <div class="checkout-errors" style="margin-bottom:1.25rem;">
      <ul>
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if($plan)
  <form method="POST" action="{{ route('store.checkout.process') }}" id="checkoutForm">
    @csrf
    <input type="hidden" name="plan_id" value="{{ $plan->slug }}">
    <input type="hidden" name="payment_method" value="gpo">

    <div class="checkout-layout">

      {{-- ── Coluna esquerda: Resumo ──────────────────────────────────── --}}
      <section class="checkout-summary-card" style="display:flex; flex-direction:column; gap:0;">

        <h2 style="font-size:1rem; font-weight:700; margin-bottom:1.1rem; color:#111827;">
          Resumo do Pedido
        </h2>

        {{-- Badge do plano --}}
        <div style="background:linear-gradient(135deg,#f7b500 0%,#e6a800 100%); border-radius:0.75rem; padding:1.25rem; color:#1a1100; margin-bottom:1.25rem;">
          <div style="font-size:1.35rem; font-weight:800; letter-spacing:-.02em;">{{ $plan->name }}</div>
          <div style="font-size:0.85rem; margin-top:0.2rem; opacity:.85;">{{ $plan->validity_label }} · {{ $plan->speed_label }}</div>
          <div style="font-size:2rem; font-weight:900; margin-top:0.75rem; letter-spacing:-.03em;">
            {{ number_format($plan->price_public_aoa, 0, ',', '.') }}<span style="font-size:1rem; font-weight:600;"> AOA</span>
          </div>
        </div>

        <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.88rem; color:#374151;">
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#6b7280;">Velocidade</span>
            <span style="font-weight:600;">{{ $plan->speed_label }}</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#6b7280;">Duração</span>
            <span style="font-weight:600;">{{ $plan->validity_label }}</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#6b7280;">Download</span>
            <span style="font-weight:600;">Ilimitado</span>
          </div>
          <div style="display:flex; justify-content:space-between;">
            <span style="color:#6b7280;">Quantidade</span>
            <span style="font-weight:600;">1 código</span>
          </div>
          <div style="height:1px; background:#e5e7eb; margin:0.4rem 0;"></div>
          <div style="display:flex; justify-content:space-between; font-size:1rem;">
            <span style="font-weight:700;">Total</span>
            <span style="font-weight:800; color:#1a1a1a;">{{ number_format($plan->price_public_aoa, 0, ',', '.') }} AOA</span>
          </div>
        </div>

        {{-- Segurança --}}
        <div style="margin-top:auto; padding-top:1.25rem; font-size:0.78rem; color:#9ca3af; display:flex; align-items:center; gap:0.4rem;">
          🔒 Pagamento seguro processado pela EMIS
        </div>
      </section>

      {{-- ── Coluna direita: Formulário ───────────────────────────────── --}}
      <section class="checkout-form-card">

        {{-- Dados do cliente --}}
        <h2 style="font-size:1rem; font-weight:700; margin-bottom:0.25rem; color:#111827;">
          Os seus dados
        </h2>
        <p style="font-size:0.8rem; color:#9ca3af; margin-bottom:1.25rem;">
          Opcional — para receber o código por e-mail e WhatsApp.
        </p>

        <div style="display:flex; flex-direction:column; gap:0.85rem; margin-bottom:1.5rem;">

          {{-- Nome --}}
          <div>
            <label for="customer_name" style="font-size:0.8rem; font-weight:600; color:#374151; display:block; margin-bottom:0.3rem;">
              Nome completo
            </label>
            <div style="position:relative;">
              <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:1rem; pointer-events:none;">👤</span>
              <input type="text" id="customer_name" name="customer_name"
                style="width:100%; padding:0.65rem 0.85rem 0.65rem 2.4rem; border:1.5px solid #e5e7eb; border-radius:0.6rem; font-size:0.9rem; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; background:#fafafa;"
                placeholder="Ex: João Silva"
                value="{{ old('customer_name') }}" maxlength="100"
                onfocus="this.style.borderColor='#f7b500';this.style.boxShadow='0 0 0 3px rgba(247,181,0,.15)'"
                onblur="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
            </div>
          </div>

          {{-- Email --}}
          <div>
            <label for="customer_email" style="font-size:0.8rem; font-weight:600; color:#374151; display:block; margin-bottom:0.3rem;">
              E-mail
            </label>
            <div style="position:relative;">
              <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:1rem; pointer-events:none;">✉️</span>
              <input type="email" id="customer_email" name="customer_email"
                style="width:100%; padding:0.65rem 0.85rem 0.65rem 2.4rem; border:1.5px solid #e5e7eb; border-radius:0.6rem; font-size:0.9rem; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; background:#fafafa;"
                placeholder="Ex: joao@exemplo.com"
                value="{{ old('customer_email') }}" maxlength="150"
                onfocus="this.style.borderColor='#f7b500';this.style.boxShadow='0 0 0 3px rgba(247,181,0,.15)'"
                onblur="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
            </div>
          </div>

          {{-- Telemóvel --}}
          <div>
            <label for="customer_phone" style="font-size:0.8rem; font-weight:600; color:#374151; display:block; margin-bottom:0.3rem;">
              Telemóvel
            </label>
            <div style="position:relative;">
              <span style="position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); font-size:1rem; pointer-events:none;">📱</span>
              <input type="tel" id="customer_phone" name="customer_phone"
                style="width:100%; padding:0.65rem 0.85rem 0.65rem 2.4rem; border:1.5px solid #e5e7eb; border-radius:0.6rem; font-size:0.9rem; transition:border-color .15s, box-shadow .15s; box-sizing:border-box; background:#fafafa;"
                placeholder="9XXXXXXXX ou 2449XXXXXXXX"
                value="{{ old('customer_phone') }}"
                pattern="^(244)?9[0-9]{8}$" inputmode="numeric" maxlength="12"
                onfocus="this.style.borderColor='#f7b500';this.style.boxShadow='0 0 0 3px rgba(247,181,0,.15)'"
                onblur="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
            </div>
          </div>

        </div>

        {{-- Método de pagamento --}}
        <div style="border:1.5px solid #e5e7eb; border-radius:0.75rem; overflow:hidden; margin-bottom:1.5rem;">
          <div style="padding:0.6rem 1rem; background:#f9fafb; border-bottom:1px solid #e5e7eb; font-size:0.78rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:.05em;">
            Método de Pagamento
          </div>
          <label style="display:flex; align-items:center; gap:0.85rem; padding:1rem; cursor:pointer; background:#fff;">
            <input type="radio" name="_payment_display" checked style="accent-color:#f7b500; width:16px; height:16px;">
            <div style="display:flex; align-items:center; gap:0.75rem; flex:1;">
              <div style="width:40px; height:28px; background:linear-gradient(135deg,#1a56db,#0f3fa8); border-radius:4px; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:700; color:#fff; letter-spacing:.03em; flex-shrink:0;">EMIS</div>
              <div>
                <div style="font-size:0.88rem; font-weight:600; color:#111827;">Pagamento Online Seguro</div>
                <div style="font-size:0.75rem; color:#9ca3af;">Cartão bancário · Multicaixa Express</div>
              </div>
            </div>
            <div style="font-size:0.7rem; color:#059669; font-weight:600; background:#d1fae5; padding:0.2rem 0.5rem; border-radius:999px; white-space:nowrap;">🔒 Seguro</div>
          </label>
        </div>

        {{-- Botão submeter --}}
        <button type="submit" id="btnSubmit" class="btn-primary"
          style="width:100%; font-size:1rem; font-weight:700; padding:0.9rem; border-radius:0.75rem; letter-spacing:.01em;">
          <span id="btnTexto">Prosseguir para pagamento →</span>
          <span id="btnSpinner" style="display:none;">A processar…</span>
        </button>

        <p style="text-align:center; font-size:0.75rem; color:#9ca3af; margin-top:0.75rem;">
          Ao prosseguir aceita os <a href="#" style="color:#f7b500;">Termos de Serviço</a> da AngolaWiFi.
        </p>

      </section>
    </div>
  </form>
  @else
    <div class="checkout-form-card" style="text-align:center;">
      <p>Não foi possível identificar o plano selecionado. <a href="{{ url('/') }}" class="btn-primary" style="display:inline-block;margin-top:1rem;">Voltar à loja</a></p>
    </div>
  @endif

</div>
@endsection

@push('scripts')
<script>
document.getElementById('checkoutForm')?.addEventListener('submit', function() {
  document.getElementById('btnTexto').style.display  = 'none';
  document.getElementById('btnSpinner').style.display = 'inline';
  document.getElementById('btnSubmit').disabled = true;
});
</script>
@endpush
