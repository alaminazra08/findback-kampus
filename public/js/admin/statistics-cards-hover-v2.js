/* FINDBACK STATISTICS CARDS — tactile feedback V2
   Cards remain informational and do not navigate or alter report data. */
(() => {
    'use strict';

    const selector = '.fs-page .fs-cards > article.fs-card.fs-card-v4';
    const activeClass = 'fb-statistics-card-active-v2';
    const timers = new WeakMap();

    const replay = (card) => {
        if (!card) return;
        card.classList.remove(activeClass);
        const previous = timers.get(card);
        if (previous) window.clearTimeout(previous);
        // Restart the short highlight when the same card is tapped again.
        void card.offsetWidth;
        card.classList.add(activeClass);
        timers.set(card, window.setTimeout(() => {
            card.classList.remove(activeClass);
            timers.delete(card);
        }, 820));
    };

    document.addEventListener('pointerdown', (event) => {
        const card = event.target.closest(selector);
        if (!card) return;
        // Hover is handled by CSS; pointer feedback adds a replayable press/tap response.
        replay(card);
    }, { passive: true });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        const card = event.target.closest(selector);
        if (card) replay(card);
    });

    const clear = () => {
        document.querySelectorAll(`${selector}.${activeClass}`).forEach((card) => {
            card.classList.remove(activeClass);
            const timer = timers.get(card);
            if (timer) window.clearTimeout(timer);
            timers.delete(card);
        });
    };

    window.addEventListener('scroll', clear, { passive: true });
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) clear();
    });
})();
