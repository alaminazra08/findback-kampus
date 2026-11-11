(() => {
    const root = document.documentElement;
    const viewport = window.visualViewport;
    let pending = false;

    const update = () => {
        pending = false;
        root.style.setProperty("--fb-viewport-height", `${Math.round(viewport?.height ?? window.innerHeight)}px`);
        root.style.setProperty("--fb-viewport-top", `${Math.round(viewport?.offsetTop ?? 0)}px`);
    };
    const schedule = () => {
        if (pending) return;
        pending = true;
        window.requestAnimationFrame(update);
    };

    update();
    window.addEventListener("resize", schedule, { passive: true });
    viewport?.addEventListener("resize", schedule, { passive: true });
    viewport?.addEventListener("scroll", schedule, { passive: true });
})();
