@extends('layouts.app')

@section('content')
  {{-- Hero (improved; uses fallback styles if Tailwind not available) --}}
  <section class="mb-8">
    <div class="container">
      <div class="hero-carousel" aria-roledescription="carousel">
      <div class="carousel-track">
        <div class="carousel-slide hero-1"></div>

        <div class="carousel-slide hero-2"></div>

        <div class="carousel-slide hero-3"></div>
      </div>

      <!-- overlay cards (positioned relative to hero-carousel, outside the track to avoid clipping) -->
      <div class="hero-cards" aria-hidden="false">
        <div class="slide-card" data-index="0">
          <div class="container">
            <h1>Internet Residencial — até 100 Mbps</h1>
            <p>Planos para famílias, streaming e gaming sem interrupções. Velocidades reais e preços claros.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
              <a href="#planos" class="btn-primary">Ver Planos</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Saiba Mais</a>
            </div>
          </div>
        </div>

        <div class="slide-card" data-index="1">
          <div class="container">
            <h1>Internet Empresarial — conexões estáveis</h1>
            <p>Soluções dedicadas para empresas com SLA e suporte técnico prioritário. Escalável e segura.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
              <a href="#planos" class="btn-primary">Ver Soluções</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Contactar Vendas</a>
            </div>
          </div>
        </div>

        <div class="slide-card" data-index="2">
          <div class="container">
            <h1>Instalação Rápida & Suporte Local</h1>
            <p>Agende instalação em 24–48h e conte com assistência técnica local sempre que precisar.</p>
            <div style="margin-top:1rem; display:flex; gap:0.5rem; flex-wrap:wrap">
              <a href="#planos" class="btn-primary">Agendar Instalação</a>
              <a href="/como-comprar" class="btn-primary" style="background:var(--success)">Fale Connosco</a>
            </div>
          </div>
        </div>
      </div>

      <!-- arrows removed per preference; swipe and indicators handle navigation -->

      <div class="carousel-indicators" role="tablist">
        <button data-index="0" aria-label="Slide 1" class="active"></button>
        <button data-index="1" aria-label="Slide 2"></button>
        <button data-index="2" aria-label="Slide 3"></button>
      </div>
      </div>
    </div>
  </section>
  <script>
    (function(){
      var carousel = document.querySelector('.hero-carousel');
      if (!carousel) return;
      var track = carousel.querySelector('.carousel-track');
      var slides = Array.prototype.slice.call(carousel.querySelectorAll('.carousel-slide'));
      var cards = Array.prototype.slice.call(carousel.querySelectorAll('.hero-cards .slide-card'));
      // no arrow controls; use indicators and swipe
      var indicators = Array.prototype.slice.call(carousel.querySelectorAll('.carousel-indicators button'));
      var index = 0;
      var slideCount = slides.length;
      var interval = null;

      function goTo(i){
        index = (i + slideCount) % slideCount;
        var x = -(index * 100);
        track.style.transform = 'translateX(' + x + '%)';
        indicators.forEach(function(btn){ btn.classList.remove('active'); });
        if (indicators[index]) indicators[index].classList.add('active');
        // manage overlay cards visibility
        if (cards && cards.length) {
          cards.forEach(function(c){ c.classList.remove('visible'); });
          if (cards[index]) cards[index].classList.add('visible');
        }
      }

      function nextSlide(){ goTo(index + 1); }
      function prevSlide(){ goTo(index - 1); }

      indicators.forEach(function(btn){
        btn.addEventListener('click', function(){ var i = Number(btn.getAttribute('data-index')||0); goTo(i); restart(); });
      });

      // touch / swipe support
      var touchStartX = null;
      carousel.addEventListener('touchstart', function(e){
        touchStartX = e.touches && e.touches[0] ? e.touches[0].clientX : null;
      }, { passive: true });
      carousel.addEventListener('touchend', function(e){
        if (touchStartX === null) return;
        var touchEndX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : null;
        if (touchEndX === null) return;
        var dx = touchEndX - touchStartX;
        if (Math.abs(dx) > 40) {
          if (dx < 0) { nextSlide(); } else { prevSlide(); }
          restart();
        }
        touchStartX = null;
      });

      function start(){ interval = setInterval(nextSlide, 5000); }
      function stop(){ if (interval) { clearInterval(interval); interval = null; } }
      function restart(){ stop(); start(); }

      carousel.addEventListener('mouseenter', stop);
      carousel.addEventListener('mouseleave', start);

      // init
      goTo(0);
      start();
    })();
  </script>
  
  

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

  <script>
    (function(){
      var container = document.getElementById('family-business-plans');
      if (!container) return;

      function renderPlan(plan){
        var el = document.createElement('div');
        el.className = 'plan-card';
        var price = plan.preco || plan.price || plan.amount || '';
        var priceHtml = price ? ('<div class="price'> + (price) + '</div>') : '';
        el.innerHTML = '<h3>' + (plan.name || 'Plano') + '</h3>' +
                       '<div class="price">' + (price ? (price + ' <small>AOA</small>') : '') + '</div>' +
                       '<p class="desc">' + (plan.description || plan.desc || '') + '</p>' +
                       '<div class="plan-actions">' +
                         '<a class="btn-primary" href="/store/checkout/' + (plan.id || '') + '">Comprar</a>' +
                         '<a class="btn-ghost" href="/planos/' + (plan.id || '') + '">Detalhes</a>' +
                       '</div>';
        return el;
      }

      function normalizeCategory(cat){
        if (!cat) return '';
        return String(cat).toLowerCase();
      }

      fetch('/sg/plans', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(json){
          var data = json.data || json || [];
          var allowed = ['familiares','familia','empresariais','empresarial'];
          var plans = data.filter(function(p){
            var cat = '';
            try { cat = normalizeCategory((p.metadata && p.metadata.category) || p.category || ''); } catch(e) { cat = '' }
            return allowed.indexOf(cat) !== -1;
          });
          container.innerHTML = '';
          if (!plans.length) {
            var empty = document.createElement('div'); empty.className = 'plan-card empty'; empty.innerHTML = '<p>Nenhum plano encontrado.</p>'; container.appendChild(empty); return;
          }
          plans.forEach(function(p){ container.appendChild(renderPlan(p)); });
        })
        .catch(function(){ container.innerHTML = '<div class="plan-card empty"><p>Erro ao carregar planos.</p></div>'; });
    })();
  </script>


