(() => {
    let controller;
    function bind() {
    controller?.abort();
    controller = new AbortController();
    const { signal } = controller;
    const wrapper = document.getElementById("fb-notif");
    const button = document.getElementById("fb-notif-trigger");
    const panel = document.getElementById("fb-notif-panel");
    if (!wrapper || !button || !panel) return;

    const setOpen = (open) => {
        panel.hidden = !open;
        button.setAttribute("aria-expanded", String(open));
    };

    button.addEventListener("click", () => setOpen(panel.hidden));
    document.addEventListener("click", (event) => {
        if (!wrapper.contains(event.target)) setOpen(false);
    }, { signal });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !panel.hidden) {
            setOpen(false);
            button.focus();
        }
    }, { signal });
    }
    document.addEventListener("DOMContentLoaded", bind, { once: true });
    document.addEventListener("fb:admin-page", bind);
})();
