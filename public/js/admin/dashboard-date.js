(() => {
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
    setInterval(update, 30000);
    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) update();
    });
})();
