document.addEventListener("DOMContentLoaded", function () {
    const dialog = document.getElementById("fcp-dialog");
    const openers = document.querySelectorAll("[data-fcp-open]");
    const close = dialog?.querySelector("[data-fcp-close]");
    openers.forEach((btn) =>
        btn.addEventListener("click", () => {
            dialog?.showModal();
        }),
    );
    close?.addEventListener("click", () => dialog?.close());
    dialog?.addEventListener("click", (event) => {
        if (event.target === dialog) dialog.close();
    });
    if (new URLSearchParams(window.location.search).get("chat") === "1") dialog?.showModal();
    const stream = document.getElementById("fcp-messages");
    if (!stream) return;
    stream.scrollTop = stream.scrollHeight;
    const url = stream.dataset.url;
    if (!url) return;
    let after = 0,
        busy = false;
    stream.querySelectorAll("[data-message-id]").forEach((el) => {
        after = Math.max(after, Number(el.dataset.messageId) || 0);
    });
    function addMessage(msg) {
        const article = document.createElement("article");
        article.className = "fcp-message " + (msg.is_mine ? "mine" : "theirs");
        article.dataset.messageId = msg.id;
        const bubble = document.createElement("div");
        bubble.className = "fcp-bubble";
        const sender = document.createElement("strong");
        sender.textContent = msg.is_mine ? "Admin FindBack" : msg.sender_name || "Pengguna";
        bubble.appendChild(sender);
        if (msg.message) {
            const p = document.createElement("p");
            p.textContent = msg.message;
            bubble.appendChild(p);
        }
        if (msg.attachment_url) {
            const a = document.createElement("a");
            a.href = msg.attachment_url;
            a.target = "_blank";
            a.rel = "noopener noreferrer";
            const img = document.createElement("img");
            img.src = msg.attachment_url;
            img.alt = "Lampiran percakapan";
            img.loading = "lazy";
            a.appendChild(img);
            bubble.appendChild(a);
        }
        const time = document.createElement("small");
        time.textContent = msg.created_at + " WIB";
        bubble.appendChild(time);
        article.appendChild(bubble);
        stream.appendChild(article);
    }
    async function refresh() {
        if (busy || document.hidden) return;
        busy = true;
        try {
            const res = await fetch(url + (url.includes("?") ? "&" : "?") + "after=" + after, {
                headers: { Accept: "application/json" },
                cache: "no-store",
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!Array.isArray(data.messages)) return;
            const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 150;
            data.messages.forEach((msg) => {
                const id = Number(msg.id);
                if (!id || id <= after) return;
                document.getElementById("fcp-empty")?.remove();
                addMessage(msg);
                after = id;
            });
            if (nearBottom) stream.scrollTop = stream.scrollHeight;
        } catch (e) {
            /* jaringan sementara tidak tersedia */
        } finally {
            busy = false;
        }
    }
    window.setInterval(refresh, 5000);
});
