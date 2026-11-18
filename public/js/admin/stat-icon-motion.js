(() => {
    const selector = [
        '.fb-v-stats-v18 > .fb-v-stat',
        '.match-wrap > .match-stats > .match-stat',
        '.fb-claim-executive .fb-claim-stats > .fb-claim-stat',
        '.fs-cards > .fs-card-v4',
    ].join(', ');
    const active = 'fb-stat-icon-pressed';
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    document.addEventListener('click', (event) => {
        if (reducedMotion.matches || !(event.target instanceof Element)) return;
        const card = event.target.closest(selector);
        if (!card || card.classList.contains(active)) return;

        const badge = card.querySelector('.fs-card-icon');
        const icon = badge ? getComputedStyle(badge) : getComputedStyle(card, '::after');
        card.style.setProperty('--fb-icon-rest-transform', icon.transform);
        card.style.setProperty('--fb-icon-rest-shadow', icon.boxShadow);
        card.classList.add(active);
        window.setTimeout(() => {
            card.classList.remove(active);
            card.style.removeProperty('--fb-icon-rest-transform');
            card.style.removeProperty('--fb-icon-rest-shadow');
        }, 400);
    });
})();
