(() => {
  'use strict';
  function mount(signal) {
    const form = document.querySelector('main[data-fb-nav-page="claims"] form.fb-claim-filters');
    if (!form) return;
    const base = new URL(form.action).pathname.replace(/\/+$/, '');
    const panelSelector = '.fb-claim-panel';
    if (location.pathname.replace(/\/+$/, '') !== base || !document.querySelector(panelSelector)) return;
    let requestId = 0;
    let controller = null;
    signal.addEventListener('abort', () => {
      requestId++;
      controller?.abort();
    }, { once: true });
    const valid = (raw) => {
      try {
        const u = new URL(raw, location.href);
        return u.origin === location.origin && u.pathname.replace(/\/+$/, '') === base ? u : null;
      } catch (_) { return null; }
    };
    const busy = (on) => {
      const p = document.querySelector(panelSelector);
      if (p) on ? p.setAttribute('aria-busy','true') : p.removeAttribute('aria-busy');
    };
    async function navigate(raw, mode = 'push') {
      const url = valid(raw);
      if (!url) return;
      const id = ++requestId;
      if (controller) controller.abort();
      controller = new AbortController();
      const x = scrollX, y = scrollY;
      busy(true);
      try {
        const response = await fetch(url.href, {
          credentials: 'same-origin', cache: 'no-store',
          headers: {'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html'},
          signal: controller.signal
        });
        if (!response.ok || response.redirected || !valid(response.url) ||
            !(response.headers.get('content-type') || '').includes('text/html')) throw Error('Unexpected response');
        const html = await response.text();
        if (id !== requestId) return;
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const next = doc.querySelector(panelSelector);
        const current = document.querySelector(panelSelector);
        if (!next || !current || !next.querySelector('form.fb-claim-filters') ||
            !next.querySelector('.fb-claim-table')) throw Error('Missing claims listing');
        current.replaceWith(document.importNode(next, true));
        if (mode === 'push' && url.href !== location.href) {
          history.pushState({fbClaimsAjax:true}, '', url.href);
        }
        window.scrollTo({left:x, top:y, behavior:'instant'});
      } catch (error) {
        if (error.name === 'AbortError' || id !== requestId) return;
        location.assign(url.href);
      } finally {
        if (id === requestId) busy(false);
      }
    }
    document.addEventListener('submit', (e) => {
      const form = e.target;
      if (e.defaultPrevented || !(form instanceof HTMLFormElement) ||
          !form.matches('.fb-claim-panel form.fb-claim-filters') || form.method.toLowerCase() !== 'get') return;
      const url = valid(form.action);
      if (!url) return;
      url.search = new URLSearchParams(new FormData(form)).toString();
      e.preventDefault();
      navigate(url.href);
    }, { signal });
    document.addEventListener('click', (e) => {
      if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
      const link = e.target.closest?.('a[href]');
      if (!link || link.target || link.hasAttribute('download')) return;
      if (!link.matches('.fb-claim-panel .fb-claim-reset, .fb-claim-panel .fb-claim-tabs a, .fb-claim-panel .fb-claim-empty-action') &&
          !link.closest('.fb-claim-panel .fb-claim-pages')) return;
      const url = valid(link.href);
      if (!url) return;
      e.preventDefault();
      navigate(url.href);
    }, { signal });
    window.addEventListener('popstate', () => {
      if (location.pathname.replace(/\/+$/, '') === base) navigate(location.href, 'none');
    }, { signal });
  }

  let scope;
  function render() {
    scope?.abort();
    scope = new AbortController();
    mount(scope.signal);
  }

  document.addEventListener('fb:admin-page', render);
  render();
})();
