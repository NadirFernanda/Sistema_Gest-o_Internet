@extends('layouts.app')

@section('content')
  <div class="page-hero">
    <div class="container">
      <span class="page-hero__eyebrow">A Nossa História</span>
      <h1 class="page-hero__title">Quem Somos</h1>
      <p class="page-hero__desc">A LuandaWiFi é uma equipa angolana dedicada a fornecer internet rápida, acessível e confiável para todos — de estudantes a empresas.</p>
    </div>
  </div>

  <div class="page-body">
    <div class="container">

      <div class="info-grid">
        <div class="info-card">
          <div class="info-card__icon">🏢</div>
          <h3>Sobre nós</h3>
          <p>Oferecemos recargas e planos fáceis de usar, sem complicações. A nossa prioridade é tornar a tecnologia acessível para famílias, estudantes e negócios.</p>
          <p class="plan-note">Estamos comprometidos com suporte local, segurança e clareza em todas as etapas do serviço.</p>
        </div>

        <div class="info-card">
          <div class="info-card__icon">🎯</div>
          <h3>Missão</h3>
          <p>Levar internet de qualidade a preços justos, com soluções simples e automáticas que facilitam o acesso digital de todos os angolanos.</p>
        </div>

        <div class="info-card">
          <div class="info-card__icon">🌍</div>
          <h3>Visão &amp; Valores</h3>
          <p class="plan-note">Ser reconhecida como a plataforma líder em conectividade digital em Angola, promovendo inclusão tecnológica e aproximando pessoas e negócios através da internet.</p>
          <ul>
            <li><strong>Acessibilidade:</strong> tornar a internet possível para todos, sem barreiras.</li>
            <li><strong>Confiabilidade:</strong> serviços seguros, estáveis e de confiança.</li>
            <li><strong>Inovação:</strong> simplificar a vida dos nossos clientes com tecnologia.</li>
            <li><strong>Proximidade:</strong> suporte local atento às necessidades do cliente.</li>
            <li><strong>Transparência:</strong> agir com clareza e honestidade.</li>
          </ul>
        </div>
      </div>

      <div class="info-banner">
        <p>Obrigado por confiar na <span class="brand-strong">{{ config('app.name', 'Loja') }}</span> — juntos conectamos Angola.</p>
      </div>

    </div>
  </div>
@endsection
