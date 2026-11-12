(() => {
function mount(signal) {
    const source = document.getElementById("fb-analytics-json");
    if (!source) return;
    let d;
    try {
        d = JSON.parse(source.textContent);
    } catch {
        return;
    }
    const donut = document.getElementById("fb-report-donut");
    if (donut && d.available && Number(d.lost) + Number(d.found) > 0) {
        const p = (Number(d.lost) / (Number(d.lost) + Number(d.found))) * 100;
        donut.style.background = `conic-gradient(#a21842 0 ${p}%, #24684a ${p}% 100%)`;
    }
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
                fill: "#a08a95",
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
            const c = add("circle", {
                cx: xx,
                cy: yy,
                r: 4,
                fill: color,
                stroke: "#fff",
                "stroke-width": 2,
            });
            const t = document.createElementNS(ns, "title");
            t.textContent = `${labels[i]}: ${values[i]}`;
            c.appendChild(t);
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
                fill: "#9b8792",
                "font-size": 10,
            },
            label,
        ),
    );
    node.replaceChildren(svg);
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
