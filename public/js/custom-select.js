document.addEventListener('DOMContentLoaded', function () {
    function createCustomSelect(select) {
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-select-wrapper';

        const custom = document.createElement('div');
        custom.className = 'custom-select';

        const selected = document.createElement('div');
        selected.className = 'selected';
        selected.textContent = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : '';
        if (!selected.textContent) {
            selected.classList.add('custom-select-placeholder');
            selected.textContent = select.getAttribute('data-placeholder') || '-- Selecionar --';
        }

        const chev = document.createElement('div');
        chev.className = 'chev';

        custom.appendChild(selected);
        custom.appendChild(chev);

        const optionsList = document.createElement('div');
        optionsList.className = 'custom-select-options';
        optionsList.style.display = 'none';

        Array.from(select.options).forEach(function (opt, idx) {
            const item = document.createElement('div');
            item.className = 'custom-select-option';
            item.dataset.value = opt.value;
            item.dataset.index = idx;
            item.textContent = opt.text;
            if (opt.disabled) item.classList.add('disabled');
            if (opt.selected) item.classList.add('active');

            item.addEventListener('click', function (e) {
                e.stopPropagation();
                // set original select value
                select.selectedIndex = idx;
                // update displayed
                selected.textContent = opt.text;
                selected.classList.remove('custom-select-placeholder');
                // mark active
                optionsList.querySelectorAll('.custom-select-option').forEach(function (el) { el.classList.remove('active'); });
                item.classList.add('active');
                // close
                optionsList.style.display = 'none';
                custom.classList.remove('open');
                // trigger change event on original select
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            optionsList.appendChild(item);
        });

        // hide native select but keep in DOM for forms
        select.style.display = 'none';

        wrapper.appendChild(custom);
        wrapper.appendChild(optionsList);

        // insert wrapper before select
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        custom.addEventListener('click', function (e) {
            const open = optionsList.style.display === 'block';
            document.querySelectorAll('.custom-select-options').forEach(function (el) { el.style.display = 'none'; el.previousSibling && el.previousSibling.classList && el.previousSibling.classList.remove('open'); });
            if (!open) {
                optionsList.style.display = 'block';
                custom.classList.add('open');
            } else {
                optionsList.style.display = 'none';
                custom.classList.remove('open');
            }
        });

        // close when clicked outside
        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                optionsList.style.display = 'none';
                custom.classList.remove('open');
            }
        });

        // keep select in sync if changed programmatically
        select.addEventListener('change', function () {
            const opt = select.options[select.selectedIndex];
            if (opt) {
                selected.textContent = opt.text;
                selected.classList.remove('custom-select-placeholder');
                optionsList.querySelectorAll('.custom-select-option').forEach(function (el) { el.classList.toggle('active', el.dataset.index == select.selectedIndex); });
            }
        });
    }

    // target selects on create user form (or all selects inside .form-group.full-width)
    const selects = document.querySelectorAll('form#create-user-form select');
    selects.forEach(function (s) { createCustomSelect(s); });
});
