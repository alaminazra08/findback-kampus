# Tahap 2 — Audit Statis CSS, JS, dan AJAX

Jalankan `python tools/findback-audit-deep.py --project <folder-proyek> --output <laporan.json>`.

Skrip **read-only** menginventaris selector CSS yang muncul lintas file, kelompok aset identik berdasarkan SHA256, indikator event listener/navigasi JavaScript, serta aset tanpa referensi literal pada Blade. Hasil adalah **kandidat pemeriksaan manual**, bukan diagnosis konflik, kode mati, atau bug.

Batasan: parser CSS berbasis heuristik, tidak mengeksekusi JavaScript, tidak menelusuri seluruh pemanggilan dinamis/Vite, tidak membuka browser, tidak menguji route/database. Lakukan review urutan stylesheet, event delegation, AJAX back/forward, serta uji screenshot mobile dan desktop sebelum menggabungkan atau menghapus file.

Installer menyimpan laporan JSON di folder backup terpisah, tidak melakukan commit, push, pull, reset, migrasi, atau perubahan aset produksi.
