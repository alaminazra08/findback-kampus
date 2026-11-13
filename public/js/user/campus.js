(() => {
    const trigger = document.getElementById("cu-menu"),
        nav = document.getElementById("cu-nav");
    const closeMenu = () => {
        nav?.classList.remove("open");
        trigger?.setAttribute("aria-expanded", "false");
        if (trigger) trigger.textContent = "☰";
    };
    trigger?.addEventListener("click", () => {
        const open = nav.classList.toggle("open");
        trigger.setAttribute("aria-expanded", String(open));
        trigger.textContent = open ? "×" : "☰";
        trigger.setAttribute("aria-label", open ? "Tutup menu" : "Buka menu");
    });
    document.addEventListener("click", (e) => {
        document.querySelectorAll(".cu-dropdown[open]").forEach((d) => {
            if (!d.contains(e.target)) d.open = false;
        });
        if (nav && !nav.contains(e.target) && !trigger?.contains(e.target)) closeMenu();
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeMenu();
            document.querySelectorAll(".cu-dropdown[open]").forEach((d) => {
                d.open = false;
                d.querySelector("summary").focus();
            });
        }
    });
    window.matchMedia("(min-width:801px)").addEventListener("change", closeMenu);
    document.querySelectorAll("[data-preview]").forEach((input) => {
        let oldUrl;
        input.addEventListener("change", () => {
            const form = input.closest("form"),
                img = form.querySelector(".cu-preview"),
                info = form.querySelector(".cu-file-info"),
                f = input.files[0];
            input.setCustomValidity("");
            if (oldUrl) URL.revokeObjectURL(oldUrl);
            img.hidden = true;
            if (!f) {
                info.textContent = "";
                return;
            }
            if (f.size > Number(input.dataset.max)) {
                input.setCustomValidity("Ukuran foto melebihi batas. Pilih foto yang lebih kecil.");
                info.textContent = input.validationMessage;
                input.reportValidity();
                return;
            }
            if (!["image/jpeg", "image/png", "image/webp"].includes(f.type)) {
                input.setCustomValidity("Pilih foto JPG, PNG, atau WEBP.");
                info.textContent = input.validationMessage;
                input.reportValidity();
                return;
            }
            oldUrl = URL.createObjectURL(f);
            img.src = oldUrl;
            img.hidden = false;
            info.textContent = f.name + " · " + (f.size / 1048576).toFixed(2) + " MB";
        });
    });
    document.querySelectorAll("[data-wizard]").forEach((form) => {
        const steps = [...form.querySelectorAll("[data-step]")],
            labels = [...form.querySelectorAll(".cu-steps li")],
            prev = form.querySelector("[data-prev]"),
            next = form.querySelector("[data-next]"),
            submit = form.querySelector("[data-submit]");
        let current = 0;
        form.noValidate = true;
        function display(n) {
            current = n;
            steps.forEach((s, i) => (s.hidden = i !== n));
            labels.forEach((l, i) => {
                l.classList.toggle("active", i === n);
                if (i === n) l.setAttribute("aria-current", "step");
                else l.removeAttribute("aria-current");
            });
            prev.hidden = n === 0;
            next.hidden = n === steps.length - 1;
            submit.hidden = n !== steps.length - 1;
            if (n === 2) {
                const box = form.querySelector("[data-review]");
                box.replaceChildren();
                for (const [name, label] of [
                    ["title", "Nama barang"],
                    ["description", "Deskripsi"],
                    ["location", "Lokasi"],
                    ["event_date", "Tanggal"],
                ]) {
                    const field = form.elements[name];
                    if (!field) continue;
                    const row = document.createElement("div"),
                        key = document.createElement("strong"),
                        value = document.createElement("span");
                    key.textContent = label;
                    value.textContent = field.value;
                    row.append(key, value);
                    box.append(row);
                }
                const photo = form.elements["photo"];
                if (photo && photo.files[0]) {
                    const row = document.createElement("div"),
                        key = document.createElement("strong"),
                        value = document.createElement("span");
                    key.textContent = "Foto";
                    value.textContent = photo.files[0].name;
                    row.append(key, value);
                    box.append(row);
                }
            }
        }
        function validStep(n) {
            for (const input of steps[n].querySelectorAll("input,select,textarea"))
                if (!input.checkValidity()) {
                    display(n);
                    input.reportValidity();
                    input.focus();
                    return false;
                }
            return true;
        }
        next.addEventListener("click", () => {
            if (validStep(current)) {
                display(current + 1);
                steps[current].querySelector("h2")?.scrollIntoView({ block: "center" });
            }
        });
        prev.addEventListener("click", () => display(Math.max(0, current - 1)));
        form.addEventListener("submit", (e) => {
            for (let n = 0; n < steps.length; n++) {
                if (!validStep(n)) {
                    e.preventDefault();
                    return;
                }
            }
            submit.disabled = true;
            submit.textContent = "Mengirim…";
        });
        display(0);
    });
    const stream = document.getElementById("chat-stream");
    if (stream) {
        stream.scrollTop = stream.scrollHeight;
        let busy = false;
        const url = stream.dataset.poll;
        const state = document.getElementById("chat-state");
        async function poll() {
            if (!url || document.hidden || busy) return;
            busy = true;
            try {
                const res = await fetch(
                    url +
                        (url.includes("?") ? "&" : "?") +
                        "after=" +
                        encodeURIComponent(stream.dataset.after),
                    {
                        headers: { Accept: "application/json" },
                        credentials: "same-origin",
                    },
                );
                if (!res.ok) throw Error();
                const data = await res.json();
                const bottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 90;
                for (const m of data.messages) {
                    if (m.id <= Number(stream.dataset.after)) continue;
                    const article = document.createElement("article");
                    article.className = "cu-message" + (m.is_mine ? " mine" : "");
                    const box = document.createElement("div"),
                        name = document.createElement("strong"),
                        body = document.createElement("p"),
                        time = document.createElement("small");
                    name.textContent = m.is_mine ? "Kamu" : "Admin FindBack";
                    body.textContent = m.message || "";
                    time.textContent = m.created_at;
                    box.append(name, body);
                    if (m.attachment_url) {
                        const a = document.createElement("a"),
                            image = document.createElement("img");
                        const target = new URL(m.attachment_url, location.href);
                        if (target.origin === location.origin) {
                            a.href = target.href;
                            a.target = "_blank";
                            a.rel = "noopener";
                            image.src = target.href;
                            image.alt = "Lampiran percakapan";
                            a.append(image);
                            box.append(a);
                        }
                    }
                    box.append(time);
                    article.append(box);
                    stream.append(article);
                    stream.dataset.after = String(m.id);
                }
                if (bottom) stream.scrollTop = stream.scrollHeight;
                state.textContent = "Terhubung · pesan diperbarui otomatis.";
            } catch {
                state.textContent = "Pembaruan terhenti. Mencoba kembali otomatis…";
            } finally {
                busy = false;
            }
        }
        setInterval(poll, 7000);
        document.addEventListener("visibilitychange", () => {
            if (!document.hidden) poll();
        });
    }
})();
