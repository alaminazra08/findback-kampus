(() => {
    'use strict';

    function initClaimToasts() {
        document.querySelectorAll('[data-fb-claim-toast]').forEach((toast) => {
            if (toast.dataset.fbClaimInitialized === '1') return;
            toast.dataset.fbClaimInitialized = '1';

            const closeButton = toast.querySelector('[data-fb-claim-toast-close]');
            closeButton?.addEventListener('click', () => toast.remove(), { once: true });

            const duration = Number(toast.dataset.duration || 0);
            if (duration > 0 && !toast.classList.contains('is-error')) {
                window.setTimeout(() => {
                    if (toast.isConnected) toast.remove();
                }, duration);
            }
        });
    }

    // Initial full-page load.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initClaimToasts, { once: true });
    } else {
        initClaimToasts();
    }

    // Subsequent SPA page replacements. The listener is installed only once
    // because this file is loaded by the persistent admin layout.
    document.addEventListener('fb:admin-page', initClaimToasts);
})();
