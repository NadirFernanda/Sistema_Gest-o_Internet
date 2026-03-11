<header class="store-header">
  <div class="store-header-inner">
    <a href="/" class="store-brand">
      <img src="{{ asset('img/logo2.jpeg') }}" alt="{{ config('app.name', 'Loja') }}" class="store-logo">
      <span class="store-title">{{ config('app.name', 'Loja') }}</span>
    </a>

    <div class="store-right">
      <nav class="store-nav" aria-label="Main navigation">
      <a href="/" class="{{ request()->is('/') ? 'store-link active' : 'store-link' }}">Início</a>

      @php
        $sobreActive = request()->is('quem-somos') || request()->is('como-comprar') || request()->is('quero-ser-revendedor');
      @endphp
      <div class="store-dropdown">
        <a href="/quem-somos" class="{{ $sobreActive ? 'store-link active' : 'store-link' }}" aria-haspopup="true" aria-expanded="false">Sobre ▾</a>
        <div class="store-dropdown-menu" role="menu" aria-hidden="true">
          <div class="dropdown-search-wrapper" style="padding: 12px 16px 8px 16px;">
            <input id="dropdown-search-input" class="search-input" type="search" autocomplete="off" name="dropdown-search" placeholder="Pesquisar planos…" aria-label="Pesquisar planos" style="width: 100%; max-width: 260px;">
          </div>
          <a href="/quem-somos" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">🏢</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Quem Somos</span>
              <span class="store-dropdown-item__desc">Conheça a empresa, missão e a rede AngolaWiFi</span>
            </span>
          </a>
          <a href="/como-comprar" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">🛒</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Como Comprar</span>
              <span class="store-dropdown-item__desc">Passo a passo para adquirir o seu plano WiFi</span>
            </span>
          </a>
          <a href="{{ route('reseller.apply') }}" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">🤝</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Ser Revendedor</span>
              <span class="store-dropdown-item__desc">Torne-se parceiro e revenda os nossos serviços</span>
            </span>
          </a>
        </div>
      </div>

      <a href="{{ route('equipment.index') }}" class="{{ request()->is('equipamentos*') ? 'store-link active' : 'store-link' }}">Equipamentos</a>
      <a href="/minha-conta" class="{{ request()->is('minha-conta') ? 'store-link active' : 'store-link' }}">A minha conta</a>

      @auth
      <div class="store-dropdown">
        <a href="/admin" class="{{ request()->is('admin*') ? 'store-link active store-link--muted' : 'store-link store-link--muted' }}" aria-haspopup="true" aria-expanded="false">Administração ▾</a>
        <div class="store-dropdown-menu" role="menu" aria-hidden="true">
          <a href="/admin/recargas" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">💳</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Gestão de recargas</span>
            </span>
          </a>
          <a href="/admin/relatorios" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">📊</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Relatórios</span>
            </span>
          </a>
          <a href="{{ route('admin.equipment.products.index') }}" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">📦</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Produtos</span>
            </span>
          </a>
          <a href="{{ route('admin.equipment.orders.index') }}" class="store-dropdown-item" role="menuitem">
            <span class="store-dropdown-item__icon">🛍️</span>
            <span class="store-dropdown-item__body">
              <span class="store-dropdown-item__title">Encomendas</span>
            </span>
          </a>
        </div>
      </div>
      @endauth
      </nav>

      <div class="store-actions">
        <a href="/{{ request()->is('/') ? '#planos' : '#planos' }}" class="store-cta" aria-label="Ver planos individuais e começar a comprar">Ver planos</a>
        <a href="/solucoes" class="store-cta" aria-label="Ver soluções empresariais">Ver soluções</a>
        <a href="/agendar-instalacao" class="store-cta" aria-label="Agendar instalação">Agendar instalação</a>
        <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-label="Abrir menu" aria-expanded="false">
          <span class="mobile-menu-icon"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="store-mobile-menu" class="store-mobile-menu">
  <a href="/" class="store-mobile-link">Início</a>
  <a href="/quem-somos" class="store-mobile-link">Quem Somos</a>
  <a href="/como-comprar" class="store-mobile-link">Como Comprar</a>
  <a href="{{ route('reseller.apply') }}" class="store-mobile-link">Quero ser revendedor</a>
  <a href="{{ route('equipment.index') }}" class="store-mobile-link">Equipamentos</a>
  <a href="/minha-conta" class="store-mobile-link">A minha conta</a>
    @auth
    <a href="/admin" class="store-mobile-link">Administração</a>
    <div class="store-mobile-submenu">
      <a href="/admin/recargas" class="store-mobile-link">Gestão de recargas</a>
      <a href="/admin/relatorios" class="store-mobile-link">Relatórios</a>
      <a href="{{ route('admin.equipment.products.index') }}" class="store-mobile-link">Produtos</a>
      <a href="{{ route('admin.equipment.orders.index') }}" class="store-mobile-link">Encomendas</a>
    </div>
    @endauth
  </div>

