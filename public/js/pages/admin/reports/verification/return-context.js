(() => {
function mount(signal) {
  'use strict';
  const base = document.querySelector('.fb-v-filters') ? new URL(document.querySelector('.fb-v-filters').action).pathname : location.pathname.replace(/\/[^/]+\/?$/, '');
  if (!base.endsWith('/admin/reports/verification')) return;
  const key = 'fb-verification-return-context-v1';
  const isList = location.pathname.replace(/\/$/, '') === base;
  const isDetail = new RegExp('^' + base + '/[^/]+/?$').test(location.pathname);
  if (!isList && !isDetail) return;
  const storage = (() => { try { return window.sessionStorage; } catch (_) { return null; } })();
  if (!storage) return;
  const safeURL = value => {
    try {
      const u = new URL(value, location.href);
      if (u.origin !== location.origin || u.pathname.replace(/\/$/, '') !== base) return null;
      return u;
    } catch (_) { return null; }
  };
  const read = () => {
    try {
      const x = JSON.parse(storage.getItem(key) || 'null');
      if (!x || Date.now() - x.at > 20 * 60 * 1000 || !safeURL(x.url)) return null;
      return x;
    } catch (_) { return null; }
  };
  const save = x => { try { storage.setItem(key, JSON.stringify(x)); } catch (_) {} };
  document.addEventListener('click', event => {
    if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.altKey || event.shiftKey) return;
    const a = event.target.closest?.('a[href]');
    if (!a) return;
    if (isList) {
      let target;
      try { target = new URL(a.href, location.href); } catch (_) { return; }
      if (target.origin !== location.origin || !target.pathname.startsWith(base + '/')) return;
      save({url:location.href, x:scrollX, y:scrollY, at:Date.now(), pending:false});
    } else if (isDetail && a.classList.contains('fb-v-back')) {
      const u = safeURL(a.href);
      if (!u) return;
      const previous = read();
      const same = previous && safeURL(previous.url)?.href === u.href;
      save({url:u.href, x:same ? previous.x : 0, y:same ? previous.y : 0, at:Date.now(), pending:true});
    }
  }, { capture: true, signal });
  if (!isList) return;
  const state = read();
  if (!state || !state.pending || safeURL(state.url)?.href !== location.href) return;
  save({...state, pending:false});
  if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
  const restore = () => !signal.aborted && scrollTo({left:state.x, top:state.y, behavior:'instant'});
  requestAnimationFrame(() => requestAnimationFrame(restore));
  window.addEventListener('load', restore, {once:true,signal});
  setTimeout(restore, 250);
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
