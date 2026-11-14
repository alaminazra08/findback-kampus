(() => {
  'use strict';
  const oldPrefix = 'fb-admin-smart-nav-v1:';
  const config = document.currentScript.dataset;
  if (!config.account) return;
  const prefix = 'fb-admin-smart-nav-v2:' + config.account + ':';
  const routes = Object.entries(config)
    .filter(([key]) => key.startsWith('list'))
    .map(([, value]) => new URL(value).pathname.replace(/\/+$/, ''));
  let loggingOut = false;
  const normalize = path => path.replace(/\/+$/, '') || '/';
  const routeOf = url => routes.find(r => url && normalize(url.pathname) === r);
  const getURL = raw => {
    try {
      const u = new URL(raw, location.href);
      return u.origin === location.origin ? u : null;
    } catch (_) { return null; }
  };
  const store = (() => { try { return localStorage; } catch (_) { return null; } })();
  if (!store) return;
  const current = getURL(location.href);
  const active = routeOf(current);
  function save(url) {
    const route = routeOf(url);
    if (!route || loggingOut) return;
    try {
      store.setItem(prefix + route, url.search);
    } catch (_) {}
  }
  function last(route) {
    try {
      const search = store.getItem(prefix + route);
      if (!search?.startsWith('?')) return null;
      const url = new URL(route, location.origin);
      url.search = search;
      return url;
    } catch (_) { return null; }
  }
  try {
    for (const route of routes) {
      const previous = JSON.parse(sessionStorage.getItem(oldPrefix + route) || 'null');
      const url = previous && getURL(previous.url);
      if (url && routeOf(url) === route && store.getItem(prefix + route) === null) save(url);
      sessionStorage.removeItem(oldPrefix + route);
    }
  } catch (_) {}
  if (active && current.search) save(current);
  // Capture changes made via AJAX history.pushState/replaceState, including filters and tabs.
  for (const method of ['pushState','replaceState']) {
    const original = history[method];
    history[method] = function (...args) {
      const result = original.apply(this,args);
      const u = getURL(location.href);
      if (u && (args.length >= 3 || u.search)) save(u);
      return result;
    };
  }
  addEventListener('popstate', () => {
    const u = getURL(location.href);
    if (u && routeOf(u)) save(u);
  });
  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const a = event.target.closest?.('a[href]');
    if (!a || a.target || a.hasAttribute('download')) return;
    if (!a.matches('.fb-sidebar a.fb-nav-link')) return;
    const url = getURL(a.href);
    const route = routeOf(url);
    if (!route || url.search || url.hash) return;
    const remembered = last(route);
    if (!remembered?.search) return;
    const original = a.getAttribute('href');
    a.href = remembered.href;
    // Keep the canonical menu URL so Reset and later clicks use current state.
    setTimeout(() => {
      if (a.href === remembered.href) a.setAttribute('href', original);
    }, 0);
  }, true);
  // Logout clears tab-only navigation state and keeps account preferences.
  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    const action = getURL(form.action);
    if (!action || !/\/logout\/?$/.test(action.pathname)) return;
    loggingOut = true;
    try {
      for (const route of routes) sessionStorage.removeItem(oldPrefix + route);
      for (const key of [
        'fb-verification-return-context-v1',
        'fb.admin.matching.lastFilter.v2',
        'fb.matching.scroll.once',
        'findback:admin:users:filters:v1',
        'findback:admin:users:filters:v1:skip'
      ]) sessionStorage.removeItem(key);
    } catch (_) {}
  }, true);
})();