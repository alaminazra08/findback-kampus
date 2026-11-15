
document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("fb-chat-overlay");
    if (!modal) return;

    const stream = document.getElementById("fb-modal-stream");
    const editForm = document.getElementById("fb-chat-edit-form");
    const editValue = document.getElementById("fb-chat-edit-value");

    let activeMenu = null;

    // Mengunci scroll halaman utama.
    document.body.style.overflow = "hidden";

    // Menampilkan pesan terbaru.
    stream.scrollTop = stream.scrollHeight;

    // Menutup menu yang sedang aktif.
    function closeMenu() {
        if (!activeMenu) return;

        activeMenu.hidden = true;
        activeMenu.classList.remove("open-below");

        activeMenu.closest(".fb-chat-bubble-row")
            ?.classList.remove("context-active");

        activeMenu = null;
    }

    // Menampilkan menu milik pengguna.
    function showMenu(row) {
        if (!row?.classList.contains("mine")) return;

        const currentMenu = row.querySelector(
            "[data-message-menu]"
        );

        if (!currentMenu) return;

        closeMenu();

        currentMenu.hidden = false;
        row.classList.add("context-active");

        if (row.getBoundingClientRect().top < 230) {
            currentMenu.classList.add("open-below");
        }

        activeMenu = currentMenu;
    }

    // Klik kanan pada bubble pesan sendiri.
    stream.addEventListener("contextmenu", (event) => {
        const bubble = event.target.closest(
            ".fb-chat-bubble"
        );

        if (!bubble) return;

        const row = bubble.closest(
            ".fb-chat-bubble-row.mine"
        );

        if (!row) return;

        event.preventDefault();
        showMenu(row);
    });

    // Mengedit pesan melalui formulir Laravel.
    stream.addEventListener("click", (event) => {
        const editButton = event.target.closest(
            ".fb-chat-edit-btn"
        );

        if (!editButton) return;

        event.preventDefault();

        const oldText = editButton.dataset.text || "";

        const newText = prompt(
            "Edit pesan:",
            oldText
        );

        if (newText === null) return;

        const value = newText.trim();

        if (!value || value.length > 3000) {
            alert(
                "Pesan harus berisi 1 sampai 3000 karakter."
            );
            return;
        }

        editForm.action = editButton.dataset.update;
        editValue.value = value;

        editForm.requestSubmit();
    });

    // Konfirmasi sebelum menarik atau menghapus pesan.
    stream.addEventListener("submit", (event) => {
        const actionForm = event.target.closest(
            "form[data-confirm]"
        );

        if (!actionForm) return;

        if (!confirm(actionForm.dataset.confirm)) {
            event.preventDefault();
        }
    });

    // Klik di luar menu akan menutup menu.
    document.addEventListener("pointerdown", (event) => {
        if (
            activeMenu &&
            !activeMenu.contains(event.target)
        ) {
            closeMenu();
        }
    });

    // Escape untuk menutup menu.
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
            closeMenu();
        }
    });

    stream.addEventListener("scroll", closeMenu, {
        passive: true
    });

    // Tekan lama pada layar sentuh.
    let longPressTimer = null;
    let startX = 0;
    let startY = 0;

    stream.addEventListener("pointerdown", (event) => {
        if (event.pointerType === "mouse") return;

        const row = event.target.closest(
            ".fb-chat-bubble"
        )?.closest(".fb-chat-bubble-row.mine");

        if (!row) return;

        startX = event.clientX;
        startY = event.clientY;

        clearTimeout(longPressTimer);

        longPressTimer = setTimeout(() => {
            showMenu(row);
            longPressTimer = null;
        }, 550);
    });

    stream.addEventListener("pointermove", (event) => {
        if (
            Math.abs(event.clientX - startX) > 12 ||
            Math.abs(event.clientY - startY) > 12
        ) {
            clearTimeout(longPressTimer);
            longPressTimer = null;
        }
    }, { passive: true });

    stream.addEventListener("pointerup", () => {
        clearTimeout(longPressTimer);
    });

    stream.addEventListener("pointercancel", () => {
        clearTimeout(longPressTimer);
    });
});
