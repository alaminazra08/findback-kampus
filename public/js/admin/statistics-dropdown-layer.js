/*
 * FindBack Kampus — Statistics dropdown layer coordination V6
 * Keeps period and PDF menus mutually exclusive and raises the period layer.
 */
(() => {
    let lifecycle;
    const init = () => {
        lifecycle?.abort();
        lifecycle = new AbortController();
        document.querySelectorAll('.fs-page .fs-header-actions').forEach((actions) => {
            if (actions.dataset.dropdownLayerV6 === 'ready') return;

            const periodTrigger = actions.querySelector('.fs-period-trigger');
            const pdfDetails = actions.querySelector('.fs-report-download');

            if (!periodTrigger) return;
            actions.dataset.dropdownLayerV6 = 'ready';

            const syncLayer = () => {
                const isOpen = periodTrigger.getAttribute('aria-expanded') === 'true';
                actions.classList.toggle('fs-period-layer-open', isOpen);
                if (isOpen && pdfDetails?.open) {
                    pdfDetails.open = false;
                }
            };

            const observer = new MutationObserver(syncLayer);
            lifecycle.signal.addEventListener('abort', () => observer.disconnect(), { once: true });
            observer.observe(periodTrigger, {
                attributes: true,
                attributeFilter: ['aria-expanded']
            });

            periodTrigger.addEventListener('click', () => {
                queueMicrotask(syncLayer);
            });

            if (pdfDetails) {
                pdfDetails.addEventListener('toggle', () => {
                    if (pdfDetails.open &&
                        periodTrigger.getAttribute('aria-expanded') === 'true') {
                        periodTrigger.click();
                    }
                });
            }

            document.addEventListener('pointerdown', (event) => {
                if (!actions.contains(event.target)) {
                    queueMicrotask(syncLayer);
                }
            }, { signal: lifecycle.signal });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    queueMicrotask(syncLayer);
                }
            }, { signal: lifecycle.signal });

            syncLayer();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }

    document.addEventListener('fb:admin-page', init);
})();
