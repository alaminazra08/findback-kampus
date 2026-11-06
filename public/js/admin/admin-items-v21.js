/* FindBack Admin V21 — progressive-enhancement dropdowns; original selects remain form source */
(() => {
    const form = document.querySelector(".fb-v20-filter");
    if (!form || form.dataset.v21Ready === "1") return;
    form.dataset.v21Ready = "1";
    const configs = {
        type: {
            "": ["◉", ""],
            lost: ["!", "is-lost"],
            found: ["◇", "is-found"],
        },
        group: {
            semua: ["◉", ""],
            aktif: ["✓", "is-active"],
            proses: ["◷", "is-wait"],
            selesai: ["✓", "is-done"],
            arsip: ["▣", "is-archive"],
        },
        status: {
            "": ["◉", ""],
            pending: ["◷", "is-wait"],
            verified: ["✓", "is-active"],
            rejected: ["×", "is-archive"],
            returned: ["✓", "is-done"],
            claimed: ["↻", "is-wait"],
        },
    };
    const dropdowns = [];
    const closeAll = (except) =>
        dropdowns.forEach((d) => {
            if (d !== except) {
                d.menu.hidden = true;
                d.trigger.setAttribute("aria-expanded", "false");
            }
        });
    form.querySelectorAll('select[name="type"],select[name="group"],select[name="status"]').forEach(
        (select, ix) => {
            const field = select.closest(".fb-v20-field");
            if (!field) return;
            const wrap = document.createElement("div");
            wrap.className = "fb-v21-select-wrap";
            select.parentNode.insertBefore(wrap, select);
            wrap.appendChild(select);
            const trigger = document.createElement("button");
            trigger.type = "button";
            trigger.className = "fb-v21-trigger";
            trigger.setAttribute("aria-expanded", "false");
            trigger.setAttribute("aria-haspopup", "listbox");
            trigger.setAttribute(
                "aria-label",
                field.querySelector("span")?.textContent?.trim() || "Pilih filter",
            );
            const menu = document.createElement("div");
            menu.className = "fb-v21-menu";
            menu.hidden = true;
            menu.setAttribute("role", "listbox");
            menu.id = "fb-v21-options-" + ix;
            trigger.setAttribute("aria-controls", menu.id);
            const choices = [];
            const meta = (value) => configs[select.name]?.[value] || ["•", ""];
            const icon = (value) => {
                const m = meta(value);
                const span = document.createElement("span");
                span.className = "fb-v21-symbol " + m[1];
                span.textContent = m[0];
                span.setAttribute("aria-hidden", "true");
                return span;
            };
            const markSelection = () => {
                const option =
                    Array.from(select.options).find((o) => o.value === select.value) ||
                    select.options[0];
                trigger.replaceChildren(icon(option.value));
                const label = document.createElement("span");
                label.className = "fb-v21-trigger-text";
                label.textContent = option.textContent.trim();
                trigger.appendChild(label);
                const chevron = document.createElement("span");
                chevron.className = "fb-v21-chevron";
                chevron.textContent = "⌄";
                chevron.setAttribute("aria-hidden", "true");
                trigger.appendChild(chevron);
                choices.forEach((c) => {
                    let chosen = c.value === select.value;
                    c.button.setAttribute("aria-selected", String(chosen));
                    c.check.textContent = chosen ? "✓" : "";
                });
            };
            Array.from(select.options).forEach((o) => {
                const b = document.createElement("button");
                b.type = "button";
                b.className = "fb-v21-option";
                b.setAttribute("role", "option");
                b.appendChild(icon(o.value));
                const text = document.createElement("span");
                text.className = "fb-v21-option-label";
                text.textContent = o.textContent.trim();
                b.appendChild(text);
                const check = document.createElement("span");
                check.className = "fb-v21-selected";
                b.appendChild(check);
                b.addEventListener("click", () => {
                    select.value = o.value;
                    select.dispatchEvent(new Event("change", { bubbles: true }));
                    markSelection();
                    menu.hidden = true;
                    trigger.setAttribute("aria-expanded", "false");
                    trigger.focus();
                });
                menu.appendChild(b);
                choices.push({ value: o.value, button: b, check });
            });
            wrap.append(trigger, menu);
            // Preserve native control for GET form and browser validation when JS is disabled.
            select.classList.add("fb-v21-native");
            markSelection();
            const data = { trigger, menu };
            dropdowns.push(data);
            trigger.addEventListener("click", () => {
                const opening = menu.hidden;
                closeAll(data);
                menu.hidden = !opening;
                trigger.setAttribute("aria-expanded", String(opening));
            });
            trigger.addEventListener("keydown", (ev) => {
                if (ev.key === "ArrowDown" || ev.key === "Enter" || ev.key === " ") {
                    ev.preventDefault();
                    closeAll(data);
                    menu.hidden = false;
                    trigger.setAttribute("aria-expanded", "true");
                    const active = choices.find((x) => x.value === select.value) || choices[0];
                    active?.button.focus();
                }
            });
            menu.addEventListener("keydown", (ev) => {
                if (ev.key === "Escape") {
                    ev.preventDefault();
                    menu.hidden = true;
                    trigger.setAttribute("aria-expanded", "false");
                    trigger.focus();
                    return;
                }
                if (ev.key === "ArrowDown" || ev.key === "ArrowUp") {
                    ev.preventDefault();
                    const all = choices.map((c) => c.button);
                    let p = all.indexOf(document.activeElement);
                    all[(p + (ev.key === "ArrowDown" ? 1 : -1) + all.length) % all.length]?.focus();
                }
            });
        },
    );
    document.addEventListener("click", (event) => {
        if (!event.target.closest(".fb-v21-select-wrap")) closeAll();
    });
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeAll();
    });
})();
