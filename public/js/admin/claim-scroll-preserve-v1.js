/* FindBack Claim Scroll Preserve V1
 * Restore position after an admin claim form POST redirects back to this claim.
 * Does not scroll when a toast appears, and does not change normal navigation.
 */
(() => {
  'use strict';
  const key = 'fb:claim:post-scroll:v1';
  const path = window.location.pathname;
  if (!/^\/admin\/claims\/\d+\/?$/.test(path)) return;
  try {
    const raw = sessionStorage.getItem(key);
    if (raw) {
      sessionStorage.removeItem(key);
      const state = JSON.parse(raw);
      if (state.path === path && Date.now() - state.at < 60000 &&
          Number.isFinite(state.y) && state.y >= 0) {
        if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
        const restore = () => window.scrollTo({ top: state.y, left: state.x || 0, behavior: 'instant' });
        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(() => requestAnimationFrame(restore)), { once: true });
        } else requestAnimationFrame(() => requestAnimationFrame(restore));
        window.addEventListener('load', restore, { once: true });
      }
    }
  } catch (_) { try { sessionStorage.removeItem(key); } catch (_) {} }

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('form.fb-claim-form')) return;
    if ((form.method || '').toUpperCase() !== 'POST') return;
    try {
      sessionStorage.setItem(key, JSON.stringify({
        path, x: window.scrollX, y: window.scrollY, at: Date.now()
      }));
    } catch (_) {}
  }, true);
})();
