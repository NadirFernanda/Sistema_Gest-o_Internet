import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
	// Hero carousel
	const carousel = document.querySelector('.hero-carousel');
	if (carousel) {
		const track = carousel.querySelector('.carousel-track');
		const slides = Array.prototype.slice.call(carousel.querySelectorAll('.carousel-slide'));
		const progressBars = Array.prototype.slice.call(carousel.querySelectorAll('.carousel-progress-bar'));
		let index = 0;
		const slideCount = slides.length;
		let interval = null;

		function goTo(i) {
			index = (i + slideCount) % slideCount;
			track.style.transform = 'translateX(' + -(index * 100) + '%)';
			slides.forEach((s, si) => s.classList.toggle('active', si === index));
			// Reset progress bars — re-add active to trigger CSS animation restart
			progressBars.forEach(bar => {
				bar.classList.remove('active');
				// Force reflow so animation restarts
				void bar.offsetWidth;
			});
			if (progressBars[index]) progressBars[index].classList.add('active');
		}

		function nextSlide() { goTo(index + 1); }
		function prevSlide() { goTo(index - 1); }

		let touchStartX = null;
		carousel.addEventListener('touchstart', e => {
			touchStartX = e.touches && e.touches[0] ? e.touches[0].clientX : null;
		}, { passive: true });

		carousel.addEventListener('touchend', e => {
			if (touchStartX === null) return;
			const touchEndX = (e.changedTouches && e.changedTouches[0]) ? e.changedTouches[0].clientX : null;
			if (touchEndX === null) return;
			const dx = touchEndX - touchStartX;
			if (Math.abs(dx) > 40) {
				if (dx < 0) { nextSlide(); } else { prevSlide(); }
				restart();
			}
			touchStartX = null;
		});

		function start() { interval = window.setInterval(nextSlide, 5000); }
		function stop() { if (interval) { window.clearInterval(interval); interval = null; } }
		function restart() { stop(); start(); }

		carousel.addEventListener('mouseenter', stop);
		carousel.addEventListener('mouseleave', start);

		// Carousel arrow navigation
		const leftArrow = carousel.querySelector('.carousel-arrow.left');
		const rightArrow = carousel.querySelector('.carousel-arrow.right');
		if (leftArrow && rightArrow) {
			leftArrow.addEventListener('click', () => { prevSlide(); restart(); });
			rightArrow.addEventListener('click', () => { nextSlide(); restart(); });
			// Keyboard accessibility
			[leftArrow, rightArrow].forEach(btn => {
				btn.addEventListener('keydown', e => {
					if (e.key === 'Enter' || e.key === ' ') {
						btn.click();
					}
				});
			});
		}
		goTo(0);
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
