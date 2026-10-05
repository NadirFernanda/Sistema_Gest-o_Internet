@php
  $isCheckout = ($context ?? 'plans') === 'checkout';
@endphp

<details class="purchase-help">
  <summary>
    <span class="purchase-help__summary-copy">
      <strong>{{ $isCheckout ? 'Precisa de ajuda para concluir a compra?' : 'Não sabe como comprar? Veja o passo a passo' }}</strong>
      <span>{{ $isCheckout ? 'É simples e o pagamento é feito com segurança.' : 'Em poucos passos terá o seu código WiFi.' }}</span>
    </span>
    <span class="purchase-help__toggle" aria-hidden="true">+</span>
  </summary>

  <div class="purchase-help__content">
    @if(!empty($purchaseHelpVideoUrl))
      <div class="purchase-help__video-wrap">
        <p class="purchase-help__video-label">Assista ao vídeo: como comprar o seu plano</p>
        <video class="purchase-help__video" controls preload="none" playsinline title="Vídeo de ajuda para comprar um plano">
          <source src="{{ $purchaseHelpVideoUrl }}">
          O seu navegador não suporta a reprodução deste vídeo.
        </video>
      </div>
    @endif

    <ol class="purchase-help__steps">
      <li>
        <span class="purchase-help__number">1</span>
        <span><strong>Escolha o plano</strong><small>Compare a duração e o preço e clique em “Comprar Agora”.</small></span>
      </li>
      <li>
        <span class="purchase-help__number">2</span>
        <span><strong>Preencha os seus dados</strong><small>Nome, telefone e e-mail são opcionais, mas ajudam a receber o código.</small></span>
      </li>
      <li>
        <span class="purchase-help__number">3</span>
        <span><strong>Faça o pagamento</strong><small>Pague com cartão bancário ou Multicaixa Express através da EMIS.</small></span>
      </li>
      <li>
        <span class="purchase-help__number">4</span>
        <span><strong>Receba e use o código</strong><small>Após a confirmação, o código aparece na página. Introduza-o no portal AngolaWiFi para se ligar.</small></span>
      </li>
    </ol>

    <a class="purchase-help__contact" href="https://wa.me/244949364505?text={{ rawurlencode('Olá, preciso de ajuda para comprar um plano AngolaWiFi.') }}" target="_blank" rel="noopener">
      Ainda tem dúvidas? Fale connosco pelo WhatsApp
    </a>
  </div>
</details>

@push('styles')
<style>
.purchase-help {
  margin: 1.25rem 0;
  border: 1px solid #dbe3ec;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
}
.purchase-help summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 1.15rem;
  cursor: pointer;
  list-style: none;
}
.purchase-help summary::-webkit-details-marker { display: none; }
.purchase-help summary:focus-visible {
  outline: 3px solid rgba(247, 181, 0, .5);
  outline-offset: 3px;
  border-radius: 12px;
}
.purchase-help__summary-copy { display: grid; gap: .2rem; }
.purchase-help__summary-copy strong { color: #1a202c; font-size: .95rem; }
.purchase-help__summary-copy > span { color: #64748b; font-size: .8rem; }
.purchase-help__toggle {
  display: grid;
  flex: 0 0 2rem;
  width: 2rem;
  height: 2rem;
  place-items: center;
  border-radius: 50%;
  background: #fff7d6;
  color: #8a6500;
  font-size: 1.35rem;
  line-height: 1;
}
.purchase-help[open] .purchase-help__toggle { transform: rotate(45deg); }
.purchase-help__content { padding: 0 1.15rem 1.15rem; }
.purchase-help__video-wrap { max-width: 720px; margin: 0 0 1rem; }
.purchase-help__video-label { margin: 0 0 .5rem; color: #334155; font-size: .82rem; font-weight: 700; }
.purchase-help__video { display: block; width: 100%; max-height: 405px; border-radius: 10px; background: #0f172a; }
.purchase-help__steps {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: .75rem;
  margin: 0;
  padding: 0;
  list-style: none;
}
.purchase-help__steps li {
  display: flex;
  align-items: flex-start;
  gap: .6rem;
  padding: .85rem;
  border: 1px solid #edf0f4;
  border-radius: 10px;
  background: #f8fafc;
}
.purchase-help__number {
  display: grid;
  flex: 0 0 1.65rem;
  width: 1.65rem;
  height: 1.65rem;
  place-items: center;
  border-radius: 50%;
  background: #f7b500;
  color: #1a202c;
  font-size: .78rem;
  font-weight: 800;
}
.purchase-help__steps li > span:last-child { display: grid; gap: .25rem; }
.purchase-help__steps strong { color: #1a202c; font-size: .8rem; }
.purchase-help__steps small { color: #64748b; font-size: .73rem; line-height: 1.5; }
.purchase-help__contact {
  display: inline-flex;
  margin-top: .9rem;
  color: #087443;
  font-size: .82rem;
  font-weight: 700;
  text-decoration: underline;
  text-underline-offset: 3px;
}
@media (max-width: 720px) {
  .purchase-help__steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 440px) {
  .purchase-help summary { padding: .85rem; }
  .purchase-help__content { padding: 0 .85rem .85rem; }
  .purchase-help__steps { grid-template-columns: 1fr; }
}
</style>
@endpush
