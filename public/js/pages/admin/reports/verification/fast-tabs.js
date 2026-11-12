(function () {
  'use strict';
  const tabPath = '/admin/reports/verification';
  if (location.pathname.replace(/\/$/, '') !== tabPath || !document.querySelector('.fb-v-panel')) return;
  const cache = new Map();
  const TTL = 20000;
  const inflight = new Map();
  let activeController = null;
  let requestId = 0;
  let isNavigating = false;
  cache.set(location.href, {html: document.documentElement.outerHTML, at: Date.now()});
  function samePage(url) {
    try {
      const u = new URL(url, location.href);
      return u.origin === location.origin && u.pathname.replace(/\/$/, '') === tabPath ? u : null;
    } catch (_) { return null; }
  }
  function isTabLink(a) {
    return !!(a && a.closest('.fb-v-tabs') && !a.target && !a.hasAttribute('download') && samePage(a.href));
  }
  function cached(url) {
    const item = cache.get(url);
    return item && Date.now() - item.at < TTL ? item.html : null;
  }
  async function load(url, signal, fresh) {
    const hit = !fresh && cached(url);
    if (hit) return hit;
    // Reuse an ongoing prefetch instead of sending a duplicate first-click request.
    if (!fresh && inflight.has(url)) return inflight.get(url);
    const task = (async () => {
      const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html'},
        signal: signal,
        cache: 'no-store'
      });
      const finalURL = samePage(response.url);
      if (!response.ok || response.redirected || !finalURL) throw Error('Response invalid');
      const html = await response.text();
      if (!html.includes('fb-v-tabs') || !html.includes('fb-v-panel')) throw Error('Unexpected HTML');
      cache.set(url, {html: html, at: Date.now()});
      return html;
    })();
    if (!fresh) inflight.set(url, task);
    try { return await task; }
    finally { if (inflight.get(url) === task) inflight.delete(url); }
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
  async function navigate(url, mode, fresh) {
    const id = ++requestId;
    if (activeController) activeController.abort();
    activeController = new AbortController();
    const currentPanel = document.querySelector('.fb-v-panel');
    if (currentPanel) currentPanel.setAttribute('aria-busy', 'true');
    isNavigating = true;
    try {
      const html = await load(url, activeController.signal, !!fresh);
      if (id !== requestId) return;
      apply(html);
      if (mode === 'push' && url !== location.href) history.pushState({fbVerificationTab:true}, '', url);
      if (mode === 'replace' && url !== location.href) history.replaceState({fbVerificationTab:true}, '', url);
    } catch (e) {
      if (e.name === 'AbortError' || id !== requestId) return;
      location.assign(url); // Normal navigation if fetching is unavailable.
    } finally {
      if (id === requestId) {
        isNavigating = false;
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
    navigate(url.href, 'push', true);
  });
  document.addEventListener('click', function (event) {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const a = event.target.closest('a');
    if (!a) return;
    if (a.matches('.fb-v-reset') && !a.target && !a.hasAttribute('download')) {
      const u = samePage(a.href);
      if (!u) return;
      event.preventDefault();
      navigate(u.href, 'push', true);
      return;
    }
    if (!isTabLink(a)) return;
    event.preventDefault();
    if (a.href !== location.href) navigate(a.href, 'push', false);
  });
  document.addEventListener('pointerover', function (event) {
    const a = event.target.closest('a');
    if (!isTabLink(a) || cached(a.href) || isNavigating) return;
    const controller = new AbortController();
    load(a.href, controller.signal, false).catch(function () {});
  }, {passive: true});
  function warmAlternateTab() {
    const links = Array.from(document.querySelectorAll('.fb-v-tabs a[href]'));
    for (const a of links) {
      const u = samePage(a.href);
      if (!u || u.href === location.href || cached(u.href) || inflight.has(u.href)) continue;
      load(u.href, undefined, false).catch(function () {});
    }
  }
  // Schedule after first paint, keeping initial page interactive.
  if (document.readyState === 'complete') setTimeout(warmAlternateTab, 100);
  else window.addEventListener('load', () => setTimeout(warmAlternateTab, 100), {once:true});
  window.addEventListener('pageshow', function (e) { if (e.persisted) warmAlternateTab(); });
  window.addEventListener('popstate', function () {
    if (location.pathname.replace(/\/$/, '') === tabPath) navigate(location.href, 'none', true);
  });
})();
