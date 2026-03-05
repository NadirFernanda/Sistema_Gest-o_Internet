@extends('layouts.app')

@section('title', 'Quero ser revendedor')

@section('content')
  <div class="container--880 reseller-page">
    <div class="reseller-layout">
      <section class="reseller-intro">
        <span class="reseller-badge">Programa de Revenda</span>
        <h1 class="reseller-title">Quero ser revendedor AngolaWiFi</h1>
        <p class="reseller-lead">
          Torne-se parceiro oficial AngolaWiFi e ofereça internet de qualidade aos seus clientes
          com margens atrativas e suporte dedicado.
        </p>

        <ul class="reseller-points">
          <li>Descontos progressivos por volume de compra.</li>
          <li>Acesso a códigos em formato digital para revenda rápida.</li>
          <li>Suporte técnico e comercial da equipa AngolaWiFi.</li>
        </ul>
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

          <div class="reseller-fixed-message">
            <p class="reseller-fixed-title">Mensagem a enviar</p>
            <p class="reseller-fixed-body">
Saudações prezados,
Venho pelo intermédio deste manifestar o interesse para ser agente revendedor do serviço AngolaWiFi.
            </p>
          </div>

          <div class="reseller-form-actions">
            <button type="submit" class="btn-primary">Enviar Pedido</button>
          </div>
        </form>
      </section>
    </div>
  </div>
@endsection
