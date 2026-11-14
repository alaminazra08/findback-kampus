(() => {
    function mount() {
        const dialog = document.getElementById("fb-proof-dialog");
        if (!dialog || dialog.dataset.fbProofReady) return;
        dialog.dataset.fbProofReady = "1";
        document.querySelectorAll("[data-fb-proof-open]").forEach(function (link) {
            link.addEventListener("click", function (event) {
                if (typeof dialog.showModal !== "function") return;
                event.preventDefault();
                dialog.showModal();
            });
        });
        dialog.querySelectorAll("[data-fb-proof-close]").forEach(function (button) {
            button.addEventListener("click", function () {
                dialog.close();
            });
        });
        dialog.addEventListener("click", function (event) {
            if (event.target === dialog) dialog.close();
        });
    }

    document.addEventListener('fb:admin-page', mount);
    mount();
})();
