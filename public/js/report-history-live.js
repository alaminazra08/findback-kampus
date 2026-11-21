(() => {
    let updating = false;

    async function refreshHistory() {
        const panel = document.querySelector('[data-live-report-history]');
        if (!panel || document.hidden || updating) return;

        const url = location.href;
        updating = true;
        try {
            const response = await fetch(url, { cache: 'no-store' });
            if (!response.ok || response.redirected) return;

            const html = await response.text();
            if (!panel.isConnected || location.href !== url) return;

            const fresh = new DOMParser().parseFromString(html, 'text/html')
                .querySelector('[data-live-report-history]');
            if (fresh && fresh.innerHTML !== panel.innerHTML) {
                panel.replaceChildren(...fresh.childNodes);
            }
        } catch {
            // Keep the last available history and retry on the next interval.
        } finally {
            updating = false;
        }
    }

    window.setInterval(refreshHistory, 10000);
    document.addEventListener('visibilitychange', refreshHistory);
})();
