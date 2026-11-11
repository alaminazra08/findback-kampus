(() => {
    const sidebar = document.getElementById("fb-sidebar");
    const backdrop = document.getElementById("fb-sidebar-backdrop");
    const toggle = document.getElementById("fb-menu-toggle");
    if (!sidebar || !backdrop || !toggle) return;

    const mobile = window.matchMedia("(max-width: 960px)");
    const setOpen = (open) => {
        sidebar.classList.toggle("is-open", open);
        backdrop.hidden = !open;
        sidebar.inert = mobile.matches && !open;
        toggle.setAttribute("aria-expanded", String(open));
        toggle.setAttribute("aria-label", open ? "Tutup menu" : "Buka menu");
        document.body.classList.toggle("fb-nav-open", open);
    };
    const close = () => {
        if (!sidebar.classList.contains("is-open")) return;
        setOpen(false);
        toggle.focus();
    };

    toggle.addEventListener("click", () => {
        const open = !sidebar.classList.contains("is-open");
        setOpen(open);
        if (open) sidebar.querySelector("button, a[href]")?.focus();
        else toggle.focus();
    });
    backdrop.addEventListener("click", close);
    document.addEventListener("keydown", (event) => {
        if (!mobile.matches || !sidebar.classList.contains("is-open")) return;
        if (event.key === "Escape") {
            event.preventDefault();
            close();
        }
        if (event.key !== "Tab") return;
        const controls = [...sidebar.querySelectorAll("a[href], button:not([disabled]), select, input")]
            .filter((element) => element.getClientRects().length > 0);
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last?.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first?.focus();
        }
    });
    mobile.addEventListener("change", () => setOpen(false));
    setOpen(false);
})();
