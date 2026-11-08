document.getElementById("report-revision-form")?.addEventListener("submit", function () {
    var b = this.querySelector("button[type=submit]");
    b.disabled = true;
    b.textContent = "Mengirim perbaikan…";
});
window.addEventListener("pageshow", function () {
    var b = document.querySelector("#report-revision-form button");
    if (b) {
        b.disabled = false;
        b.textContent = "Kirim Ulang untuk Pemeriksaan";
    }
});
