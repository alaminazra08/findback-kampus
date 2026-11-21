(() => {
    function mount() {
        document.querySelectorAll("[data-fb-proof-open]").forEach(function (link) {
            if (link.dataset.fbProofReady) return;
            const dialog = document.getElementById(link.dataset.fbProofOpen || "fb-proof-dialog");
            if (!dialog || typeof dialog.showModal !== "function") return;

            link.dataset.fbProofReady = "1";
            link.addEventListener("click", function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                if (dialog.open) return;

                const title = link.dataset.fbProofTitle;
                if (title) {
                    const image = dialog.querySelector("[data-fb-proof-image]");
                    dialog.querySelector("[data-fb-proof-title]").textContent = title;
                    image.alt = title;
                    image.src = link.href;
                    dialog.addEventListener("close", () => link.focus({ preventScroll: true }), { once: true });
                }

                dialog.showModal();
            });

            if (dialog.dataset.fbProofReady) return;
            dialog.dataset.fbProofReady = "1";
            dialog.querySelector("[data-fb-proof-close]")?.addEventListener("click", () => dialog.close());
            dialog.addEventListener("click", function (event) {
                if (event.target === dialog) dialog.close();
            });
        });
    }

    document.addEventListener("fb:admin-page", mount);
    mount();
})();
