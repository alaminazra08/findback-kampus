(() => {
    "use strict";

    const splash = document.getElementById("splash-screen");
    const toggle = document.getElementById("menu-toggle");
    const links = document.getElementById("nav-links");

    let splashHidden = false;

    function hideSplash() {
        if (!splash || splashHidden) {
            return;
        }

        splashHidden = true;
        splash.classList.add("hide");
        document.body.classList.remove("loading");
    }

    // Splash screen tidak menghalangi halaman terlalu lama.
    window.addEventListener(
        "load",
        () => {
            window.setTimeout(hideSplash, 700);
        },
        { once: true },
    );

    // Pengaman apabila halaman lambat dimuat.
    window.setTimeout(hideSplash, 3000);

    // Navigasi mobile.
    if (toggle && links) {
        toggle.addEventListener("click", () => {
            const opened = links.classList.toggle("is-open");

            toggle.setAttribute("aria-expanded", String(opened));

            toggle.setAttribute(
                "aria-label",
                opened ? "Tutup menu navigasi" : "Buka menu navigasi",
            );
        });

        // Tutup menu setelah tautan navigasi dipilih.
        function closeMobileMenu() {
            links.classList.remove("is-open");

            toggle.setAttribute("aria-expanded", "false");
            toggle.setAttribute("aria-label", "Buka menu navigasi");
        }

        links.querySelectorAll("a").forEach((link) => {
            link.addEventListener("click", closeMobileMenu);
        });

        // Tutup menu setelah tombol Masuk/Daftar dipilih.
        document.querySelectorAll(".nav-actions a").forEach((link) => {
            link.addEventListener("click", closeMobileMenu);
        });

        // Tutup menu dengan tombol Escape.
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                links.classList.remove("is-open");

                toggle.setAttribute("aria-expanded", "false");

                toggle.setAttribute("aria-label", "Buka menu navigasi");

                toggle.focus();
            }
        });
    }

    // Animasi ringkasan manfaat: muncul bertahap saat memasuki layar.
    const benefitItems = Array.from(document.querySelectorAll(".status-strip .status-item"));

    if (benefitItems.length && "IntersectionObserver" in window) {
        const benefitObserver = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                    } else {
                        entry.target.classList.remove("is-visible");
                    }
                });
            },
            { threshold: 0.2 },
        );

        benefitItems.forEach((item) => benefitObserver.observe(item));
    } else {
        benefitItems.forEach((item) => item.classList.add("is-visible"));
    }

    // Animasi kartu Cara Penggunaan saat masuk dan keluar viewport.
    // Saat scroll turun: kartu 1 dari kiri, 2 dari atas, 3 dari kanan.
    // Saat scroll naik: arah masuk dibalik.
    const stepCards = Array.from(document.querySelectorAll(".steps .step"));
    let previousScrollY = window.scrollY;

    if (stepCards.length && "IntersectionObserver" in window) {
        stepCards.forEach((card) => {
            card.classList.add("step-reveal-ready");
        });

        const stepObserver = new IntersectionObserver(
            (entries) => {
                const scrollingDown = window.scrollY >= previousScrollY;
                previousScrollY = window.scrollY;

                entries.forEach((entry) => {
                    const card = entry.target;
                    const index = stepCards.indexOf(card);

                    if (entry.isIntersecting) {
                        card.classList.remove(
                            "reveal-from-left",
                            "reveal-from-top",
                            "reveal-from-right",
                            "reveal-from-bottom",
                            "reveal-from-left-reverse",
                            "reveal-from-right-reverse",
                            "is-revealed",
                        );

                        if (scrollingDown) {
                            if (index === 0) card.classList.add("reveal-from-left");
                            if (index === 1) card.classList.add("reveal-from-top");
                            if (index === 2) card.classList.add("reveal-from-right");
                        } else {
                            if (index === 0) card.classList.add("reveal-from-left-reverse");
                            if (index === 1) card.classList.add("reveal-from-bottom");
                            if (index === 2) card.classList.add("reveal-from-right-reverse");
                        }

                        // Paksa browser membaca posisi awal sebelum menjalankan transisi.
                        void card.offsetWidth;
                        requestAnimationFrame(() => {
                            card.classList.add("is-revealed");
                        });
                    } else {
                        // Reset supaya animasi bisa diputar ulang saat pengguna kembali scroll.
                        card.classList.remove(
                            "reveal-from-left",
                            "reveal-from-top",
                            "reveal-from-right",
                            "reveal-from-bottom",
                            "reveal-from-left-reverse",
                            "reveal-from-right-reverse",
                            "is-revealed",
                        );
                    }
                });
            },
            {
                threshold: 0.18,
                rootMargin: "0px 0px -5% 0px",
            },
        );

        stepCards.forEach((card) => stepObserver.observe(card));
    } else {
        stepCards.forEach((card) => card.classList.add("is-revealed"));
    }

    // ==================================================
    // NAVBAR AKTIF: BERUBAH SAAT DIKLIK DAN SAAT SCROLL
    // Hanya mengatur Beranda, Fitur, dan Cara Kerja.
    // Tombol Masuk dan Daftar tidak diubah.
    // ==================================================
    const sectionLinks = Array.from(
        document.querySelectorAll(
            '#nav-links a[href="#beranda"], ' +
                '#nav-links a[href="#fitur"], ' +
                '#nav-links a[href="#cara-kerja"]',
        ),
    );

    const pageSections = Array.from(document.querySelectorAll("#beranda, #fitur, #cara-kerja"));

    function setActiveNav(sectionId) {
        sectionLinks.forEach((link) => {
            const isActive = link.getAttribute("href") === `#${sectionId}`;

            link.classList.toggle("active", isActive);

            if (isActive) {
                link.setAttribute("aria-current", "location");
            } else {
                link.removeAttribute("aria-current");
            }
        });
    }

    // Perbarui garis aktif saat pengguna mengeklik menu.
    sectionLinks.forEach((link) => {
        link.addEventListener("click", () => {
            setActiveNav(link.getAttribute("href").slice(1));
        });
    });

    // Perbarui garis aktif berdasarkan bagian yang sedang terlihat.
    if ("IntersectionObserver" in window && pageSections.length > 0) {
        const sectionObserver = new IntersectionObserver(
            (entries) => {
                const visibleSections = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => {
                        return b.intersectionRatio - a.intersectionRatio;
                    });

                if (visibleSections.length > 0) {
                    setActiveNav(visibleSections[0].target.id);
                }
            },
            {
                root: null,
                rootMargin: "-20% 0px -55% 0px",
                threshold: [0, 0.1, 0.25, 0.5],
            },
        );

        pageSections.forEach((section) => {
            sectionObserver.observe(section);
        });
    }

    // Kondisi awal halaman.
    setActiveNav("beranda");
})();
