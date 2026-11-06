(() => {
    const node = document.getElementById("fs-trend");
    const raw = document.getElementById("fs-trend-data");
    const summary = document.getElementById("fs-chart-summary");
    const scale = document.getElementById("fs-chart-scale");
    if (!node || !raw) return;
    let data;
    try {
        data = JSON.parse(raw.textContent);
    } catch (_) {
        data = [];
    }
    if (!Array.isArray(data) || !data.length) {
        node.classList.add("fs-chart-empty");
        node.textContent = "Belum ada data tren pada periode ini.";
        if (summary) summary.textContent = "Grafik akan tampil setelah laporan tersedia.";
        return;
    }
    // Data 90 hari dari controller dikelompokkan per 7 hari di UI.
    // Backend, filter, dan ekspor tetap tidak berubah.
    const period = raw.dataset.period;
    const source = data.map((item) => ({
        label: String(item.label || ""),
        lost: Math.max(0, Number(item.lost) || 0),
        found: Math.max(0, Number(item.found) || 0),
    }));
    let buckets = source;
    let granularity = period === "365" ? "bulanan" : "harian";
    if (period === "90") {
        granularity = "mingguan";
        buckets = [];
        for (let i = 0; i < source.length; i += 7) {
            const part = source.slice(i, i + 7);
            buckets.push({
                label: part[0].label + "–" + part[part.length - 1].label,
                lost: part.reduce((n, x) => n + x.lost, 0),
                found: part.reduce((n, x) => n + x.found, 0),
            });
        }
    }
    const total = source.reduce((a, x) => ({ lost: a.lost + x.lost, found: a.found + x.found }), {
        lost: 0,
        found: 0,
    });
    const peak = Math.max(1, ...buckets.map((x) => Math.max(x.lost, x.found)));
    // Integer ticks, with at least 1 as the upper bound.
    const top = peak <= 4 ? 4 : Math.ceil(peak / 4) * 4;
    if (scale)
        scale.replaceChildren(
            ...[4, 3, 2, 1, 0].map((i) => {
                const span = document.createElement("span");
                span.textContent = String((top * i) / 4);
                return span;
            }),
        );
    const active = buckets.filter((x) => x.lost || x.found).length;
    if (summary)
        summary.textContent = `Total pada tren ${granularity}: ${total.lost} hilang dan ${total.found} ditemukan. ${active === 0 ? "Belum ada aktivitas pada periode ini." : active <= 2 ? "Aktivitas masih rendah pada periode ini." : "Arahkan kursor atau sentuh batang untuk rincian."}`;
    node.style.setProperty("--fs-items", String(buckets.length));
    node.classList.toggle("fs-chart-compact", buckets.length <= 14);
    const fragment = document.createDocumentFragment();
    const step = buckets.length > 25 ? 5 : buckets.length > 14 ? 3 : buckets.length > 9 ? 2 : 1;
    buckets.forEach((entry, i) => {
        const group = document.createElement("div");
        group.className = "fs-chart-group";
        const bars = document.createElement("div");
        bars.className = "fs-bars";
        [
            ["lost", "Hilang"],
            ["found", "Ditemukan"],
        ].forEach(([key, label]) => {
            const value = entry[key];
            const bar = document.createElement("div");
            bar.className = "fs-bar " + key + (value ? "" : " is-zero");
            bar.style.height = value ? (value / top) * 100 + "%" : "2px";
            bar.title = `${label}: ${value} laporan (${entry.label})`;
            bar.setAttribute("aria-label", bar.title);
            bars.appendChild(bar);
        });
        group.appendChild(bars);
        const caption = document.createElement("small");
        caption.textContent = i % step === 0 || i === buckets.length - 1 ? entry.label : "";
        group.appendChild(caption);
        fragment.appendChild(group);
    });
    node.replaceChildren(fragment);
})();
