@extends('layouts.app')

@section('content')
  <section class="planos-section" role="main" aria-label="Quem Somos" style="padding-top:2rem;padding-bottom:2rem;">
      <div class="howto-hero">
        <h2>Quem Somos</h2>
        <p>A LuandaWiFi é uma equipa angolana dedicada a fornecer internet rápida, acessível e confiável para todos — de estudantes a empresas.</p>
      </div>

      <div class="info-grid">
        <div class="info-card">
          <h3>Sobre nós</h3>
          <p>Oferecemos recargas e planos fáceis de usar, sem complicações. A nossa prioridade é tornar a tecnologia acessível para famílias, estudantes e negócios.</p>
          <p class="plan-note">Estamos comprometidos com suporte local, segurança e clareza em todas as etapas do serviço.</p>
        </div>

        <div class="info-card">
          <h3>Missão</h3>
          <p>Levar internet de qualidade a preços justos, com soluções simples e automáticas que facilitam o acesso digital de todos os angolanos.</p>
        </div>

        <div class="info-card">
          <h3>Visão & Valores</h3>
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
  
  </section>
@endsection
