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
            <h4>Planos por Código (Individuais)</h4>
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
              <div class="plan-item"><span>Plano Semanal</span><strong>500 Kz</strong></div>
              <div class="plan-item"><span>Plano Mensal</span><strong>1.000 Kz</strong></div>
            </div>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">2</div>
          <div class="step-body">
            <h4>Planos Familiares / Empresariais/Institucionais</h4>
            <ul class="steps-list">
              <li>Acesse a rede LuandaWiFi ou entre em contato pelo site.</li>
              <li>Escolha o plano familiar ou empresarial (6 Mbps, 8 Mbps, 10 Mbps).</li>
              <li>Preencha os seus dados: Nome, Telefone, E‑mail.</li>
              <li>Pague com Multicaixa Express ou outro método disponível.</li>
              <li>A ativação será comunicada após confirmação do pagamento.</li>
              <li><strong>Importante:</strong> Estes planos requerem dados pessoais para ativação e suporte.</li>
            </ul>
            <div class="howto-plan-list">
              <div class="plan-item"><span>6 Mbps</span><strong>27.500 Kz</strong></div>
              <div class="plan-item"><span>8 Mbps</span><strong>32.500 Kz</strong></div>
              <div class="plan-item"><span>10 Mbps</span><strong>35.750 Kz</strong></div>
            </div>
          </div>
        </div>

        <!-- info-banner removido conforme solicitado -->
      </div>
    </div>
  </div>
@endsection
