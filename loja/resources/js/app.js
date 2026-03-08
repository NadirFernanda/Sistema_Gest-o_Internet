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
