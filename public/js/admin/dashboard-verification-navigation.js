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
    function canonicalNavigationURL(value) {
        const url = value instanceof URL ? new URL(value.href) : new URL(value, location.href);
        if (pageOf(url.pathname) === 'claims') {
            // Controller mengkanonisasi query kosong dengan HTTP 302.
            // Buang parameter kosong sebelum fetch agar SPA menerima respons 200 langsung.
            for (const name of ['search', 'status']) {
                if (url.searchParams.has(name) && !String(url.searchParams.get(name) ?? '').trim()) {
                    url.searchParams.delete(name);
                }
            }
        }
        return url;
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

    // Form Klaim selalu diproses oleh navigasi global.
    // Parameter kosong tidak pernah dimasukkan ke URL, termasuk setelah AJAX navigation.
    document.addEventListener('formdata', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('form.fb-claim-filters')) return;

        for (const name of ['search', 'status']) {
            const value = String(event.formData.get(name) ?? '').trim();
            if (!value) event.formData.delete(name);
        }
    }, true);

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('form.fb-claim-filters')) return;

        event.preventDefault();
        const url = new URL(form.action || location.href, location.href);
        const data = new FormData(form);
        const params = new URLSearchParams();

        for (const [name, rawValue] of data.entries()) {
            const value = String(rawValue ?? '').trim();
            if (value !== '') params.append(name, value);
        }

        url.search = params.toString();
        if (url.origin !== location.origin) {
            location.assign(url.href);
            return;
        }
        if (url.href === location.href && url.href === displayedURL) return;
        // Filter Klaim memakai mesin navigasi global yang sama dengan perpindahan halaman.
        // Pertahankan posisi baca agar hasil filter tidak memaksa halaman melompat ke atas.
        navigate(url, 'push', position());
    }, true);

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
                let settled = false;
                let timer = null;
                const finish = () => {
                    if (settled) return;
                    settled = true;
                    if (timer) clearTimeout(timer);
                    signal.removeEventListener('abort', abort);
                    resolve([link, media]);
                };
                const fail = () => {
                    if (settled) return;
                    settled = true;
                    if (timer) clearTimeout(timer);
                    signal.removeEventListener('abort', abort);
                    link.remove();
                    console.warn('[FindBack Nav] Stylesheet gagal dimuat:', source.href);
                    resolve([link, media]);
                };
                const abort = () => {
                    if (settled) return;
                    settled = true;
                    if (timer) clearTimeout(timer);
                    reject(new Error('Navigation aborted'));
                };
                link.addEventListener('load', finish, { once: true });
                link.addEventListener('error', fail, { once: true });
                signal.addEventListener('abort', abort, { once: true });
                // Batasi waktu tunggu stylesheet; file tetap aktif dan akan diterapkan
                // saat unduhan selesai, tanpa menahan navigasi seluruh halaman.
                timer = setTimeout(finish, 250);
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
    const QUICK_CACHE_TTL = 15000;
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
        // Prefetch URL yang sama persis dengan URL yang akan dipakai klik,
        // termasuk filter terakhir yang dipulihkan untuk halaman Pencocokan.
        const url = rememberedListURL(canonicalNavigationURL(new URL(value, location.href)));
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
            // Klaim & Pengembalian dipersiapkan dari seluruh halaman admin.
            // Pencocokan tetap menjadi tujuan prioritas kedua.
            dashboard: [config.claims, config.matching],
            verification: [config.claims, config.matching],
            items: [config.claims, config.matching],
            matching: [config.claims, config.items],
            claims: [config.dashboard, config.matching],
            statistics: [config.claims, config.matching],
            users: [config.claims, config.matching],
            settings: [config.claims, config.users]
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
            const contentType = response.headers.get('content-type') || '';
            if (!response.ok || response.redirected || response.url !== url.href ||
                !contentType.includes('text/html')) {
                throw Error(`Navigation response (status=${response.status}, redirected=${response.redirected}, finalURL=${response.url}, contentType=${contentType})`);
            }
            return response.text();
        });
    }

    async function navigate(url, historyMode, scroll, back) {
        url = canonicalNavigationURL(url);
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
        } catch (error) {
            if (!signal.aborted || timedOut) {
                console.warn('[FindBack Nav] Navigasi SPA gagal, menggunakan fallback:', {
                    from: fromPage,
                    to: pageOf(url.pathname),
                    reason: error?.message || error
                });
                if (location.href === from || location.href === url.href) {
                    if (historyMode === 'pop' || location.href === url.href) location.reload();
                    else location.assign(url.href);
                }
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

        let url = canonicalNavigationURL(new URL(link.href));
        const target = pageOf(url.pathname);
        if (url.origin !== location.origin || url.hash || !target) return;
        const current = main().dataset.fbNavPage;
        if (current === target && !target.endsWith('-detail') && target !== 'claims') {
            pending?.abort();
            return; // Listing lain tetap ditangani oleh pengelola halaman masing-masing.
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
        // Tab, reset, dan pagination Klaim juga melalui mesin global.
        // Ini menghindari dua pengelola AJAX yang saling berebut event/history.
        const preserveClaimsScroll = current === 'claims' && target === 'claims' ? position() : scroll;
        navigate(scroll ? url : rememberedListURL(url), 'push', preserveClaimsScroll, back);
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
        if (current === target && target !== 'statistics' && target !== 'claims' && !target.endsWith('-detail')) {
            displayedURL = url.href;
            return;
        }
        if (url.href === displayedURL && current === target) return;
        event.stopImmediatePropagation();
        const scroll = event.state?.fbAdminURL === url.href ? event.state.fbAdminScroll : undefined;
        navigate(url, 'pop', scroll);
    }, true);
})();
