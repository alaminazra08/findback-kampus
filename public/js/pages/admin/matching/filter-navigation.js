(function () {
  'use strict';
  let lifecycle;
  function mountFeedback() {
    lifecycle?.abort();
    lifecycle = new AbortController();
    const toast = document.querySelector('[data-fb-matching-toast]');
    if (toast) {
      const dismiss = () => toast.remove();
      const timer = toast.classList.contains('is-error') ? null : setTimeout(dismiss, 5000);
      toast.querySelector('[data-fb-matching-toast-close]').addEventListener('click', () => {
        clearTimeout(timer);
        dismiss();
      });
    }

    const form = document.querySelector('form[data-fb-match-generate]');
    const dialog = document.getElementById('fb-match-confirm');
    if (!form || !dialog?.showModal || !form.requestSubmit) return;
    let approved = false;
    let sent = false;
    let submitter;
    const button = form.querySelector('button[type="submit"]');
    const label = button.querySelector('span');
    const originalLabel = label.textContent;
    form.addEventListener('submit', event => {
      if (event.defaultPrevented) return;
      if (sent) {
        event.preventDefault();
        return;
      }
      if (approved) {
        queueMicrotask(() => {
          if (event.defaultPrevented) return;
          sent = true;
          button.disabled = true;
          label.textContent = 'Memproses…';
        });
        return;
      }
      event.preventDefault();
      if (dialog.open) return;
      submitter = event.submitter;
      dialog.showModal();
    });
    dialog.querySelector('[data-fb-match-accept]').addEventListener('click', () => {
      if (!dialog.open || sent) return;
      dialog.close();
      approved = true;
      try { form.requestSubmit(submitter || undefined); }
      finally { approved = false; }
    });
    window.addEventListener('pageshow', event => {
      if (!event.persisted) return;
      sent = false;
      button.disabled = false;
      label.textContent = originalLabel;
    }, { signal: lifecycle.signal });
  }

  mountFeedback();
  document.addEventListener('fb:admin-page', mountFeedback);
})();
