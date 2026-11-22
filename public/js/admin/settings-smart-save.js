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
    const panels = [...root.querySelectorAll('details[name="fb-settings-sections"]')];
    const panelKey = 'findback.admin.settings.open-panel';
    const panelId = (panel) => panel?.getAttribute('aria-labelledby') || '';
    let storedPanel = null;
    try {
      storedPanel = sessionStorage.getItem(panelKey);
    } catch {
      // Storage may be unavailable in a restricted browser.
    }

    if (storedPanel === '') {
      panels.forEach((panel) => { panel.open = false; });
    } else {
      const previous = panels.find((panel) => panelId(panel) === storedPanel);
      if (previous) previous.open = true;
    }

    panels.forEach((panel) => {
      panel.addEventListener('toggle', () => {
        if (!root.isConnected) return;
        try {
          sessionStorage.setItem(panelKey, panelId(panels.find((item) => item.open)));
        } catch {
          // The accordion remains usable when storage is unavailable.
        }
      });
    });
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
    const preview = form.querySelector('.fb-brand-preview');
    if (preview) {
      const logo = preview.querySelector('[data-brand-preview-logo]');
      const name = preview.querySelector('[data-brand-preview-name]');
      const savedLogo = logo.src;
      const fields = form.elements;
      let objectUrl;
      function updatePreview() {
        const file = fields.brand_logo_upload.files[0];
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = file ? URL.createObjectURL(file) : null;
        logo.src = objectUrl || (fields.brand_logo_reset.checked ? logo.dataset.defaultLogo : savedLogo);
        preview.style.setProperty('--preview-color', fields.brand_color_reset.checked ? '#8b1038' : fields.brand_color.value);
        name.textContent = fields.site_name.value;
        preview.querySelector('[data-brand-preview-heading]').textContent =
          fields.home_heading.value.trim() || 'Barang Hilang? Temukan Kembali Bersama FindBack.';
        preview.querySelector('[data-brand-preview-description]').textContent =
          fields.site_description.value.trim();
      }
      fields.brand_color.addEventListener('input', () => {
        fields.brand_color_reset.checked = false;
      });
      form.addEventListener('input', updatePreview);
      updatePreview();
    }
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
