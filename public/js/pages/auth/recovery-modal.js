(() => {
    const overlay = document.getElementById("fb-recovery-modal");
    if (!overlay) return;
    const dialog = overlay.querySelector(".fb-recovery-dialog");
    const email = document.getElementById("recovery-email");
    let previousFocus = null;
    const isRecoveryUrl = () => new URL(location.href).searchParams.get("recovery") === "1";
    const setRecoveryUrl = (active) => {
        const url = new URL(location.href);
        if (active) url.searchParams.set("recovery", "1");
        else url.searchParams.delete("recovery");
        history.replaceState(history.state, "", url.pathname + url.search + url.hash);
    };
    const open = (updateUrl = true) => {
        previousFocus = document.activeElement;
        overlay.hidden = false;
        document.body.classList.add("fb-recovery-is-open");
        if (updateUrl) setRecoveryUrl(true);
        requestAnimationFrame(() => email?.focus());
    };
    const close = () => {
        overlay.hidden = true;
        document.body.classList.remove("fb-recovery-is-open");
        setRecoveryUrl(false);
        if (previousFocus?.isConnected) previousFocus.focus();
        else document.querySelector("[data-open-recovery]")?.focus();
    };
    document
        .querySelectorAll("[data-open-recovery]")
        .forEach((el) => el.addEventListener("click", () => open()));
    overlay
        .querySelectorAll("[data-close-recovery]")
        .forEach((el) => el.addEventListener("click", close));
    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) close();
    });
    document.addEventListener("keydown", (e) => {
        if (overlay.hidden) return;
        if (e.key === "Escape") {
            e.preventDefault();
            close();
        }
        if (e.key !== "Tab") return;
        const items = [
            ...dialog.querySelectorAll("button:not([disabled]), input:not([disabled]), a[href]"),
        ].filter((el) => el.getClientRects().length);
        if (!items.length) return;
        const first = items[0],
            last = items[items.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });
    if (isRecoveryUrl()) open(false);
})();
