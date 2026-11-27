(() => {
    "use strict";

    const mobileQuery = window.matchMedia("(max-width: 800px)");
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    // Directional hysteresis: small scroll noise must not toggle the dock.
    const HIDE_DISTANCE = 30;
    const REVEAL_DISTANCE = 18;
    const TOP_GUARD = 40;

    let lastY = Math.max(0, window.scrollY || window.pageYOffset || 0);
    let directionStartY = lastY;
    let direction = 0;
    let framePending = false;

    const getNav = () => document.querySelector(".cu-mobile-nav");

    const reveal = () => {
        getNav()?.classList.remove("is-scrolled-away");
    };

    const conceal = () => {
        const nav = getNav();
        // Pertahankan dock hanya untuk fokus keyboard yang terlihat.
        // Fokus sisa dari tap layar sentuh tidak boleh mengunci navbar.
        const keyboardFocus = nav?.querySelector(":focus-visible");
        if (mobileQuery.matches && nav && !keyboardFocus) {
            nav.classList.add("is-scrolled-away");
        }
    };

    const update = () => {
        framePending = false;
        const currentY = Math.max(0, window.scrollY || window.pageYOffset || 0);

        if (!mobileQuery.matches || reducedMotion.matches) {
            reveal();
            lastY = currentY;
            directionStartY = currentY;
            direction = 0;
            return;
        }

        if (currentY <= TOP_GUARD) {
            reveal();
            direction = 0;
            directionStartY = currentY;
            lastY = currentY;
            return;
        }

        const delta = currentY - lastY;

        if (delta > 0) {
            if (direction !== 1) {
                direction = 1;
                directionStartY = lastY;
            }

            if (currentY - directionStartY >= HIDE_DISTANCE) {
                conceal();
            }
        } else if (delta < 0) {
            if (direction !== -1) {
                direction = -1;
                directionStartY = lastY;
            }

            if (directionStartY - currentY >= REVEAL_DISTANCE) {
                reveal();
            }
        }

        lastY = currentY;
    };

    const onScroll = () => {
        if (framePending) return;
        framePending = true;
        window.requestAnimationFrame(update);
    };

    const resetForPage = () => {
        lastY = Math.max(0, window.scrollY || window.pageYOffset || 0);
        directionStartY = lastY;
        direction = 0;
        reveal();
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("pageshow", resetForPage);
    window.addEventListener("popstate", resetForPage);
    document.addEventListener("fb:pagechange", resetForPage);

    document.addEventListener("pointerdown", (event) => {
        if (event.target.closest?.(".cu-mobile-nav")) {
            reveal();
            direction = 0;
            directionStartY = Math.max(0, window.scrollY || window.pageYOffset || 0);
            lastY = directionStartY;
        }
    }, { passive: true });

    document.addEventListener("focusin", (event) => {
        if (event.target.closest?.(".cu-mobile-nav")) {
            reveal();
        }
    });

    const onBreakpointChange = () => resetForPage();

    if (mobileQuery.addEventListener) {
        mobileQuery.addEventListener("change", onBreakpointChange);
    } else {
        mobileQuery.addListener(onBreakpointChange);
    }

    if (reducedMotion.addEventListener) {
        reducedMotion.addEventListener("change", onBreakpointChange);
    } else {
        reducedMotion.addListener(onBreakpointChange);
    }

    resetForPage();
})();
