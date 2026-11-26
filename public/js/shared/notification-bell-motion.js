(() => {
    const selector = '.cu-notice-preview > summary, #fb-notif-trigger, .fb-notification-bell';
    const hover = window.matchMedia('(hover: hover) and (pointer: fine)');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const activeAnimations = new WeakMap();
    const lastPointerAnimation = new WeakMap();

    function ring(button, repeats = 1) {
        if (!button || reducedMotion.matches) return;
        const icon = button.querySelector('svg');
        if (!icon || typeof icon.animate !== 'function') return;

        activeAnimations.get(icon)?.cancel();
        const animation = icon.animate(
            [
                { transform: 'rotate(0deg)' },
                { transform: 'rotate(-14deg)' },
                { transform: 'rotate(12deg)' },
                { transform: 'rotate(-9deg)' },
                { transform: 'rotate(6deg)' },
                { transform: 'rotate(0deg)' },
            ],
            {
                duration: repeats > 1 ? 560 : 430,
                iterations: repeats,
                easing: 'ease-in-out',
            }
        );
        activeAnimations.set(icon, animation);
    }

    // Hover: one short shake, only for mouse/trackpad users.
    document.addEventListener('pointerover', event => {
        const button = event.target.closest?.(selector);
        if (!button || !hover.matches || event.pointerType === 'touch') return;
        if (!button.contains(event.relatedTarget)) ring(button, 1);
    });

    // Touch devices: start on pointerdown so the motion is visible immediately.
    // The following synthetic click is ignored to prevent a double animation.
    document.addEventListener('pointerdown', event => {
        const button = event.target.closest?.(selector);
        if (!button || event.pointerType !== 'touch') return;
        lastPointerAnimation.set(button, Date.now());
        ring(button, 1);
    }, { passive: true });

    // Mouse/keyboard click and browsers without Pointer Events.
    document.addEventListener('click', event => {
        const button = event.target.closest?.(selector);
        if (!button) return;
        if (Date.now() - (lastPointerAnimation.get(button) || 0) < 700) return;
        ring(button, 1);
    });

    // New notification: two brief shakes, then stop.
    document.addEventListener('fb:notification-arrived', event => {
        const button = event.target.closest?.(selector) || document.querySelector('#fb-notif-trigger');
        if (button) ring(button, 2);
    });

    // Initial unread state: give a subtle two-shake cue when the page opens.
    document.querySelectorAll(selector).forEach(button => {
        if (button.querySelector('.fb-notif-count, b')) ring(button, 2);
    });

    // User preview refresh remains limited to the user dashboard.
    const preview = document.querySelector('.cu-notice-preview');
    if (!preview) return;

    let pending = false;
    async function refreshUserNotices() {
        if (pending || document.hidden || preview.open) return;
        const url = window.location.href;
        pending = true;
        try {
            const response = await fetch(url, {
                cache: 'no-store',
                headers: { Accept: 'text/html' },
                signal: AbortSignal.timeout(8000),
            });
            if (!response.ok || response.redirected) return;
            const html = await response.text();
            if (window.location.href !== url || preview.open || document.hidden) return;
            const next = new DOMParser().parseFromString(html, 'text/html').querySelector('.cu-notice-preview');
            if (!next) return;

            const known = new Set([...preview.querySelectorAll('[data-notice-id]')].map(row => row.dataset.noticeId));
            const arrived = [...next.querySelectorAll('[data-notice-id].is-unread')]
                .some(row => !known.has(row.dataset.noticeId));
            const button = preview.querySelector('summary');
            const nextButton = next.querySelector('summary');
            if (!button || !nextButton) return;

            button.setAttribute('aria-label', nextButton.getAttribute('aria-label'));
            button.querySelector('b')?.remove();
            const badge = nextButton.querySelector('b');
            if (badge) button.append(document.importNode(badge, true));
            preview.querySelector('.cu-notice-panel')?.replaceWith(
                document.importNode(next.querySelector('.cu-notice-panel'), true)
            );
            if (arrived) ring(button, 2);
        } catch {
            // Keep the last rendered notification summary when refresh fails.
        } finally {
            pending = false;
        }
    }

    window.setInterval(refreshUserNotices, 30000);
    document.addEventListener('visibilitychange', refreshUserNotices);
})();
