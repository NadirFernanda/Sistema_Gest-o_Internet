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
            <h3>Planos por Código (Individuais)</h3>
            <ul class="steps-list">
              <li>Acesse a rede LuandaWiFi num dos pontos de cobertura.</li>
              <li>Escolha um plano individual (Hora, Diário, Semanal, Mensal).</li>
              <li>Pague com Multicaixa Express ou outro método disponível.</li>
              <li>O código de acesso será exibido imediatamente e enviado por e‑mail.</li>
              <li>Conecte-se à rede, abra o portal, insira o código e comece a navegar.</li>
              <li><strong>Importante:</strong> Não há coleta de dados pessoais. Se perder o código, não poderá recuperá-lo.</li>
            </ul>
            <div class="howto-plan-list">
              <div class="plan-item"><span>Plano Hora</span><strong>200 Kz</strong></div>
              <div class="plan-item"><span>Plano Diário</span><strong>350 Kz</strong></div>
              <div class="plan-item"><span>Plano Semanal</span><strong>1.000 Kz</strong></div>
              <div class="plan-item"><span>Plano Mensal</span><strong>3.500 Kz</strong></div>
            </div>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">2</div>
          <div class="step-body">
            <h3>Planos Familiares / Empresas</h3>
            <ul class="steps-list">
              <li>Acesse a rede LuandaWiFi ou entre em contato pelo site.</li>
              <li>Escolha o plano familiar ou empresarial (4 Mbps, 6 Mbps, 8 Mbps, Satélite).</li>
              <li>Preencha os seus dados: Nome, Telefone, E‑mail.</li>
              <li>Pague com Multicaixa Express ou outro método disponível.</li>
              <li>A ativação será comunicada após confirmação do pagamento.</li>
              <li><strong>Importante:</strong> Estes planos requerem dados pessoais para ativação e suporte.</li>
            </ul>
            <div class="howto-plan-list">
              <div class="plan-item"><span>4 Mbps</span><strong>22.500 Kz</strong></div>
              <div class="plan-item"><span>6 Mbps</span><strong>27.500 Kz</strong></div>
              <div class="plan-item"><span>8 Mbps</span><strong>31.500 Kz</strong></div>
              <div class="plan-item"><span>Via Satélite</span><strong>Preço sob consulta</strong></div>
            </div>
          </div>
        </div>

        <div class="info-banner">
          <p>Para mais perguntas, visite <a class="contact-link" href="https://www.angolawifi.ao" target="_blank" rel="noopener">www.angolawifi.ao</a> ou contacte suporte@luandawifi.ao</p>
        </div>
      </div>
    </div>
  </div>
@endsection
