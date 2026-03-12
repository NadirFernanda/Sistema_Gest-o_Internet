@extends('layouts.app')

@section('content')
  <div class="page-hero">
    <div class="container">
      <span class="page-hero__eyebrow">Guia de Compra</span>
      <h1 class="page-hero__title">Como Comprar</h1>
      <p class="page-hero__desc">Passo a passo simples para escolher um plano, pagar e começar a navegar com a AngolaWiFi.</p>
    </div>
  </div>

  <div class="page-body">
    <div class="container">
      <div class="howto-steps">
        <div class="howto-step">
          <div class="step-badge">1</div>
          <div class="step-body">
            <h4>Planos Individuais – Internet por Hotspot</h4>
            <ul class="steps-list">
              <li>Seleccione a rede AngolaWiFi num dos pontos de cobertura</li>
              <li>Acesse a loja online clicando no link: <a href="https://www.angolawifi.ao" target="_blank">www.angolawifi.ao</a> ou pode fazé-lo por inter do browser;</li>
              <li>Escolha o Seu Plano Ideal;</li>
              <li>Clique em finalizar compra;</li>
              <li>Preenche os seus dados;</li>
              <li>Escolha a opções de pagamento (multicaixa expresso ou outra disponivel no sistema); e,</li>
              <li>Finalize a compra clicando em escolher a forma de pagamento</li>
            </ul>
            <p><strong>Nota:</strong> Após o pagamento, o código de recarga é exibido imediatamente na página e também fica disponível no e-mail e whatsapp fornecido.</p>
            <h5>Como activar?</h5>
            <ul class="steps-list">
              <li>Certifique-se de que esteja conectado a rede AngolaWiFi num dos pontos de cobertura;</li>
              <li>Acesse o link: www.a.com;</li>
              <li>Introduza o codigo voucher;</li>
              <li>Clique em recarregar.</li>
            </ul>
            <p><strong>Obs.:</strong> O sistema irá processar a recarga em 20 segundos</p>
          </div>
        </div>

        <div class="howto-step">
          <div class="step-badge">2</div>
          <div class="step-body">
            <h4>Planos Família, Empresa e Institucional</h4>
            <ul class="steps-list">
              <li>Acesse a loja através do link <a href="https://www.angolawifi.ao" target="_blank">www.angolawifi.ao</a>;</li>
              <li>Escolha o Seu Plano;</li>
              <li>Clique em finalizar compra;</li>
              <li>Preenche os seus dados;</li>
              <li>Escolha a opções de pagamento (multicaixa expresso ou outra disponivel no sistema); e,</li>
              <li>Finalize a compra clicando em escolher a forma de pagamento</li>
            </ul>
            <p><strong>Nota 1:</strong> Após o pagamento, o receberá uma notificação no e-mail cadastrado;</p>
            <p><strong>Nota 2:</strong> As empresas e famílias são obrigados a cadastrarem contas</p>
          </div>
        </div>

        <!-- info-banner removido conforme solicitado -->
      </div>
    </div>
  </div>
@endsection
