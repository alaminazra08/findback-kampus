/* Replays a short visual response on pointer/tap without making statistic cards navigate. */
(() => {
    'use strict';
    const selector = '.fb-users-page .fb-users-stats > .fb-users-stat-card';
    document.addEventListener('pointerdown', (event) => {
        const card = event.target.closest(selector);
        if (!card) return;
        card.classList.remove('is-animating');
        // Force a style boundary so repeated taps replay the animation.
        void card.offsetWidth;
        card.classList.add('is-animating');
        window.setTimeout(() => card.classList.remove('is-animating'), 760);
    }, { passive: true });
})();
