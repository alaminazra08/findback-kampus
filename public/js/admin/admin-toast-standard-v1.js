/* FindBack Admin Toast Standard V1
 * Success: 5s, warning: 8s, errors: manually dismissed.
 * Works for full page loads and AJAX navigation without replacing form logic.
 */
(() => {
  'use strict';
  const scheduled = new WeakSet();
  function scan() {
    document.querySelectorAll('.fb-verification-toast').forEach((toast) => {
      if (scheduled.has(toast)) return;
      scheduled.add(toast);
      const ms = toast.classList.contains('is-warning') ? 8000 : 5000;
      window.setTimeout(() => {
        if (toast.isConnected) toast.remove();
      }, ms);
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', scan, { once: true });
  } else scan();
  const observer = new MutationObserver(scan);
  observer.observe(document.documentElement, { childList: true, subtree: true });
})();