<script>
(function(){
  var btn = document.getElementById('mobile-menu-toggle');
  var menu = document.getElementById('store-mobile-menu');
  if (btn && menu) {
    btn.addEventListener('click', function(){
      var isOpen = menu.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
  }

  // Search — client-side, reads plan cards already rendered in the DOM
  var input   = document.getElementById('store-search-input');
  var results = document.getElementById('store-search-results');

  function buildIndex() {
    var cards = document.querySelectorAll('.plan-card-modern:not(.family-empty-state)');
    var index = [];
    cards.forEach(function(card) {
      var titleEl = card.querySelector('.plan-title');
      var priceEl = card.querySelector('.plan-price');
      var currEl  = card.querySelector('.plan-currency');
      var descEl  = card.querySelector('.plan-desc');
      var featEls = card.querySelectorAll('.plan-feature');
      var link    = card.querySelector('a[href]');
      if (!titleEl) return;
      var features = Array.from(featEls).map(function(f){ return f.textContent.trim(); }).join(' · ');
      index.push({
        title:   titleEl.textContent.trim(),
        price:   priceEl ? (priceEl.textContent.trim() + (currEl ? ' ' + currEl.textContent.trim() : '')) : '',
        desc:    descEl  ? descEl.textContent.trim() : features,
        href:    link    ? link.getAttribute('href') : '#planos',
        section: card.closest('.planos-section--family') ? 'Familiar'
               : card.closest('.planos-section--company') ? 'Empresarial'
               : card.closest('.planos-section--institutional') ? 'Institucional'
               : 'Individual',
      });
    });
    return index;
  }

  function doSearch(q) {
    results.innerHTML = '';
    if (!q || q.trim().length < 2) { hideResults(); return; }
    var needle = q.trim().toLowerCase();
    var index  = buildIndex();
    var hits   = index.filter(function(it){
      return it.title.toLowerCase().indexOf(needle) !== -1
          || it.desc.toLowerCase().indexOf(needle)  !== -1
          || it.price.toLowerCase().indexOf(needle) !== -1;
    });
    if (!hits.length) { hideResults(); return; }
    results.style.display = 'block';
    results.setAttribute('aria-hidden','false');
    hits.forEach(function(it){
      var el = document.createElement('a');
      el.href = it.href;
      el.className = 'search-result-item';
      el.setAttribute('role','option');
      el.innerHTML = '<div class="res-title">' + it.title + '</div>'
                   + '<div class="res-sub">' + (it.price ? it.price + ' · ' : '') + it.section + '</div>';
      el.addEventListener('click', function(){ results.innerHTML = ''; results.setAttribute('aria-hidden','true'); });
      results.appendChild(el);
    });
    results.setAttribute('aria-hidden','false');
  }

  function hideResults() {
    results.style.display = 'none';
    results.setAttribute('aria-hidden','true');
    results.innerHTML = '';
  }

  if (input) {
    var userTyped = false;
    input.addEventListener('keydown', function() { userTyped = true; });
    input.addEventListener('blur',    function() { userTyped = false; });
    input.addEventListener('input', function(e){
      if (!userTyped) { hideResults(); return; }
      doSearch(String(e.target.value || ''));
    });
    document.addEventListener('click', function(ev){
      if (!ev.target.closest('.search-wrapper')) hideResults();
    });
  }

  // Dropdown aria toggle for Administração (click to open on small devices)
  var dropdownToggles = document.querySelectorAll('.store-dropdown > a');
  dropdownToggles.forEach(function(toggle){
    var menu = toggle.parentElement.querySelector('.store-dropdown-menu');
    if (!menu) return;
    toggle.addEventListener('click', function(ev){
      // on small screens, toggle the menu instead of following link
      if (window.innerWidth < 768) {
        ev.preventDefault();
        var visible = menu.style.display === 'block';
        menu.style.display = visible ? 'none' : 'block';
        toggle.setAttribute('aria-expanded', (!visible).toString());
        menu.setAttribute('aria-hidden', visible ? 'true' : 'false');
      }
    });
    // close when clicking outside
    document.addEventListener('click', function(ev){
      if (!ev.target.closest('.store-dropdown')) {
        menu.style.display = 'none';
        toggle.setAttribute('aria-expanded','false');
        menu.setAttribute('aria-hidden','true');
      }
    });
  });

})();
</script>
</header>
