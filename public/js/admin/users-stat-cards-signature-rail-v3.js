/* FINDBACK USERS STAT CARDS — TAP/CLICK REPLAY V3 */
(() => {
    'use strict';
    const selector = '.fb-users-page .fb-users-stats > article.fb-users-stat-card';
    let clearHandle = 0;

    document.addEventListener('pointerdown', (event) => {
        const card = event.target.closest(selector);
        if (!card) return;

        card.classList.remove('is-animating');
        // Reflow allows the rail highlight to replay on consecutive taps.
        void card.offsetWidth;
        card.classList.add('is-animating');

        window.clearTimeout(clearHandle);
        clearHandle = window.setTimeout(() => {
            card.classList.remove('is-animating');
        }, 850);
    }, { passive: true });
})();
