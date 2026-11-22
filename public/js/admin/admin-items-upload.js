/** FindBack Kampus — unggah foto admin (tanpa sintaks Blade) */
(() => {
    'use strict';
    function mount(signal) {
        const dialog = document.getElementById('fb-admin-add-dialog');
        const input = document.getElementById('fb-add-photo');
        const filename = document.getElementById('fb-add-file-name');
        const preview = document.getElementById('fb-add-photo-preview');
        let previewUrl = null;

        function clearPreview() {
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
            if (preview) {
                preview.removeAttribute('src');
                preview.hidden = true;
            }
        }

        if (!dialog) return;
        signal.addEventListener('abort', clearPreview, { once: true });
        if (input && filename && preview) {
            input.addEventListener('change', function () {
                clearPreview();
                const file = input.files && input.files[0];
                filename.textContent = file ? file.name : 'Belum ada foto yang dipilih';
                if (file && file.type.startsWith('image/')) {
                    previewUrl = URL.createObjectURL(file);
                    preview.src = previewUrl;
                    preview.hidden = false;
                }
            }, { signal });
        }

        const form = dialog.querySelector('form');
        const submit = form.querySelector('button[type="submit"]');
        const feedback = form.querySelector('[data-fb-publish-feedback]');
        let publishing = false;

        form.addEventListener('submit', async event => {
            if (event.defaultPrevented) return;
            event.preventDefault();
            if (publishing) return;

            publishing = true;
            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            feedback.hidden = false;
            feedback.textContent = 'Sedang memublikasikan laporan…';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { Accept: 'application/json' },
                    signal,
                });
                const data = await response.json();
                if (!response.ok || response.redirected) {
                    throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Publikasi gagal.');
                }
                const destination = new URL(data.destination);
                if (destination.origin !== location.origin) throw new Error('Tujuan publikasi tidak valid.');
                feedback.textContent = 'Laporan dipublikasikan. Membuka daftar barang…';
                const follow = document.dispatchEvent(new CustomEvent('fb:item-published', {
                    detail: destination.href,
                    cancelable: true,
                }));
                if (follow) location.assign(destination.href);
            } catch (error) {
                if (signal.aborted) return;
                feedback.textContent = error instanceof SyntaxError || error instanceof TypeError
                    ? 'Hasil publikasi belum bisa dikonfirmasi. Periksa daftar barang sebelum mencoba lagi.'
                    : error.message;
                publishing = false;
                submit.disabled = false;
                submit.removeAttribute('aria-busy');
            }
        }, { signal });

        dialog.addEventListener('cancel', event => {
            if (publishing) event.preventDefault();
        }, { signal });
        dialog.addEventListener('click', event => {
            if (publishing && (event.target === dialog || event.target.closest('[data-dialog-close]'))) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, { capture: true, signal });

        if (dialog) {
            if (dialog.dataset.hasErrors === 'true' && !dialog.open) dialog.showModal();
            dialog.addEventListener('close', function () {
                clearPreview();
                if (input) input.value = '';
                if (filename) filename.textContent = 'Belum ada foto yang dipilih';
            }, { signal });
        }
    }

    let scope;
    function render() {
        scope?.abort();
        scope = new AbortController();
        mount(scope.signal);
    }

    document.addEventListener('fb:admin-page', render);
    render();
})();
