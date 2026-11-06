(function () {
    var wrap = document.getElementById("fb-v27-choice");
    if (!wrap) return;
    var radios = Array.prototype.slice.call(wrap.querySelectorAll('input[name="fb_v27_choice"]'));
    var panels = Array.prototype.slice.call(wrap.querySelectorAll("[data-fb-v27-panel]"));
    function activate(value) {
        radios.forEach(function (r) {
            r.checked = r.value === value;
        });
        panels.forEach(function (p) {
            var on = p.getAttribute("data-fb-v27-panel") === value;
            p.hidden = !on;
            var fields = p.querySelectorAll("textarea");
            fields.forEach(function (f) {
                f.required = on;
                f.disabled = !on;
            });
        });
    }
    radios.forEach(function (r) {
        r.addEventListener("change", function () {
            activate(r.value);
        });
    });
    var initial = wrap.getAttribute("data-default") || "verified";
    if (
        !radios.some(function (r) {
            return r.value === initial;
        })
    )
        initial = "verified";
    activate(initial);
})();
