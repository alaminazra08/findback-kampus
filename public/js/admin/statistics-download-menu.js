/*
 * FindBack Kampus — Floating PDF menu V4
 * Native <details> keeps keyboard behavior; this adds outside-click and Escape close.
 */
(() => {
    let lifecycle;
    const init = () => {
        lifecycle?.abort();
        lifecycle = new AbortController();
        document.querySelectorAll('.fs-page .fs-report-download').forEach((details) => {
            if (details.dataset.floatingMenuV4 === 'ready') return;
            details.dataset.floatingMenuV4 = 'ready';

            document.addEventListener('pointerdown', (event) => {
                if (details.open && !details.contains(event.target)) {
                    details.open = false;
                }
            }, { signal: lifecycle.signal });

            details.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && details.open) {
                    details.open = false;
                    const trigger = details.querySelector('.fs-report-download-trigger');
                    if (trigger) trigger.focus();
                }
            });

            details.querySelectorAll('.fs-report-download-option').forEach((option) => {
                option.addEventListener('click', () => {
                    details.open = false;
                });
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
    document.addEventListener('fb:admin-page', init);
})();
