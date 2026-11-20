/*
 * FindBack — Stat Card Accent Animation V2
 * Finds statistic-card groups without requiring a Blade markup rewrite.
 */
(function () {
    'use strict';

    const CARD_CLASS = 'findback-stat-card-animated';

    function visible(el) {
        const r = el.getBoundingClientRect();
        const s = getComputedStyle(el);
        return r.width >= 150 && r.height >= 90 &&
            s.display !== 'none' &&
            s.visibility !== 'hidden';
    }

    function looksLikeCard(el) {
        if (!visible(el)) return false;
        const text = (el.innerText || '').trim();
        if (text.length < 2 || text.length > 500) return false;

        const r = el.getBoundingClientRect();
        const radius = parseFloat(getComputedStyle(el).borderTopLeftRadius) || 0;
        return radius >= 10 && r.height >= 120 && r.width >= 150;
    }

    function scoreGroup(parent, children) {
        if (children.length < 2 || children.length > 8) return -1;
        const rects = children.map(x => x.getBoundingClientRect());
        const widths = rects.map(r => r.width);
        const heights = rects.map(r => r.height);
        const avgW = widths.reduce((a,b) => a+b,0)/widths.length;
        const avgH = heights.reduce((a,b) => a+b,0)/heights.length;
        const similar = widths.every(w => Math.abs(w-avgW) <= avgW*.28) &&
                        heights.every(h => Math.abs(h-avgH) <= avgH*.28);
        if (!similar) return -1;
        return children.length * 10 + Math.min(avgW/100, 10);
    }

    function findGroups() {
        const groups = [];
        const all = Array.from(document.querySelectorAll('body *'));

        for (const parent of all) {
            const kids = Array.from(parent.children || []);
            if (kids.length < 2 || kids.length > 8) continue;

            const cards = kids.filter(looksLikeCard);
            if (cards.length !== kids.length) continue;

            const score = scoreGroup(parent, cards);
            if (score < 0) continue;

            const parentRect = parent.getBoundingClientRect();
            if (parentRect.width < 500 || parentRect.height < 100) continue;

            groups.push({ parent, cards, score });
        }

        groups.sort((a,b) => b.score - a.score);
        return groups;
    }

    function apply() {
        // Prefer explicit/common statistic-card class names when present.
        const explicit = document.querySelectorAll(
            '.stat-card, .stats-card, .statistic-card, .statistics-card, ' +
            '.dashboard-stat-card, .admin-stat-card, [data-stat-card]'
        );
        explicit.forEach(el => el.classList.add(CARD_CLASS));

        // Then use layout detection for FindBack pages whose card class differs.
        const groups = findGroups();
        const chosen = [];
        for (const group of groups) {
            if (chosen.some(g => g.parent.contains(group.parent) || group.parent.contains(g.parent))) continue;
            group.cards.forEach(c => c.classList.add(CARD_CLASS));
            chosen.push(group);
            // Usually the first stats group is enough; allow another small group
            // such as a 2-card stats block elsewhere on the same page.
            if (chosen.length >= 2) break;
        }

        bind();
    }

    function bind() {
        document.querySelectorAll('.' + CARD_CLASS).forEach(card => {
            if (card.dataset.findbackStatBound === '1') return;
            card.dataset.findbackStatBound = '1';
            card.setAttribute('tabindex', card.getAttribute('tabindex') || '0');

            card.addEventListener('click', function () {
                card.classList.remove('is-active');
                void card.offsetWidth;
                card.classList.add('is-active');
                window.setTimeout(() => card.classList.remove('is-active'), 780);
            }, { passive: true });

            card.addEventListener('touchstart', function () {
                card.classList.remove('is-active');
                void card.offsetWidth;
                card.classList.add('is-active');
                window.setTimeout(() => card.classList.remove('is-active'), 780);
            }, { passive: true });
        });
    }

    function start() {
        apply();
        const observer = new MutationObserver(() => apply());
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
})();
