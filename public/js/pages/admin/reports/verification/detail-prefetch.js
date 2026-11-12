(() => {
  'use strict';
  if (!location.pathname.startsWith('/admin/reports/verification')) return;
  const seen = new Set();
  function eligible(a) {
    if (!a || a.target || a.hasAttribute('download')) return null;
    let u;
    try { u = new URL(a.href, location.href); } catch { return null; }
    if (u.origin !== location.origin || !/^\/admin\/reports\/verification(?:\/\d+)?\/?$/.test(u.pathname)) return null;
    if (u.href === location.href) return null;
    return u.href;
  }
  function warm(a) {
    const url = eligible(a);
    if (!url || seen.has(url)) return;
    if (navigator.connection?.saveData) return;
    seen.add(url);
    const l = document.createElement('link');
    l.rel = 'prefetch';
    l.as = 'document';
    l.href = url;
    document.head.appendChild(l);
  }
  document.addEventListener('pointerover', e => warm(e.target.closest?.('a')), {passive:true});
  document.addEventListener('focusin', e => warm(e.target.closest?.('a')), {passive:true});
  document.addEventListener('touchstart', e => warm(e.target.closest?.('a')), {passive:true});
})();