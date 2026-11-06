document.querySelectorAll("[data-toggle-password]").forEach((button) => {
    button.addEventListener("click", () => {
        const input = document.getElementById(button.dataset.togglePassword);

        if (!input) return;

        const visible = input.type === "password";

        input.type = visible ? "text" : "password";

        button.setAttribute("aria-pressed", String(visible));

        const label =
            button.dataset.togglePassword === "password" ? "kata sandi" : "konfirmasi kata sandi";

        const action = visible ? "Sembunyikan" : "Tampilkan";

        button.setAttribute("aria-label", `${action} ${label}`);

        button.title = `${action} ${label}`;
    });
});
