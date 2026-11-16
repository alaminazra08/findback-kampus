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
    const detailPages = new Map([
        ['verification', 'verification-detail'], ['items', 'item-detail'],
        ['matching', 'match-detail'], ['claims', 'claim-detail'], ['users', 'user-detail']
    ]);
    function pageOf(path) {
        if (pages.has(path)) return pages.get(path);
        const match = path.match(/^(.*)\/[0-9]+\/?$/);
        return match ? detailPages.get(pages.get(match[1])) : undefined;
    }
    function rememberedListURL(url) {
        const page = pageOf(url.pathname);
        if ((page !== 'matching' && page !== 'users') || url.search) return url;
        try {
            if (page === 'users' && sessionStorage.getItem('findback:admin:users:filters:v1:skip')) return url;
            const key = page === 'matching' ? 'fb.admin.matching.lastFilter.v2' : 'findback:admin:users:filters:v1';
            const previous = sessionStorage.getItem(key);
            if (previous) {
                const saved = new URL(previous, url.href);
                if (saved.origin === url.origin && saved.pathname === url.pathname && saved.search) {
                    // Abaikan status filter lama yang tidak lagi didukung controller.
                    if (page === 'matching' && saved.searchParams.has('status') &&
                        !['all', 'pending', 'confirmed', 'rejected'].includes(saved.searchParams.get('status'))) {
                        saved.searchParams.delete('status');
                    }
                    return saved.searchParams.toString() ? saved : url;
                }
            }
        } catch {}
        return url;
    }

    const main = () => document.querySelector('main[data-fb-nav-page]');
    let pending;
    let pendingURL;
    let submitting = false;
    let displayedURL = location.href;
    const position = () => ({left: window.scrollX, top: window.scrollY});
    history.replaceState({...history.state, fbAdminURL: location.href}, '');

    function prepareStyles(doc, signal, staged) {
        const old = [...document.head.querySelectorAll('link[rel="stylesheet"]')];
        const reusable = new Set(old);
        const fresh = [...doc.head.querySelectorAll('link[rel="stylesheet"]')];
        const ready = fresh.map(source => {
            const media = source.getAttribute('media');
            const existing = [...reusable].find(link =>
                link.href === source.href && link.media === source.media &&
                link.crossOrigin === source.crossOrigin && link.integrity === source.integrity &&
                link.referrerPolicy === source.referrerPolicy && link.sheet
            );
            if (existing) {
                reusable.delete(existing);
                return [existing, media];
            }
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
        return Promise.all(ready).then(links => ({unused: reusable, links}));
    }

    // Fast path khusus Dashboard <-> Verifikasi:
    // ambil HTML halaman tujuan lebih awal, lalu gunakan cache singkat saat diklik.
    const quickPages = new Set([
        'dashboard', 'verification', 'items', 'matching',
        'claims', 'statistics', 'users', 'settings',
        'verification-detail', 'item-detail', 'match-detail',
        'claim-detail', 'user-detail'
    ]);
    const quickHTMLCache = new Map();
    const quickHTMLPending = new Map();
    const QUICK_CACHE_TTL = 8000;
    const quickStylePreloads = new Set();

    // Panaskan CSS halaman tujuan saat HTML diprefetch, bukan setelah menu diklik.
    // Link preload tidak menerapkan CSS sebelum navigasi, jadi tampilan halaman aktif
    // tidak berubah. Browser dapat memakai hasil unduhan saat stylesheet dipasang.
    function warmQuickStyles(html) {
        const doc = new DOMParser().parseFromString(html, 'text/html');
        for (const source of doc.head.querySelectorAll('link[rel="stylesheet"][href]')) {
            const href = new URL(source.href, location.href);
            if (href.origin !== location.origin || quickStylePreloads.has(href.href)) continue;
            const alreadyLoaded = [...document.head.querySelectorAll('link[rel="stylesheet"][href]')]
                .some(link => link.href === href.href && link.sheet);
            if (alreadyLoaded) {
                quickStylePreloads.add(href.href);
                continue;
            }
            const preload = document.createElement('link');
            preload.rel = 'preload';
            preload.as = 'style';
            preload.href = href.href;
            if (source.crossOrigin) preload.crossOrigin = source.crossOrigin;
            if (source.integrity) preload.integrity = source.integrity;
            if (source.referrerPolicy) preload.referrerPolicy = source.referrerPolicy;
            preload.addEventListener('error', () => quickStylePreloads.delete(href.href), { once: true });
            document.head.append(preload);
            quickStylePreloads.add(href.href);
        }
    }

    function isQuickNavigation(url) {
        return quickPages.has(pageOf(url.pathname));
    }

    async function fetchQuickHTML(url) {
        const key = url.href;
        const cached = quickHTMLCache.get(key);
        if (cached && Date.now() - cached.time < QUICK_CACHE_TTL) return cached.html;

        if (quickHTMLPending.has(key)) return quickHTMLPending.get(key);

        const task = (async () => {
            const response = await fetch(url, {
                cache: 'no-store',
                credentials: 'same-origin',
                headers: { Accept: 'text/html', 'X-FB-Navigation-Prefetch': '1' }
            });
            if (!response.ok || response.redirected || response.url !== key ||
                !(response.headers.get('content-type') || '').includes('text/html')) {
                throw Error('Prefetch response');
            }
            const html = await response.text();
            warmQuickStyles(html);
            quickHTMLCache.set(key, { html, time: Date.now() });
            return html;
        })();

        quickHTMLPending.set(key, task);
        try {
            return await task;
        } finally {
            if (quickHTMLPending.get(key) === task) quickHTMLPending.delete(key);
        }
    }

    function warmQuickURL(value) {
        if (!value || document.hidden || navigator.connection?.saveData) return Promise.resolve();
        const url = new URL(value, location.href);
        if (url.origin !== location.origin || !isQuickNavigation(url) || url.href === location.href) {
            return Promise.resolve();
        }
        // Kembalikan promise agar halaman prioritas dapat disiapkan lebih dulu.
        return fetchQuickHTML(url).catch(() => {});
    }

    function warmQuickDestinations() {
        if (document.hidden || navigator.connection?.saveData) return Promise.resolve();

        const currentPage = main()?.dataset.fbNavPage;
        const destinations = {
            dashboard: [config.verification, config.items],
            verification: [config.dashboard, config.items],
            items: [config.dashboard, config.verification],
            matching: [config.items, config.claims],
            claims: [config.matching, config.statistics],
            statistics: [config.claims, config.users],
            users: [config.statistics, config.settings],
            settings: [config.users, config.dashboard]
        }[currentPage] || [];

        // Ketiga halaman memakai aturan identik:
        // dua tujuan langsung diprefetch paralel sejak halaman asal aktif.
        // fetchQuickHTML menggabungkan request duplikat dan menyimpan HTML 20 detik.
        return Promise.all(destinations.map(value => warmQuickURL(value)));
    }

    // Mulai prefetch saat pengguna mengarahkan pointer/fokus ke tautan.
    // Permintaan yang sama digabung dengan cache/pending yang sudah berjalan.
    function warmFromInteraction(event) {
        const link = event.target.closest?.('.fb-sidebar a.fb-nav-link[href], main[data-fb-nav-page] a[href]');
        if (!link || link.target || link.hasAttribute('download')) return;
        warmQuickURL(link.href);
    }
    document.addEventListener('pointerover', warmFromInteraction, { passive: true });
    document.addEventListener('focusin', warmFromInteraction);
    document.addEventListener('pointerdown', warmFromInteraction, { passive: true });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) warmQuickDestinations();
    });

    function navigationHTML(url, signal) {
        if (isQuickNavigation(url)) {
            const cached = quickHTMLCache.get(url.href);
            if (cached && Date.now() - cached.time < QUICK_CACHE_TTL) {
                return Promise.resolve(cached.html);
            }
            const pendingTask = quickHTMLPending.get(url.href);
            if (pendingTask) {
                return new Promise((resolve, reject) => {
                    if (signal.aborted) return reject(Error('Navigation aborted'));
                    const onAbort = () => reject(Error('Navigation aborted'));
                    signal.addEventListener('abort', onAbort, { once: true });
                    pendingTask.then(resolve, reject).finally(() => {
                        signal.removeEventListener('abort', onAbort);
                    });
                });
            }
        }
        return fetch(url, {
            cache: 'no-store',
            credentials: 'same-origin',
            signal,
            headers: { Accept: 'text/html' }
        }).then(response => {
            if (!response.ok || response.redirected || response.url !== url.href ||
                !(response.headers.get('content-type') || '').includes('text/html')) {
                throw Error('Navigation response');
            }
            return response.text();
        });
    }

    // Mulai memanaskan semua tujuan segera setelah skrip navigasi aktif.
    // Angka 50 ms bukan jeda buatan: targetnya adalah klik memakai cache siap.
    warmQuickDestinations();

    async function navigate(url, historyMode, scroll, back) {
        if (submitting || (pending && !pending.signal.aborted && pendingURL === url.href)) return;
        pending?.abort();
        const controller = new AbortController();
        pending = controller;
        pendingURL = url.href;
        const from = location.href;
        const fromPage = main()?.dataset.fbNavPage || pageOf(new URL(from).pathname) || 'unknown';
        const navigationStartedAt = performance.now();
        let timedOut = false;
        const timeout = setTimeout(() => {
            timedOut = true;
            controller.abort();
        }, 15000);
        const { signal } = controller;
        const staged = [];
        let committed = false;
        try {
            const html = await navigationHTML(url, signal);
            if (signal.aborted) return;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.querySelector('main[data-fb-nav-page]');
            const crumb = doc.querySelector('.fb-breadcrumb');
            const topbar = doc.querySelector('.fb-topbar-right');
            const active = doc.querySelector('.fb-sidebar .fb-nav-link[aria-current="page"]');
            if (next?.dataset.fbNavPage !== pageOf(url.pathname) || !crumb || !topbar || !active) {
                throw Error('Incomplete admin page');
            }
            // DOMParser resolves relative references against the current document.
            // These Blade views emit absolute stylesheet URLs through asset().
            const {unused, links} = await prepareStyles(doc, signal, staged);
            if (signal.aborted || location.href !== from) return;
            const oldMain = main();
            const returnContext = historyMode === 'push' ? back : history.state?.fbAdminBack;
            const returnLink = next.querySelector('[data-fb-detail-back]');
            if (returnLink && next.dataset.fbNavPage === 'item-detail' && returnContext &&
                new URL(returnContext.url).pathname === new URL(config.statistics).pathname) {
                returnLink.href = returnContext.url;
                returnLink.textContent = '← Kembali ke Laporan & Statistik';
            }
            if (historyMode === 'push') {
                history.replaceState({...history.state, fbAdminURL: from, fbAdminScroll: position()}, '');
                history.pushState({fbAdminURL: url.href, fbAdminBack: back}, '', url);
            }
            for (const link of unused) link.remove();
            let following = null;
            for (const [link] of [...links].reverse()) {
                let sibling = link.nextElementSibling;
                while (sibling && !sibling.matches('link[rel="stylesheet"]')) sibling = sibling.nextElementSibling;
                if (sibling !== following) {
                    if (document.head.moveBefore) document.head.moveBefore(link, following);
                    else document.head.insertBefore(link, following);
                }
                following = link;
            }
            for (const [link, media] of links) {
                if (media === null) link.removeAttribute('media');
                else link.media = media;
            }
            displayedURL = url.href;
            document.title = doc.title;
            oldMain.replaceWith(document.importNode(next, true));
            document.querySelector('.fb-breadcrumb').replaceWith(document.importNode(crumb, true));
            document.querySelector('.fb-topbar-right').replaceWith(document.importNode(topbar, true));
            const activePath = new URL(active.href).pathname;
            for (const link of document.querySelectorAll('.fb-sidebar a.fb-nav-link')) {
                const selected = new URL(link.href).pathname === activePath;
                link.classList.toggle('is-active', selected);
                if (selected) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            }
            const menu = document.getElementById('fb-menu-toggle');
            if (menu?.getAttribute('aria-expanded') === 'true') menu.click();
            committed = true;
            const navigationDuration = performance.now() - navigationStartedAt;
            console.info(`[FindBack Nav] ${fromPage} -> ${next.dataset.fbNavPage}: ${navigationDuration.toFixed(1)} ms`);
            document.dispatchEvent(new Event('fb:admin-page'));
            window.scrollTo({top: 0, left: 0, ...scroll, behavior: 'instant'});
            warmQuickDestinations();
        } catch {
            if ((!signal.aborted || timedOut) && (location.href === from || location.href === url.href)) {
                if (historyMode === 'pop' || location.href === url.href) location.reload();
                else location.assign(url.href);
            }
        } finally {
            clearTimeout(timeout);
            if (!committed) for (const link of staged) link.remove();
            if (pending === controller) pending = null;
        }
    }

    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest?.('.fb-sidebar a.fb-nav-link[href], main[data-fb-nav-page] a[href], #fb-notif a.fb-notif-action[href]');
        if (!link || link.target || link.hasAttribute('download') || !main()) return;

        let url = new URL(link.href);
        const target = pageOf(url.pathname);
        if (url.origin !== location.origin || url.hash || !target) return;
        const current = main().dataset.fbNavPage;
        if (current === target && !target.endsWith('-detail')) {
            pending?.abort();
            return; // Existing listing scripts own filters, tabs and pagination.
        }
        event.preventDefault();
        if (url.href === location.href && url.href === displayedURL) {
            pending?.abort();
            return;
        }
        let scroll;
        let back;
        if (link.hasAttribute('data-fb-detail-back')) {
            const source = history.state?.fbAdminBack;
            if (source) {
                const previous = new URL(source.url);
                if (previous.origin === url.origin &&
                    (previous.pathname === url.pathname || (current === 'item-detail' && pages.get(previous.pathname) === 'statistics'))) {
                    url = previous;
                    scroll = source.scroll;
                }
            }
        } else if (target.endsWith('-detail')) {
            back = current.endsWith('-detail') ? history.state?.fbAdminBack : {url: location.href, scroll: position()};
        }
        navigate(scroll ? url : rememberedListURL(url), 'push', scroll, back);
    });

    document.addEventListener('fb:notification-open', event => {
        const url = new URL(event.detail, location.href);
        const target = pageOf(url.pathname);
        if (url.origin !== location.origin || !target || url.hash || !main() || submitting) return;
        event.preventDefault();
        if (url.href === location.href) return;
        const back = target.endsWith('-detail') ? {url: location.href, scroll: position()} : undefined;
        navigate(url, 'push', undefined, back);
    });

    function changeStatisticsPeriod(form) {
        const url = new URL(form.action);
        if (url.origin !== location.origin || pageOf(url.pathname) !== 'statistics') return;
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

    // Native POST submissions keep their submitter, CSRF token and redirect.
    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || form.method !== 'post' || form.target || event.submitter?.formTarget) return;
        if (submitting) {
            event.preventDefault();
            return;
        }
        submitting = true;
        pending?.abort();
    });
    window.addEventListener('scrollend', () => {
        if (history.state?.fbAdminURL === location.href) {
            history.replaceState({...history.state, fbAdminScroll: position()}, '');
        }
    });
    window.addEventListener('pageshow', () => { submitting = false; });
    window.addEventListener('pagehide', () => pending?.abort());

    window.addEventListener('popstate', event => {
        pending?.abort();
        const url = new URL(location.href);
        const current = main()?.dataset.fbNavPage;
        const target = pageOf(url.pathname);
        if (!current || !target) return;
        if (current === target && target !== 'statistics' && !target.endsWith('-detail')) {
            displayedURL = url.href;
            return;
        }
        if (url.href === displayedURL && current === target) return;
        event.stopImmediatePropagation();
        const scroll = event.state?.fbAdminURL === url.href ? event.state.fbAdminScroll : undefined;
        navigate(url, 'pop', scroll);
    }, true);
})();
