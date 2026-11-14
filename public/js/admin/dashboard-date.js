(() => {
function mount(signal) {
    const dateLabel = document.getElementById("fb-live-date");
    if (!dateLabel) return;
    const options = {
        timeZone: "Asia/Jakarta",
        day: "numeric",
        year: "numeric",
    };
    const fullDate = dateLabel.querySelector(".fb-date-text");
    const shortDate = dateLabel.querySelector(".fb-date-text-short");
    const fullFormat = new Intl.DateTimeFormat("id-ID", { ...options, month: "long" });
    const shortFormat = new Intl.DateTimeFormat("id-ID", { ...options, month: "short" });
    const update = () => {
        const now = new Date();
        fullDate.textContent = fullFormat.format(now);
        if (shortDate) shortDate.textContent = shortFormat.format(now);
    };
    update();
    const timer = setInterval(update, 30000);
    signal.addEventListener("abort", () => clearInterval(timer), { once: true });
    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) update();
    }, { signal });
}

    let controller;
    function render() {
        controller?.abort();
        controller = new AbortController();
        mount(controller.signal);
    }
    document.addEventListener('fb:admin-page', render);
    render();
})();
