@extends('layouts.app')

@section('title', 'Quero ser revendedor')

@section('content')
  <div class="container--880 reseller-page">
    <div class="reseller-layout">
      <section class="reseller-intro">
        <span class="reseller-badge">Programa de Revenda</span>
        <h1 class="reseller-title">Torne-se Parceiro Oficial da AngolaWiFi</h1>
        <p class="reseller-lead">
          Ofereça internet de qualidade aos seus clientes e transforme o seu estabelecimento num ponto
          de acesso Wi-Fi rentável, com margens de lucro atrativas e suporte dedicado da nossa equipa.
        </p>

        <p style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:var(--text-muted);margin:0 0 0.6rem;">Vantagens para os Parceiros AngolaWiFi</p>
        <ul class="reseller-points">
          <li>Descontos progressivos de acordo com o volume de compra de vouchers.</li>
          <li>Códigos de acesso em formato digital, permitindo uma revenda rápida e simples aos seus clientes.</li>
          <li>Suporte técnico e comercial permanente da equipa AngolaWiFi para garantir o bom funcionamento do serviço.</li>
          <li>Modelo de negócio acessível, ideal para lojas, bares, restaurantes, salões, mercados, escolas e outros estabelecimentos.</li>
        </ul>

        <p style="margin:1.1rem 0 0.35rem;font-size:0.88rem;color:var(--text-muted);line-height:1.65;">
          Com a AngolaWiFi, o seu espaço pode tornar-se um ponto estratégico de acesso à internet,
          gerando receitas adicionais todos os dias.
        </p>
        <p style="margin:0;font-size:0.82rem;font-weight:700;color:var(--text-dark);">AngolaWiFi — Internet acessível, oportunidades reais de negócio.</p>
      </section>

      <section class="reseller-form-card">
        <h2 class="reseller-form-title">Formulário de adesão</h2>
        <p class="reseller-form-subtitle">Preencha os seus dados. Campos marcados com * são obrigatórios.</p>

        @if ($errors->any())
          <div class="reseller-errors">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form method="POST" action="{{ route('reseller.apply.submit') }}" class="reseller-form">
          @csrf

          <div class="reseller-form-row">
            <label for="full_name">Nome Completo *</label>
            <input id="full_name" name="full_name" type="text" required value="{{ old('full_name') }}" />
          </div>

          <div class="reseller-form-row">
            <label for="document_number">Nº do BI ou NIF *</label>
            <input id="document_number" name="document_number" type="text" required value="{{ old('document_number') }}" />
          </div>

          <div class="reseller-form-row">
            <label for="address">Morada Completa *</label>
            <input id="address" name="address" type="text" required value="{{ old('address') }}" />
          </div>

          <div class="reseller-form-row">
            <label for="email">E-mail *</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}" />
          </div>

          <div class="reseller-form-row">
            <label for="phone">Telefone / WhatsApp *</label>
            <input id="phone" name="phone" type="text" required value="{{ old('phone') }}" />
          </div>

          <div class="reseller-form-row">
            <label for="installation_location">Local de Instalação Pretendido *</label>
            <input id="installation_location" name="installation_location" type="text" required value="{{ old('installation_location') }}" />
          </div>

          <div class="reseller-form-row">
            <label>Tipo de ligação à internet *</label>
            <div class="reseller-radio-group">
              <label class="reseller-radio-option">
                <input type="radio" name="internet_type" value="own" required
                  {{ old('internet_type') === 'own' ? 'checked' : '' }}>
                <span>Tenho internet própria no local de instalação</span>
              </label>
              <label class="reseller-radio-option">
                <input type="radio" name="internet_type" value="angolawifi"
                  {{ old('internet_type') === 'angolawifi' ? 'checked' : '' }}>
                <span>Necessito de internet fornecida pela AngolaWiFi</span>
              </label>
            </div>
            @error('internet_type')
              <span class="reseller-field-error">{{ $message }}</span>
            @enderror
          </div>

          <div class="reseller-fixed-message">
            <p class="reseller-fixed-title">Mensagem a enviar</p>
            <p class="reseller-fixed-body">Saudações prezados,
Venho pelo intermédio deste manifestar o interesse para ser agente revendedor do serviço AngolaWiFi.</p>
          </div>

          <div class="reseller-form-actions">
            <button type="submit" class="btn-primary">Enviar Pedido</button>
          </div>
        </form>
      </section>
    </div>
  </div>
@endsection
