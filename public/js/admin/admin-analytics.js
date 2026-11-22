(() => {
function mountDonut(data, signal) {
    const donut = document.getElementById("fb-report-donut");
    const total = Number(data.lost) + Number(data.found);
    if (!donut || !data.available || !(total > 0)) return;

    const center = donut.querySelector(".fb-donut-center");
    const count = center.querySelector("strong");
    const caption = center.querySelector("small");
    const legend = donut.parentElement.querySelectorAll(".fb-dist-legend > div");
    const ns = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(ns, "svg");
    svg.setAttribute("viewBox", "0 0 120 120");
    svg.setAttribute("aria-hidden", "true");
    svg.setAttribute("class", "fb-donut-segments");
    const format = new Intl.NumberFormat("id-ID");

    const slices = [
        { label: "Barang Hilang", amount: Number(data.lost), color: "#a21842" },
        { label: "Barang Ditemukan", amount: Number(data.found), color: "#24684a" },
    ];
    const arcs = [];
    const hits = [];
    const select = (index) => {
        arcs.forEach((arc, i) => arc.classList.toggle("is-highlighted", i === index));
        legend.forEach((row, i) => row.classList.toggle("is-highlighted", i === index));
        const slice = slices[index];
        count.textContent = format.format(slice ? slice.amount : total);
        caption.textContent = slice
            ? `${slice.label} · ${format.format(Math.round(100 * slice.amount / total))}%`
            : "Total laporan";
    };
    const bind = (element, index) => {
        element.addEventListener("pointerenter", () => select(index), { signal });
        element.addEventListener("pointerleave", () => {
            if (document.activeElement !== element) select(-1);
        }, { signal });
        element.addEventListener("click", () => select(index), { signal });
        element.addEventListener("focus", () => select(index), { signal });
        element.addEventListener("blur", () => select(-1), { signal });
        element.addEventListener("keydown", (event) => {
            if (event.key === "Escape") select(-1);
        }, { signal });
    };

    let offset = 0;
    slices.forEach((slice, i) => {
        if (slice.amount <= 0) return;
        const share = 100 * slice.amount / total;
        const arc = document.createElementNS(ns, "circle");
        const attrs = {
            cx: 60, cy: 60, r: 51, pathLength: 100, fill: "none",
            stroke: slice.color, "stroke-width": 18,
            "stroke-dasharray": `${share} ${100 - share}`,
            "stroke-dashoffset": -offset,
        };
        Object.entries(attrs).forEach(([key, value]) => arc.setAttribute(key, value));
        arc.setAttribute("class", "fb-donut-arc");
        arcs[i] = arc;
        const hit = arc.cloneNode();
        hit.setAttribute("class", "fb-donut-arc-hit");
        hit.setAttribute("stroke", "transparent");
        hits.push(hit);
        bind(hit, i);

        const row = legend[i];
        if (row) {
            row.tabIndex = 0;
            bind(row, i);
        }
        offset += share;
    });
    svg.append(...arcs.filter(Boolean), ...hits);
    donut.querySelector(".fb-donut-segments")?.remove();
    donut.prepend(svg);
    donut.classList.add("fb-donut-interactive");
    select(-1);
}

function mount(signal) {
    const source = document.getElementById("fb-analytics-json");
    if (!source) return;
    let d;
    try {
        d = JSON.parse(source.textContent);
    } catch {
        return;
    }
    mountDonut(d, signal);
    const node = document.getElementById("fb-trend-chart");
    if (!node) return;
    const ns = "http://www.w3.org/2000/svg";
    const svg = document.createElementNS(ns, "svg");
    svg.setAttribute("viewBox", "0 0 700 240");
    svg.setAttribute("preserveAspectRatio", "none");
    const add = (tag, attrs, value) => {
        const e = document.createElementNS(ns, tag);
        Object.entries(attrs).forEach(([k, v]) => e.setAttribute(k, String(v)));
        if (value !== undefined) e.textContent = value;
        svg.appendChild(e);
        return e;
    };
    const labels = Array.isArray(d.labels) ? d.labels : [];
    const lost = Array.isArray(d.lostTrend) ? d.lostTrend.map(Number) : [];
    const found = Array.isArray(d.foundTrend) ? d.foundTrend.map(Number) : [];
    const valid =
        d.available &&
        labels.length > 0 &&
        labels.length === lost.length &&
        labels.length === found.length;
    if (!valid) {
        node.innerHTML =
            '<div style="height:100%;display:grid;place-items:center;text-align:center;color:#9b8792;font-size:12px;padding:20px">Grafik akan tersedia setelah data laporan barang terhubung.</div>';
        return;
    }
    const max = Math.max(1, ...lost, ...found);
    const tickStep = Math.max(1, Math.ceil(max / 4));
    const axisMax = tickStep * 4;
    const x = (i) => 42 + (i / (labels.length - 1 || 1)) * 636;
    const y = (v) => 195 - (v / axisMax) * 157;
    for (let j = 0; j < 5; j++) {
        const yy = 195 - j * 39.25;
        add("line", {
            x1: 42,
            y1: yy,
            x2: 678,
            y2: yy,
            stroke: "#f1e7ec",
            "stroke-width": 1,
        });
        add(
            "text",
            {
                x: 32,
                y: yy + 4,
                "text-anchor": "end",
                fill: "#715866",
                "font-size": 10,
            },
            String(tickStep * j),
        );
    }
    const draw = (values, color) => {
        const points = values.map((v, i) => [x(i), y(v)]);
        const line = points.map(([xx, yy], i) => `${i ? "L" : "M"}${xx} ${yy}`).join(" ");
        const area = line + ` L${points.at(-1)[0]} 195 L${points[0][0]} 195 Z`;
        add("path", { d: area, fill: color, "fill-opacity": ".075" });
        add("path", {
            d: line,
            fill: "none",
            stroke: color,
            "stroke-width": 3,
            "stroke-linecap": "round",
            "stroke-linejoin": "round",
        });
        points.forEach(([xx, yy], i) => {
            add("circle", {
                cx: xx,
                cy: yy,
                r: 4,
                fill: color,
                stroke: "#fff",
                "stroke-width": 2,
            });

        });
    };
    draw(lost, "#a21842");
    draw(found, "#24684a");
    labels.forEach((label, i) =>
        add(
            "text",
            {
                x: x(i),
                y: 226,
                "text-anchor": "middle",
                fill: "#715866",
                "font-size": 10,
            },
            label,
        ),
    );
    const guide = add("line", {
        class: "fb-trend-guide", y1: 38, y2: 195, visibility: "hidden",
    });
    const tooltip = document.createElement("div");
    tooltip.className = "fb-trend-tooltip";
    tooltip.setAttribute("aria-hidden", "true");
    const date = document.createElement("strong");
    const lostText = document.createElement("span");
    const foundText = document.createElement("span");
    tooltip.append(date, lostText, foundText);

    const hide = () => {
        guide.setAttribute("visibility", "hidden");
        tooltip.classList.remove("is-visible");
    };
    labels.forEach((label, i) => {
        const xx = x(i);
        const left = i === 0 ? 0 : (x(i - 1) + xx) / 2;
        const right = i === labels.length - 1 ? 700 : (xx + x(i + 1)) / 2;
        const name = `${label}, ${lost[i]} barang hilang, ${found[i]} barang ditemukan`;
        const hit = add("rect", {
            x: left, y: 26, width: right - left, height: 180,
            class: "fb-trend-hit", tabindex: 0, "aria-label": name, fill: "transparent",
        });
        const show = () => {
            date.textContent = label;
            lostText.textContent = `Barang hilang · ${lost[i]}`;
            foundText.textContent = `Barang ditemukan · ${found[i]}`;
            tooltip.style.setProperty("--fb-trend-x", `${xx / 7}%`);
            guide.setAttribute("x1", xx);
            guide.setAttribute("x2", xx);
            guide.setAttribute("visibility", "visible");
            tooltip.classList.add("is-visible");
        };
        hit.addEventListener("pointerenter", show, { signal });
        hit.addEventListener("pointerleave", () => {
            if (document.activeElement !== hit) hide();
        }, { signal });
        hit.addEventListener("click", show, { signal });
        hit.addEventListener("focus", show, { signal });
        hit.addEventListener("blur", hide, { signal });
        hit.addEventListener("keydown", (event) => {
            if (event.key === "Escape") hide();
        }, { signal });
    });
    node.replaceChildren(svg, tooltip);
}

    let controller;
    function render() {
        controller?.abort();
        controller = new AbortController();
        mount(controller.signal);
    }
    document.addEventListener('fb:admin-page', render);
    render();
})();
