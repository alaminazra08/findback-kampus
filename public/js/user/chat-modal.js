
document.addEventListener("DOMContentLoaded", () => {
    const $ = id => document.getElementById(id);

    const overlay = $("fb-chat-overlay");
    if (!overlay) return;

    const stream = $("fb-modal-stream");
    const form = $("fb-chat-form");
    const input = $("fb-chat-textarea");
    const file = $("fb-chat-file");
    const errorBox = $("fb-chat-ajax-error");

    const editOverlay = $("fb-edit-overlay");
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
    let editingButton = null;
    let busy = false;

    const originalOverflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";

    window.addEventListener("pagehide", () => {
        document.body.style.overflow = originalOverflow;
    });

    // Dialog selalu berada di atas modal utama.
    if (editOverlay) document.body.append(editOverlay);

/* ==========================================
   POSISI SCROLL BERDASARKAN PESAN
   ========================================== */

const claimId = new URLSearchParams(location.search)
    .get("claim_id") || "default";

const chatScrollKey = `findback:chat:position:${claimId}`;

let scrollReady = false;
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
    if (!stream || !scrollReady) {
        return;
    }

    const bottomDistance =
        stream.scrollHeight - stream.scrollTop - stream.clientHeight;

    const position = {
        bottom: bottomDistance <= 4,
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
}

// Simpan posisi ketika pengguna menggulir chat.
stream.addEventListener("scroll", () => {
    if (!scrollReady) return;

    clearTimeout(scrollSaveTimer);

    scrollSaveTimer = setTimeout(() => {
        saveChatScroll();
    }, 120);
}, { passive: true });

// Pulihkan sekali ketika markup dan stylesheet sudah siap.
restoreChatScroll();

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

        activeMenu = null;
    }

    function closeDialogs() {
        if (editOverlay) editOverlay.hidden = true;

        editingButton = null;
    }

    function openMenu(row, point) {
        if (!row) return;

        const menu = row.querySelector("[data-message-menu]");
        if (!menu) return;

        closeMenu();

        menu.hidden = false;
        const bounds = stream.getBoundingClientRect();
        const rowBounds = row.getBoundingClientRect();
        const bubble = row.querySelector(".fb-chat-bubble").getBoundingClientRect();
        const x = point?.x || bubble.left;
        const y = point?.y || bubble.top;
        const left = Math.max(bounds.left + 8, Math.min(x, bounds.right - menu.offsetWidth - 8));
        const top = Math.max(bounds.top + 8, Math.min(y, bounds.bottom - menu.offsetHeight - 8));
        menu.style.left = `${left - rowBounds.left}px`;
        menu.style.top = `${top - rowBounds.top}px`;
        activeMenu = menu;
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
    // Membuat menu tindakan pesan.
    function buildMenu(row, message) {
        row.querySelector("[data-message-menu]")?.remove();

        const menu = document.createElement("div");
        menu.className = "fb-chat-message-menu";
        menu.dataset.messageMenu = "";
        menu.hidden = true;

        const content = document.createElement("div");
        content.className = "fb-chat-menu-content";
        content.setAttribute("role", "group");
        content.setAttribute("aria-label", "Tindakan pesan");

        const select = document.createElement("button");
        select.type = "button";
        select.dataset.chatSelect = "";
        select.textContent = "Pilih pesan";
        content.append(select);

        const url = type => {
            return stream.dataset[`${type}Url`]
                ?.replace("__MESSAGE_ID__", String(message.id));
        };

        if (message.is_mine && !message.recalled_at && message.message) {
            const edit = document.createElement("button");

            edit.type = "button";
            edit.className = "fb-chat-edit-btn";
            edit.textContent = "✎ Edit pesan";
            edit.dataset.text = message.message;
            edit.dataset.update = url("update");

            content.append(edit);
        }

        menu.append(content);
        row.append(menu);
    }

    // Membuat bubble chat.
    function createBubble(message) {
        const row = document.createElement("article");

        row.className = "fb-chat-bubble-row" +
            (message.is_mine ? " mine" : "") + (message.recalled_at ? " is-recalled" : "");

        row.dataset.id = String(message.id);
        if (message.is_mine && !message.recalled_at && message.recall_until) {
            row.dataset.recallUntil = message.recall_until;
        }
        if (message.sent_at) row.dataset.sentAt = message.sent_at;

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
            window.findBackChat.appendAttachment(bubble, message.attachment_url, message.attachment_type, message.attachment_photo_ratio);
        }

        const footer = document.createElement("footer");

        footer.textContent =
            (message.created_at || "") +
            (
                message.edited_at && !message.recalled_at
                    ? " · Diedit"
                    : ""
            );

        if (Number.isSafeInteger(Number(message.id))) {
            const options = document.createElement("button");
            options.type = "button";
            options.className = "fb-chat-options";
            options.dataset.userChatOptions = "";
            options.setAttribute("aria-label", "Opsi pesan");
            options.textContent = "⌄";
            bubble.append(options);
            buildMenu(row, message);
        }

        bubble.append(footer);
        row.prepend(bubble);

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

    stream.addEventListener("fb:chat-selected", closeMenu);
    stream.addEventListener("fb:chat-menu", (event) => {
        openMenu(event.target, event.detail);
        activeMenu?.querySelector("[data-chat-select]")?.focus({ preventScroll: true });
    });
    stream.addEventListener("fb:chat-recalled", (event) => updateBubble(event.target, event.detail));

    // Membuka dialog edit.
    stream.addEventListener("click", event => {
        const options = event.target.closest("[data-user-chat-options]");
        if (options) {
            openMenu(options.closest(".fb-chat-bubble-row"));
            return;
        }
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
        "fb-edit-cancel",
        "fb-edit-close"
    ].forEach(id => {
        $(id)?.addEventListener("click", closeDialogs);
    });

    editOverlay?.addEventListener("click", event => {
        if (event.target === editOverlay) closeDialogs();
    });

    document.addEventListener("pointerdown", event => {
        if (activeMenu && !activeMenu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;

        if (editOverlay && !editOverlay.hidden) {
            closeDialogs();
        } else {
            closeMenu();
        }
    });

    window.addEventListener("resize", closeMenu);
    window.visualViewport?.addEventListener("resize", closeMenu);
    stream.addEventListener("scroll", closeMenu, {
        passive: true
    });

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

        if (attachment && !window.findBackChat.validAttachment(attachment)) {
            showError("Gunakan JPG, PNG, WebP, MP4, atau WebM maksimal 4 MB.");
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
            attachment_type: attachment?.type,
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

            const confirmedRow = createBubble(result.message);
            const confirmedPhoto = confirmedRow.querySelector(".fb-chat-bubble > a > img");
            if (confirmedPhoto && temporaryUrl) {
                // Keep the local preview visible while its saved copy loads.
                let timer;
                await Promise.race([
                    confirmedPhoto.decode().catch(() => {}),
                    new Promise(resolve => { timer = setTimeout(resolve, 4000); }),
                ]);
                clearTimeout(timer);
            }
            const existing = stream.querySelector(`[data-id="${Number(result.message.id)}"]`);
            if (existing) pendingRow.remove();
            else pendingRow.replaceWith(confirmedRow);

            form.reset();


        } catch (error) {
            pendingRow.remove();

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
    let receivedThrough = [...stream.querySelectorAll(".fb-chat-bubble-row[data-id]")]
        .reduce((latest, row) => Math.max(latest, Number(row.dataset.id) || 0), 0);

    async function pollMessages() {
        if (!stream.dataset.poll || document.hidden || polling || busy) return;

        polling = true;

        try {
            const url = new URL(
                stream.dataset.poll,
                location.href
            );

            url.searchParams.set("recent", "1");
            url.searchParams.set("through", receivedThrough);
            let recalledIds = [];

            async function fetchMessages() {
                const response = await fetch(url, {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                });
                if (!response.ok) return null;
                const data = await response.json();
                recalledIds = data.recalled_ids || [];
                return data.messages;
            }

            let messages = await fetchMessages();
            if (!Array.isArray(messages)) return;
            if (messages.length === 100 && Number(messages[0].id) > receivedThrough) {
                url.searchParams.delete("recent");
                url.searchParams.set("after", receivedThrough);
                messages = await fetchMessages();
                if (!Array.isArray(messages)) return;
            }
            if (busy || !stream.isConnected) return;
            const existingRows = new Map([...stream.querySelectorAll(".fb-chat-bubble-row[data-id]")]
                .map(row => [Number(row.dataset.id), row]).filter(([id]) => Number.isSafeInteger(id)));
            const previousCursor = receivedThrough;

            const followLatest = !activeMenu
                && stream.scrollHeight - stream.scrollTop - stream.clientHeight <= 4;

            let hasNewMessages = false;
            for (const message of messages) {
                const id = Number(message.id);
                receivedThrough = Math.max(receivedThrough, id);
                const row = existingRows.get(id);
                if (!row) {
                    if (id <= previousCursor) continue;
                    const nextRow = [...existingRows].find(([existingId]) => existingId > id)?.[1];
                    stream.insertBefore(createBubble(message), nextRow || null);
                    hasNewMessages = true;
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

            for (const id of recalledIds) {
                const row = existingRows.get(Number(id));
                if (!row?.isConnected || row.classList.contains("is-recalled")) continue;
                const bubble = row.querySelector(".fb-chat-bubble");
                const createdAt = bubble.querySelector("footer time")?.textContent.trim()
                    || bubble.querySelector("footer")?.textContent.split(" · Diedit")[0].trim();
                updateBubble(row, {
                    id,
                    created_at: createdAt,
                    sent_at: row.dataset.sentAt,
                    is_mine: row.classList.contains("mine"),
                    recalled_at: true
                });
            }
            if (hasNewMessages && followLatest) scrollBottom();
        } catch (error) {
            console.warn("Chat polling:", error);
        } finally {
            polling = false;
        }
    }

    setInterval(pollMessages, 1500);

    document.addEventListener("visibilitychange", () => {
        if (!document.hidden) {
            pollMessages();
        }
    });
});
