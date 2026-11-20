(() => {
    'use strict';

    const { account, ...routes } = document.currentScript.dataset;
    const pages = new Map(Object.entries(routes).map(([page, href]) => [new URL(href).pathname, page]));
    const main = () => document.querySelector('main[data-fb-nav-page]');
    const menuLinks = '.fb-sidebar a.fb-nav-link[href]';
    const handled = url => url.origin === location.origin && !url.hash && pages.has(url.pathname);
    const position = () => ({ top: window.scrollY, left: window.scrollX });
    const pagePositions = new Map();
    let displayed = location.href;
    let pending;
    let prefetched;

    function filters(url, save = false) {
        if (!handled(url) || pages.get(url.pathname) === 'dashboard') return url;
        const key = 'findback:admin:filters:' + account + ':' + url.pathname;
        try {
            if (save) {
                const params = new URLSearchParams(url.search);
                params.delete('page');
                if (params.size) sessionStorage.setItem(key, params.toString());
                else sessionStorage.removeItem(key);
            } else if (!url.search) {
                url.search = sessionStorage.getItem(key) || '';
            }
        } catch {
            // Navigation still works when session storage is unavailable.
        }
        return url;
    }

    async function load(url, signal) {
        const response = await fetch(url, { signal, cache: 'no-store', headers: { Accept: 'text/html' } });
        const destination = new URL(response.url);
        if (!response.ok || !handled(destination) || destination.pathname !== url.pathname ||
            !response.headers.get('content-type')?.includes('text/html')) {
            throw new Error('Unexpected navigation response');
        }
        return { html: await response.text(), destination };
    }

    function warm(event) {
        if (!main() || pending || navigator.connection?.saveData) return;
        const link = event.target.closest?.(menuLinks);
        if (!link || link.target || link.hasAttribute('download')) return;
        const url = filters(new URL(link.href));
        if (!handled(url) || url.href === displayed || prefetched?.url === url.href) return;
        prefetched?.controller.abort();
        const controller = new AbortController();
        const task = load(url, controller.signal).catch(() => null);
        prefetched = { url: url.href, task, controller, until: Date.now() + 3000 };
    }

    function waitForStyles(doc, signal, staged) {
        const selector = 'link[rel="stylesheet"]';
        const unused = new Set(document.head.querySelectorAll(selector));
        const ready = [...doc.head.querySelectorAll(selector)].map(source => {
            const media = source.getAttribute('media');
            const existing = [...unused].find(link => link.href === source.href && link.media === source.media &&
                link.integrity === source.integrity && link.crossOrigin === source.crossOrigin &&
                link.referrerPolicy === source.referrerPolicy && link.sheet);
            if (existing) {
                unused.delete(existing);
                return [existing, media];
            }

            const link = document.importNode(source, true);
            link.media = 'not all';
            staged.push(link);
            return new Promise((resolve, reject) => {
                link.addEventListener('load', () => resolve([link, media]), { once: true, signal });
                link.addEventListener('error', () => reject(new Error('Stylesheet failed to load')), { once: true, signal });
                signal.addEventListener('abort', () => reject(signal.reason), { once: true });
                document.head.append(link);
            });
        });
        return Promise.all(ready).then(links => ({ unused, links }));
    }

    function applyStyles(unused, links) {
        for (const link of unused) link.remove();
        let following = null;
        for (const [link, media] of [...links].reverse()) {
            let sibling = link.nextElementSibling;
            while (sibling && !sibling.matches('link[rel="stylesheet"]')) sibling = sibling.nextElementSibling;
            if (sibling !== following) {
                if (document.head.moveBefore) document.head.moveBefore(link, following);
                else document.head.insertBefore(link, following);
            }
            if (media === null) link.removeAttribute('media');
            else link.media = media;
            following = link;
        }
    }

    async function navigate(url, back = false, historyScroll = undefined) {
        pending?.abort();
        const controller = new AbortController();
        const { signal } = controller;
        pending = controller;
        const staged = [];
        const warm = prefetched;
        prefetched = null;
        const cached = warm?.url === url.href && warm.until > Date.now() ? warm : null;
        if (cached) signal.addEventListener('abort', () => cached.controller.abort(), { once: true });
        else warm?.controller.abort();
        const timeout = setTimeout(() => controller.abort(new Error('Navigation timed out')), 12000);
        try {
            const result = (cached && await cached.task) || await load(url, signal);
            signal.throwIfAborted();
            const { html, destination } = result;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.querySelector('main[data-fb-nav-page]');
            const crumb = doc.querySelector('.fb-breadcrumb');
            const topbar = doc.querySelector('.fb-topbar-right');
            if (doc.compatMode !== 'CSS1Compat' || next?.dataset.fbNavPage !== pages.get(destination.pathname) ||
                !crumb || !topbar || !main()) throw new Error('Incomplete admin page');

            const { unused, links } = await waitForStyles(doc, signal, staged);
            signal.throwIfAborted();
            const currentPosition = position();
            const fromPath = new URL(displayed).pathname;
            const samePage = fromPath === destination.pathname;
            const scrollTarget = back
                ? historyScroll ?? (samePage ? currentPosition : pagePositions.get(destination.pathname))
                : samePage ? currentPosition : pagePositions.get(destination.pathname);

            if (!back) {
                pagePositions.set(fromPath, currentPosition);
                history.replaceState({ ...history.state, adminScroll: currentPosition }, '');
                history.pushState({}, '', destination.href);
            } else if (destination.href !== location.href) {
                history.replaceState(history.state, '', destination.href);
            }
            applyStyles(unused, links);
            staged.length = 0;
            main().replaceWith(document.importNode(next, true));
            document.querySelector('.fb-breadcrumb').replaceWith(document.importNode(crumb, true));
            document.querySelector('.fb-topbar-right').replaceWith(document.importNode(topbar, true));
            document.title = doc.title;
            displayed = destination.href;
            for (const link of document.querySelectorAll(menuLinks)) {
                const active = new URL(link.href).pathname === destination.pathname;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            }
            filters(destination, true);
            const menu = document.getElementById('fb-menu-toggle');
            if (menu?.getAttribute('aria-expanded') === 'true') menu.click();
            document.dispatchEvent(new Event('fb:admin-page'));
            window.scrollTo({ top: 0, left: 0, ...scrollTarget, behavior: 'instant' });
        } catch {
            if (pending === controller && (!signal.aborted || signal.reason?.message === 'Navigation timed out')) {
                if (back) location.reload();
                else location.assign(url.href);
            }
        } finally {
            clearTimeout(timeout);
            cached?.controller.abort();
            for (const link of staged) link.remove();
            if (pending === controller) pending = undefined;
        }
    }

    if (main() && location.search) filters(new URL(location.href), true);
    document.addEventListener('pointerover', warm, { passive: true });
    document.addEventListener('focusin', warm);

    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || !main()) return;
        const link = event.target.closest?.(menuLinks + ', main[data-fb-nav-page] a[href]');
        if (!link || link.target || link.hasAttribute('download')) return;
        const url = new URL(link.href);
        if (!handled(url)) return;
        event.preventDefault();
        if (link.matches(menuLinks)) filters(url);
        if (url.href !== displayed || location.href !== displayed) navigate(url);
        else pending?.abort();
    });

    function search(form, submitter) {
        const url = new URL(form.action);
        const data = new FormData(form, submitter);
        url.search = '';
        for (const [key, value] of data) {
            if (typeof value === 'string' && value.trim()) url.searchParams.append(key, value);
        }
        return url;
    }

    document.addEventListener('submit', event => {
        const form = event.target;
        if (event.defaultPrevented || !main() || form.method !== 'get' || form.target ||
            event.submitter?.formTarget || event.submitter?.hasAttribute('formaction') ||
            event.submitter?.hasAttribute('formmethod') || !handled(new URL(form.action))) return;
        event.preventDefault();
        navigate(search(form, event.submitter));
    });

    document.addEventListener('change', event => {
        if (event.target.matches?.('[data-fb-stat-period]') && event.target.form) {
            navigate(search(event.target.form));
        }
    });

    window.addEventListener('scrollend', () => {
        if (location.href === displayed && handled(new URL(displayed))) {
            history.replaceState({ ...history.state, adminScroll: position() }, '');
        }
    });
    window.addEventListener('popstate', event => {
        pending?.abort();
        const url = new URL(location.href);
        if (url.href === displayed) return;
        if (!handled(url) || !main()) location.reload();
        else navigate(url, true, event.state?.adminScroll);
    });
    window.addEventListener('pagehide', () => {
        const navigation = pending;
        pending = undefined;
        navigation?.abort();
        prefetched?.controller.abort();
        prefetched = null;
    });
})();
