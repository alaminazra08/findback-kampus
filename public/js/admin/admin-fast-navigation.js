(() => {
    'use strict';

    const { account, itemDetail, verificationDetail, matchingDetail, userDetail, claimDetail, ...routes } = document.currentScript.dataset;
    const pages = new Map(Object.entries(routes).map(([page, href]) => [new URL(href).pathname, page]));
    const main = () => document.querySelector('main[data-fb-nav-page]');
    const menuLinks = '.fb-sidebar a.fb-nav-link[href]';
    const entryLinks = menuLinks + ', .fb-sidebar a.fb-brand[href]:not([role=button]), .fb-topbar-right a[href]';
    const detailRoutes = [
        { url: itemDetail, page: 'item-detail', list: 'items' },
        { url: verificationDetail, page: 'verification-detail', list: 'verification' },
        { url: matchingDetail, page: 'match-detail', list: 'matching' },
        { url: userDetail, page: 'user-detail', list: 'users' },
        { url: claimDetail, page: 'claim-detail', list: 'claims' },
    ].map(({ url, page, list }) => ({
        prefix: new URL(url).pathname.slice(0, -'__item__'.length),
        page,
        list,
    }));
    function pageFor(pathname) {
        return pages.get(pathname) ?? detailRoutes.find(({ prefix }) =>
            pathname.startsWith(prefix) && /^\d+$/.test(pathname.slice(prefix.length)))?.page ?? null;
    }
    const handled = url => url.origin === location.origin && !url.hash && pageFor(url.pathname) !== null;
    const position = () => ({ top: window.scrollY, left: window.scrollX });
    const pagePositions = new Map();
    let displayed = location.href;
    const lastListUrls = new Map();
    let pending;
    let pendingUrl;
    let prefetched;

    function filters(url, save = false) {
        if (!handled(url) || !pages.has(url.pathname) || pageFor(url.pathname) === 'dashboard') return url;
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
        const link = event.target.closest?.(entryLinks + ', main[data-fb-nav-page] a[href]');
        if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;
        const url = linkUrl(link);
        if (!handled(url) || url.href === displayed || (prefetched?.url === url.href && prefetched.until > Date.now())) return;
        prefetched?.controller.abort();
        const controller = new AbortController();
        const task = load(url, controller.signal).catch(() => null);
        prefetched = { url: url.href, task, controller, until: Date.now() + 3000 };
    }

    function waitForStyles(doc, destination, signal, staged) {
        const selector = 'link[rel="stylesheet"]';
        const unused = new Set(document.head.querySelectorAll(selector));
        const ready = [...doc.head.querySelectorAll(selector)].map(source => {
            const href = new URL(source.getAttribute('href'), destination).href;
            const media = source.getAttribute('media');
            const existing = [...unused].find(link => link.href === href && link.media === source.media &&
                link.integrity === source.integrity && link.crossOrigin === source.crossOrigin &&
                link.referrerPolicy === source.referrerPolicy && link.sheet);
            if (existing) {
                unused.delete(existing);
                return [existing, media];
            }

            const link = document.importNode(source, true);
            link.href = href;
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

    async function navigate(url, back = false, historyScroll = undefined, submittedPage = null, itemsPagination = false) {
        pending?.abort();
        const controller = new AbortController();
        const { signal } = controller;
        pending = controller;
        pendingUrl = url.href;
        const staged = [];
        const warm = prefetched;
        prefetched = null;
        const cached = warm?.url === url.href && warm.until > Date.now() ? warm : null;
        if (cached) signal.addEventListener('abort', () => cached.controller.abort(), { once: true });
        else warm?.controller.abort();
        const timeout = setTimeout(() => controller.abort(new Error('Navigation timed out')), 12000);
        try {
            const result = submittedPage || (cached && await cached.task) || await load(url, signal);
            signal.throwIfAborted();
            const { html, destination } = result;
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const next = doc.querySelector('main[data-fb-nav-page]');
            const crumb = doc.querySelector('.fb-breadcrumb');
            const topbar = doc.querySelector('.fb-topbar-right');
            if (doc.compatMode !== 'CSS1Compat' || next?.dataset.fbNavPage !== pageFor(destination.pathname) ||
                !crumb || !topbar || !main()) throw new Error('Incomplete admin page');

            if (itemsPagination && pageFor(destination.pathname) === 'items') {
                const table = main().querySelector('.fb-items-table-wrap');
                const pager = main().querySelector('.fb-items-pagination');
                const nextTable = next.querySelector('.fb-items-table-wrap');
                const nextPager = next.querySelector('.fb-items-pagination');
                if (!table || !pager || !nextTable) throw new Error('Incomplete report list');

                const before = position();
                const pagerTop = pager.getBoundingClientRect().top;
                const replacement = nextPager ? document.importNode(nextPager, true) : null;
                if (!back) {
                    history.replaceState({ ...history.state, adminScroll: before }, '');
                    history.pushState({}, '', destination.href);
                }
                table.replaceWith(document.importNode(nextTable, true));
                if (replacement) pager.replaceWith(replacement);
                else pager.remove();

                displayed = destination.href;
                lastListUrls.set('items', displayed);
                filters(destination, true);
                const top = replacement
                    ? window.scrollY + replacement.getBoundingClientRect().top - pagerTop
                    : before.top;
                window.scrollTo({ top, left: before.left, behavior: 'instant' });
                return;
            }

            const { unused, links } = await waitForStyles(doc, destination, signal, staged);
            signal.throwIfAborted();
            const currentPosition = position();
            const fromPath = new URL(displayed).pathname;
            const fromPage = pageFor(fromPath);
            if (detailRoutes.some(({ list }) => list === fromPage)) lastListUrls.set(fromPage, displayed);
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
            document.dispatchEvent(new Event('fb:admin-before-swap'));
            staged.length = 0;
            main().replaceWith(document.importNode(next, true));
            document.querySelector('.fb-breadcrumb').replaceWith(document.importNode(crumb, true));
            const currentTopbar = document.querySelector('.fb-topbar-right');
            const nextTopbar = document.importNode(topbar, true);
            const currentAvatar = currentTopbar.querySelector('.fb-account-profile-link [data-account-photo]');
            const nextAvatar = nextTopbar.querySelector('.fb-account-profile-link [data-account-photo]');

            // Keep the decoded image when the server returns the same avatar.
            if (currentAvatar && nextAvatar &&
                currentAvatar.dataset.accountPhoto === nextAvatar.dataset.accountPhoto &&
                currentAvatar.textContent.trim() === nextAvatar.textContent.trim() &&
                currentAvatar.querySelector('img')?.getAttribute('src') === nextAvatar.querySelector('img')?.getAttribute('src')) {
                nextAvatar.replaceWith(currentAvatar);
            }
            currentTopbar.replaceWith(nextTopbar);
            document.title = doc.title;
            displayed = destination.href;
            for (const link of document.querySelectorAll(menuLinks)) {
                const sidebarPage = pageFor(new URL(link.href).pathname);
                const active = new URL(link.href).pathname === destination.pathname ||
                    detailRoutes.some(({ page, list }) => sidebarPage === list && pageFor(destination.pathname) === page);
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
                if (itemsPagination && !back) {
                    const pager = main()?.querySelector('.fb-items-pagination');
                    if (pager && !pager.querySelector('[data-fb-pagination-error]')) {
                        const error = document.createElement('span');
                        error.dataset.fbPaginationError = '';
                        error.setAttribute('role', 'alert');
                        error.textContent = 'Halaman gagal dimuat. Coba lagi.';
                        pager.append(error);
                    }
                    return;
                }
                if (submittedPage) throw new Error('Decision view could not be applied');
                if (back) location.reload();
                else location.assign(url.href);
            }
        } finally {
            clearTimeout(timeout);
            cached?.controller.abort();
            for (const link of staged) link.remove();
            if (pending === controller) {
                pending = undefined;
                pendingUrl = undefined;
            }
        }
    }

    function linkUrl(link) {
        const url = new URL(link.href);
        const listPage = pageFor(url.pathname);
        const detail = detailRoutes.find(({ page }) => page === main()?.dataset.fbNavPage);
        if (detail?.list === listPage && link.hasAttribute('data-fb-detail-back')) {
            const lastListUrl = lastListUrls.get(listPage);
            if (lastListUrl) return new URL(lastListUrl);
            return filters(url);
        }
        return link.matches(entryLinks) ? filters(url) : url;
    }

    if (main() && location.search) filters(new URL(location.href), true);
    document.addEventListener('pointerover', warm, { passive: true });
    document.addEventListener('focusin', warm);
    document.addEventListener('pointerdown', warm, { passive: true });

    document.addEventListener('click', event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || !main()) return;
        const link = event.target.closest?.(entryLinks + ', main[data-fb-nav-page] a[href]');
        if (!link || (link.target && link.target !== '_self') || link.hasAttribute('download')) return;
        const url = linkUrl(link);
        if (!handled(url)) return;
        event.preventDefault();
        if (url.href === pendingUrl && pending && !pending.signal.aborted) return;
        if (url.href !== displayed || location.href !== displayed) {
            const itemsPagination = Boolean(link.closest('.fb-items-pagination')) &&
                main()?.dataset.fbNavPage === 'items' && pageFor(url.pathname) === 'items';
            navigate(url, false, undefined, null, itemsPagination);
        }
        else {
            pending?.abort();
            const menu = document.getElementById('fb-menu-toggle');
            if (menu?.getAttribute('aria-expanded') === 'true') menu.click();
        }
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

    async function submitDecision(event) {
        const form = event.target;
        if (event.defaultPrevented || !form.matches?.('form[data-fb-async-decision]') ||
            !main()?.contains(form) || form.method.toLowerCase() !== 'post' || new URL(form.action).origin !== location.origin) return;
        event.preventDefault();
        const profilePanel = form.closest('.fb-admin-profile-body');
        if (form.hasAttribute('aria-busy') || profilePanel?.hasAttribute('aria-busy')) return;

        const data = new FormData(form, event.submitter);
        const button = event.submitter || form.querySelector('button[type="submit"]');
        const feedback = document.createElement('p');
        form.querySelector('[data-fb-decision-feedback]')?.remove();
        feedback.dataset.fbDecisionFeedback = '';
        feedback.setAttribute('role', 'status');
        feedback.textContent = 'Memproses…';
        if (!profilePanel) form.append(feedback);
        button?.classList.toggle('fb-admin-profile-processing', Boolean(profilePanel));
        form.setAttribute('aria-busy', 'true');
        const buttons = profilePanel
            ? [...profilePanel.querySelectorAll('.fb-admin-profile-actions button:not(:disabled)')]
            : button ? [button] : [];
        profilePanel?.setAttribute('aria-busy', 'true');
        for (const control of buttons) control.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: data,
                headers: { Accept: 'text/html' },
            });
            const destination = new URL(response.url);
            if (!response.ok || !handled(destination) || !response.headers.get('content-type')?.includes('text/html')) {
                throw new Error('Unexpected response');
            }
            const html = await response.text();
            if (!form.isConnected) return;
            prefetched?.controller.abort();
            prefetched = null;
            const stay = destination.href === displayed;
            await navigate(destination, stay, stay ? position() : undefined, { html, destination });
        } catch {
            if (profilePanel) profilePanel.querySelector('.fb-admin-profile-actions')?.after(feedback);
            feedback.setAttribute('role', 'alert');
            feedback.textContent = 'Hasil tindakan belum bisa dikonfirmasi. Periksa status sebelum mencoba lagi.';
        } finally {
            form.removeAttribute('aria-busy');
            profilePanel?.removeAttribute('aria-busy');
            button?.classList.remove('fb-admin-profile-processing');
            for (const control of buttons) control.disabled = false;
        }
    }

    document.addEventListener('submit', event => {
        // Review forms have their own confirmation dialog. Capture their final submit
        // before page-level handlers can stop it from reaching the navigation layer.
        if (event.target.closest?.('dialog.fb-review-dialog')) submitDecision(event);
    }, true);

    document.addEventListener('submit', submitDecision);

    document.addEventListener('change', event => {
        if (event.target.matches?.('[data-fb-stat-period]') && event.target.form) {
            navigate(search(event.target.form));
        }
    });

    function followAction(event) {
        if (event.defaultPrevented || !main() || typeof event.detail !== 'string') return;
        const url = new URL(event.detail, location.href);
        if (!handled(url)) return;
        event.preventDefault();
        prefetched?.controller.abort();
        prefetched = null;
        navigate(url, url.href === displayed, url.href === displayed ? position() : undefined);
    }

    document.addEventListener('fb:notification-open', followAction);
    document.addEventListener('fb:item-published', followAction);

    document.addEventListener('fb:notification-history', () => {
        displayed = location.href;
        filters(new URL(displayed), true);
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
