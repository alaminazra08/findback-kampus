(() => {
  'use strict';
  function init() {
    document.querySelectorAll('[data-fb-claim-toast]').forEach((toast) => {
      if (toast.dataset.fbClaimInitialized === '1') return;
      toast.dataset.fbClaimInitialized = '1';
      toast.querySelector('[data-fb-claim-toast-close]')?.addEventListener('click', () => toast.remove());
      const duration = Number(toast.dataset.duration || 0);
      if (duration > 0 && !toast.classList.contains('is-error')) {
        window.setTimeout(() => { if (toast.isConnected) toast.remove(); }, duration);
      }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once:true });
  else init();
})();
