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
            const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 80;
            for (const message of data.messages || []) {
                if (Number(message.id) <= after) continue;
                stream.querySelector(".fb-inbox-empty")?.remove();
                const bubble = document.createElement("div");
                bubble.className = "fb-inbox-bubble" + (message.is_mine ? " mine" : "");
                const name = document.createElement("strong");
                name.textContent = message.sender_name;
                bubble.append(name);
                if (message.message) {
                    const body = document.createElement("p");
                    body.textContent = message.message;
                    bubble.append(body);
                }
                if (message.attachment_url) {
                    const url = new URL(message.attachment_url, location.origin);
                    if (["https:", "http:"].includes(url.protocol)) {
                        const link = document.createElement("a");
                        link.href = url.href; link.target = "_blank"; link.rel = "noopener noreferrer";
                        const image = document.createElement("img");
                        image.src = url.href; image.alt = "Lampiran pesan";
                        link.append(image); bubble.append(link);
                    }
                }
                const time = document.createElement("small");
                time.textContent = message.created_at;
                bubble.append(time); stream.append(bubble);
                after = Number(message.id);
            }
            if (nearBottom) stream.scrollTop = stream.scrollHeight;
            status.textContent = "Pesan diperbarui otomatis.";
        } catch {
            status.textContent = "Pembaruan pesan gagal. Mencoba kembali otomatis.";
        } finally {
            window.setTimeout(poll, 5000);
        }
    }
    window.setTimeout(poll, 3000);
});
