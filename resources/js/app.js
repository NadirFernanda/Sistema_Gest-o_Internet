import './bootstrap';

// Import Choices.js and its styles (bundled via Vite)
import Choices from 'choices.js';
import 'choices.js/public/assets/styles/choices.min.css';

document.addEventListener('DOMContentLoaded', function () {
	try {
		document.querySelectorAll('.select').forEach(function(el){
			if (el && !el.classList.contains('choices-initialized')){
				window._choicesMap = window._choicesMap || {};
				window._choicesMap[el.id] = new Choices(el, { searchEnabled: false, itemSelectText: '', shouldSort: false, allowHTML: false });
				el.classList.add('choices-initialized');
			}
		});
	} catch (e) { console.error('Choices init error', e); }
});
