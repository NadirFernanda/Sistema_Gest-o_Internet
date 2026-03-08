import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
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

	// Load family & business plans
	const plansContainer = document.getElementById('family-business-plans');
	if (plansContainer) {
		function renderPlan(plan) {
			const el = document.createElement('div');
			el.className = 'plan-card';
			const price = plan.preco || plan.price || plan.amount || '';
			el.innerHTML =
				'<h3>' + (plan.name || 'Plano') + '</h3>' +
				'<div class="price">' + (price ? (price + ' <small>AOA</small>') : '') + '</div>' +
				'<p class="desc">' + (plan.description || plan.desc || '') + '</p>' +
				'<div class="plan-actions">' +
					'<a class="btn-primary" href="/store/checkout/' + (plan.id || '') + '">Comprar</a>' +
					'<a class="btn-ghost" href="/planos/' + (plan.id || '') + '">Detalhes</a>' +
				'</div>';
			return el;
		}

		function normalizeCategory(cat) {
			if (!cat) return '';
			return String(cat).toLowerCase();
		}

		fetch('/sg/plans', { credentials: 'same-origin' })
			.then(r => r.json())
			.then(json => {
				const data = json.data || json || [];
				const allowed = ['familiares', 'familia', 'empresariais', 'empresarial'];
				const plans = data.filter(p => {
					let cat = '';
					try {
						cat = normalizeCategory((p.metadata && p.metadata.category) || p.category || '');
					} catch (e) {
						cat = '';
					}
					return allowed.indexOf(cat) !== -1;
				});

				plansContainer.innerHTML = '';
				if (!plans.length) {
					const empty = document.createElement('div');
					empty.className = 'plan-card empty';
					empty.innerHTML = '<p>Nenhum plano encontrado.</p>';
					plansContainer.appendChild(empty);
					return;
				}
				plans.forEach(p => plansContainer.appendChild(renderPlan(p)));
			})
			.catch(() => {
				plansContainer.innerHTML = '<div class="plan-card empty"><p>Erro ao carregar planos.</p></div>';
			});
	}
});
