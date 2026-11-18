
/*
 * FindBack Claims Navigation Transition v1
 * - Applies only to navigation clicks, not to cards/content.
 * - Keeps normal browser navigation semantics.
 * - Skips external links, downloads, anchors, new tabs, forms, AJAX controls.
 * - Works responsively on desktop/mobile.
 */
(function () {
    'use strict';

    if (window.__findbackClaimsNavigationV1) return;
    window.__findbackClaimsNavigationV1 = true;

    var reduced = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    document.documentElement.classList.add('findback-nav-transition-enabled');

    function isNavigationLink(a) {
        if (!a || !a.href) return false;
        if (a.target && a.target !== '_self') return false;
        if (a.hasAttribute('download')) return false;
        if (a.getAttribute('rel') && /\bexternal\b/i.test(a.getAttribute('rel'))) return false;

        var url;
        try { url = new URL(a.href, window.location.href); } catch (e) { return false; }

        if (url.origin !== window.location.origin) return false;
        if (url.pathname === window.location.pathname &&
            url.search === window.location.search &&
            url.hash) return false;

        if (a.getAttribute('href') && a.getAttribute('href').trim().charAt(0) === '#') return false;

        return true;
    }

    function isInteractiveControl(a) {
        return !!a.closest(
            'form, button, input, select, textarea, [role="button"], ' +
            '[data-no-page-transition], .no-page-transition'
        );
    }

    function go(url) {
        if (reduced) {
            window.location.href = url;
            return;
        }

        var root = document.querySelector('main') ||
                   document.querySelector('.admin-content') ||
                   document.querySelector('.content-wrapper') ||
                   document.body;

        root.classList.add('findback-page-transition', 'is-leaving');

        window.setTimeout(function () {
            window.location.href = url;
        }, 115);
    }

    document.addEventListener('click', function (event) {
        if (event.defaultPrevented) return;
        if (event.button !== 0) return;
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

        var a = event.target.closest && event.target.closest('a');
        if (!a || !isNavigationLink(a) || isInteractiveControl(a)) return;

        // Do not interfere with JavaScript/AJAX links that explicitly opt out.
        if (a.dataset && a.dataset.ajax === 'true') return;

        event.preventDefault();
        go(a.href);
    }, true);
})();
