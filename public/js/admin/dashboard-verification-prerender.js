(() => {
    'use strict';

    const destination = document.currentScript?.dataset.destination;
    if (!destination || !window.HTMLScriptElement?.supports?.('speculationrules')) return;
    if (navigator.connection?.saveData || document.prerendering) return;

    const url = new URL(destination, location.href);
    if (url.origin !== location.origin || url.pathname === location.pathname) return;

    const links = document.querySelectorAll('.fb-sidebar a[href]');
    const link = Array.from(links).find(anchor => anchor.href === url.href);
    if (!link) return;

    // The browser prepares only this sidebar link after an intent signal.
    // A native navigation preserves Laravel redirects and page initialization.
    const rules = document.createElement('script');
    rules.type = 'speculationrules';
    rules.textContent = JSON.stringify({
        prerender: [{
            source: 'document',
            where: { selector_matches: '.fb-dashboard-verification-prerender' },
            eagerness: 'moderate'
        }]
    });
    link.classList.add('fb-dashboard-verification-prerender');
    document.head.append(rules);
})();
