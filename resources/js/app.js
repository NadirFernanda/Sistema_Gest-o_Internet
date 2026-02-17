import './bootstrap';

// Import Choices.js and its styles (bundled via Vite)
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

document.addEventListener('DOMContentLoaded', function () {
	try {
			document.querySelectorAll('.select').forEach(function(el){
				if (!el) return;
				if (!el.id) el.id = 'select-' + Math.random().toString(36).slice(2,9);
				if (el && !el.classList.contains('choices-initialized')){
					window._choicesMap = window._choicesMap || {};
					window._choicesMap[el.id] = new Choices(el, {
						searchEnabled: true,
						searchFloor: 1,
						itemSelectText: '',
						shouldSort: false,
						allowHTML: false,
						placeholder: true,
					});
					el.classList.add('choices-initialized');
				}
			});
	} catch (e) { console.error('Choices init error', e); }
});

// Page-specific scripts
import './planos';
