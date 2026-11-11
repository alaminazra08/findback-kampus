/**
 * Shared, declarative interactions formerly repeated as HTML event attributes.
 * Loaded synchronously in the head so cached/broken images are covered too.
 */
(() => {
    document.addEventListener("click", (event) => {
        const opener = event.target.closest?.("[data-dialog-open]");
        const closer = event.target.closest?.("[data-dialog-close]");
        if (opener) document.getElementById(opener.dataset.dialogOpen)?.showModal();
        if (closer) document.getElementById(closer.dataset.dialogClose)?.close();
        const target = event.target.closest?.(
            "[data-confirm], [data-close-admin-nav], [data-print-page]",
        );
        if (!target) return;
        if (target.hasAttribute("data-close-admin-nav"))
            document.getElementById("fb-menu-toggle")?.click();
        if (target.hasAttribute("data-print-page")) window.print();
        if (target.dataset.confirmEvent === "click" && !window.confirm(target.dataset.confirm))
            event.preventDefault();
    });
    document.addEventListener("submit", (event) => {
        const form = event.target;
        if (form.dataset.confirmEvent === "submit" && !window.confirm(form.dataset.confirm))
            event.preventDefault();
    });
    document.addEventListener("change", (event) => {
        if (event.target.hasAttribute("data-submit-on-change")) event.target.form?.submit();
    });
    const fallback = (image) => {
        if (!image?.dataset?.imageFallback) return;
        if (image.dataset.imageFallback === "hidden") {
            image.hidden = true;
            if (image.nextElementSibling) image.nextElementSibling.hidden = false;
        } else {
            image.style.display = "none";
            if (image.nextElementSibling) image.nextElementSibling.style.display = "grid";
        }
    };
    document.addEventListener("error", (event) => fallback(event.target), true);
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll("img[data-image-fallback]").forEach((image) => {
            if (image.complete && image.naturalWidth === 0) fallback(image);
        });
    });
})();
