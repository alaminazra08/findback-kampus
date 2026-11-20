/* FindBack Admin Toast Standard V1
 * Success: 5s, warning: 8s, errors: manually dismissed.
 */
(() => {
  'use strict';
  function mount() {
    document.querySelectorAll('.fb-verification-toast').forEach((toast) => {
      const duration = toast.classList.contains('is-warning') ? 8000 : 5000;
      const timer = window.setTimeout(() => toast.remove(), duration);
      toast.querySelector('.fb-verification-toast-close')?.addEventListener('click', () => {
        window.clearTimeout(timer);
        toast.remove();
      }, { once: true });
    });
  }
  mount();
  document.addEventListener('fb:admin-page', mount);
})();
