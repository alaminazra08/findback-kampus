(function () {
  'use strict';
  function mount(signal, initialLoad) {
    const filters = document.querySelector('main[data-fb-nav-page="users"] form.fb-users-filters');
    if (!filters) return;
    const base = new URL(filters.action).pathname;
    if (location.pathname !== base) return;
    const key = 'findback:admin:users:filters:v1';
    const panelSelector = '.fb-users-panel';
    let controller = null;
    let serial = 0;
    signal.addEventListener('abort', () => {
      serial++;
      controller?.abort();
    }, { once: true });
    const getPanel = () => document.querySelector(panelSelector);
    function samePage(url) { try { const u = new URL(url, location.href); return u.origin === location.origin && u.pathname === base; } catch (_) { return false; } }
    function store(url) { try { const u = new URL(url, location.href); if (u.search) sessionStorage.setItem(key, u.search); else sessionStorage.removeItem(key); } catch (_) {} }
    async function navigate(url, mode) {
      const id = ++serial;
      if (controller) controller.abort();
      controller = new AbortController();
      const panel = getPanel();
      if (panel) panel.setAttribute('aria-busy', 'true');
      const y = window.scrollY, x = window.scrollX;
      try {
        const response = await fetch(url, { credentials: 'same-origin', headers: {'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}, cache:'no-store', signal:controller.signal });
        if (!response.ok || response.redirected || !samePage(response.url)) throw Error('Invalid response');
        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const next = doc.querySelector(panelSelector);
        if (!next || !getPanel()) throw Error('Missing panel');
        if (id !== serial) return;
        getPanel().replaceWith(document.importNode(next, true));
        if (mode === 'push') history.pushState({fbUsers:true}, '', url);
        store(url);
        window.scrollTo(x, y);
      } catch (err) {
        if (err.name === 'AbortError' || id !== serial) return;
        location.assign(url);
      } finally { if (id === serial && getPanel()) getPanel().removeAttribute('aria-busy'); }
    }
    document.addEventListener('submit', function (e) {
      const form = e.target.closest('.fb-users-filters');
      if (!form || e.defaultPrevented) return;
      e.preventDefault();
      const u = new URL(form.action, location.href);
      const params = new URLSearchParams(new FormData(form));
      for (const [k,v] of Array.from(params)) if (!v || v === 'all') params.delete(k);
      u.search = params.toString();
      navigate(u.href, 'push');
    }, { signal });
    document.addEventListener('click', function (e) {
      if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
      const link = e.target.closest('a');
      if (!link || !samePage(link.href) || link.target || link.hasAttribute('download')) return;
      if (!link.closest('.fb-users-reset, .fb-users-pagination')) return;
      e.preventDefault();
      if (link.matches('.fb-users-reset')) { try { sessionStorage.removeItem(key); } catch (_) {} }
      navigate(link.href, 'push');
    }, { signal });
    window.addEventListener('popstate', function () { if (location.pathname === base) navigate(location.href, 'none'); }, { signal });
    // Restore only on a fresh, unfiltered visit. Explicit reset remains unfiltered.
    try {
      const saved = sessionStorage.getItem(key);
      if (initialLoad && !location.search && saved?.startsWith('?') && !history.state?.fbUsers && !sessionStorage.getItem(key + ':skip')) {
        const url = base + saved;
        history.replaceState({fbUsers:true}, '', url);
        navigate(location.href, 'none');
      }
    } catch (_) {}
  }

  let scope;
  function render(initialLoad = false) {
    scope?.abort();
    scope = new AbortController();
    mount(scope.signal, initialLoad);
  }

  document.addEventListener('fb:admin-page', () => render());
  render(true);
})();
