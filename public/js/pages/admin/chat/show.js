document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("chat-messages");

    if (!container) return;

    let afterId = 0;
    let polling = false;

    function updateLastId() {
        container.querySelectorAll("[data-message-id]").forEach(function (element) {
            afterId = Math.max(afterId, Number(element.dataset.messageId) || 0);
        });
    }

    function renderMessage(message) {
        const article = document.createElement("article");

        article.className = "chat-message " + (message.is_mine ? "mine" : "theirs");

        article.dataset.messageId = message.id;

        const bubble = document.createElement("div");
        bubble.className = "chat-bubble";

        const sender = document.createElement("span");
        sender.className = "chat-sender";
        sender.textContent = message.is_mine ? "Admin" : message.sender_name || "Pengguna";

        bubble.appendChild(sender);

        if (message.attachment_url) {
            const link = document.createElement("a");
            link.href = message.attachment_url;
            link.target = "_blank";
            link.rel = "noopener noreferrer";

            const image = document.createElement("img");
            image.className = "chat-attachment";
            image.src = message.attachment_url;
            image.alt = "Lampiran pesan";
            image.loading = "lazy";

            link.appendChild(image);
            bubble.appendChild(link);
        }

        if (message.message) {
            const paragraph = document.createElement("p");
            paragraph.textContent = message.message;
            bubble.appendChild(paragraph);
        }

        const time = document.createElement("small");
        time.textContent = message.created_at;
        bubble.appendChild(time);

        article.appendChild(bubble);

        return article;
    }

    async function refreshMessages() {
        if (polling || document.hidden || !container.dataset.messagesUrl) return;

        polling = true;

        try {
            const response = await fetch(
                container.dataset.messagesUrl +
                    (container.dataset.messagesUrl.includes("?") ? "&" : "?") +
                    "after=" +
                    afterId,
                {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    cache: "no-store",
                },
            );

            if (!response.ok) return;

            const data = await response.json();

            if (!Array.isArray(data.messages) || !data.messages.length) {
                return;
            }

            const isNearBottom =
                container.scrollHeight - container.scrollTop - container.clientHeight < 150;

            const empty = container.querySelector(".chat-empty");
            if (empty) empty.remove();

            data.messages.forEach(function (message) {
                const id = Number(message.id);

                if (!id || id <= afterId) return;

                container.appendChild(renderMessage(message));
                afterId = id;
            });

            if (isNearBottom) {
                container.scrollTop = container.scrollHeight;
            }
        } catch (error) {
            console.error("FindBack Admin Chat:", error);
        } finally {
            polling = false;
        }
    }

    updateLastId();
    container.scrollTop = container.scrollHeight;

    refreshMessages();
    setInterval(refreshMessages, 4000);
});
