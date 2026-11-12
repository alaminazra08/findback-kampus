(() => {
    'use strict';
    const config = document.currentScript.dataset;
    const pages = new Map([
        [new URL(config.dashboard).pathname, 'dashboard'],
        [new URL(config.verification).pathname, 'verification'],
        [new URL(config.items).pathname, 'items'],
        [new URL(config.matching).pathname, 'matching'],
        [new URL(config.claims).pathname, 'claims'],
        [new URL(config.statistics).pathname, 'statistics'],
        [new URL(config.users).pathname, 'users'],
        [new URL(config.settings).pathname, 'settings']
    ]);
    function rememberedListURL(url) {
        const page = pages.get(url.pathname);
        if ((page !== 'matching' && page !== 'users') || url.search) return url;
        try {
            if (page === 'users' && sessionStorage.getItem('findback:admin:users:filters:v1:skip')) return url;
            const key = page === 'matching' ? 'fb.admin.matching.lastFilter.v2' : 'findback:admin:users:filters:v1';
            const previous = sessionStorage.getItem(key);
            if (previous) {
                const saved = new URL(previous, url.href);
                if (saved.origin === url.origin && saved.pathname === url.pathname && saved.search) return saved;
            }
        } catch {}
        return url;
    }

    const main = () => document.querySelector('main[data-fb-nav-page]');
    let pending;
    let displayedURL = location.href;

    function prepareStyles(doc, signal, staged) {
        const old = [...document.head.querySelectorAll('link[rel="stylesheet"]')];
        const fresh = [...doc.head.querySelectorAll('link[rel="stylesheet"]')];
        const ready = fresh.map(source => {
            const media = source.getAttribute('media');
            const link = document.importNode(source, true);
            link.media = 'not all';
            staged.push(link);
            return new Promise((resolve, reject) => {
                link.addEventListener('load', () => resolve([link, media]), { once: true });
                link.addEventListener('error', reject, { once: true });
                signal.addEventListener('abort', reject, { once: true });
                document.head.append(link);
            });
        });
        return Promise.all(ready).then(links => ({old, links}));
    }

    async function navigate(url, historyMode) {
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        const { signal } = controller;
        const staged = [];
        let committed = false;
        try {
            const response = await fetch(url, { cache: 'no-store', signal, headers: { Accept: 'text/html' } });
            if (!response.ok || response.redirected || response.url !== url.href) throw Error('Navigation response');
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            const next = doc.querySelector('main[data-fb-nav-page]');
            const crumb = doc.querySelector('.fb-breadcrumb');
            const topbar = doc.querySelector('.fb-topbar-right');
            const active = doc.querySelector('.fb-sidebar .fb-nav-link[aria-current="page"]');
            if (next?.dataset.fbNavPage !== pages.get(url.pathname) || !crumb || !topbar || !active) {
                throw Error('Incomplete admin page');
            }
            // DOMParser resolves relative references against the current document.
            // These Blade views emit absolute stylesheet URLs through asset().
            const {old, links} = await prepareStyles(doc, signal, staged);
            if (signal.aborted) return;
            for (const [link, media] of links) {
                if (media === null) link.removeAttribute('media');
                else link.media = media;
            }
            for (const link of old) link.remove();
            if (historyMode === 'push') history.pushState(null, '', url);
            displayedURL = url.href;
            document.title = doc.title;
            main().replaceWith(document.importNode(next, true));
            document.querySelector('.fb-breadcrumb').replaceWith(document.importNode(crumb, true));
            document.querySelector('.fb-topbar-right').replaceWith(document.importNode(topbar, true));
            for (const link of document.querySelectorAll('.fb-sidebar a.fb-nav-link')) {
                const selected = new URL(link.href).pathname === url.pathname;
                link.classList.toggle('is-active', selected);
                if (selected) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            }
            const menu = document.getElementById('fb-menu-toggle');
            if (menu?.getAttribute('aria-expanded') === 'true') menu.click();
            window.scrollTo({top: 0, left: 0, behavior: 'instant'});
            committed = true;
            document.dispatchEvent(new Event('fb:admin-page'));
        } catch {
            if (!signal.aborted) {
                if (historyMode === 'pop') location.reload();
                else location.assign(url.href);
            }
        } finally {
            if (!committed) for (const link of staged) link.remove();
            if (pending === controller) pending = null;
        }
    }

    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest?.('.fb-sidebar a.fb-nav-link[href], main[data-fb-nav-page] a[href]');
        if (!link || link.target || link.hasAttribute('download') || !main()) return;
        const url = new URL(link.href);
        if (url.origin !== location.origin || !pages.has(url.pathname)) return;
        if (main().dataset.fbNavPage === pages.get(url.pathname)) {
            pending?.abort();
            return;
        }
        event.preventDefault();
        navigate(rememberedListURL(url), 'push');
    });

    function changeStatisticsPeriod(form) {
        const url = new URL(form.action);
        if (url.origin !== location.origin || pages.get(url.pathname) !== 'statistics') return;
        url.search = new URLSearchParams(new FormData(form)).toString();
        if (url.href === displayedURL) {
            pending?.abort();
            return;
        }
        navigate(url, 'push');
    }

    document.addEventListener('change', event => {
        const field = event.target;
        if (main()?.dataset.fbNavPage === 'statistics' && field.matches?.('[data-fb-stat-period]') && field.form) {
            changeStatisticsPeriod(field.form);
        }
    });

    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || main()?.dataset.fbNavPage !== 'statistics' ||
            !form.matches?.('form.fs-filter') || form.method.toLowerCase() !== 'get') return;
        event.preventDefault();
        changeStatisticsPeriod(form);
    });

    window.addEventListener('popstate', event => {
        pending?.abort();
        const url = new URL(location.href);
        const current = main()?.dataset.fbNavPage;
        const target = pages.get(url.pathname);
        if (!current || !target) return;
        if (current === target && (target !== 'statistics' || url.href === displayedURL)) return;
        event.stopImmediatePropagation();
        navigate(url, 'pop');
    }, true);
})();
