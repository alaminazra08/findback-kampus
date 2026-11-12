(() => {
function mount(signal) {
    const dateLabel = document.getElementById("fb-live-date");
    if (!dateLabel) return;
    const formatter = new Intl.DateTimeFormat("id-ID", {
        timeZone: "Asia/Jakarta",
        day: "numeric",
        month: "long",
        year: "numeric",
    });
    const update = () => {
        dateLabel.querySelector(".fb-date-text").textContent = formatter.format(new Date());
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
