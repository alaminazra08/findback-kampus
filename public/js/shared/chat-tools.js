(() => {
    const previews = new WeakMap();

    function clearPreview(form) {
        const preview = previews.get(form);
        if (!preview) return;
        if (preview.url) URL.revokeObjectURL(preview.url);
        preview.element.remove();
        previews.delete(form);
    }

    function openDialog(parent, title) {
        const panel = document.createElement("dialog");
        panel.className = "fb-chat-tool-dialog";
        panel.setAttribute("aria-label", title);
        const close = document.createElement("button");
        close.type = "button";
        close.className = "fb-chat-tool-close";
        close.setAttribute("aria-label", "Tutup");
        close.textContent = "×";
        close.addEventListener("click", () => panel.close());
        panel.addEventListener("close", () => panel.remove(), { once: true });
        panel.append(close);
        (parent.closest("dialog[open]") || document.body).append(panel);
        return panel;
    }

    function showPhoto(target, url, video = false) {
        const panel = openDialog(target, video ? "Video percakapan" : "Foto percakapan");
        panel.classList.add("fb-chat-media-viewer");
        const close = panel.querySelector(".fb-chat-tool-close");
        close.replaceChildren(chatIcon("M6 6l12 12M18 6 6 18"));
        const media = mediaElement(url, video, true);
        media.draggable = false;
        panel.addEventListener("copy", (event) => event.preventDefault());
        media.addEventListener("contextmenu", (event) => event.preventDefault());
        panel.append(media);
        panel.showModal();
    }

    const attachmentTypes = new Set(["image/jpeg", "image/png", "image/webp", "video/mp4", "video/webm"]);
    const attachmentLimit = 4 * 1024 * 1024;
    const validAttachment = (file) => attachmentTypes.has(file.type) && file.size <= attachmentLimit;
    const isVideo = (url, type) => type?.startsWith("video/") || /\.(mp4|webm)(?:$|[?#])/i.test(url);

    function mediaElement(url, video, controls = false) {
        const media = document.createElement(video ? "video" : "img");
        if (video) {
            media.playsInline = true;
            media.controls = controls;
            media.muted = !controls;
            media.preload = "metadata";
        } else {
            media.alt = "Lampiran foto";
        }
        media.src = url;
        return media;
    }

    function appendAttachment(bubble, url, type, ratio) {
        if (Number.isFinite(ratio) && ratio > 0) {
            bubble.style.setProperty("--chat-photo-ratio", ratio);
        }
        const link = document.createElement("a");
        link.href = url;
        link.target = "_blank";
        link.rel = "noopener noreferrer";
        const video = isVideo(url, type);
        link.append(mediaElement(url, video));
        if (video) {
            link.setAttribute("aria-label", "Putar video lampiran");
            const icon = document.createElement("span");
            icon.className = "fb-chat-video-play";
            icon.setAttribute("aria-hidden", "true");
            icon.textContent = "▶";
            link.append(icon);
        }
        bubble.append(link);
    }

    function chatIcon(pathData) {
        const ns = "http://www.w3.org/2000/svg";
        const svg = document.createElementNS(ns, "svg");
        const path = document.createElementNS(ns, "path");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("aria-hidden", "true");
        path.setAttribute("d", pathData);
        svg.append(path);
        return svg;
    }

    function labelCameraAction(button, label, pathData) {
        button.replaceChildren(chatIcon(pathData), document.createTextNode(label));
        button.setAttribute("aria-label", label);
    }

    function attachmentOption(label, description, kind, pathData) {
        const button = createButton("", label);
        button.className = `fb-chat-attachment-option is-${kind}`;
        const icon = document.createElement("span");
        icon.className = "fb-chat-attachment-icon";
        icon.setAttribute("aria-hidden", "true");
        icon.append(chatIcon(pathData));

        const copy = document.createElement("span");
        const name = document.createElement("strong");
        const detail = document.createElement("small");
        name.textContent = label;
        detail.textContent = description;
        detail.id = `fb-chat-attachment-${kind}`;
        button.setAttribute("aria-describedby", detail.id);
        copy.append(name, detail);
        button.append(icon, copy);
        return button;
    }

    function chooseAttachment(trigger) {
        const form = trigger.form;
        const panel = openDialog(trigger, "Tambah lampiran");
        panel.classList.add("fb-chat-attachment-menu");
        const gallery = attachmentOption("Foto & Video", "Pilih dari perangkat", "gallery",
            "M4 4h16v16H4zM4 16l5-5 4 4 3-3 4 4M14 8h.01");
        const camera = attachmentOption("Kamera", "Ambil foto atau rekam", "camera",
            "M4 7h4l2-3h4l2 3h4v13H4zM15.5 13.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 1 1 7 0");
        panel.append(gallery, camera);
        gallery.addEventListener("click", () => {
            panel.close();
            form.querySelector('input[name="attachment"]').click();
        });
        camera.addEventListener("click", () => {
            panel.close();
            openCamera(form, trigger);
        });
        panel.addEventListener("click", (event) => {
            if (event.target === panel) panel.close();
        });
        panel.showModal();
        const anchor = trigger.getBoundingClientRect();
        panel.style.setProperty("--chat-menu-left", `${Math.max(8, Math.min(anchor.left, innerWidth - panel.offsetWidth - 8))}px`);
        panel.style.setProperty("--chat-menu-bottom", `${Math.max(8, innerHeight - anchor.top + 6)}px`);
    }

    function openCamera(form, trigger) {
        const panel = openDialog(trigger, "Kamera");
        panel.classList.add("fb-chat-camera");
        const heading = document.createElement("h2");
        heading.textContent = "Kamera";
        panel.append(heading);
        const live = document.createElement("video");
        live.autoplay = true;
        live.muted = true;
        live.playsInline = true;
        const status = document.createElement("p");
        status.setAttribute("role", "status");
        const clock = document.createElement("span");
        clock.className = "fb-chat-camera-clock";
        clock.hidden = true;
        const controls = document.createElement("div");
        controls.className = "fb-chat-camera-actions";
        const photo = createButton("Ambil foto");
        const record = createButton("Rekam video");
        photo.className = "fb-chat-camera-photo";
        record.className = "fb-chat-camera-record";
        const videoIcon = "M4 6h11v12H4zM15 10l5-3v10l-5-3";
        labelCameraAction(photo, "Ambil foto", "M4 7h4l2-3h4l2 3h4v13H4zM15.5 13.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 1 1 7 0");
        labelCameraAction(record, "Rekam video", videoIcon);
        const gallery = createButton("Buka galeri");
        gallery.hidden = true;
        controls.append(photo, record, gallery);
        panel.append(live, clock, status, controls);

        let stream;
        let microphone;
        let recorder;
        let timer;
        let capturedUrl;
        let opening = false;
        const lifecycle = new AbortController();
        panel.addEventListener("close", () => {
            lifecycle.abort();
            stopCamera();
            if (capturedUrl) URL.revokeObjectURL(capturedUrl);
        }, { once: true });
        window.addEventListener("pagehide", () => panel.close(), { signal: lifecycle.signal });

        function stopCamera() {
            clearInterval(timer);
            if (recorder?.state === "recording") recorder.stop();
            for (const source of [stream, microphone]) source?.getTracks().forEach((track) => track.stop());
            live.srcObject = null;
        }

        async function startCamera() {
            photo.disabled = record.disabled = true;
            status.textContent = "Membuka kamera…";
            try {
                const source = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: "environment" }, width: { ideal: 1280 }, height: { ideal: 720 } },
                    audio: false,
                });
                if (!panel.open) {
                    source.getTracks().forEach((track) => track.stop());
                    return;
                }
                stream = source;
                live.srcObject = source;
                await live.play();
                if (!panel.open) return;
                status.textContent = "";
                photo.disabled = false;
                record.disabled = !window.MediaRecorder;
                if (record.disabled) status.textContent = "Perekaman video tidak tersedia di browser ini.";
            } catch {
                if (!panel.open) return;
                stopCamera();
                status.textContent = "Kamera tidak tersedia. Periksa izin kamera atau gunakan galeri.";
                gallery.hidden = false;
            }
        }

        function previewCapture(blob) {
            if (!panel.open) return;
            if (!blob?.size || blob.size > attachmentLimit) {
                status.textContent = "Hasil rekaman atau foto melebihi 4 MB atau belum siap. Coba lagi dengan durasi lebih pendek.";
                photo.disabled = false;
                record.disabled = !window.MediaRecorder;
                return;
            }

            stopCamera();
            live.hidden = true;
            heading.textContent = "Pratinjau";
            const type = blob.type.split(";")[0];
            const video = type.startsWith("video/");
            const extension = video ? (type === "video/mp4" ? "mp4" : "webm") : "jpg";
            const file = new File([blob], `${video ? "video" : "foto"}-kamera.${extension}`, { type });
            capturedUrl = URL.createObjectURL(file);
            const preview = mediaElement(capturedUrl, video, true);
            preview.className = "fb-chat-captured-preview";
            const caption = document.createElement("textarea");
            caption.placeholder = "Tambah caption…";
            caption.setAttribute("aria-label", "Caption opsional");
            caption.maxLength = 3000;
            const message = form.querySelector('textarea[name="message"]');
            caption.value = message.value;
            const retry = createButton("Ulangi");
            const cancel = createButton("Batal");
            const send = createButton("Kirim");
            send.className = "fb-chat-camera-send";
            labelCameraAction(retry, "Ulangi", "M4 11a8 8 0 1 1 2 7M4 5v6h6");
            labelCameraAction(send, "Kirim", "m22 2-7 20-4-9-9-4zM22 2 11 13");
            panel.insertBefore(preview, status);
            panel.insertBefore(caption, controls);
            controls.replaceChildren(retry, cancel, send);
            status.textContent = "";
            retry.addEventListener("click", () => {
                URL.revokeObjectURL(capturedUrl);
                capturedUrl = null;
                preview.remove();
                caption.remove();
                live.hidden = false;
                heading.textContent = "Kamera";
                labelCameraAction(record, "Rekam video", videoIcon);
                record.classList.remove("is-recording");
                controls.replaceChildren(photo, record, gallery);
                startCamera();
            });
            cancel.addEventListener("click", () => panel.close());
            send.addEventListener("click", () => {
                const input = form.querySelector('input[name="attachment"]');
                const transfer = new DataTransfer();
                transfer.items.add(file);
                input.files = transfer.files;
                message.value = caption.value;
                input.dispatchEvent(new Event("change", { bubbles: true }));
                panel.close();
                form.requestSubmit();
            });
        }

        photo.addEventListener("click", () => {
            if (!live.videoWidth) return;
            photo.disabled = record.disabled = true;
            const canvas = document.createElement("canvas");
            const scale = Math.min(1, 1600 / Math.max(live.videoWidth, live.videoHeight));
            canvas.width = Math.round(live.videoWidth * scale);
            canvas.height = Math.round(live.videoHeight * scale);
            canvas.getContext("2d").drawImage(live, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(previewCapture, "image/jpeg", .88);
        });

        record.addEventListener("click", async () => {
            if (recorder?.state === "recording") {
                recorder.stop();
                return;
            }
            if (opening) return;
            opening = true;
            photo.disabled = record.disabled = true;
            let bytes = 0;
            const chunks = [];
            try {
                const mimeType = ["video/webm;codecs=vp8,opus", "video/webm", "video/mp4"]
                    .find((type) => MediaRecorder.isTypeSupported(type));
                if (!mimeType) throw new Error("Format rekaman tidak didukung.");
                status.textContent = "Menyiapkan rekaman…";
                microphone = await navigator.mediaDevices.getUserMedia({ audio: true }).catch(() => null);
                if (!panel.open) {
                    microphone?.getTracks().forEach((track) => track.stop());
                    return;
                }
                const source = new MediaStream([...stream.getVideoTracks(), ...(microphone?.getAudioTracks() || [])]);
                recorder = new MediaRecorder(source, { mimeType, videoBitsPerSecond: 750000, audioBitsPerSecond: 48000 });
                recorder.addEventListener("dataavailable", (event) => {
                    bytes += event.data.size;
                    if (bytes <= attachmentLimit) chunks.push(event.data);
                    if (bytes > attachmentLimit - 140000 && recorder.state === "recording") recorder.stop();
                });
                recorder.addEventListener("stop", () => {
                    clearInterval(timer);
                    clock.hidden = true;
                    microphone?.getTracks().forEach((track) => track.stop());
                    microphone = null;
                    labelCameraAction(record, "Rekam video", videoIcon);
                    record.classList.remove("is-recording");
                    if (panel.open) previewCapture(bytes <= attachmentLimit ? new Blob(chunks, { type: recorder.mimeType }) : null);
                }, { once: true });
                recorder.start(250);
                const started = performance.now();
                clock.textContent = "● 00:00";
                clock.hidden = false;
                status.textContent = microphone ? "" : "Merekam tanpa suara.";
                labelCameraAction(record, "Berhenti", "M7 7h10v10H7z");
                record.classList.add("is-recording");
                record.disabled = false;
                timer = setInterval(() => {
                    const seconds = Math.min(30, Math.floor((performance.now() - started) / 1000));
                    clock.textContent = `● 00:${String(seconds).padStart(2, "0")}`;
                    if (seconds === 30 && recorder.state === "recording") recorder.stop();
                }, 200);
            } catch (error) {
                microphone?.getTracks().forEach((track) => track.stop());
                microphone = null;
                status.textContent = error.message || "Tidak dapat merekam video.";
                photo.disabled = record.disabled = false;
            } finally {
                opening = false;
            }
        });
        gallery.addEventListener("click", () => {
            panel.close();
            form.querySelector('input[name="attachment"]').click();
        });
        panel.showModal();
        startCamera();
    }

    document.addEventListener("change", (event) => {
        const input = event.target.closest('input[name="attachment"]');
        const form = input?.closest("[data-chat-compose]");
        if (!form) return;
        clearPreview(form);
        const file = input.files[0];
        if (!file) return;

        const element = document.createElement("div");
        element.className = "fb-chat-photo-preview";
        const video = file.type.startsWith("video/");
        const valid = validAttachment(file);
        const url = valid ? URL.createObjectURL(file) : null;
        const label = document.createElement("span");
        label.textContent = valid ? file.name : "Pilih foto JPG, PNG, WebP atau video MP4, WebM maksimal 4 MB.";

        if (valid) {
            const view = document.createElement("button");
            view.type = "button";
            view.setAttribute("aria-label", video ? "Lihat pratinjau video" : "Lihat pratinjau foto");
            view.append(mediaElement(url, video));
            view.addEventListener("click", () => showPhoto(view, url, video));
            element.append(view);
        } else {
            input.value = "";
            label.setAttribute("role", "alert");
        }

        const remove = document.createElement("button");
        remove.type = "button";
        remove.className = "fb-chat-preview-remove";
        remove.setAttribute("aria-label", video ? "Lepas video" : "Lepas foto");
        remove.textContent = "×";
        remove.addEventListener("click", () => {
            input.value = "";
            clearPreview(form);
            (form.querySelector("[data-chat-pick]") || input).focus({ preventScroll: true });
        });
        element.append(label, remove);
        previews.set(form, { element, url });
        form.prepend(element);
        const stream = form.closest(".fcp-panel, .fb-chat-modal, .chat-panel, .fb-inbox-main")
            ?.querySelector(".fcp-messages, .fb-chat-messages, .chat-messages, .fb-inbox-messages");
        if (stream && stream.id !== "fb-modal-stream"
            && stream.scrollHeight - stream.scrollTop - stream.clientHeight < 150) {
            stream.scrollTop = stream.scrollHeight;
        }
    });

    document.addEventListener("reset", (event) => clearPreview(event.target), true);
    document.addEventListener("fb:admin-page", () => {
        document.querySelectorAll(".fb-chat-tool-dialog").forEach((panel) => panel.close());
    });

    function markRecalled(row) {
        if (!row || row.dataset.recalled) return;
        row.dataset.recalled = "true";
        row.querySelector(".fb-chat-receipt")?.remove();
        const bubble = row.matches(".fb-inbox-bubble") ? row : row.querySelector(".fcp-bubble, .chat-bubble");
        bubble.querySelectorAll("p, a, .chat-edited").forEach((node) => node.remove());
        const options = bubble.querySelector("[data-admin-chat-options]");
        delete options.dataset.recallUntil;
        const text = document.createElement("p");
        text.textContent = "Pesan telah ditarik";
        bubble.insertBefore(text, options);
    }

    const streamSelector = "[data-chat-admin-stream], #fb-modal-stream";
    const rowSelector = "[data-message-id], .fb-chat-bubble-row[data-id]";
    const rowId = (row) => row.dataset.messageId || row.dataset.id;
    const selections = new WeakMap();

    const displayedStreams = new WeakSet();
    const chatDateFormat = new Intl.DateTimeFormat("id-ID", {
        day: "numeric", month: "long", year: "numeric", timeZone: "Asia/Jakarta",
    });
    const chatClockFormat = new Intl.DateTimeFormat("id-ID", {
        hour: "2-digit", minute: "2-digit", hourCycle: "h23", timeZone: "Asia/Jakarta",
    });

    function refreshChatDates(stream) {
        const atBottom = stream.id !== "fb-modal-stream"
            && stream.scrollHeight - stream.scrollTop - stream.clientHeight < 24;
        let previousDate = "";
        let divider;
        for (const row of [...stream.children]) {
            if (row.classList.contains("fb-chat-date-divider")) {
                divider?.remove();
                divider = row;
                continue;
            }
            if (!row.dataset.sentAt) continue;
            const instant = new Date(row.dataset.sentAt);
            if (Number.isNaN(instant.getTime())) continue;
            const date = chatDateFormat.format(instant);
            const clock = chatClockFormat.format(instant);
            const bubble = row.matches(".fb-inbox-bubble") ? row
                : row.querySelector(".fcp-bubble, .fb-chat-bubble, .chat-bubble");
            const time = bubble?.querySelector(":scope > footer > time, :scope > small:last-child, :scope > footer");
            if (time && !time.dataset.compactTime) {
                const value = time.querySelector("time") || time;
                const edited = value === time && time.textContent.includes(" · Diedit");
                value.title = `${date}, ${clock} WIB`;
                value.textContent = clock + (edited ? " · Diedit" : "");
                time.dataset.compactTime = "true";
            }
            if (row.classList.contains("mine") && /^\d+$/.test(rowId(row))) renderReceipt(row);
            if (date !== previousDate) {
                if (!divider) {
                    divider = document.createElement("div");
                    divider.className = "fb-chat-date-divider";
                    stream.insertBefore(divider, row);
                }
                if (divider.textContent !== date) divider.textContent = date;
            } else {
                divider?.remove();
            }
            previousDate = date;
            divider = undefined;
        }
        divider?.remove();
        if (atBottom) stream.scrollTop = stream.scrollHeight;
    }

    const chatMediaSelector = ".fcp-bubble > a > img, .fb-chat-bubble > a > img, .chat-bubble > a > img, .fb-inbox-bubble > a > img";

    function syncChatMedia(image) {
        const link = image.parentElement;
        const unavailable = image.complete && image.naturalWidth === 0;
        link.classList.toggle("fb-chat-media-unavailable", unavailable);
        if (unavailable) {
            link.setAttribute("aria-disabled", "true");
            link.setAttribute("aria-label", "Foto tidak tersedia");
        } else {
            link.removeAttribute("aria-disabled");
            link.removeAttribute("aria-label");
        }

        if (image.naturalWidth && image.naturalHeight
            && !link.parentElement.style.getPropertyValue("--chat-photo-ratio")) {
            const stream = image.closest(streamSelector);
            const atBottom = stream?.clientHeight && stream.id !== "fb-modal-stream"
                && stream.scrollHeight - stream.scrollTop - stream.clientHeight < 30;
            link.parentElement.style.setProperty("--chat-photo-ratio", image.naturalWidth / image.naturalHeight);
            if (atBottom) stream.scrollTop = stream.scrollHeight;
        }
    }

    for (const type of ["load", "error"]) {
        document.addEventListener(type, (event) => {
            if (event.target instanceof HTMLImageElement && event.target.matches(chatMediaSelector)) {
                syncChatMedia(event.target);
            }
        }, true);
    }

    function initializeChatDates() {
        document.querySelectorAll(streamSelector).forEach((stream) => {
            if (displayedStreams.has(stream)) return;
            displayedStreams.add(stream);
            const photos = [...stream.querySelectorAll(chatMediaSelector)];
            // Start a small batch before the hidden claim dialog is opened.
            for (const image of photos.slice(-3).reverse()) {
                image.fetchPriority = image === photos.at(-1) ? "high" : "auto";
                image.loading = "eager";
            }
            photos.forEach(syncChatMedia);
            refreshChatDates(stream);
            new MutationObserver((records) => {
                if (records.some((record) => [...record.addedNodes, ...record.removedNodes]
                    .some((node) => node instanceof HTMLElement && node.matches(rowSelector)))) {
                    refreshChatDates(stream);
                }
            }).observe(stream, { childList: true });
        });
    }

    initializeChatDates();
    document.addEventListener("fb:admin-page", initializeChatDates);

    const receiptRequests = new WeakSet();

    function renderReceipt(row) {
        const bubble = row.matches(".fb-inbox-bubble") ? row
            : row.querySelector(".fcp-bubble, .fb-chat-bubble, .chat-bubble");
        const time = bubble?.querySelector(":scope > small:last-child, :scope > footer");
        if (!time) return;
        let receipt = time.querySelector(".fb-chat-receipt");
        if (row.dataset.recalled || row.classList.contains("is-recalled")) {
            receipt?.remove();
            return;
        }
        if (!receipt) {
            receipt = createButton("");
            receipt.className = "fb-chat-receipt";
            receipt.addEventListener("click", () => receipt.toggleAttribute("data-show-label"));
            receipt.addEventListener("blur", () => receipt.removeAttribute("data-show-label"));
            time.append(receipt);
        }
        const readAt = row.dataset.readAt;
        const label = readAt ? `Dibaca pukul ${chatClockFormat.format(new Date(readAt))} WIB` : "Terkirim";
        receipt.toggleAttribute("data-read", Boolean(readAt));
        receipt.dataset.label = label;
        receipt.setAttribute("aria-label", label);
        const symbol = readAt ? "✓✓" : "✓";
        if (receipt.textContent !== symbol) receipt.textContent = symbol;
    }

    async function syncReceipts(stream) {
        const url = stream.dataset.receiptsUrl || stream.dataset.url || stream.dataset.poll;
        if (!url || document.hidden || !stream.clientHeight || receiptRequests.has(stream)) return;
        const bounds = stream.getBoundingClientRect();
        const viewport = window.visualViewport;
        const top = Math.max(bounds.top, viewport?.offsetTop ?? 0);
        const bottom = Math.min(bounds.bottom, (viewport?.offsetTop ?? 0) + (viewport?.height ?? innerHeight));
        if (bottom <= top) return;

        const seen = [];
        const outgoing = [];
        for (const row of stream.querySelectorAll(rowSelector)) {
            const id = Number(rowId(row));
            if (!Number.isSafeInteger(id) || id < 1 || row.dataset.recalled || row.classList.contains("is-recalled")) continue;
            if (row.classList.contains("mine")) {
                renderReceipt(row);
                if (!row.dataset.readAt) outgoing.push(id);
                continue;
            }
            if (row.dataset.readAt || row.dataset.receiptSent) continue;
            const box = row.getBoundingClientRect();
            const visible = Math.min(box.bottom, bottom) - Math.max(box.top, top);
            if (box.height && visible >= Math.min(box.height, bottom - top) / 2) seen.push(id);
        }
        const requested = outgoing.slice(-100);
        const visibleIds = seen.slice(0, 100);
        if (!requested.length && !visibleIds.length) return;

        receiptRequests.add(stream);
        try {
            const response = await fetch(url, {
                method: "POST",
                headers: { Accept: "application/json", "Content-Type": "application/json", "X-CSRF-TOKEN": stream.dataset.chatToken },
                body: JSON.stringify({ seen: visibleIds, outgoing: requested }),
            });
            if (!response.ok) return;
            const { receipts } = await response.json();
            const acknowledged = new Set(visibleIds);
            for (const row of stream.querySelectorAll(rowSelector)) {
                const id = Number(rowId(row));
                if (acknowledged.has(id)) row.dataset.receiptSent = "true";
                if (receipts[id]) {
                    row.dataset.readAt = receipts[id];
                    renderReceipt(row);
                }
            }
        } catch {
            // Retry when the chat is visible again.
        } finally {
            receiptRequests.delete(stream);
        }
    }

    window.setInterval(() => document.querySelectorAll(streamSelector).forEach(syncReceipts), 1500);

    function messageRow(target) {
        const row = target.closest(rowSelector);
        return row?.closest(streamSelector) && /^\d+$/.test(rowId(row)) ? row : null;
    }

    function createButton(text, title = text) {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = text;
        button.setAttribute("aria-label", title);
        return button;
    }

    function stopSelection(stream) {
        const state = selections.get(stream);
        if (!state) return;
        state.observer.disconnect();
        state.cleanup.abort();
        state.bar.remove();
        state.slot?.classList.remove("is-selecting");
        stream.classList.remove("fb-chat-selecting");
        stream.querySelectorAll(".fb-chat-selected").forEach((row) => row.classList.remove("fb-chat-selected"));
        stream.querySelectorAll(".fb-chat-check").forEach((check) => check.remove());
        selections.delete(stream);
    }

    function updateSelection(stream, state) {
        const rows = [...stream.querySelectorAll(rowSelector)].filter((row) => /^\d+$/.test(rowId(row)));
        const visible = new Set(rows.map(rowId));
        for (const id of state.ids) {
            if (!visible.has(id)) state.ids.delete(id);
        }
        for (const row of rows) {
            let check = row.querySelector(".fb-chat-check");
            if (!check) {
                check = document.createElement("input");
                check.type = "checkbox";
                check.className = "fb-chat-check";
                check.setAttribute("aria-label", "Pilih pesan");
                row.append(check);
            }
            check.checked = state.ids.has(rowId(row));
            row.classList.toggle("fb-chat-selected", check.checked);
        }
        state.count.textContent = `${state.ids.size} pesan dipilih`;
        state.remove.disabled = state.ids.size === 0;
    }

    function toggleSelection(row) {
        const stream = row.closest(streamSelector);
        let state = selections.get(stream);
        if (!state) {
            const bar = document.createElement("div");
            bar.className = "fb-chat-selection-bar";
            const cancel = createButton("Batal", "Tutup mode pilih pesan");
            const count = document.createElement("span");
            count.setAttribute("role", "status");
            const remove = createButton("", "Hapus pesan terpilih");
            remove.title = "Hapus pesan terpilih";
            remove.className = "fb-chat-delete-button";
            const icon = document.createElementNS("http://www.w3.org/2000/svg", "svg");
            icon.setAttribute("viewBox", "0 0 24 24");
            icon.setAttribute("aria-hidden", "true");
            const path = document.createElementNS(icon.namespaceURI, "path");
            path.setAttribute("d", "M3 6h18M9 6V4h6v2M6 6l1 14h10l1-14M10 10v6M14 10v6");
            icon.append(path);
            remove.append(icon);
            const slot = stream.parentElement.querySelector(
                ":scope > .fb-chat-action-slot, :scope > .fb-chat-bottom > .fb-chat-action-slot"
            );
            state = {
                bar, count, remove, slot,
                ids: new Set(),
                observer: new MutationObserver(() => updateSelection(stream, state)),
                cleanup: new AbortController(),
            };
            selections.set(stream, state);
            cancel.addEventListener("click", () => {
                stopSelection(stream);
                row.querySelector(".fb-chat-options")?.focus({ preventScroll: true });
            });
            remove.addEventListener("click", () => confirmDeletion(stream, state));
            bar.append(cancel, count, remove);
            if (slot) {
                slot.append(bar);
                slot.classList.add("is-selecting");
            } else {
                stream.after(bar);
            }
            stream.classList.add("fb-chat-selecting");
            state.observer.observe(stream, { childList: true, subtree: true });
            stream.closest("dialog")?.addEventListener("close", () => stopSelection(stream), {
                once: true,
                signal: state.cleanup.signal,
            });
        }
        const focusChoice = document.activeElement?.matches(".fb-chat-options, [data-chat-select]");
        row.dispatchEvent(new Event("fb:chat-selected", { bubbles: true }));
        const id = rowId(row);
        if (state.ids.has(id)) state.ids.delete(id);
        else state.ids.add(id);
        updateSelection(stream, state);
        if (focusChoice) {
            row.querySelector(".fb-chat-check")?.focus({ preventScroll: true });
        }
    }

    function recallDeadline(row) {
        return row.dataset.recallUntil || row.querySelector("[data-recall-until]")?.dataset.recallUntil;
    }

    async function deleteMessage(stream, row, kind) {
        const id = rowId(row);
        const url = stream.dataset[kind + "Url"].replace(/__MESSAGE_ID__|__ID__/, id);
        const response = await fetch(url, {
            method: "PATCH",
            headers: { Accept: "application/json", "X-CSRF-TOKEN": stream.dataset.chatToken },
        });
        if (!response.ok) {
            const payload = await response.json().catch(() => null);
            throw new Error(payload?.message || "Pesan gagal dihapus. Coba lagi.");
        }
        const current = stream.querySelector(`[data-message-id="${id}"], [data-id="${id}"]`);
        if (!current) return;
        if (kind === "hide") current.remove();
        else if (stream.matches("[data-chat-admin-stream]")) markRecalled(current);
        else {
            const { message } = await response.json();
            current.dispatchEvent(new CustomEvent("fb:chat-recalled", { bubbles: true, detail: message }));
        }
    }

    function confirmDeletion(stream, state) {
        const rows = [...stream.querySelectorAll(rowSelector)].filter((row) => state.ids.has(rowId(row)));
        if (!rows.length) return;
        const panel = openDialog(stream, "Hapus pesan");
        panel.classList.add("fb-chat-delete-dialog");
        const actions = document.createElement("div");
        actions.className = "fb-chat-tool-actions";
        const title = document.createElement("strong");
        title.textContent = rows.length === 1 ? "Hapus pesan ini?" : `Hapus ${rows.length} pesan?`;
        const error = document.createElement("p");
        error.setAttribute("role", "alert");
        const cancel = createButton("Batal");
        cancel.addEventListener("click", () => panel.close());
        const local = createButton("Hapus untuk saya");
        actions.append(local);
        const shared = createButton("Hapus untuk semua orang");
        shared.className = "fb-chat-delete-shared";
        const canRecall = () => rows.every((row) => !state.ids.has(rowId(row)) || Date.parse(recallDeadline(row)) > Date.now());
        if (canRecall()) actions.prepend(shared);
        actions.append(cancel);
        const close = panel.querySelector(".fb-chat-tool-close");
        close.hidden = true;
        panel.append(title, actions, error);

        async function apply(kind) {
            actions.querySelectorAll("button").forEach((button) => button.disabled = true);
            error.textContent = "";
            try {
                for (const row of rows) {
                    if (!state.ids.has(rowId(row))) continue;
                    await deleteMessage(stream, row, kind);
                    state.ids.delete(rowId(row));
                    updateSelection(stream, state);
                }
                stopSelection(stream);
                panel.close();
            } catch (failure) {
                error.textContent = failure.message;
            } finally {
                actions.querySelectorAll("button").forEach((button) => button.disabled = false);
                shared.disabled = !canRecall();
            }
        }
        local.addEventListener("click", () => apply("hide"));
        shared.addEventListener("click", () => apply("recall"));
        panel.addEventListener("cancel", (event) => {
            if (cancel.disabled) event.preventDefault();
        });
        cancel.autofocus = true;
        panel.showModal();
    }

    let contextChoice;

    function closeContextChoice() {
        contextChoice?.remove();
        contextChoice = null;
    }

    function openContextChoice(row, x, y) {
        closeContextChoice();
        if (row.closest("#fb-modal-stream")) {
            row.dispatchEvent(new CustomEvent("fb:chat-menu", { bubbles: true, detail: { x, y } }));
            return;
        }

        const choice = createButton("Pilih pesan");
        choice.className = "fb-chat-context-choice";
        (row.closest("dialog") || document.body).append(choice);
        choice.style.left = `${Math.max(8, Math.min(x, innerWidth - choice.offsetWidth - 8))}px`;
        choice.style.top = `${Math.max(8, Math.min(y, innerHeight - choice.offsetHeight - 8))}px`;
        choice.addEventListener("click", () => {
            closeContextChoice();
            if (!row.isConnected) return;
            toggleSelection(row);
            row.querySelector(".fb-chat-check")?.focus({ preventScroll: true });
        });
        contextChoice = choice;
        choice.focus({ preventScroll: true });
    }

    document.addEventListener("pointerdown", (event) => {
        if (contextChoice && event.target !== contextChoice) closeContextChoice();
    }, true);
    document.addEventListener("scroll", closeContextChoice, true);
    document.addEventListener("close", closeContextChoice, true);
    document.addEventListener("fb:admin-page", closeContextChoice);
    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape" || document.querySelector(".fb-chat-tool-dialog[open]")) return;
        const stream = document.querySelector(".fb-chat-selecting");
        if (!contextChoice && !stream) return;
        event.preventDefault();
        event.stopPropagation();
        if (contextChoice) closeContextChoice();
        else stopSelection(stream);
    }, true);

    document.addEventListener("contextmenu", (event) => {
        const row = messageRow(event.target);
        if (!row) return;
        event.preventDefault();
        event.stopPropagation();
        if (touchHold?.activated) return;
        if (touchHold) {
            clearTimeout(touchHold.timer);
            touchHold.activated = true;
            toggleSelection(row);
            return;
        }
        if (event.pointerType === "touch" || suppressTap?.row === row && Date.now() < suppressTap.until) return;
        openContextChoice(row, event.clientX, event.clientY);
    }, true);

    let touchHold;
    let suppressTap;
    document.addEventListener("pointerdown", (event) => {
        if (touchHold || event.pointerType === "mouse" || event.target.closest("button, input")) return;
        const row = messageRow(event.target);
        if (!row || row.closest(".fb-chat-selecting")) return;
        const { pointerId, clientX, clientY } = event;
        const timer = setTimeout(() => {
            if (!row.isConnected) return;
            touchHold.activated = true;
            toggleSelection(row);
        }, 500);
        touchHold = { row, pointerId, clientX, clientY, timer, activated: false };
    }, true);
    document.addEventListener("pointermove", (event) => {
        if (touchHold?.pointerId !== event.pointerId || touchHold.activated) return;
        if (Math.hypot(touchHold.clientX - event.clientX, touchHold.clientY - event.clientY) > 12) {
            clearTimeout(touchHold.timer);
        }
    }, { passive: true });
    for (const type of ["pointerup", "pointercancel"]) {
        document.addEventListener(type, (event) => {
            if (touchHold?.pointerId !== event.pointerId) return;
            clearTimeout(touchHold.timer);
            if (touchHold.activated) suppressTap = { row: touchHold.row, until: Date.now() + 400 };
            touchHold = null;
        }, true);
    }

    document.addEventListener("click", (event) => {
        const row = messageRow(event.target);
        if (!row) return;
        const adminOptions = event.target.closest("[data-admin-chat-options]");
        if (adminOptions) {
            const bounds = row.getBoundingClientRect();
            openContextChoice(row, bounds.left, bounds.top);
            return;
        }
        const select = event.target.closest("[data-chat-select]");
        if (!select && !row.closest(".fb-chat-selecting")) return;
        event.stopPropagation();
        if (suppressTap?.row === row && Date.now() < suppressTap.until) {
            suppressTap = null;
            event.preventDefault();
            return;
        }
        if (!event.target.matches(".fb-chat-check")) event.preventDefault();
        toggleSelection(row);
    }, true);

    document.addEventListener("click", (event) => {
        const picker = event.target.closest("[data-chat-pick]");
        if (picker) chooseAttachment(picker);

        const link = event.target.closest(".fcp-bubble a, .fb-chat-bubble a, .chat-bubble a, .fb-inbox-bubble a");
        if (!link?.querySelector("img, video")) return;
        if (link.classList.contains("fb-chat-media-unavailable")) {
            event.preventDefault();
            return;
        }
        if (event.ctrlKey || event.metaKey || event.shiftKey) return;
        event.preventDefault();
        showPhoto(link, link.href, Boolean(link.querySelector("video")));
    });

    window.findBackChat = {
        appendAttachment,
        validAttachment,
        addAdminOptions(bubble, message) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "fb-chat-options";
            button.dataset.adminChatOptions = "";
            if (message.is_mine && message.recall_until) button.dataset.recallUntil = message.recall_until;
            button.setAttribute("aria-label", "Pilih pesan");
            button.textContent = "⌄";
            bubble.append(button);
        },
        markAdminRecalls(stream, ids = []) {
            ids.forEach((id) => markRecalled(stream.querySelector(`[data-message-id="${Number(id)}"]`)));
        },
    };
})();
