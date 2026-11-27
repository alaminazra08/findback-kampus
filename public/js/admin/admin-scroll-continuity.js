/* FindBack Admin Scroll Continuity V1
 * Preserve scroll on POST->redirect->GET to the SAME admin page.
 * Never scroll in response to a toast; normal navigation remains unchanged.
 */
(() => {
  'use strict';
  const KEY = 'findback:admin:scroll-continuity:v1';
  const current = location.pathname;
  if (!current.startsWith('/admin/')) return;
  const storage = () => { try { return window.sessionStorage; } catch (_) { return null; } };
  const st = storage();
  if (!st) return;
  try {
    const saved = JSON.parse(st.getItem(KEY) || 'null');
    st.removeItem(KEY);
    if (saved && saved.path === current && Number.isFinite(saved.y) &&
        Date.now() - saved.at < 45000 && saved.y > 0) {
      const apply = () => {
        const max = Math.max(0, document.documentElement.scrollHeight - innerHeight);
        window.scrollTo({top: Math.min(saved.y, max), left: saved.x || 0, behavior: 'instant'});
      };
      requestAnimationFrame(apply);
    }
  } catch (_) { try { st.removeItem(KEY); } catch (_) {} }
  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    if ((form.method || 'GET').toUpperCase() !== 'POST') return;
    const action = new URL(form.action || location.href, location.href);
    if (action.pathname === '/logout' || !action.pathname.startsWith('/admin/')) return;
    // Only same-origin POSTs; do not change form submission.
    if (action.origin !== location.origin) return;
    try {
      st.setItem(KEY, JSON.stringify({
        path: current, x: window.scrollX, y: window.scrollY, at: Date.now()
      }));
    } catch (_) {}
  }, true);
})();
