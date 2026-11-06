document.addEventListener("DOMContentLoaded", function () {
    const dialog = document.getElementById("fb-proof-dialog");
    if (!dialog) return;
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
});
