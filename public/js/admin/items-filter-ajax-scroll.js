(() => {
  'use strict';
  function mount(signal) {
    const form = document.querySelector('main[data-fb-nav-page="items"] form.fb-items-filter');
    if (!form) return;
    const basePath = new URL(form.action).pathname.replace(/\/$/, '');
    if (location.pathname.replace(/\/$/, '') !== basePath) return;
    const selector = '.fb-items-panel';
    const formSelector = '.fb-items-panel form.fb-items-filter';
    const resetSelector = '.fb-items-panel a.fb-items-reset';
    let requestId = 0;
    let controller = null;
    signal.addEventListener('abort', () => {
      requestId++;
      controller?.abort();
    }, { once: true });

    const validURL = (raw) => {
      try {
        const url = new URL(raw, location.href);
        return url.origin === location.origin && url.pathname.replace(/\/$/, '') === basePath ? url : null;
      } catch (_) { return null; }
    };
    const normalNavigate = (url) => { location.assign(url); };
    const setBusy = (busy) => {
      const panel = document.querySelector(selector);
      if (panel) {
        if (busy) panel.setAttribute('aria-busy', 'true');
        else panel.removeAttribute('aria-busy');
      }
    };
    async function navigate(raw, mode = 'push') {
      const url = validURL(raw);
      if (!url) return;
      const id = ++requestId;
      if (controller) controller.abort();
      controller = new AbortController();
      setBusy(true);
      const x = window.scrollX, y = window.scrollY;
      try {
        const response = await fetch(url.href, {
          method: 'GET',
          credentials: 'same-origin',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
          cache: 'no-store',
          signal: controller.signal
        });
        const responseURL = validURL(response.url);
        if (!response.ok || response.redirected || !responseURL ||
            !(response.headers.get('content-type') || '').includes('text/html')) {
          throw new Error('Invalid response');
        }
        const html = await response.text();
        if (id !== requestId) return;
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const next = doc.querySelector(selector);
        const current = document.querySelector(selector);
        if (!next || !current || !next.querySelector(formSelector.replace('.fb-items-panel ', '')) ||
            !next.querySelector('.fb-items-table')) throw new Error('Missing listing');
        current.replaceWith(document.importNode(next, true));
        if (mode === 'push' && url.href !== location.href) history.pushState({fbItemsAjax:true}, '', url.href);
        window.scrollTo({left:x, top:y, behavior:'instant'});
      } catch (error) {
        if (error.name === 'AbortError' || id !== requestId) return;
        normalNavigate(url.href);
      } finally {
        if (id === requestId) setBusy(false);
      }
    }
    document.addEventListener('submit', (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement) || !form.matches(formSelector) || event.defaultPrevented) return;
      if (form.method.toLowerCase() !== 'get') return;
      const url = validURL(form.action);
      if (!url) return;
      const data = new FormData(form);
      url.search = new URLSearchParams(data).toString();
      event.preventDefault();
      navigate(url.href);
    }, { signal });
    document.addEventListener('click', (event) => {
      if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) return;
      const link = event.target.closest?.('a[href]');
      if (!link || link.target || link.hasAttribute('download')) return;
      if (!link.matches(resetSelector) && !link.closest('.fb-items-pagination')) return;
      const url = validURL(link.href);
      if (!url) return;
      event.preventDefault();
      navigate(url.href);
    }, { signal });
    window.addEventListener('popstate', () => {
      if (location.pathname.replace(/\/$/, '') === basePath) navigate(location.href, 'none');
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
