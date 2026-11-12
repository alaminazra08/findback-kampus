(() => {
function mount(signal) {
  'use strict';
  const filters = document.querySelector('.fb-v-filters');
  if (!filters) return;
  const tabPath = new URL(filters.action).pathname.replace(/\/$/, '');
  if (location.pathname.replace(/\/$/, '') !== tabPath || !document.querySelector('.fb-v-panel')) return;
  let activeController = null;
  let requestId = 0;
  signal.addEventListener('abort', () => {
    requestId++;
    activeController?.abort();
  }, { once: true });
  function samePage(url) {
    try {
      const u = new URL(url, location.href);
      return u.origin === location.origin && u.pathname.replace(/\/$/, '') === tabPath ? u : null;
    } catch (_) { return null; }
  }
  function isTabLink(a) {
    return !!(a && a.closest('.fb-v-tabs') && !a.target && !a.hasAttribute('download') && samePage(a.href));
  }
  async function load(url, signal) {
    const response = await fetch(url, {
      credentials: 'same-origin',
      headers: {'X-FindBack-Verification': 'results', 'Accept': 'text/html'},
      signal,
      cache: 'no-store'
    });
    if (!response.ok || response.redirected || !samePage(response.url)) {
      throw Error('Response invalid');
    }
    return response.text();
  }
  function apply(html) {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const nextPanel = doc.querySelector('.fb-v-panel');
    const nextStats = doc.querySelector('.fb-v-stats');
    const currentPanel = document.querySelector('.fb-v-panel');
    const currentStats = document.querySelector('.fb-v-stats');
    if (!nextPanel || !nextStats || !currentPanel || !currentStats) throw Error('Missing verification content');
    const x = window.scrollX, y = window.scrollY;
    currentPanel.replaceWith(document.importNode(nextPanel, true));
    currentStats.replaceWith(document.importNode(nextStats, true));
    window.scrollTo({left:x, top:y, behavior:'instant'});
  }
  async function navigate(url, mode) {
    const id = ++requestId;
    if (activeController) activeController.abort();
    activeController = new AbortController();
    const currentPanel = document.querySelector('.fb-v-panel');
    if (currentPanel) currentPanel.setAttribute('aria-busy', 'true');
    try {
      const html = await load(url, activeController.signal);
      if (id !== requestId) return;
      apply(html);
      if (mode === 'push' && url !== location.href) history.pushState({fbVerificationTab:true}, '', url);
    } catch (e) {
      if (e.name === 'AbortError' || id !== requestId) return;
      location.assign(url); // Normal navigation if fetching is unavailable.
    } finally {
      if (id === requestId) {
        const panel = document.querySelector('.fb-v-panel');
        if (panel) panel.removeAttribute('aria-busy');
      }
    }
  }
  document.addEventListener('submit', function (event) {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement) || !form.matches('.fb-v-filters') || form.method.toLowerCase() !== 'get') return;
    const url = samePage(form.action);
    if (!url) return;
    const params = new URLSearchParams(new FormData(form));
    url.search = params.toString();
    event.preventDefault();
    navigate(url.href, 'push');
  }, { signal });
  document.addEventListener('click', function (event) {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const a = event.target.closest('a');
    if (!a) return;
    if (a.matches('.fb-v-reset') && !a.target && !a.hasAttribute('download')) {
      const u = samePage(a.href);
      if (!u) return;
      event.preventDefault();
      navigate(u.href, 'push');
      return;
    }
    if (!isTabLink(a)) return;
    event.preventDefault();
    if (a.href !== location.href || document.querySelector('.fb-v-panel')?.getAttribute('aria-busy') === 'true') {
      navigate(a.href, 'push');
    }
  }, { signal });
  window.addEventListener('popstate', function () {
    if (location.pathname.replace(/\/$/, '') === tabPath) navigate(location.href, 'none');
  }, { signal });
}

    let controller;
    function render() {
        controller?.abort();
        controller = new AbortController();
        mount(controller.signal);
    }
    document.addEventListener('fb:admin-page', render);
    render();
})();
