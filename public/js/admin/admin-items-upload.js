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
