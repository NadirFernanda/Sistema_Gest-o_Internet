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
          <a href="/quem-somos" class="store-link store-dropdown-item" role="menuitem">Quem Somos</a>
          <a href="/como-comprar" class="store-link store-dropdown-item" role="menuitem">Como Comprar</a>
          <a href="{{ route('reseller.apply') }}" class="store-link store-dropdown-item" role="menuitem">Quero ser revendedor</a>
        </div>
      </div>

      <a href="{{ route('equipment.index') }}" class="{{ request()->is('equipamentos*') ? 'store-link active' : 'store-link' }}">Equipamentos</a>
      <a href="/minha-conta" class="{{ request()->is('minha-conta') ? 'store-link active' : 'store-link' }}">A minha conta</a>

      @auth
      <div class="store-dropdown">
        <a href="/admin" class="{{ request()->is('admin*') ? 'store-link active store-link--muted' : 'store-link store-link--muted' }}" aria-haspopup="true" aria-expanded="false">Administração ▾</a>
        <div class="store-dropdown-menu" role="menu" aria-hidden="true">
          <a href="/admin/recargas" class="store-link store-dropdown-item" role="menuitem">Gestão de recargas</a>
          <a href="/admin/relatorios" class="store-link store-dropdown-item" role="menuitem">Relatórios</a>
          <a href="{{ route('admin.equipment.products.index') }}" class="store-link store-dropdown-item" role="menuitem">Produtos</a>
          <a href="{{ route('admin.equipment.orders.index') }}" class="store-link store-dropdown-item" role="menuitem">Encomendas</a>
        </div>
      </div>
      @endauth
      </nav>

      <div class="store-actions">
        <div class="search-wrapper">
          <input id="store-search-input" class="search-input" type="search" placeholder="Pesquisar planos, ex.: 10 Mbps" aria-label="Pesquisar planos">
          <div id="store-search-results" class="search-results" role="listbox" aria-hidden="true"></div>
        </div>
        <a href="/{{ request()->is('/') ? '#planos' : '#planos' }}" class="store-cta" aria-label="Ver planos individuais e começar a comprar">Ver planos</a>
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

  // Search behaviour
  var input = document.getElementById('store-search-input');
  var results = document.getElementById('store-search-results');
  var debounceTimer = null;

  function renderResults(items) {
    results.innerHTML = '';
    if (!items || items.length === 0) {
      results.setAttribute('aria-hidden','true');
      return;
    }
    items.forEach(function(it){
      var el = document.createElement('a');
      el.href = '/planos/' + it.id;
      el.className = 'search-result-item';
      el.setAttribute('role','option');
      el.innerHTML = '<div class="res-title">' + (it.name || '') + '</div>' + '<div class="res-sub">' + (it.description ? (it.description.substring(0,80)) : '') + '</div>';
      results.appendChild(el);
    });
    results.setAttribute('aria-hidden','false');
  }

  function doSearch(q) {
    if (!q || q.trim().length < 2) { renderResults([]); return; }
    fetch('/sg/plans?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(json){ renderResults(json.data || []); })
      .catch(function(){ renderResults([]); });
  }

  if (input) {
    input.addEventListener('input', function(e){
      clearTimeout(debounceTimer);
      var q = String(e.target.value || '');
      debounceTimer = setTimeout(function(){ doSearch(q); }, 250);
    });

    // hide results when clicking outside
    document.addEventListener('click', function(ev){
      if (!ev.target.closest('.search-wrapper')) {
        results.innerHTML = '';
        results.setAttribute('aria-hidden','true');
      }
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
