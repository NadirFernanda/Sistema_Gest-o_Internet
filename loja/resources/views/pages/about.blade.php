@extends('layouts.app')

@section('content')
  <div class="page-hero">
    <div class="container">
      <span class="page-hero__eyebrow">Quem Somos – AngolaWiFi</span>
      <h1 class="page-hero__title">AngolaWiFi</h1>
      <p class="page-hero__desc">A AngolaWiFi é uma plataforma digital inovadora que conecta pessoas, famílias e empresas à internet de forma simples, segura e confiável. Atuamos na revenda de serviços por hotspot, fibra e micro-ondas, além da comercialização de equipamentos conexos. Nosso foco é oferecer soluções acessíveis, suporte local eficiente e experiências digitais de qualidade, democratizando a conectividade em Angola.</p>
    </div>
  </div>

  <div class="page-body">
    <div class="container">
      <div class="info-grid">
        <div class="info-card">
          <div class="info-card__icon">
            <!-- Globo SVG -->
            <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><ellipse cx="12" cy="12" rx="6" ry="10" stroke="currentColor" stroke-width="2" fill="none"/><ellipse cx="12" cy="12" rx="10" ry="6" stroke="currentColor" stroke-width="2" fill="none"/></svg>
          </div>
          <h3>Visão</h3>
          <p>Ser a maior e mais confiável plataforma de internet de Angola, reconhecida pela excelência na distribuição, inovação tecnológica e impacto positivo na vida dos usuários, transformando a conectividade em uma experiência acessível e de qualidade para todos.</p>
        </div>
        <div class="info-card">
          <div class="info-card__icon">
            <!-- Estrela SVG -->
            <svg width="32" height="32" fill="currentColor" viewBox="0 0 24 24"><polygon points="12,2 15,9 22,9 17,14 18,21 12,17 6,21 7,14 2,9 9,9"/></svg>
          </div>
          <h3>Valores</h3>
          <ul>
            <li><strong>Conectividade de qualidade</strong> – Garantimos acesso estável e velocidades reais para todos os clientes.</li>
            <li><strong>Inovação contínua</strong> – Buscamos soluções digitais e tecnológicas que facilitem a vida de usuários e revendedores.</li>
            <li><strong>Transparência e ética</strong> – Preços claros, políticas justas e comunicação aberta com clientes e parceiros.</li>
            <li><strong>Foco no cliente</strong> – Atendimento local, rápido e eficiente, com suporte prioritário sempre que necessário.</li>
            <li><strong>Inclusão digital</strong> – Democratizar o acesso à internet em Angola, alcançando famílias, empresas e comunidades.</li>
            <li><strong>Crescimento colaborativo</strong> – Incentivamos revendedores, parceiros e equipes a crescerem juntos com a plataforma.</li>
          </ul>
        </div>
      </div>
      <div class="info-banner">
        <p>Obrigado por confiar na <span class="brand-strong">{{ config('app.name', 'Loja') }}</span> — juntos conectamos Angola.</p>
      </div>
    </div>
  </div>
@endsection
