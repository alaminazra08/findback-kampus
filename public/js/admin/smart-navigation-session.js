(() => {
  'use strict';
  // Session-only, tab-local navigation preferences. Never persist across logout.
  const prefix = 'fb-admin-smart-nav-v1:';
  const routes = ['/admin/reports/verification', '/admin/items', '/admin/claims'];
  const normalize = path => path.replace(/\/+$/, '') || '/';
  const routeOf = url => routes.find(r => normalize(url.pathname) === r);
  const getURL = raw => {
    try {
      const u = new URL(raw, location.href);
      return u.origin === location.origin ? u : null;
    } catch (_) { return null; }
  };
  const store = (() => { try { return sessionStorage; } catch (_) { return null; } })();
  if (!store) return;
  const current = getURL(location.href);
  const active = routeOf(current);
  function save(url) {
    const route = routeOf(url);
    if (!route) return;
    try {
      store.setItem(prefix + route, JSON.stringify({url:url.href, at:Date.now()}));
    } catch (_) {}
  }
  function last(route) {
    try {
      const data = JSON.parse(store.getItem(prefix + route) || 'null');
      if (!data || Date.now()-data.at > 8*60*60*1000) return null;
      const u = getURL(data.url);
      return u && routeOf(u) === route ? u : null;
    } catch (_) { return null; }
  }
  if (active) save(current);
  // Capture changes made via AJAX history.pushState/replaceState, including filters and tabs.
  for (const method of ['pushState','replaceState']) {
    const original = history[method];
    history[method] = function (...args) {
      const result = original.apply(this,args);
      const u = getURL(location.href);
      if (u && routeOf(u)) save(u);
      return result;
    };
  }
  addEventListener('popstate', () => {
    const u = getURL(location.href);
    if (u && routeOf(u)) save(u);
  });
  addEventListener('pagehide', () => {
    const u = getURL(location.href);
    if (u && routeOf(u)) save(u);
  });
  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const a = event.target.closest?.('a[href]');
    if (!a || a.target || a.hasAttribute('download')) return;
    // Only rewrite a navigation from the admin sidebar, not a detail/back link.
    if (!a.closest('.fb-sidebar, .fb-nav, [aria-label="Menu utama"]')) return;
    const u = getURL(a.href);
    if (!u) return;
    const route = routeOf(u);
    if (!route || u.search) return;
    const remembered = last(route);
    if (remembered && remembered.href !== u.href) a.href = remembered.href;
  }, true);
  // Logout: clear preferences before the POST is submitted.
  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const action = getURL(form.action);
    if (!action || !/\/logout\/?$/.test(action.pathname)) return;
    for (const route of routes) store.removeItem(prefix + route);
    // Also clear prior return-context state so no stale scroll/filter survives logout.
    store.removeItem('fb-verification-return-context-v1');
  }, true);
})();