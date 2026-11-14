(() => {
    let scope;
    let known;
    let toast;

    function dismiss() {
        toast?.remove();
        toast = null;
    }

    function announce(rows, wrapper) {
        dismiss();
        if (!rows.length) return;
        const first = rows[0];
        const notice = document.createElement('div');
        notice.className = 'fb-notif-toast';
        const action = document.createElement('button');
        action.type = 'button';
        const single = rows.length === 1;
        const noun = { reports: 'laporan', claims: 'klaim' }[first.dataset.category];
        const message = rows.every(row => row.dataset.category === first.dataset.category)
            ? `${rows.length}${rows.length === 8 ? '+' : ''} ${noun} baru`
            : `${rows.length}${rows.length === 8 ? '+' : ''} notifikasi baru`;
        const icon = wrapper.querySelector('#fb-notif-trigger svg')?.cloneNode(true);
        if (icon) action.append(icon);
        action.append(document.createTextNode(message));
        action.setAttribute('aria-label', message + (single ? ', buka detail' : ', buka daftar notifikasi'));
        const close = document.createElement('button');
        close.type = 'button';
        close.textContent = '×';
        close.setAttribute('aria-label', 'Tutup pemberitahuan');
        action.addEventListener('click', event => {
            event.stopPropagation();
            const row = [...wrapper.querySelectorAll('[data-notification-id]')]
                .find(item => item.dataset.notificationId === first.dataset.notificationId);
            const form = row?.querySelector('form');
            dismiss();
            if (single && form?.isConnected) form.requestSubmit();
            else wrapper.querySelector('#fb-notif-trigger')?.click();
        });
        close.addEventListener('click', dismiss);
        notice.append(action, close);
        const header = wrapper.closest('.fb-topbar');
        notice.style.setProperty('--fb-notif-top', Math.max(8, header?.getBoundingClientRect().bottom ?? 0) + 8 + 'px');
        document.body.append(notice);
        toast = notice;
        setTimeout(() => { if (toast === notice) dismiss(); }, 5000);
    }

    function bind() {
        scope?.abort();
        dismiss();
        scope = new AbortController();
        const { signal } = scope;
        const wrapper = document.getElementById('fb-notif');
        const button = wrapper?.querySelector('#fb-notif-trigger');
        const panel = wrapper?.querySelector('#fb-notif-panel');
        if (!button || !panel) return;
        const rows = root => [...root.querySelectorAll('[data-notification-id]')];
        if (!known) known = new Set(rows(wrapper).map(row => row.dataset.notificationId));
        const setOpen = open => {
            if (open) {
                const header = wrapper.closest('.fb-topbar');
                panel.style.setProperty('--fb-notif-panel-top', Math.max(8, header?.getBoundingClientRect().bottom ?? 0) + 8 + 'px');
            }
            panel.hidden = !open;
            button.setAttribute('aria-expanded', String(open));
            if (open) dismiss();
        };
        button.addEventListener('click', () => setOpen(panel.hidden), { signal });
        document.addEventListener('click', event => {
            if (!wrapper.contains(event.target) && !toast?.contains(event.target)) setOpen(false);
        }, { signal });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && !panel.hidden) {
                setOpen(false);
                button.focus();
            }
        }, { signal });

        let opening = false;
        let readVersion = 0;
        panel.addEventListener('click', event => {
            const link = event.target.closest('a.fb-notif-action');
            if (!link || event.defaultPrevented || event.button !== 0 ||
                event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
            setOpen(false);
        }, { signal });

        panel.addEventListener('submit', async event => {
            const form = event.target;
            if (event.defaultPrevented || !form.closest('[data-notification-id]')) return;
            event.preventDefault();
            if (opening) return;
            opening = true;
            readVersion++;
            const button = form.querySelector('button');
            const label = button.textContent;
            button.disabled = true;
            button.textContent = 'Membuka…';
            panel.querySelector('[data-fb-notif-error]')?.remove();
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json' },
                    signal,
                });
                if (!response.ok || response.redirected) throw new Error('Notification request failed');
                const { destination } = await response.json();
                const url = new URL(destination);
                if (url.origin !== location.origin) throw new Error('Unexpected destination');
                setOpen(false);
                const follow = document.dispatchEvent(new CustomEvent('fb:notification-open', {
                    detail: url.href,
                    cancelable: true,
                }));
                if (follow) location.assign(url.href);
            } catch {
                if (signal.aborted) return;
                setOpen(true);
                const message = document.createElement('p');
                message.dataset.fbNotifError = '';
                message.className = 'fb-notif-open-error';
                message.setAttribute('role', 'alert');
                message.textContent = 'Notifikasi gagal dibuka. Muat ulang halaman jika sesi telah berakhir.';
                panel.querySelector('.fb-notif-head').after(message);
            } finally {
                opening = false;
                button.disabled = false;
                button.textContent = label;
                if (!signal.aborted && panel.hidden) refresh();
            }
        }, { signal });

        let busy = false;
        async function refresh() {
            if (busy || opening || document.hidden || !wrapper.dataset.feed) return;
            busy = true;
            const version = readVersion;
            try {
                const response = await fetch(wrapper.dataset.feed, { cache: 'no-store', signal });
                if (!response.ok || response.redirected || signal.aborted) return;
                const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
                if (signal.aborted || !wrapper.isConnected || opening || version !== readVersion) return;
                const next = doc.getElementById('fb-notif');
                if (!next) return;
                const nextRows = rows(next);
                const arrivals = nextRows.filter(row => !known.has(row.dataset.notificationId) &&
                    row.classList.contains('is-unread') && ['reports', 'claims'].includes(row.dataset.category));
                // Keep a bounded list of recent IDs per document.
                known = new Set(nextRows.map(row => row.dataset.notificationId));
                const nextButton = next.querySelector('#fb-notif-trigger');
                if (button.getAttribute('aria-label') !== nextButton.getAttribute('aria-label')) {
                    button.setAttribute('aria-label', nextButton.getAttribute('aria-label'));
                    button.querySelector('.fb-notif-count')?.remove();
                    const count = nextButton.querySelector('.fb-notif-count');
                    if (count) button.append(document.importNode(count, true));
                }
                const nextPanel = next.querySelector('#fb-notif-panel');
                const signature = root => rows(root).map(row => row.dataset.notificationId + ':' + row.classList.contains('is-unread')).join(',');
                if (signature(panel) !== signature(nextPanel) ||
                    panel.querySelector('.fb-notif-head strong').textContent !== nextPanel.querySelector('.fb-notif-head strong').textContent) {
                    const scroll = panel.scrollTop;
                    panel.replaceChildren(...[...nextPanel.childNodes].map(node => document.importNode(node, true)));
                    panel.scrollTop = scroll;
                }
                if (arrivals.length && panel.hidden && !document.hidden) {
                    const ids = new Set(arrivals.map(row => row.dataset.notificationId));
                    announce(rows(panel).filter(row => ids.has(row.dataset.notificationId)), wrapper);
                }
            } catch (error) {
                // Keep the existing list if the connection is interrupted.
            } finally {
                busy = false;
            }
        }
        const timer = setInterval(refresh, 15000);
        signal.addEventListener('abort', () => clearInterval(timer), { once: true });
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); }, { signal });
    }

    document.addEventListener('DOMContentLoaded', bind, { once: true });
    document.addEventListener('fb:admin-page', bind);
})();
