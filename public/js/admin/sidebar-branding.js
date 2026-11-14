(() => {
    const side = document.getElementById("fb-sidebar");
    if (!side) return;
    const brand = side.querySelector(".fb-brand");
    if (!brand) return;
    // Guard against duplicate initialization when scripts are evaluated again.
    if (side.dataset.fbSidebarBrandingInitialized === "1") return;
    side.dataset.fbSidebarBrandingInitialized = "1";
    const key = "findback-sidebar-compact-v44";
    const desktop = () => window.matchMedia("(min-width:961px)").matches;
    let compact = false;
    try {
        compact = localStorage.getItem(key) === "1";
    } catch (e) {}
    const toggle = document.createElement("button");
    toggle.type = "button";
    toggle.className = "fb44-collapse";
    toggle.innerHTML =
        '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 6l-6 6 6 6"/></svg>';
    side.appendChild(toggle);
    const apply = () => {
        const small = compact && desktop();
        document.body.classList.toggle("fb44-compact", small);
        document.documentElement.classList.toggle("fb45-precompact", small);
        toggle.setAttribute("aria-label", small ? "Perluas menu samping" : "Ringkas menu samping");
        toggle.title = small ? "Perluas menu samping" : "Ringkas menu samping";
        toggle.setAttribute("aria-expanded", String(!small));
        brand.setAttribute("aria-label", small ? "Perluas menu samping" : "Beranda administrator");
        brand.setAttribute("title", small ? "Perluas menu samping" : "Beranda administrator");
        if (small) {
            brand.setAttribute("role", "button");
            brand.setAttribute("aria-expanded", "false");
        } else {
            brand.removeAttribute("role");
            brand.removeAttribute("aria-expanded");
        }
        toggle.querySelector("svg").style.transform = small ? "rotate(180deg)" : "";
    };
    const setCompact = (value) => {
        compact = value;
        try {
            localStorage.setItem(key, compact ? "1" : "0");
        } catch (e) {}
        apply();
    };
    apply();
    toggle.addEventListener("click", () => {
        if (desktop()) setCompact(!compact);
    });
    // Intercept only compact desktop brand clicks. On expanded/mobile, logo remains a normal dashboard link.
    brand.addEventListener(
        "click",
        (e) => {
            if (!(desktop() && compact)) return;
            if (e.button !== 0 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            setCompact(false);
        },
        true,
    );
    brand.addEventListener(
        "keydown",
        (e) => {
            if (!(desktop() && compact) || e.key !== " ") return;
            e.preventDefault();
            e.stopImmediatePropagation();
            setCompact(false);
        },
        true,
    );
    // FindBack: avoid reloading the same active admin sidebar destination.
    // Match the actual URL, not just the is-active class (detail pages and filters may differ).
    side.addEventListener("click", (event) => {
        const link = event.target.closest("a.fb-nav-link[href]");
        if (!link || !side.contains(link) || event.defaultPrevented) return;
        if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        if (link.target && link.target !== "_self") return;
        if (link.hasAttribute("download") || link.getAttribute("aria-current") !== "page") return;
        const destination = new URL(link.href, window.location.href);
        const current = new URL(window.location.href);
        if (destination.href !== current.href) return;
        event.preventDefault();
        // In the mobile drawer, a repeat click should close the menu instead.
        if (window.matchMedia("(max-width: 960px)").matches && side.classList.contains("is-open")) {
            document.getElementById("fb-menu-toggle")?.click();
        }
    });
    window.addEventListener("resize", apply);
    side.querySelectorAll(".fb-nav-link,.fb-sidebar-logout").forEach((el) => {
        const label =
            el.querySelector(".fb-nav-text")?.textContent?.trim() || el.textContent.trim();
        if (label) el.setAttribute("title", label);
    });
})();
