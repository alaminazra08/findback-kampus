(() => {
  'use strict';
  function init() {
    const root = document.querySelector('.fb-settings');
    if (!root || root.dataset.smartSaveReady === '1') return;
    const form = root.querySelector('form.fb-settings-form');
    const toast = root.querySelector('#fb-settings-toast');
    const button = form?.querySelector('button[type="submit"]');
    if (!form || !toast || !button) return;
    root.dataset.smartSaveReady = '1';
    const title = toast.querySelector('#fb-settings-toast-title');
    const message = toast.querySelector('#fb-settings-toast-message');
    const close = toast.querySelector('#fb-settings-toast-close');
    const original = button.innerHTML;
    const state = form.querySelector('[data-settings-state]');
    const snapshot = (data = new FormData(form)) =>
      JSON.stringify([...data].filter(([key]) => !['_token', '_method'].includes(key))
        .map(([key, value]) => [key, value instanceof File ? [value.name, value.size, value.lastModified] : value]));
    const saved = snapshot();
    const updateState = () => {
      if (state) state.textContent = snapshot() === saved ? '' : 'Perubahan belum disimpan';
    };
    form.addEventListener('input', updateState);
    form.addEventListener('change', updateState);
    let timeout;
    function dismiss() {
      toast.classList.remove('is-visible');
      clearTimeout(timeout);
      setTimeout(() => { if (!toast.classList.contains('is-visible')) toast.hidden = true; }, 230);
    }
    function show(kind, heading, detail) {
      clearTimeout(timeout);
      toast.hidden = false;
      toast.classList.toggle('is-error', kind === 'error');
      toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');
      title.textContent = heading;
      message.textContent = detail;
      requestAnimationFrame(() => toast.classList.add('is-visible'));
      if (kind === 'error') { timeout = null; } else { timeout = setTimeout(dismiss, 5000); }
    }
    close.addEventListener('click', dismiss);
    if (toast.dataset.flashSuccess === '1') {
      show('success', 'Pengaturan berhasil disimpan', 'Perubahan telah diterapkan ke sistem.');
      toast.dataset.flashSuccess = '0';
    }
    // Open the section containing the first invalid field before the browser focuses it.
    let handlingInvalid = false;
    form.addEventListener('invalid', (event) => {
      if (handlingInvalid) return;
      handlingInvalid = true;
      const section = event.target.closest('details.fb-settings-section');
      if (section) section.open = true;
      queueMicrotask(() => { handlingInvalid = false; });
    }, true);
    form.addEventListener('submit', async (event) => {
      if (event.defaultPrevented) return;
      event.preventDefault();
      if (button.disabled) return;
      if (!form.reportValidity()) return;
      const payload = new FormData(form);
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = 'Menyimpan...';
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: payload,
          credentials: 'same-origin',
          redirect: 'follow',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const fieldName = Object.keys(data.errors || {})[0];
          const field = fieldName ? form.elements.namedItem(fieldName) : null;
          const section = field instanceof HTMLElement ? field.closest('details.fb-settings-section') : null;
          if (section) {
            section.open = true;
            field.focus();
          }
          const first = data.errors && Object.values(data.errors).flat()[0];
          throw new Error(first || data.message || (response.status === 419 ? 'Sesi berakhir. Muat ulang halaman lalu coba lagi.' : 'Silakan coba lagi.'));
        }
        if (response.redirected || typeof data.message !== 'string') {
          throw new Error('Respons penyimpanan tidak valid. Muat ulang halaman dan periksa sesi Anda.');
        }
        window.location.reload();
      } catch (error) {
        show('error', 'Gagal menyimpan pengaturan', error.message || 'Periksa koneksi dan coba lagi.');
      } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.innerHTML = original;
      }
    });
  }
  init();
  // Inisialisasi ulang form yang dimuat melalui navigasi AJAX Admin.
  document.addEventListener('fb:admin-page', init);
})();
