/* FindBack Matching — Stat Card Motion V12
 * Touch/click state for the four Matching statistic cards.
 * Does not navigate, submit forms, or affect page scroll.
 */
(function () {
    'use strict';

    const selector =
        '.match-wrap:has(> .match-index-heading) > .match-stats > .match-stat';

    let activeCard = null;
    let timer = null;
    let startX = 0;
    let startY = 0;

    function clearCard() {
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
        if (activeCard) {
            activeCard.classList.remove('fb-match-stat-active');
            activeCard = null;
        }
    }

    function activate(card, duration) {
        clearCard();
        activeCard = card;
        card.classList.add('fb-match-stat-active');

        timer = window.setTimeout(function () {
            if (activeCard === card) {
                card.classList.remove('fb-match-stat-active');
                activeCard = null;
            }
            timer = null;
        }, duration);
    }

    /* Desktop click + keyboard activation. */
    document.addEventListener('click', function (event) {
        const card = event.target.closest(selector);
        if (!card) return;
        activate(card, 420);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;

        const card = event.target.closest(selector);
        if (!card) return;

        activate(card, 420);
    });

    /* Touch: press animation, but cancel it when the finger is scrolling. */
    document.addEventListener('pointerdown', function (event) {
        if (event.pointerType === 'mouse') return;

        const card = event.target.closest(selector);
        if (!card) return;

        clearCard();
        activeCard = card;
        startX = event.clientX;
        startY = event.clientY;
        card.classList.add('fb-match-stat-active');
    }, { passive: true });

    document.addEventListener('pointermove', function (event) {
        if (!activeCard || event.pointerType === 'mouse') return;

        if (Math.hypot(event.clientX - startX, event.clientY - startY) > 10) {
            clearCard();
        }
    }, { passive: true });

    document.addEventListener('pointerup', function (event) {
        if (!activeCard || event.pointerType === 'mouse') return;

        const card = activeCard;
        if (timer) clearTimeout(timer);

        timer = window.setTimeout(function () {
            if (activeCard === card) {
                card.classList.remove('fb-match-stat-active');
                activeCard = null;
            }
            timer = null;
        }, 380);
    }, { passive: true });

    document.addEventListener('pointercancel', clearCard, { passive: true });
    window.addEventListener('scroll', clearCard, { passive: true });
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) clearCard();
    });
})();
