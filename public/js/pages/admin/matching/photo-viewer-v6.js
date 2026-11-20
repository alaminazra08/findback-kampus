(() => {
    function mount() {
        const dialog = document.getElementById("fb-match-photo-dialog");
        if (!dialog || dialog.dataset.ready || typeof dialog.showModal !== "function") return;
        dialog.dataset.ready = "true";
        const image = dialog.querySelector("[data-fb-photo-image]");
        const error = dialog.querySelector("[data-fb-photo-error]");
        const title = dialog.querySelector("h2");
        const zoom = dialog.querySelector("[data-fb-photo-zoom]");
        const stage = dialog.querySelector(".fb-proof-content");
        let opener;

        function resetZoom() {
            dialog.removeAttribute("data-zoom");
            image.style.removeProperty("width");
            stage.scrollTo(0, 0);
            zoom.setAttribute("aria-pressed", "false");
        }

        dialog.closest("main")?.querySelectorAll("[data-fb-match-photo]").forEach((link) => {
            link.addEventListener("click", (event) => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                opener = link;
                const label = link.dataset.photoLabel.replace(/^Laporan\s+/i, "");
                title.textContent = `Foto Laporan ${label}`;
                image.alt = title.textContent;
                error.hidden = true;
                image.hidden = true;
                zoom.disabled = true;
                resetZoom();
                image.src = link.href;
                dialog.showModal();
            });
        });

        image.addEventListener("load", () => {
            image.hidden = false;
            zoom.disabled = false;
        });
        image.addEventListener("error", () => {
            image.hidden = true;
            error.hidden = false;
            zoom.disabled = true;
        });
        zoom.addEventListener("click", () => {
            if (dialog.hasAttribute("data-zoom")) {
                resetZoom();
                return;
            }
            image.style.width = `${image.getBoundingClientRect().width * 2}px`;
            dialog.setAttribute("data-zoom", "");
            zoom.setAttribute("aria-pressed", "true");
        });
        dialog.querySelector("[data-fb-photo-close]").addEventListener("click", () => dialog.close());
        dialog.addEventListener("click", (event) => {
            if (event.target === dialog) dialog.close();
        });
        dialog.addEventListener("close", () => opener?.focus({ preventScroll: true }));
        window.addEventListener("resize", () => {
            if (dialog.open && dialog.hasAttribute("data-zoom")) resetZoom();
        });
    }

    document.addEventListener("fb:admin-page", mount);
    mount();
})();
