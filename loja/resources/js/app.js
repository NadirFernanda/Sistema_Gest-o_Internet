import './bootstrap';

// Escape HTML to prevent XSS when rendering server data into the DOM
function esc(str) {
	return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// Render one family/business plan card (mirrors the Blade template CSS classes)
function renderFamilyCard(plan) {
	var isCompany = (plan.name || '').toLowerCase().indexOf('empresa') !== -1;
	var emoji = isCompany ? '\uD83C\uDFE2' : '\uD83C\uDFE0'; // 🏢 or 🏠
	var body = '';
	if (plan.preco) {
		body += '<div class="plan-price-row"><span class="plan-price">'
			+ Number(plan.preco).toLocaleString('pt-PT')
			+ '</span><span class="plan-currency">Kz</span></div>';
	}
	if (plan.ciclo) {
		body += '<div class="plan-features"><span class="plan-feature"><strong>'
			+ esc(plan.ciclo) + ' dias</strong></span></div>';
	}
	if (plan.description) {
		body += '<p class="plan-desc">' + esc(plan.description) + '</p>';
	}
	return '<div class="plan-card-modern">'
		+ '<div class="plan-card-modern-inner">'
		+ '<div class="plan-card-modern-header">'
		+ '<span class="plan-emoji" aria-hidden="true">' + emoji + '</span>'
		+ '<h3 class="plan-title">' + esc(plan.name || 'Plano') + '</h3>'
		+ '</div>'
		+ '<div class="plan-card-modern-body">' + body + '</div>'
		+ '<div class="plan-card-modern-footer">'
		+ '<a class="btn-modern" href="/quero-ser-revendedor">Solicitar Plano</a>'
		+ '</div></div></div>';
}

// Render one equipment card from SG catalog
function renderEquipmentCard(item) {
	var imgHtml = item.imagem_url
		? '<img src="' + esc(item.imagem_url) + '" alt="' + esc(item.nome) + '" class="product-img">'
		: '<div class="product-placeholder">\uD83D\uDCE6</div>'; // 📦
	var stockBadge = item.em_stock ? '' : '<p class="product-stock-warn">Sem stock</p>';
	var catBadge   = item.categoria ? '<span class="plan-feature" style="margin-bottom:0.5rem;">' + esc(item.categoria) + '</span>' : '';
	var desc       = item.descricao ? '<p class="plan-desc">' + esc(item.descricao) + '</p>' : '';
	var precoHtml  = (item.preco && item.preco > 0)
		? '<div class="plan-price-row"><span class="plan-price">' + Number(item.preco).toLocaleString('pt-PT') + '</span><span class="plan-currency">Kz</span></div>'
		: '<div class="plan-price-row"><span class="plan-feature" style="font-style:italic;">Consultar pre\u00e7o</span></div>';
	return '<div class="plan-card-modern">'
		+ '<div class="plan-card-modern-inner">'
		+ imgHtml
		+ '<div class="plan-card-modern-header">'
		+ '<h3 class="plan-title">' + esc(item.nome) + '</h3>'
		+ '</div>'
		+ catBadge
		+ '<div class="plan-card-modern-body">'
		+ precoHtml
		+ desc
		+ stockBadge
		+ '</div>'
		+ '<div class="product-actions">'
		+ (item.em_stock
			? '<button type="button" class="btn-modern" onclick="sgAddToCart(' + item.id + ', this)">&#x1F6D2; Adicionar</button>'
			: '<span class="btn-modern" style="opacity:0.45;cursor:not-allowed;">Sem stock</span>')
		+ '</div>'
		+ '</div></div>';
}

var EQUIPMENT_EMPTY_HTML =
	'<div class="family-empty-state" style="grid-column:1/-1">'
	+ '<div class="family-empty-state__icon">\uD83D\uDCE6</div>'
	+ '<h3 class="family-empty-state__title">Sem produtos dispon\u00edveis</h3>'
	+ '<p class="family-empty-state__text">O cat\u00e1logo de equipamentos ser\u00e1 actualizado em breve. Contacte-nos para consultar disponibilidade.</p>'
	+ '</div>';

var EQUIPMENT_ERROR_HTML =
	'<div class="family-empty-state" style="grid-column:1/-1">'
	+ '<div class="family-empty-state__icon">\u26A0\uFE0F</div>'
	+ '<h3 class="family-empty-state__title">Cat\u00e1logo indispon\u00edvel</h3>'
	+ '<p class="family-empty-state__text">N\u00e3o foi poss\u00edvel carregar os equipamentos. Tente novamente mais tarde.</p>'
	+ '</div>';

// Add equipment from SG to loja cart (POST to loja cart endpoint)
window.sgAddToCart = function(productId, btn) {
	if (!btn) return;
	btn.disabled = true;
	btn.textContent = 'A adicionar\u2026';
	var form = document.createElement('form');
	form.method = 'POST';
	form.action = '/carrinho/adicionar';
	var csrf = document.querySelector('meta[name="csrf-token"]');
	if (csrf) {
		var t = document.createElement('input'); t.type = 'hidden'; t.name = '_token'; t.value = csrf.content; form.appendChild(t);
	}
	var pid = document.createElement('input'); pid.type = 'hidden'; pid.name = 'product_id'; pid.value = productId; form.appendChild(pid);
	document.body.appendChild(form);
	form.submit();
};

var FAMILY_EMPTY_HTML =
	'<div class="family-empty-state">'
	+ '<div class="family-empty-state__icon">\uD83D\uDCF6</div>'  // 📶
	+ '<h3 class="family-empty-state__title">Planos sob consulta</h3>'
	+ '<p class="family-empty-state__text">Os nossos planos familiares e empresariais s\u00e3o configurados de acordo com as necessidades de cada cliente. Contacte-nos para obter uma proposta personalizada.</p>'
	+ '<a href="/quero-ser-revendedor" class="btn-cta">Falar com a equipa &rarr;</a>'
	+ '</div>';

var FAMILY_ERROR_HTML =
	'<div class="family-empty-state">'
	+ '<div class="family-empty-state__icon">\u26A0\uFE0F</div>'  // ⚠️
	+ '<h3 class="family-empty-state__title">Servi\u00e7o temporariamente indispon\u00edvel</h3>'
	+ '<p class="family-empty-state__text">N\u00e3o foi poss\u00edvel carregar os planos de momento. Por favor tente mais tarde ou contacte-nos directamente.</p>'
	+ '<a href="/quero-ser-revendedor" class="btn-cta">Falar com a equipa &rarr;</a>'
	+ '</div>';

document.addEventListener('DOMContentLoaded', () => {

	// ---- Async family/business plans loader ----
	// Loads AFTER the page and CSS are fully applied — zero FOUC.
	var familyGrid = document.getElementById('family-plans-grid');
	if (familyGrid) {
		fetch('/sg/plan-templates', { credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(json) {
				var plans = json.data || [];
				familyGrid.innerHTML = plans.length
					? plans.map(renderFamilyCard).join('')
					: FAMILY_EMPTY_HTML;
			})
			.catch(function() { familyGrid.innerHTML = FAMILY_ERROR_HTML; });
	}

	// ---- Async equipment catalog loader ----
	// Loads equipment from SG after page is ready — same zero-FOUC pattern.
	var equipGrid = document.getElementById('sg-equipment-grid');
	if (equipGrid) {
		fetch('/sg/equipment-catalog', { credentials: 'same-origin' })
			.then(function(r) { return r.json(); })
			.then(function(json) {
				var items = json.data || [];
				equipGrid.innerHTML = items.length
					? items.map(renderEquipmentCard).join('')
					: EQUIPMENT_EMPTY_HTML;
			})
			.catch(function() { equipGrid.innerHTML = EQUIPMENT_ERROR_HTML; });
	}
	// Hero carousel
	var heroEl = document.getElementById('hero');
	if (heroEl) {
		var heroTrack  = heroEl.querySelector('#heroTrack');
		var heroDots   = heroEl.querySelectorAll('.hero__dot');
		var heroPrev   = heroEl.querySelector('.hero__arrow--prev');
		var heroNext   = heroEl.querySelector('.hero__arrow--next');
		var heroCount  = heroEl.querySelectorAll('.hero__slide').length;
		var heroCur    = 0;
		var heroTid    = null;

		function heroGoTo(n) {
			heroCur = ((n % heroCount) + heroCount) % heroCount;
			heroTrack.style.transform = 'translateX(' + (-heroCur * 100) + '%)';
			heroDots.forEach(function(d, i) {
				d.classList.toggle('is-active', i === heroCur);
				d.setAttribute('aria-selected', i === heroCur ? 'true' : 'false');
			});
		}

		function heroPlay()  { heroTid = window.setInterval(function() { heroGoTo(heroCur + 1); }, 5000); }
		function heroPause() { window.clearInterval(heroTid); heroTid = null; }
		function heroReset() { heroPause(); heroPlay(); }

		if (heroPrev) heroPrev.addEventListener('click', function() { heroGoTo(heroCur - 1); heroReset(); });
		if (heroNext) heroNext.addEventListener('click', function() { heroGoTo(heroCur + 1); heroReset(); });

		heroDots.forEach(function(d) {
			d.addEventListener('click', function() { heroGoTo(parseInt(d.dataset.slide, 10)); heroReset(); });
		});

		heroEl.addEventListener('mouseenter', heroPause);
		heroEl.addEventListener('mouseleave', heroPlay);

		var heroSwipeX = null;
		heroEl.addEventListener('touchstart', function(e) { heroSwipeX = e.touches[0].clientX; }, { passive: true });
		heroEl.addEventListener('touchend', function(e) {
			if (heroSwipeX === null) return;
			var dx = e.changedTouches[0].clientX - heroSwipeX;
			if (Math.abs(dx) > 40) { dx < 0 ? heroGoTo(heroCur + 1) : heroGoTo(heroCur - 1); heroReset(); }
			heroSwipeX = null;
		});

		heroGoTo(0);
		heroPlay();
	}
});
