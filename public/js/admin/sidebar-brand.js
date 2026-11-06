(() => {
    const brand = document.querySelector(".fb-sidebar .fb-brand");

    const mark = brand?.querySelector(".fb-brand-mark");

    if (!brand || !mark) return;

    brand.addEventListener("click", (event) => {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.altKey ||
            event.shiftKey
        ) {
            return;
        }

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            return;
        }

        event.preventDefault();

        mark.classList.remove("fb-logo-clicked");

        void mark.offsetWidth;

        mark.classList.add("fb-logo-clicked");

        const target = new URL(brand.href, window.location.href);

        if (
            target.pathname !== window.location.pathname ||
            target.search !== window.location.search
        ) {
            window.setTimeout(() => {
                window.location.assign(target.href);
            }, 360);
        }
    });
})();
