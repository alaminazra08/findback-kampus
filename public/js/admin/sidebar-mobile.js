(() => {
    const sidebar = document.getElementById("fb-sidebar");
    const backdrop = document.getElementById("fb-sidebar-backdrop");
    const toggle = document.getElementById("fb-menu-toggle");

    if (!sidebar || !backdrop || !toggle) return;

    const setOpen = (open) => {
        sidebar.classList.toggle("is-open", open);

        backdrop.hidden = !open;

        toggle.setAttribute("aria-expanded", String(open));

        toggle.setAttribute("aria-label", open ? "Tutup menu" : "Buka menu");

        document.body.classList.toggle("fb-nav-open", open);
    };

    toggle.addEventListener("click", () => {
        setOpen(!sidebar.classList.contains("is-open"));
    });

    backdrop.addEventListener("click", () => {
        setOpen(false);
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            setOpen(false);
        }
    });

    window.addEventListener("resize", () => {
        if (window.innerWidth > 960) {
            setOpen(false);
        }
    });
})();
