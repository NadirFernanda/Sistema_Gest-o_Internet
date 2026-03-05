@extends('layouts.app')

@section('content')
  {{-- Hero simples temporário (carrossel desativado) --}}
  <section class="planos-section">
    <div class="container">
      <h1>Internet rápida, simples e perto de si</h1>
      <p class="lead">Escolha um dos nossos planos individuais, familiares ou empresariais e comece a navegar em poucos minutos.</p>
      <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
        <a href="#planos" class="btn-primary">Ver Planos Individuais</a>
        <a href="#planos-familia-empresarial" class="btn-primary" style="background:var(--success)">Planos Família & Empresa</a>
      </div>
    </div>
  </section>
  
  

  <section class="planos-section" id="planos">
    <div class="container">
      <h2>Planos Individuais</h2>
      <p class="lead">Os planos individuais garantem maior autonomia, permitindo que qualquer utilizador compre o seu código de acesso e navegue de forma independente em qualquer um dos vários pontos da rede Luanda WiFi.</p>

      <div class="plans-grid" aria-live="polite">
        @forelse ($individualPlans as $plan)
          <div class="plan-card plan-card--individual plan-card--{{ $plan['id'] }}">
            @if (!empty($plan['image']))
              <div class="plan-thumb">
                <img src="{{ $plan['image'] }}" alt="{{ $plan['name'] }}" loading="lazy">
              </div>
            @endif
            <h3>{{ $plan['name'] }}</h3>
            <div class="price">{{ number_format($plan['price_kwanza'], 0, ',', '.') }} <small>AOA</small></div>
            <p class="desc">
              {{ $plan['duration_label'] }} &mdash; {{ $plan['speed'] }}
            </p>
            @if (!empty($plan['description']))
              <p class="desc">{{ $plan['description'] }}</p>
            @endif
            <div class="plan-actions">
              <a class="btn-primary" href="{{ route('store.checkout', ['plan' => $plan['id']]) }}">Comprar</a>
            </div>
          </div>
        @empty
          <div class="plan-card empty">
            <p>Os planos individuais serão exibidos aqui após cadastro no painel administrativo.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="planos-section" id="planos-familia-empresarial">
    <div class="container">
      <h2>Planos Familiares & Empresariais</h2>
      <p class="lead">Soluções para famílias e empresas — planos com duração de 30 dias, partilháveis ou com SLA dedicado conforme necessidade.</p>

      <div class="plans-grid" id="family-business-plans" aria-live="polite">
        <div class="plan-card empty">
          <p>Carregando planos...</p>
        </div>
      </div>
    </div>
  </section>


