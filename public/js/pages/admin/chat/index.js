document.addEventListener("DOMContentLoaded", () => {
    const stream = document.getElementById("fb-inbox-messages");
    if (!stream) return;
    stream.scrollTop = stream.scrollHeight;
    if (!stream.dataset.poll) return;
    const status = document.createElement("p");
    status.className = "fb-inbox-note";
    status.setAttribute("role", "status");
    stream.after(status);
    let after = Number(stream.dataset.after) || 0;
    async function poll() {
        try {
            if (document.hidden) return;
            const url = new URL(stream.dataset.poll, location.origin);
            url.searchParams.set("after", String(after));
            const response = await fetch(url, { headers: { Accept: "application/json" }, credentials: "same-origin" });
            if (!response.ok) throw new Error("poll");
            const data = await response.json();
            window.findBackChat.markAdminRecalls(stream, data.recalled_ids);
            const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 80;
            for (const message of data.messages || []) {
                if (Number(message.id) <= after) continue;
                stream.querySelector(".fb-inbox-empty")?.remove();
                const bubble = document.createElement("div");
                bubble.className = "fb-inbox-bubble" + (message.is_mine ? " mine" : "");
                bubble.dataset.messageId = message.id;
                if (message.sent_at) bubble.dataset.sentAt = message.sent_at;
                if (message.recalled) bubble.dataset.recalled = "true";
                const name = document.createElement("strong");
                name.textContent = message.sender_name;
                bubble.append(name);
                if (message.message) {
                    const body = document.createElement("p");
                    body.textContent = message.message;
                    bubble.append(body);
                }
                if (message.attachment_url) {
                    window.findBackChat.appendAttachment(bubble, message.attachment_url, message.attachment_type, message.attachment_photo_ratio);
                }
                window.findBackChat.addAdminOptions(bubble, message);
                const time = document.createElement("small");
                time.textContent = message.created_at;
                bubble.append(time); stream.append(bubble);
                after = Number(message.id);
            }
            if (nearBottom && data.messages?.length) stream.scrollTop = stream.scrollHeight;
            status.textContent = "";
        } catch {
            status.textContent = "Pembaruan pesan gagal. Mencoba kembali otomatis.";
        } finally {
            window.setTimeout(poll, 1500);
        }
    }
    window.setTimeout(poll, 0);
});
