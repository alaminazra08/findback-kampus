(() => {
    function mount(signal) {
        const dialog = document.getElementById("fcp-dialog");
        if (!dialog) return;
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
        let after = 0;
        let busy = false;
        let queued = false;
        let acknowledge = true;
        stream.querySelectorAll("[data-message-id]").forEach((el) => {
            after = Math.max(after, Number(el.dataset.messageId) || 0);
        });
        function addMessage(msg) {
            const article = document.createElement("article");
            article.className = "fcp-message " + (msg.is_mine ? "mine" : "theirs");
            article.dataset.messageId = msg.id;
            if (msg.sent_at) article.dataset.sentAt = msg.sent_at;
            if (msg.recalled) article.dataset.recalled = "true";
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
                window.findBackChat.appendAttachment(bubble, msg.attachment_url, msg.attachment_type, msg.attachment_photo_ratio);
            }
            window.findBackChat.addAdminOptions(bubble, msg);
            const time = document.createElement("small");
            time.textContent = msg.created_at + " WIB";
            bubble.appendChild(time);
            article.appendChild(bubble);
            stream.appendChild(article);
        }
        async function refresh() {
            if (signal.aborted || document.hidden || !dialog.open) return;
            if (busy) {
                queued = true;
                return;
            }
            busy = true;
            try {
                const requestUrl = new URL(url, location.href);
                requestUrl.searchParams.set("after", after);
                if (acknowledge) requestUrl.searchParams.set("seen", "1");
                const res = await fetch(requestUrl, {
                    headers: { Accept: "application/json" },
                    cache: "no-store",
                    signal,
                });
                if (!res.ok) return;
                const data = await res.json();
                if (signal.aborted || !dialog.open || !Array.isArray(data.messages)) return;
                window.findBackChat.markAdminRecalls(stream, data.recalled_ids);
                const readChanged = acknowledge || data.messages.some((msg) => !msg.is_mine);
                acknowledge = false;
                const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 150;
                data.messages.forEach((msg) => {
                    const id = Number(msg.id);
                    if (!id || id <= after) return;
                    document.getElementById("fcp-empty")?.remove();
                    addMessage(msg);
                    after = id;
                });
                if (data.messages.length && nearBottom) stream.scrollTop = stream.scrollHeight;
                if (readChanged) document.dispatchEvent(new Event("fb:claim-chat-read"));
                if (data.messages.length === 100) queued = true;
            } catch (e) {
                /* jaringan sementara tidak tersedia */
            } finally {
                busy = false;
                if (queued) {
                    queued = false;
                    refresh();
                }
            }
        }
        const form = dialog.querySelector(".fcp-compose");
        const errorBox = dialog.querySelector(".fcp-send-error");
        const submit = form?.querySelector('[type="submit"]');
        form?.addEventListener("submit", async (event) => {
            event.preventDefault();
            if (submit.disabled) return;
            const body = new FormData(form);
            submit.disabled = true;
            errorBox.hidden = true;
            try {
                const response = await fetch(form.action, {
                    method: "POST",
                    headers: { Accept: "application/json" },
                    body,
                    signal,
                });
                if (response.status !== 204) {
                    const data = await response.json().catch(() => null);
                    throw new Error(data?.message || "Pesan gagal dikirim. Coba lagi.");
                }
                form.reset();
                refresh();
            } catch (error) {
                if (signal.aborted) return;
                errorBox.textContent = error.message || "Pesan gagal dikirim. Coba lagi.";
                errorBox.hidden = false;
            } finally {
                submit.disabled = false;
            }
        }, { signal });

        let timer;
        function stopPolling() {
            window.clearInterval(timer);
            timer = undefined;
        }
        signal.addEventListener("abort", stopPolling, { once: true });
        function startPolling() {
            if (timer !== undefined) return;
            acknowledge = true;
            stream.scrollTop = stream.scrollHeight;
            refresh();
            timer = window.setInterval(refresh, 1500);
        }
        openers.forEach((button) => button.addEventListener("click", startPolling));
        dialog.addEventListener("close", () => {
            if (!dialog.open) stopPolling();
        });
        document.addEventListener("visibilitychange", () => {
            if (!document.hidden) refresh();
        }, { signal });
        if (dialog.open) startPolling();
    }

    let scope;
    function render() {
        scope?.abort();
        scope = new AbortController();
        mount(scope.signal);
    }
    document.addEventListener('fb:admin-page', render);
    render();
})();
