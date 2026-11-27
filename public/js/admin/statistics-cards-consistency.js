/* FindBack — tactile feedback for Statistics summary cards V1.
   Visual-only: cards remain informational and do not navigate. */
(() => {
    'use strict';

    const selector = '.fs-page .fs-cards > article.fs-card.fs-card-v4';
    const timers = new WeakMap();

    document.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && !window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;

        const card = event.target.closest(selector);
        if (!card) return;

        card.classList.remove('is-animating');
        window.clearTimeout(timers.get(card));
        // Reflow lets consecutive presses replay the short rail animation.
        void card.offsetWidth;
        card.classList.add('is-animating');

        timers.set(card, window.setTimeout(() => {
            card.classList.remove('is-animating');
        }, 780));
    }, { passive: true });

    const clearActive = () => {
        document.querySelectorAll(`${selector}.is-animating`).forEach((card) => {
            card.classList.remove('is-animating');
            window.clearTimeout(timers.get(card));
        });
    };

    window.addEventListener('scroll', clearActive, { passive: true });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) clearActive();
    });
})();
