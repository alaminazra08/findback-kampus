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
      timeout = setTimeout(dismiss, 4200);
    }
    close.addEventListener('click', dismiss);
    if (toast.dataset.flashSuccess === '1') {
      show('success', 'Pengaturan berhasil disimpan', 'Perubahan telah diterapkan ke sistem.');
      toast.dataset.flashSuccess = '0';
    }
    form.addEventListener('submit', async (event) => {
      if (event.defaultPrevented || button.disabled) return;
      event.preventDefault();
      if (!form.reportValidity()) return;
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = 'Menyimpan...';
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          credentials: 'same-origin',
          redirect: 'follow',
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const first = data.errors && Object.values(data.errors).flat()[0];
          throw new Error(first || data.message || (response.status === 419 ? 'Sesi berakhir. Muat ulang halaman lalu coba lagi.' : 'Silakan coba lagi.'));
        }
        show('success', 'Pengaturan berhasil disimpan', 'Perubahan telah diterapkan ke sistem.');
      } catch (error) {
        show('error', 'Gagal menyimpan pengaturan', error.message || 'Periksa koneksi dan coba lagi.');
      } finally {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.innerHTML = original;
      }
    });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
  document.addEventListener('DOMContentLoaded', init);
  document.addEventListener('pageshow', init);
  // Inisialisasi ulang form yang dimuat melalui navigasi AJAX Admin.
  document.addEventListener('fb:admin-page', init);
})();
