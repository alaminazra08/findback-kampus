(() => {
    function reopenAfterValidation() {
        const dialog = document.querySelector('#fb-claim-reject-dialog[data-reopen]');
        if (dialog && !dialog.open) {
            dialog.removeAttribute('data-reopen');
            dialog.showModal();
        }
    }

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-fb-claim-reject-open]')) {
            document.getElementById('fb-claim-reject-dialog').showModal();
        }

        const cancel = event.target.closest('[data-fb-claim-reject-cancel]');
        if (cancel) {
            cancel.closest('dialog').close();
        }
    });

    document.addEventListener('fb:admin-page', reopenAfterValidation);
    reopenAfterValidation();
})();
