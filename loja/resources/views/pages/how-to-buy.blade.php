@extends('layouts.app')

@section('content')
  <div class="page-hero">
    <div class="container">
      <span class="page-hero__eyebrow">Guia de Compra</span>
      <h1 class="page-hero__title">Como Comprar</h1>
      <p class="page-hero__desc">Passo a passo simples para escolher um plano, pagar e começar a navegar com a LuandaWiFi.</p>
    </div>
  </div>

  <div class="page-body">
    <div class="container">
      <div class="howto-steps">
        <div class="howto-step">
          <div class="step-badge">1</div>
          <div class="step-body">
            <h4>Acesse a rede</h4>
            <p>Acesse a rede LuandaWiFi num dos pontos de cobertura. Visite <a class="contact-link" href="https://www.angolawifi.ao" target="_blank" rel="noopener">www.angolawifi.ao</a>.</p>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">2</div>
          <div class="step-body">
            <h4>Escolha o seu plano</h4>
            <p>Temos opções individuais para recarga e planos familiares/empresas. Escolha o que melhor se adapta às suas necessidades.</p>
            <div class="howto-plan-list">
              <div class="plan-item"><span>Plano Hora</span><strong>200 Kz</strong></div>
              <div class="plan-item"><span>Plano Diário</span><strong>350 Kz</strong></div>
              <div class="plan-item"><span>Plano Semanal</span><strong>1.000 Kz</strong></div>
              <div class="plan-item"><span>Plano Mensal</span><strong>3.500 Kz</strong></div>
            </div>
            <p class="plan-note">Nota: após pagamento o código de recarga é exibido imediatamente e enviado por e‑mail.</p>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">3</div>
          <div class="step-body">
            <h4>Planos Familiares / Empresas</h4>
            <div class="howto-plan-list">
              <div class="plan-item"><span>4 Mbps</span><strong>22.500 Kz</strong></div>
              <div class="plan-item"><span>6 Mbps</span><strong>27.500 Kz</strong></div>
              <div class="plan-item"><span>8 Mbps</span><strong>31.500 Kz</strong></div>
              <div class="plan-item"><span>Via Satélite</span><strong>Preço sob consulta</strong></div>
            </div>
            <p class="plan-note">Nota: nestes planos a ativação é comunicada após confirmação do pagamento.</p>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">4</div>
          <div class="step-body">
            <h4>Preencha os seus dados</h4>
            <p>Antes do pagamento preencha: Nome, Telefone, E‑mail. Estes dados são essenciais para ativação e suporte.</p>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">5</div>
          <div class="step-body">
            <h4>Pague com Multicaixa Express</h4>
            <ol class="steps-list">
              <li>Escolha “Multicaixa Express” nas opções</li>
              <li>Clique em “Escolher forma de pagamento”</li>
              <li>Insira o contacto associado ao Express e confirme</li>
              <li>Autorize o pagamento no telemóvel (1m30s)</li>
            </ol>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">6</div>
          <div class="step-body">
            <h4>Receba e comece a navegar</h4>
            <p>Planos individuais: o código aparece imediatamente no e‑crã e no e‑mail. Planos empresariais: será notificado sobre a ativação.</p>
          </div>
        </div>

        <div class="info-banner">
          <p>Para mais perguntas, visite <a class="contact-link" href="https://www.angolawifi.ao" target="_blank" rel="noopener">www.angolawifi.ao</a> ou contacte suporte@luandawifi.ao</p>
        </div>

      </div>
    </div>
  </div>
@endsection
