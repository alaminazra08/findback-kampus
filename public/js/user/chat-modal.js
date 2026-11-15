
document.addEventListener("DOMContentLoaded", () => {
    const $ = id => document.getElementById(id);

    const overlay = $("fb-chat-overlay");
    if (!overlay) return;

    const stream = $("fb-modal-stream");
    const form = $("fb-chat-form");
    const input = $("fb-chat-textarea");
    const file = $("fb-chat-file");
    const fileLabel = $("fb-chat-file-label");
    const errorBox = $("fb-chat-ajax-error");

    const editOverlay = $("fb-edit-overlay");
    const confirmOverlay = $("fb-confirm-overlay");
    const editText = $("fb-edit-textarea");
    const editHelp = $("fb-edit-help");

    const sendButton = form?.querySelector(
        'button[type="submit"]'
    );

    const csrf =
        document.querySelector('meta[name="csrf-token"]')?.content ||
        form?.querySelector('[name="_token"]')?.value ||
        document.querySelector('#fb-chat-edit-form [name="_token"]')?.value;

    let activeMenu = null;
    let actionForm = null;
    let editingButton = null;
    let busy = false;
    let timer = null;

    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    window.addEventListener("pagehide", () => {
        document.body.style.overflow = originalOverflow;
    });

    // Dialog selalu berada di atas modal utama.
    [confirmOverlay, editOverlay].forEach(element => {
        if (element && element.parentElement !== document.body) {
            document.body.appendChild(element);
        }
    });

    



/* ==========================================
   POSISI SCROLL BERDASARKAN PESAN
   ========================================== */

const claimId = new URLSearchParams(location.search)
    .get("claim_id") || "default";

const chatScrollKey = `findback:chat:position:${claimId}`;

let scrollReady = false;
let restoringScroll = true;
let scrollSaveTimer = null;

function scrollBottom() {
    if (!stream) return;
    stream.scrollTop = stream.scrollHeight;
}

function getChatRows() {
    return Array.from(
        stream.querySelectorAll(
            ".fb-chat-bubble-row[data-id]"
        )
    );
}

function saveChatScroll() {
    if (!stream || !scrollReady || restoringScroll) {
        return;
    }

    const bottomDistance =
        stream.scrollHeight -
        stream.scrollTop -
        stream.clientHeight;

    const position = {
        bottom: bottomDistance <= 20,
        scrollTop: stream.scrollTop,
        messageId: null,
        offset: 0
    };

    if (!position.bottom) {
        const streamTop =
            stream.getBoundingClientRect().top;

        const anchor = getChatRows().find(row =>
            row.getBoundingClientRect().bottom >
            streamTop
        );

        if (anchor) {
            position.messageId = anchor.dataset.id;
            position.offset =
                anchor.getBoundingClientRect().top -
                streamTop;
        }
    }

    try {
        sessionStorage.setItem(
            chatScrollKey,
            JSON.stringify(position)
        );
    } catch (error) {
        console.warn(
            "Posisi percakapan tidak dapat disimpan.",
            error
        );
    }
}

function getSavedPosition() {
    try {
        const raw = sessionStorage.getItem(chatScrollKey);

        if (!raw) return null;

        const data = JSON.parse(raw);

        if (
            !data ||
            typeof data !== "object" ||
            typeof data.bottom !== "boolean"
        ) {
            return null;
        }

        return data;
    } catch (error) {
        return null;
    }
}

function restoreChatScroll() {
    if (!stream) return;

    restoringScroll = true;

    const saved = getSavedPosition();

    if (!saved || saved.bottom) {
        scrollBottom();
    } else {
        const anchor = getChatRows().find(
            row => row.dataset.id ===
                String(saved.messageId)
        );

        if (anchor) {
            const streamTop =
                stream.getBoundingClientRect().top;

            const currentOffset =
                anchor.getBoundingClientRect().top -
                streamTop;

            stream.scrollTop +=
                currentOffset -
                Number(saved.offset || 0);
        } else {
            stream.scrollTop =
                Math.max(0, Number(saved.scrollTop) || 0);
        }
    }

    scrollReady = true;
    restoringScroll = false;
    stream.style.removeProperty("visibility");
}

// Simpan posisi ketika pengguna menggulir chat.
stream.addEventListener("scroll", () => {
    if (!scrollReady || restoringScroll) return;

    clearTimeout(scrollSaveTimer);

    scrollSaveTimer = setTimeout(() => {
        saveChatScroll();
    }, 120);
}, { passive: true });

// Pulihkan setelah halaman dan CSS selesai dimuat.
function initChatPosition() {
    requestAnimationFrame(() => {
        restoreChatScroll();
    });
}

if (document.readyState === "complete") {
    initChatPosition();
} else {
    window.addEventListener(
        "load",
        initChatPosition,
        { once: true }
    );
}

// Simpan posisi terakhir sebelum halaman ditutup.
window.addEventListener("pagehide", () => {
    clearTimeout(scrollSaveTimer);
    saveChatScroll();
});





    function showError(message) {
        if (errorBox) {
            errorBox.textContent = message || "";
            errorBox.hidden = !message;
        } else if (message) {
            console.error(message);
        }
    }

    function closeMenu() {
        if (!activeMenu) return;

        activeMenu.hidden = true;
        activeMenu.classList.remove("open-below");

        activeMenu.closest(".fb-chat-bubble-row")
            ?.classList.remove("context-active");

        activeMenu = null;
    }

    function closeDialogs() {
        if (editOverlay) editOverlay.hidden = true;
        if (confirmOverlay) confirmOverlay.hidden = true;

        actionForm = null;
        editingButton = null;
    }

    function openMenu(row) {
        if (!row?.classList.contains("mine")) return;

        const menu = row.querySelector("[data-message-menu]");
        if (!menu) return;

        closeMenu();

        menu.hidden = false;
        menu.classList.toggle(
            "open-below",
            row.getBoundingClientRect().top < 240
        );

        activeMenu = menu;
        row.classList.add("context-active");
    }

    // Mengambil pesan kesalahan dari Laravel.
    async function readError(response) {
        try {
            const result = await response.json();

            return (
                Object.values(result.errors || {})[0]?.[0] ||
                result.message ||
                `HTTP ${response.status}`
            );
        } catch {
            return `HTTP ${response.status}`;
        }
    }

    // Mengirim permintaan tanpa reload.
    async function sendAjax(url, body) {
        const response = await fetch(url, {
            method: "POST",
            body,
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                ...(csrf ? { "X-CSRF-TOKEN": csrf } : {})
            },
            credentials: "same-origin"
        });

        if (!response.ok) {
            throw new Error(await readError(response));
        }

        return response.json();
    }

    // Membuat formulir aksi pesan.
    function makeAction(url, label, confirmation, danger = false) {
        const action = document.createElement("form");

        action.method = "POST";
        action.action = url;
        action.dataset.confirm = confirmation;

        const token = document.createElement("input");
        token.type = "hidden";
        token.name = "_token";
        token.value = csrf || "";

        const method = document.createElement("input");
        method.type = "hidden";
        method.name = "_method";
        method.value = "PATCH";

        const button = document.createElement("button");
        button.type = "submit";
        button.textContent = label;

        if (danger) button.className = "fb-chat-danger";

        action.append(token, method, button);

        return action;
    }

    // Membuat menu untuk pesan milik pengguna.
    function buildMenu(row, message) {
        if (!message.is_mine) return;

        row.querySelector("[data-message-menu]")?.remove();

        const menu = document.createElement("div");
        menu.className = "fb-chat-message-menu";
        menu.dataset.messageMenu = "";
        menu.hidden = true;

        const content = document.createElement("div");
        content.className = "fb-chat-menu-content";
        content.setAttribute("role", "group");
        content.setAttribute("aria-label", "Tindakan pesan");

        const url = type => {
            return stream.dataset[`${type}Url`]
                ?.replace("__MESSAGE_ID__", String(message.id));
        };

        if (!message.recalled_at && message.message) {
            const edit = document.createElement("button");

            edit.type = "button";
            edit.className = "fb-chat-edit-btn";
            edit.textContent = "✎ Edit pesan";
            edit.dataset.text = message.message;
            edit.dataset.update = url("update");

            content.append(edit);
        }

        if (!message.recalled_at) {
            content.append(
                makeAction(
                    url("recall"),
                    "↶ Tarik pesan",
                    "Tarik pesan untuk semua pihak?"
                )
            );
        }

        content.append(
            makeAction(
                url("hide"),
                "Hapus untuk saya",
                "Hapus pesan hanya dari tampilan kamu?",
                true
            )
        );

        menu.append(content);
        row.append(menu);
    }

    // Membuat bubble chat.
    function createBubble(message) {
        const row = document.createElement("article");

        row.className = "fb-chat-bubble-row" +
            (message.is_mine ? " mine" : "");

        row.dataset.id = String(message.id);

        const bubble = document.createElement("div");
        bubble.className = "fb-chat-bubble";

        const title = document.createElement("strong");
        title.textContent = message.is_mine
            ? "Kamu"
            : "Admin FindBack";

        bubble.append(title);

        if (message.message || message.recalled_at) {
            const text = document.createElement("p");

            text.className = message.recalled_at
                ? "fb-chat-recalled"
                : "fb-chat-text";

            text.textContent = message.recalled_at
                ? "Pesan telah ditarik"
                : message.message || "";

            bubble.append(text);
        }

        if (message.attachment_url && !message.recalled_at) {
            const link = document.createElement("a");
            link.href = message.attachment_url;
            link.target = "_blank";
            link.rel = "noopener noreferrer";

            const image = document.createElement("img");
            image.src = message.attachment_url;
            image.alt = "Lampiran gambar";

            link.append(image);
            bubble.append(link);
        }

        const footer = document.createElement("footer");

        footer.textContent =
            (message.created_at || "") +
            (
                message.edited_at && !message.recalled_at
                    ? " · Diedit"
                    : ""
            );

        bubble.append(footer);
        row.append(bubble);

        if (message.is_mine) {
            buildMenu(row, message);
        }

        return row;
    }

    // Memperbarui bubble tanpa reload halaman.
    function updateBubble(row, message) {
        if (row.querySelector("[data-message-menu]") === activeMenu) {
            activeMenu = null;
        }

        const updated = createBubble(message);
        row.replaceWith(updated);

        return updated;
    }

    // Menjalankan Tarik / Hapus lewat AJAX.
    async function performAction(targetForm) {
        if (busy) return;

        busy = true;

        const button = $("fb-confirm-submit");
        if (button) button.disabled = true;

        const row = targetForm.closest(".fb-chat-bubble-row");

        try {
            const result = await sendAjax(
                targetForm.action,
                new FormData(targetForm)
            );

            if (result.hidden) {
                row?.remove();
            } else if (result.message && row) {
                updateBubble(row, result.message);
            }

            closeDialogs();
            showError("");
        } catch (error) {
            showError(error.message);
            closeDialogs();
        } finally {
            busy = false;
            if (button) button.disabled = false;
        }
    }

    // Klik kanan pada pesan sendiri.
    stream.addEventListener("contextmenu", event => {
        const row = event.target
            .closest(".fb-chat-bubble")
            ?.closest(".fb-chat-bubble-row.mine");

        if (!row) return;

        event.preventDefault();
        openMenu(row);
    });

    // Membuka dialog Edit atau Konfirmasi.
    stream.addEventListener("click", event => {
        const edit = event.target.closest(".fb-chat-edit-btn");

        if (edit) {
            editingButton = edit;

            if (editOverlay && editText) {
                editText.value = edit.dataset.text || "";
                editOverlay.hidden = false;
                closeMenu();
                editText.focus({ preventScroll: true });
            }

            return;
        }

        const button = event.target.closest(
            'form[data-confirm] button[type="submit"]'
        );

        if (button) {
            event.preventDefault();

            actionForm = button.form;
            closeMenu();

            if (confirmOverlay) {
                $("fb-confirm-message").textContent =
                    actionForm.dataset.confirm ||
                    "Lanjutkan tindakan ini?";

                confirmOverlay.hidden = false;
            }
        }
    });

    // Mencegah formulir aksi melakukan reload.
    stream.addEventListener("submit", event => {
        if (event.target.matches("form[data-confirm]")) {
            event.preventDefault();
        }
    });

    $("fb-confirm-submit")?.addEventListener("click", () => {
        if (actionForm) {
            performAction(actionForm);
        }
    });

    // Simpan perubahan Edit melalui AJAX.
    $("fb-edit-save")?.addEventListener("click", async () => {
        if (busy || !editingButton) return;

        const value = editText.value.trim();

        if (!value || value.length > 3000) {
            if (editHelp) {
                editHelp.textContent =
                    "Isi pesan wajib diisi (maksimal 3000 karakter).";
            }
            return;
        }

        busy = true;

        const save = $("fb-edit-save");
        save.disabled = true;

        const row = editingButton.closest(".fb-chat-bubble-row");
        const url = editingButton.dataset.update;

        const body = new FormData();
        body.append("_method", "PATCH");
        body.append("message", value);

        try {
            const result = await sendAjax(url, body);

            if (row && result.message) {
                updateBubble(row, result.message);
            }

            closeDialogs();
            showError("");
        } catch (error) {
            if (editHelp) {
                editHelp.textContent = error.message;
            }
        } finally {
            busy = false;
            save.disabled = false;
        }
    });

    // Menutup dialog.
    [
        "fb-confirm-cancel",
        "fb-confirm-close",
        "fb-edit-cancel",
        "fb-edit-close"
    ].forEach(id => {
        $(id)?.addEventListener("click", closeDialogs);
    });

    [confirmOverlay, editOverlay].forEach(element => {
        element?.addEventListener("click", event => {
            if (event.target === element) {
                closeDialogs();
            }
        });
    });

    document.addEventListener("pointerdown", event => {
        if (activeMenu && !activeMenu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;

        if (!editOverlay?.hidden || !confirmOverlay?.hidden) {
            closeDialogs();
        } else {
            closeMenu();
        }
    });

    stream.addEventListener("scroll", closeMenu, {
        passive: true
    });

    // Tekan lama pada layar sentuh.
    let touchRow = null;
    let startX = 0;
    let startY = 0;

    stream.addEventListener("pointerdown", event => {
        if (event.pointerType === "mouse") return;

        touchRow = event.target
            .closest(".fb-chat-bubble")
            ?.closest(".fb-chat-bubble-row.mine");

        if (!touchRow) return;

        startX = event.clientX;
        startY = event.clientY;

        clearTimeout(timer);

        timer = setTimeout(() => {
            openMenu(touchRow);
            timer = null;
        }, 550);
    });

    stream.addEventListener("pointermove", event => {
        if (
            timer &&
            (
                Math.abs(event.clientX - startX) > 12 ||
                Math.abs(event.clientY - startY) > 12
            )
        ) {
            clearTimeout(timer);
        }
    }, { passive: true });

    ["pointerup", "pointercancel"].forEach(type => {
        stream.addEventListener(type, () => {
            clearTimeout(timer);
        });
    });

    // Menyesuaikan tinggi kolom pesan.
    const adjustInput = () => {
        if (!input) return;

        input.style.height = "auto";
        input.style.height =
            Math.min(input.scrollHeight, 140) + "px";
    };

    input?.addEventListener("input", adjustInput);
    adjustInput();

    input?.addEventListener("keydown", event => {
        if (
            event.key === "Enter" &&
            !event.shiftKey &&
            !event.isComposing &&
            !event.repeat
        ) {
            event.preventDefault();

            if (form && !busy) {
                form.requestSubmit();
            }
        }
    });

    file?.addEventListener("change", () => {
        if (!fileLabel) return;

        fileLabel.textContent = file.files?.[0]
            ? `Lampiran: ${file.files[0].name}`
            : "JPG, PNG, atau WebP · Maks. 4 MB";
    });

    // Kirim pesan tanpa reload.
    form?.addEventListener("submit", async event => {
        event.preventDefault();

        if (busy) return;

        const text = input.value.trim();
        const attachment = file?.files?.[0];

        if (!text && !attachment) {
            showError("Tulis pesan atau lampirkan gambar dahulu.");
            return;
        }

        if (
            attachment &&
            (
                attachment.size > 4 * 1024 * 1024 ||
                ![
                    "image/jpeg",
                    "image/png",
                    "image/webp"
                ].includes(attachment.type)
            )
        ) {
            showError("Hanya JPG, PNG, atau WebP maksimal 4 MB.");
            return;
        }

        busy = true;
        showError("");

        const body = new FormData(form);
        body.set("message", text);

        // Tampilkan bubble segera saat tombol Kirim diklik.
        const temporaryUrl = attachment
            ? URL.createObjectURL(attachment)
            : null;

        const pendingMessage = {
            id: `pending-${Date.now()}`,
            is_mine: true,
            message: text,
            attachment_url: temporaryUrl,
            created_at: "Mengirim..."
        };

        const pendingRow = createBubble(pendingMessage);
        pendingRow.classList.add("is-pending");
        pendingRow.querySelector("[data-message-menu]")?.remove();

        stream.append(pendingRow);
        scrollBottom();

        if (sendButton) sendButton.disabled = true;

        try {
            const result = await sendAjax(
                form.action,
                body
            );

            updateBubble(pendingRow, result.message);

            form.reset();
            adjustInput();

            if (fileLabel) {
                fileLabel.textContent =
                    "JPG, PNG, atau WebP · Maks. 4 MB";
            }

            scrollBottom();
        } catch (error) {
            pendingRow.classList.remove("is-pending");
            pendingRow.classList.add("is-failed");

            pendingRow.querySelector("footer").textContent =
                "Gagal dikirim";

            showError(error.message);
        } finally {
            if (temporaryUrl) {
                URL.revokeObjectURL(temporaryUrl);
            }

            busy = false;

            if (sendButton) {
                sendButton.disabled = false;
            }
        }
    });

    // Memeriksa pesan terbaru dari Admin.
    let polling = false;

    async function pollMessages() {
        if (
          !stream.dataset.poll ||
document.hidden ||
polling ||
busy ||
!scrollReady ||
restoringScroll
        ) return;

        polling = true;

        try {
            const url = new URL(
                stream.dataset.poll,
                location.href
            );

            url.searchParams.set("after", "0");

            const response = await fetch(url, {
                headers: {
                    Accept: "application/json"
                },
                credentials: "same-origin"
            });

            if (!response.ok) return;

            const data = await response.json();

            const nearBottom =
                stream.scrollHeight -
                stream.scrollTop -
                stream.clientHeight < 100;

            let hasNewMessages = false;
            for (const message of data.messages || []) {
                const row = Array.from(
                    stream.querySelectorAll(
                        ".fb-chat-bubble-row[data-id]"
                    )
                ).find(
                    item =>
                        item.dataset.id === String(message.id)
                );

                
if (!row) {
    stream.append(createBubble(message));
    hasNewMessages = true;
    continue;
}


                if (row.classList.contains("is-pending")) {
                    continue;
                }

                const currentText = row.querySelector(
                    ".fb-chat-text, .fb-chat-recalled"
                )?.textContent || "";

                const newText = message.recalled_at
                    ? "Pesan telah ditarik"
                    : (message.message || "");

                if (currentText !== newText) {
                    updateBubble(row, message);
                }
            }

         
// Gulir hanya jika ada pesan baru dan
// pengguna sebelumnya berada dekat bagian bawah.
if (
    hasNewMessages &&
    nearBottom &&
    scrollReady &&
    !restoringScroll
) {
    scrollBottom();
}

}
         catch (error) {
            console.warn("Chat polling:", error);
        } finally {
            polling = false;
        }
    }

    setInterval(pollMessages, 7000);

    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) {
            pollMessages();
        }
    });
});
