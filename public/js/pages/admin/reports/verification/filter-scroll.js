(() => {
  'use strict';
  const path = '/admin/reports/verification';
  if (location.pathname.replace(/\/$/, '') !== path) return;
  const key = 'fb-verification-filter-scroll-v1';
  let store;
  try { store = sessionStorage; } catch (_) { return; }
  const normalized = input => {
    try {
      const u = new URL(input, location.href);
      if (u.origin !== location.origin || u.pathname.replace(/\/$/, '') !== path) return null;
      const entries = [...u.searchParams.entries()].filter(([k,v]) => v !== '').sort(([a,av],[b,bv]) => a.localeCompare(b) || av.localeCompare(bv));
      return u.origin + u.pathname.replace(/\/$/, '') + '?' + new URLSearchParams(entries).toString();
    } catch (_) { return null; }
  };
  const save = url => {
    const target = normalized(url);
    if (!target) return;
    try { store.setItem(key, JSON.stringify({target, x:scrollX, y:scrollY, time:Date.now()})); } catch (_) {}
  };
  document.addEventListener('submit', event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('.fb-v-filters') || form.method.toLowerCase() !== 'get') return;
    if (event.defaultPrevented) return;
    const u = new URL(form.action, location.href);
    const params = new URLSearchParams(new FormData(form));
    u.search = params.toString();
    save(u.href);
  }, false);
  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    const a = event.target.closest?.('a.fb-v-reset');
    if (a) save(a.href);
  }, false);
  let state;
  try { state = JSON.parse(store.getItem(key) || 'null'); } catch (_) { return; }
  if (!state || Date.now() - state.time > 30000 || state.target !== normalized(location.href)) return;
  try { store.removeItem(key); } catch (_) {}
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  const restore = () => window.scrollTo({left:state.x, top:state.y, behavior:'instant'});
  requestAnimationFrame(() => requestAnimationFrame(restore));
  window.addEventListener('load', restore, {once:true});
  window.addEventListener('pageshow', restore, {once:true});
  setTimeout(restore, 180);
})();
