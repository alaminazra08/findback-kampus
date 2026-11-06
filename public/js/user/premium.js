(() => {
    const trigger = document.getElementById("fp-menu"),
        side = document.getElementById("fp-sidebar"),
        overlay = document.getElementById("fp-overlay");
    if (!trigger || !side || !overlay) return;
    const media = window.matchMedia("(max-width: 1100px)");
    function setOpen(open, focus = true) {
        document.body.classList.toggle("fp-menu-open", open);
        trigger.setAttribute("aria-expanded", String(open));
        trigger.setAttribute("aria-label", open ? "Tutup navigasi" : "Buka navigasi");
        overlay.hidden = !open;
        side.inert = media.matches && !open;
        if (open && focus) side.querySelector("a")?.focus();
        else if (focus) trigger.focus();
    }
    trigger.addEventListener("click", () =>
        setOpen(trigger.getAttribute("aria-expanded") !== "true"),
    );
    overlay.addEventListener("click", () => setOpen(false));
    document.addEventListener("keydown", (e) => {
        if (!document.body.classList.contains("fp-menu-open")) return;
        if (e.key === "Escape") setOpen(false);
        if (e.key === "Tab") {
            const links = [...side.querySelectorAll("a,button")];
            const first = links[0],
                last = links[links.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });
    media.addEventListener("change", () => setOpen(false, false));
    setOpen(false, false);
    document.querySelectorAll(".fp-thumb img").forEach((img) =>
        img.addEventListener("error", () => {
            img.hidden = true;
        }),
    );
})();
