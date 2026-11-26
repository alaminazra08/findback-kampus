
(() => {
    "use strict";

    // Navigasi instan khusus empat halaman utama.
    const config = document.getElementById("fb-fast-navigation");
    if (!config) return;

    let urls;

    try {
        urls = JSON.parse(config.textContent || "[]");
    } catch {
        return;
    }

    const routes = new Map(
        urls.map((url) => {
            const full = new URL(url, location.href);
            return [full.pathname, full.href];
        })
    );

    if (routes.size !== 4 || !routes.has(location.pathname)) {
        return;
    }

    // Simpan halaman yang telah dimuat selama 15 detik.
    const memory = new Map();
    const pending = new Map();
    const lifetime = 15000;

    let navigationId = 0;
    let activePath = location.pathname;

    // Batasi navigasi hanya untuk empat halaman.
    function allowed(url) {
        try {
            const target = new URL(url, location.href);

            if (
                target.origin !== location.origin ||
                !routes.has(target.pathname) ||
                target.search ||
                target.hash
            ) {
                return null;
            }

            return target;
        } catch {
            return null;
        }
    }

    // Ambil halaman dari cache atau server.
    async function getPage(url) {
        const target = allowed(url);

        if (!target) {
            throw new Error("Halaman tidak tersedia");
        }

        const key = target.pathname;
        const saved = memory.get(key);

        if (saved && Date.now() - saved.time < lifetime) {
            return saved.doc;
        }

        if (pending.has(key)) {
            return pending.get(key);
        }

        const task = (async () => {
            const response = await fetch(target.href, {
                credentials: "same-origin",
                headers: {
                    Accept: "text/html"
                },
                cache: "no-store"
            });

            if (!response.ok || !allowed(response.url)) {
                throw new Error("Respons navigasi tidak valid");
            }

            const markup = await response.text();

            const doc = new DOMParser().parseFromString(
                markup,
                "text/html"
            );

            if (
                !doc.querySelector("main.cu-main") ||
                !doc.querySelector("#cu-nav")
            ) {
                throw new Error("Struktur halaman tidak cocok");
            }

            memory.set(key, {
                doc,
                time: Date.now()
            });

            return doc;
        })();

        pending.set(key, task);

        try {
            return await task;
        } finally {
            pending.delete(key);
        }
    }

    // Muat CSS sebelum konten baru ditampilkan.
    async function prepareStyles(doc) {
        const required = [
            ...doc.querySelectorAll('head link[rel="stylesheet"]')
        ];

        await Promise.all(
            required.map((link) => new Promise((resolve) => {
                const href = link.href;

                const exists = [
                    ...document.querySelectorAll(
                        'head link[rel="stylesheet"]'
                    )
                ].some((item) => item.href === href);

                if (exists) {
                    resolve();
                    return;
                }

                const style = document.createElement("link");

                style.rel = "stylesheet";
                style.href = href;

                style.addEventListener("load", resolve, {
                    once: true
                });

                style.addEventListener("error", resolve, {
                    once: true
                });

                document.head.appendChild(style);
            }))
        );
    }

    // Perbarui status aktif pada navbar.
    function refreshNavigation(doc) {
        const currentNav = document.querySelector("#cu-nav");
        const nextNav = doc.querySelector("#cu-nav");

        if (!currentNav || !nextNav) return;

        const mobileNav = document.querySelector(".cu-mobile-nav");
        const nextMobileNav = doc.querySelector(".cu-mobile-nav");

        if (mobileNav && nextMobileNav) {
            for (const tab of mobileNav.querySelectorAll("[data-cu-tab]")) {
                const nextTab = nextMobileNav.querySelector(`[data-cu-tab="${tab.dataset.cuTab}"]`);
                const current = nextTab?.getAttribute("aria-current");
                if (current) tab.setAttribute("aria-current", current);
                else tab.removeAttribute("aria-current");
            }
        }

        const oldAnchors = [
            ...currentNav.querySelectorAll("a[href]")
        ];

        const newAnchors = [
            ...nextNav.querySelectorAll("a[href]")
        ];

        oldAnchors.forEach((oldLink) => {
            const newer = newAnchors.find(
                (link) => link.href === oldLink.href
            );

            oldLink.classList.toggle(
                "active",
                !!newer?.classList.contains("active")
            );

            if (newer?.hasAttribute("aria-current")) {
                oldLink.setAttribute(
                    "aria-current",
                    newer.getAttribute("aria-current")
                );
            } else {
                oldLink.removeAttribute("aria-current");
            }
        });

        const summary = currentNav.querySelector(
            ".cu-report-dropdown summary"
        );

        const incoming = nextNav.querySelector(
            ".cu-report-dropdown summary"
        );

        if (summary && incoming) {
            summary.classList.toggle(
                "active",
                incoming.classList.contains("active")
            );

            const currentLabel = summary.querySelector("span");
            const nextLabel = incoming.querySelector("span");

            if (currentLabel && nextLabel) {
                currentLabel.textContent = nextLabel.textContent;
            }
        }

        // Tutup menu setelah pindah halaman.
        currentNav.querySelectorAll("details[open]").forEach(
            (item) => {
                item.open = false;
            }
        );

        currentNav.classList.remove("open");

        const menu = document.getElementById("cu-menu");

        if (menu) {
            menu.setAttribute("aria-expanded", "false");
            menu.textContent = "☰";
        }
    }

    // Jalankan script yang terdapat dalam konten baru.
    function runContentScripts(main) {
        main.querySelectorAll("script:not([src])").forEach(
            (oldScript) => {
                const script = document.createElement("script");
                script.textContent = oldScript.textContent;
                oldScript.replaceWith(script);
            }
        );

        if (
            main.querySelector("[data-custom-select]") &&
            typeof window.fbInitActivityFilters === "function"
        ) {
            window.fbInitActivityFilters();
        } else if (window.fbActivityFilterController) {
            window.fbActivityFilterController.abort();
        }
    }

    // Ganti konten tanpa memuat ulang seluruh halaman.
    async function changePage(url, push = true) {
        const target = allowed(url);

        if (!target) {
            location.assign(url);
            return;
        }

        if (target.pathname === activePath) return;

        const id = ++navigationId;

        try {
            const doc = await getPage(target.href);

            await prepareStyles(doc);

            if (id !== navigationId) return;

            const currentMain = document.querySelector(
                "main.cu-main"
            );

            const nextMain = doc.querySelector(
                "main.cu-main"
            );

            if (!currentMain || !nextMain) {
                throw new Error("Konten tidak tersedia");
            }

            const replacement = document.importNode(
                nextMain,
                true
            );

            currentMain.replaceWith(replacement);

           
// Perbarui kelas halaman tanpa mengganti seluruh identitas body.
document.body.classList.toggle(
    "fb-activity-page",
    doc.body.classList.contains("fb-activity-page")
);

            document.title = doc.title;

            refreshNavigation(doc);

            if (push) {
                history.pushState(
                    { fbFast: true },
                    "",
                    target.href
                );
            }

            activePath = target.pathname;

            window.scrollTo(0, 0);

            runContentScripts(replacement);

            document.dispatchEvent(
                new Event("fb:pagechange")
            );

            preloadOthers();
        } catch {
            // Gunakan navigasi normal jika gagal.
            location.assign(target.href);
        }
    }

    // Tangani klik pada empat halaman.
    document.addEventListener("click", (event) => {
        const link = event.target.closest?.("a[href]");

        if (
            !link ||
            event.defaultPrevented ||
            event.button !== 0 ||
            event.ctrlKey ||
            event.metaKey ||
            event.shiftKey ||
            event.altKey ||
            (link.target && link.target !== "_self") ||
            link.hasAttribute("download")
        ) {
            return;
        }

        const target = allowed(link.href);

        if (!target) return;

        event.preventDefault();
        changePage(target.href);
    });

    // Persiapkan halaman ketika kursor mendekati tautan.
    document.addEventListener("pointerover", (event) => {
        const link = event.target.closest?.("a[href]");
        const target = link && allowed(link.href);

        if (target && target.pathname !== activePath) {
            getPage(target.href).catch(() => {});
        }
    }, { passive: true });

    // Persiapkan halaman untuk navigasi keyboard.
    document.addEventListener("focusin", (event) => {
        const link = event.target.closest?.("a[href]");
        const target = link && allowed(link.href);

        if (target && target.pathname !== activePath) {
            getPage(target.href).catch(() => {});
        }
    });

    // Mendukung tombol Back dan Forward browser.
    window.addEventListener("popstate", () => {
        const target = allowed(location.href);

        if (target) {
            changePage(target.href, false);
        } else {
            location.reload();
        }
    });

    // Muat halaman lainnya secara berurutan.
    function preloadOthers() {
        const others = [...routes.entries()].filter(
            ([path]) => path !== activePath
        );

        (async () => {
            for (const [, url] of others) {
                try {
                    await getPage(url);
                } catch {
                    // Navigasi normal tetap tersedia.
                }
            }
        })();
    }

    // Mulai pemuatan awal saat browser memiliki waktu luang.
    if ("requestIdleCallback" in window) {
        requestIdleCallback(preloadOthers, {
            timeout: 700
        });
    } else {
        window.addEventListener(
            "load",
            preloadOthers,
            { once: true }
        );
    }
})();
