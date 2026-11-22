(() => {
    let dispose;
    function init() {
        dispose?.();
        dispose = undefined;
        const root = document.querySelector('[data-fb-stats-url][data-fb-index-url]');
        if (!root) return;
        const controller = new AbortController();
        const { signal } = controller;
        let relativeTimer;
        dispose = () => {
            controller.abort();
            stopPolling();
            clearInterval(relativeTimer);
            cancelAnimationFrame(scrollSaveFrame);
        };

        const statsUrl = root.dataset.fbStatsUrl;
        const indexUrl = root.dataset.fbIndexUrl;
        const storageKey = `fb-notification-center:${root.dataset.fbUserKey || 'admin'}:v2`;
        const fmt = new Intl.NumberFormat('id-ID');
        const cards = Object.fromEntries(
            [...root.querySelectorAll('[data-fb-stat]')].map(card => [card.dataset.fbStat, card])
        );
        const pill = root.querySelector('[data-fb-unread-pill]');
        const markAll = root.querySelector('[data-fb-mark-all]');
        const form = root.querySelector('[data-fb-filter-form]');
        const listHost = root.querySelector('[data-fb-live-list]');
        const tabs = root.querySelector('[data-fb-notif-tabs]');
        let busy = false;
        let readVersion = 0;
        let pollTimer = null;
        let scrollSaveFrame = 0;
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

        const currentUrl = () => new URL(window.location.href);
        const safeReadState = () => {
            try { return JSON.parse(sessionStorage.getItem(storageKey) || 'null'); }
            catch (_) { return null; }
        };
        const safeWriteState = (extra = {}) => {
            try {
                const url = currentUrl();
                sessionStorage.setItem(storageKey, JSON.stringify({
                    url: `${url.pathname}${url.search}`,
                    scrollY: window.scrollY,
                    savedAt: Date.now(),
                    ...extra,
                }));
            } catch (_) { /* Storage may be unavailable in private browsing. */ }
        };

        // Restore after the filtered list has been rendered and the browser has
        // completed the current layout pass. This avoids a jump to the top.
        function restoreViewport(y) {
            const targetY = Math.max(0, Number(y) || 0);
            requestAnimationFrame(() => requestAnimationFrame(() => {
                if (signal.aborted) return;
                window.scrollTo({ top: targetY, left: 0, behavior: 'auto' });
                requestAnimationFrame(() => { if (!signal.aborted) safeWriteState(); });
            }));
        }

        function startPolling() {
            if (pollTimer) return;
            pollTimer = window.setInterval(poll, 2000);
        }

        function stopPolling() {
            if (!pollTimer) return;
            window.clearInterval(pollTimer);
            pollTimer = null;
        }

        function paramsFromUrl(url) {
            return {
                category: url.searchParams.get('category') || 'all',
                q: url.searchParams.get('q') || '',
                sort: url.searchParams.get('sort') || 'newest',
                page: url.searchParams.get('page') || '1',
            };
        }

        function syncControls(url) {
            if (!form) return;
            const params = paramsFromUrl(url);
            const category = form.querySelector('[name="category"]');
            const search = form.querySelector('[name="q"]');
            const sort = form.querySelector('[name="sort"]');
            if (category) category.value = params.category;
            if (search) search.value = params.q;
            if (sort) sort.value = params.sort;

            tabs?.querySelectorAll('a').forEach(link => {
                const linkUrl = new URL(link.href, window.location.origin);
                const active = (linkUrl.searchParams.get('category') || 'all') === params.category;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            });
        }

        function updateCounters(data) {
            for (const [key, value] of Object.entries({
                total: data.total,
                unread: data.unread,
                today: data.today,
            })) {
                const number = cards[key]?.querySelector('[data-fb-stat-value]');
                if (number && Number.isFinite(Number(value))) number.textContent = fmt.format(Number(value));
            }

            const unread = Number(data.unread) || 0;
            cards.unread?.classList.toggle('is-highlight', unread > 0);
            const desc = cards.unread?.querySelector('[data-fb-unread-description]');
            if (desc) desc.textContent = unread > 0
                ? 'Ada notifikasi yang perlu diperiksa'
                : 'Semua notifikasi telah dibaca';

            if (pill) {
                const count = pill.lastChild;
                if (count?.nodeType === Node.TEXT_NODE) count.textContent = `${fmt.format(unread)} belum dibaca`;
            }
            if (markAll) markAll.disabled = unread === 0;

        }

        function updateRelativeTimes() {
            const now = Date.now();
            root.querySelectorAll('time[data-fb-relative-time][datetime]').forEach(node => {
                const timestamp = Date.parse(node.dateTime || node.getAttribute('datetime'));
                if (!Number.isFinite(timestamp)) return;
                const seconds = Math.max(0, Math.floor((now - timestamp) / 1000));
                let label;
                if (seconds < 45) label = 'Baru saja';
                else if (seconds < 90) label = '1 menit yang lalu';
                else if (seconds < 3600) label = `${Math.floor(seconds / 60)} menit yang lalu`;
                else if (seconds < 5400) label = '1 jam yang lalu';
                else if (seconds < 86400) label = `${Math.floor(seconds / 3600)} jam yang lalu`;
                else if (seconds < 172800) label = '1 hari yang lalu';
                else if (seconds < 604800) label = `${Math.floor(seconds / 86400)} hari yang lalu`;
                else label = new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(timestamp);
                node.textContent = label;
            });
        }

        function updateList(html) {
            if (typeof html !== 'string' || !listHost) return false;
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextContent = parsed.querySelector('[data-fb-live-list-content]');
            if (!nextContent) return false;
            const currentContent = listHost.querySelector('[data-fb-live-list-content]');
            const withoutRelativeTimes = content => {
                const copy = content.cloneNode(true);
                copy.querySelectorAll('time[data-fb-relative-time][datetime]').forEach(time => { time.textContent = ''; });
                return copy;
            };
            if (currentContent && withoutRelativeTimes(currentContent).isEqualNode(withoutRelativeTimes(nextContent))) {
                updateRelativeTimes();
                return false;
            }
            listHost.replaceChildren(document.importNode(nextContent, true));
            updateRelativeTimes();
            return true;
        }

        async function requestList(url, { historyMode = 'none', preserveScroll = true, restoreScroll = null, syncForm = true } = {}) {
            if (busy || signal.aborted) return false;
            busy = true;
            const version = readVersion;
            const scrollAtRequest = window.scrollY;
            const target = new URL(url, window.location.origin);
            target.pathname = indexUrl ? new URL(indexUrl, window.location.origin).pathname : target.pathname;
            const requestUrl = new URL(statsUrl, window.location.origin);
            ['category', 'q', 'sort', 'page'].forEach(key => {
                requestUrl.searchParams.delete(key);
                if (target.searchParams.has(key)) requestUrl.searchParams.set(key, target.searchParams.get(key));
            });

            try {
                const response = await fetch(requestUrl.toString(), {
                    credentials: 'same-origin',
                    cache: 'no-store',
                    signal,
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok || response.redirected) return false;
                const data = await response.json();
                if (signal.aborted || version !== readVersion) return false;
                const currentScroll = window.scrollY;
                updateList(data.list_html);
                updateCounters(data);
                if (syncForm) syncControls(target);

                if (historyMode === 'push') history.pushState({}, '', target.pathname + target.search);
                else if (historyMode === 'replace') history.replaceState({}, '', target.pathname + target.search);

                if (historyMode !== 'none') document.dispatchEvent(new Event('fb:notification-history'));

                const canRestore = Number.isFinite(restoreScroll) && currentScroll === scrollAtRequest;
                const y = canRestore ? restoreScroll : (preserveScroll || Number.isFinite(restoreScroll) ? currentScroll : 0);
                // Apply any correction in the same turn so a later frame cannot undo new input.
                if (window.scrollY !== y) window.scrollTo({ top: y, left: window.scrollX, behavior: 'instant' });
                safeWriteState();
                return true;
            } catch (_) {
                return false;
            } finally {
                busy = false;
                if (version !== readVersion) requestList(target, { historyMode, preserveScroll, restoreScroll, syncForm });
            }
        }

        form?.addEventListener('submit', event => {
            event.preventDefault();
            const values = new FormData(form);
            const target = new URL(indexUrl, window.location.origin);
            const category = String(values.get('category') || 'all');
            const q = String(values.get('q') || '').trim();
            const sort = String(values.get('sort') || 'newest');
            if (category !== 'all') target.searchParams.set('category', category);
            if (q) target.searchParams.set('q', q);
            if (sort !== 'newest') target.searchParams.set('sort', sort);
            requestList(target, { historyMode: 'push', preserveScroll: true });
        });

        root.addEventListener('click', event => {
            const tab = event.target.closest('[data-fb-notif-tabs] a');
            if (tab && new URL(tab.href, window.location.href).origin === window.location.origin) {
                event.preventDefault();
                const target = new URL(tab.href);
                const active = currentUrl();
                ['q', 'sort'].forEach(key => {
                    if (active.searchParams.has(key)) target.searchParams.set(key, active.searchParams.get(key));
                });
                requestList(target, { historyMode: 'push', preserveScroll: true });
                return;
            }

            const pageLink = event.target.closest('[data-fb-live-list] .fb-notif-center-pagination a');
            if (pageLink && pageLink.href && !pageLink.classList.contains('is-disabled')) {
                event.preventDefault();
                requestList(new URL(pageLink.href), { historyMode: 'push', preserveScroll: true });
            }
        });

        root.querySelector('[data-fb-reset]')?.addEventListener('click', event => {
            event.preventDefault();
            const target = new URL(indexUrl, window.location.origin);
            if (form) {
                const search = form.querySelector('[name="q"]');
                const sort = form.querySelector('[name="sort"]');
                const category = form.querySelector('[name="category"]');
                if (search) search.value = '';
                if (sort) sort.value = 'newest';
                if (category) category.value = 'all';
            }
            requestList(target, { historyMode: 'push', preserveScroll: true });
        });


        // Keep the current filter and viewport when the admin visits another menu
        // and later returns to this page in the same browser tab.
        window.addEventListener('scroll', () => {
            if (scrollSaveFrame) return;
            scrollSaveFrame = requestAnimationFrame(() => {
                scrollSaveFrame = 0;
                safeWriteState();
            });
        }, { passive: true, signal });

        document.addEventListener('fb:notifications-read-all', () => {
            readVersion++;
            updateCounters({ unread: 0 });
            poll();
        }, { signal });

        async function poll() {
            if (busy || document.hidden) return;
            await requestList(currentUrl(), { historyMode: 'none', preserveScroll: true, syncForm: false });
        }

        async function initialize() {
            const url = currentUrl();
            const saved = safeReadState();
            const currentPathAndQuery = `${url.pathname}${url.search}`;
            const hasUrlState = ['category', 'q', 'sort', 'page'].some(key => url.searchParams.has(key));
            // sessionStorage is scoped to this browser tab and naturally clears
            // when the tab closes, so keep the user's applied state for the full tab session.
            const savedPathMatches = typeof saved?.url === 'string' &&
                saved.url.startsWith(new URL(indexUrl, window.location.origin).pathname);

            if (!hasUrlState && savedPathMatches) {
                // Sidebar navigation returns to the canonical URL; restore the
                // last applied filters, page number and viewport for this tab.
                const savedUrl = new URL(saved.url, window.location.origin);
                syncControls(savedUrl);
                await requestList(savedUrl, {
                    historyMode: 'replace',
                    preserveScroll: false,
                    restoreScroll: Number(saved.scrollY) || 0,
                });
            } else if (hasUrlState) {
                syncControls(url);
                if (savedPathMatches && saved.url === currentPathAndQuery) {
                    // Direct URL/history navigation with the same applied filters
                    // should also restore the previous position.
                    await requestList(url, {
                        historyMode: 'none',
                        preserveScroll: false,
                        restoreScroll: Number(saved.scrollY) || 0,
                        syncForm: true,
                    });
                } else {
                    safeWriteState();
                }
            } else {
                syncControls(url);
                safeWriteState();
            }

            if (signal.aborted) return;
            updateRelativeTimes();
            relativeTimer = window.setInterval(updateRelativeTimes, 30000);
            startPolling();
            poll();
        }

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) poll();
        }, { signal });

        // Save the exact applied state before navigating to Dashboard or another
        // menu. If the browser uses BFCache, restart live refresh on return.
        window.addEventListener('pagehide', () => {
            safeWriteState();
            stopPolling();
        }, { signal });
        window.addEventListener('pageshow', event => {
            if (event.persisted) {
                startPolling();
                poll();
                restoreViewport(safeReadState()?.scrollY || 0);
            }
        }, { signal });

        initialize();
    }

    document.addEventListener('fb:admin-before-swap', () => {
        dispose?.();
        dispose = undefined;
    });
    document.addEventListener('fb:admin-page', init);
    init();
})();
