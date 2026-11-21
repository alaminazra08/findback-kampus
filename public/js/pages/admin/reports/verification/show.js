(() => {
    function reopenAfterValidation() {
        const dialog = document.querySelector('.fb-review-dialog[data-review-reopen]');
        if (dialog) {
            dialog.removeAttribute('data-review-reopen');
            dialog.showModal();
        }
    }

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-review-open]');
        if (opener) {
            const dialog = document.getElementById(opener.dataset.reviewOpen);
            if (dialog && !dialog.open) {
                dialog.addEventListener('close', () => opener.focus({ preventScroll: true }), { once: true });
                dialog.showModal();
            }
        }

        const closer = event.target.closest('[data-review-close]');
        if (closer) {
            closer.closest('dialog').close();
        }
    });

    document.addEventListener('fb:admin-page', reopenAfterValidation);
    reopenAfterValidation();
})();
