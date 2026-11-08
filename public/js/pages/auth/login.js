document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.togglePassword);
        if (!input) return;
        const visible = input.type === "password";
        input.type = visible ? "text" : "password";
        button.setAttribute("aria-pressed", String(visible));
        const label = visible ? "Sembunyikan kata sandi" : "Tampilkan kata sandi";
        button.setAttribute("aria-label", label);
        button.title = label;
    });
});
