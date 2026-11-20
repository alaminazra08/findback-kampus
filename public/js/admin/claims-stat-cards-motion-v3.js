/*
 * FindBack Klaim Barang — Stat Card Motion V3
 * Touch/click feedback for the five claim summary cards.
 */
(function () {
    'use strict';

    const selector = '.fb-claim-executive .fb-claim-stat';
    const activeClass = 'fb-claim-stat-active';
    const timers = new WeakMap();
    let gesture = null;

    function reset(card) {
        if (!card) return;
        const timer = timers.get(card);
        if (timer) clearTimeout(timer);
        card.classList.remove(activeClass);
    }

    function start(card, x, y) {
        if (gesture) reset(gesture.card);
        reset(card);
        gesture = { card, x, y, moved: false };
        card.classList.add(activeClass);
    }

    function move(x, y) {
        if (!gesture) return;
        if (Math.hypot(x - gesture.x, y - gesture.y) > 10) {
            reset(gesture.card);
            gesture.moved = true;
        }
    }

    function end() {
        if (!gesture) return;
        const current = gesture;
        gesture = null;

        if (current.moved) {
            reset(current.card);
            return;
        }

        timers.set(
            current.card,
            setTimeout(function () {
                reset(current.card);
            }, 420)
        );
    }

    function cancel() {
        if (gesture) reset(gesture.card);
        gesture = null;
    }

    const isTouchLike = function () {
        return window.matchMedia('(hover: none), (pointer: coarse)').matches;
    };

    if (window.PointerEvent) {
        document.addEventListener('pointerdown', function (event) {
            const card = event.target.closest(selector);
            if (!card) return;

            if (event.pointerType === 'mouse') {
                /* Mouse hover is handled by CSS; click still gets a short pulse. */
                card.classList.add(activeClass);
                window.setTimeout(function () {
                    card.classList.remove(activeClass);
                }, 260);
                return;
            }

            if (!isTouchLike()) return;
            start(card, event.clientX, event.clientY);
        }, { passive: true });

        document.addEventListener('pointermove', function (event) {
            if (event.pointerType !== 'mouse') {
                move(event.clientX, event.clientY);
            }
        }, { passive: true });

        document.addEventListener('pointerup', function (event) {
            if (event.pointerType !== 'mouse') end();
        }, { passive: true });

        document.addEventListener('pointercancel', cancel, { passive: true });
    } else {
        document.addEventListener('touchstart', function (event) {
            if (!isTouchLike() || !event.touches.length) return;
            const card = event.target.closest(selector);
            if (card) start(card, event.touches[0].clientX, event.touches[0].clientY);
        }, { passive: true });

        document.addEventListener('touchmove', function (event) {
            if (event.touches.length) move(event.touches[0].clientX, event.touches[0].clientY);
        }, { passive: true });

        document.addEventListener('touchend', end, { passive: true });
        document.addEventListener('touchcancel', cancel, { passive: true });
    }

    window.addEventListener('scroll', cancel, { passive: true });
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) cancel();
    });
})();
