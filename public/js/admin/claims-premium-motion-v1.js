/* FindBack Kampus — Klaim & Pengembalian premium motion V1.
 * Visual-only: animates displayed statistic numbers without changing data.
 */
(function () {
    'use strict';

    function animateCount(el) {
        if (!el || el.dataset.fbCountAnimated === '1') return;

        var raw = (el.textContent || '').trim().replace(/[^\d]/g, '');
        var target = Number(raw);
        if (!Number.isFinite(target)) return;

        el.dataset.fbCountAnimated = '1';

        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            el.textContent = target.toLocaleString('id-ID');
            return;
        }

        var duration = 520;
        var start = performance.now();

        function frame(now) {
            var progress = Math.min((now - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(target * eased).toLocaleString('id-ID');
            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                el.textContent = target.toLocaleString('id-ID');
            }
        }

        el.textContent = '0';
        requestAnimationFrame(frame);
    }

    function init() {
        document.querySelectorAll('.fb-claim-executive .fb-claim-stat strong')
            .forEach(animateCount);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
