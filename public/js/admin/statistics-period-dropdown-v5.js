/* FindBack Kampus — Custom period dropdown V5
 * Keeps the original select as the submitted source of truth. Changing an option
 * dispatches the same bubbling change event consumed by the existing AJAX navigation.
 */
(() => {
    const init = (root = document) => {
        root.querySelectorAll('.fs-page select[data-fb-stat-period]').forEach((select) => {
            if (select.dataset.customPeriodV5 === 'ready' || !select.options.length) return;
            select.dataset.customPeriodV5 = 'ready';

            const control = document.createElement('div');
            control.className = 'fs-period-control';
            const trigger = document.createElement('button');
            trigger.type = 'button';
            trigger.className = 'fs-period-trigger';
            trigger.setAttribute('aria-haspopup', 'listbox');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.setAttribute('aria-label', 'Pilih periode laporan');
            const label = document.createElement('span');
            label.className = 'fs-period-current';
            const chevron = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            chevron.setAttribute('viewBox', '0 0 24 24');
            chevron.setAttribute('aria-hidden', 'true');
            const chevronPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            chevronPath.setAttribute('d', 'm6 9 6 6 6-6');
            chevron.appendChild(chevronPath);
            trigger.append(label, chevron);

            const menu = document.createElement('div');
            menu.className = 'fs-period-menu';
            menu.id = `fs-period-menu-${Math.random().toString(36).slice(2, 9)}`;
            menu.hidden = true;
            menu.setAttribute('role', 'listbox');
            menu.setAttribute('aria-label', 'Pilihan periode laporan');
            trigger.setAttribute('aria-controls', menu.id);
            control.append(trigger, menu);
            select.insertAdjacentElement('afterend', control);
            select.setAttribute('aria-hidden', 'true');
            select.tabIndex = -1;

            const options = [];
            const close = (returnFocus = false) => {
                control.classList.remove('is-open');
                menu.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                if (returnFocus) trigger.focus();
            };
            const open = (focusIndex = -1) => {
                control.classList.add('is-open');
                menu.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
                if (focusIndex >= 0 && options[focusIndex]) options[focusIndex].focus();
            };
            const sync = () => {
                const selected = select.selectedOptions[0];
                label.textContent = selected ? selected.textContent.trim() : 'Pilih periode';
                options.forEach((button, index) => {
                    const active = select.options[index].value === select.value;
                    button.setAttribute('aria-selected', active ? 'true' : 'false');
                    if (active) button.innerHTML = `${escapeText(select.options[index].textContent.trim())}<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>`;
                    else button.textContent = select.options[index].textContent.trim();
                });
            };
            const escapeText = (value) => {
                const node = document.createElement('span');
                node.textContent = value;
                return node.innerHTML;
            };

            Array.from(select.options).forEach((option, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'fs-period-option';
                item.setAttribute('role', 'option');
                item.dataset.index = String(index);
                item.addEventListener('click', () => {
                    if (select.value !== option.value) {
                        select.value = option.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    sync();
                    close(true);
                });
                item.addEventListener('keydown', (event) => {
                    let next = index;
                    if (event.key === 'ArrowDown') next = Math.min(options.length - 1, index + 1);
                    else if (event.key === 'ArrowUp') next = Math.max(0, index - 1);
                    else if (event.key === 'Home') next = 0;
                    else if (event.key === 'End') next = options.length - 1;
                    else if (event.key === 'Escape') { event.preventDefault(); close(true); return; }
                    else if (event.key === 'Tab') { close(false); return; }
                    else return;
                    event.preventDefault();
                    options[next]?.focus();
                });
                menu.appendChild(item);
                options.push(item);
            });

            const periodLabel = select.form?.querySelector('label[for="period"]');
            if (periodLabel) {
                periodLabel.removeAttribute('for');
                periodLabel.addEventListener('click', () => trigger.focus());
            }

            trigger.addEventListener('click', () => {
                if (menu.hidden) open();
                else close();
            });
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    open(Math.max(0, select.selectedIndex));
                }
            });
            select.addEventListener('change', sync);
            document.addEventListener('pointerdown', (event) => {
                if (!control.contains(event.target)) close(false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !menu.hidden) close(true);
            });
            sync();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => init(), { once: true });
    } else init();
    document.addEventListener('fb:admin-page', () => init());
})();
