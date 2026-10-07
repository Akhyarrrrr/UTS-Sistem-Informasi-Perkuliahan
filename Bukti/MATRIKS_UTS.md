# Matriks persyaratan UTS

Akhyar, 260820701100010, Kelas A. Acuan: PDF soal UTS asli dua halaman. Audit berlangsung 6–7 Oktober 2026. Enam bab dipertahankan; desain database, ERD, SQL, dan normalisasi mendapat penjelasan terbesar sesuai bobot 40%.

| Persyaratan | Implementasi final | Bagian laporan | Bukti pemeriksaan |
|---|---|---|---|
| Komputer dan web server | PHP 8.5.0, Laravel 13.34.0, MySQL 8.0.46; localhost:8088 dan port MySQL 3319 | Bab I | spesifikasi.json; konfigurasi layanan; persistence.json |
| Pemrograman web | Blade, autentikasi sesi, tiga peran, 11 master CRUD, query berparameter, validasi, CSRF, audit | Bab II | routes/web.php; controller/services; phpunit-final-20261007.xml; csrf.json |
| Database server | 16 tabel akademik + users + activity_log = 18 tabel ekspor; 8 tabel infrastruktur, total database 26 | Bab III | migration; SQL/struktur.sql; database.json; schema-audit.json |
| Database relasional, ERD, SQL, normalisasi | PK/FK/UNIQUE/CHECK dan enam trigger; ERD dua kelompok; kandidat kunci dan FD; UNF–3NF; rekonstruksi JOIN; SELECT/UPDATE/DELETE | Bab IV | kamus data dihasilkan dari MySQL; schema-audit.json; sql-import.json; manual-calculations.json; SQL/query-pembuktian.sql |
| Implementasi lokal | Instalasi dari lockfile, database khusus pengujian/replay, layanan lokal, backup demonstrasi, paket sumber | Bab V | README.md; install.ps1; replay.json; phpunit-final-20261007.xml; concurrency.json; manifest-paket.json |
| Hasil aplikasi | KRS → pengembalian/revisi/persetujuan → pertemuan/presensi → nilai/publikasi/koreksi → KHS/IPS/IPK/CSV/cetak | Bab VI | browser-flow.txt; screenshots/00-14; khs-browser.csv; ui-controls-final.json; ui-final-summary.json |

## Penelusuran bagian berisiko

| Pemeriksaan | Hasil dan tindakan | Bukti |
|---|---|---|
| Semua kolom, NULL/default, indeks, FK/CHECK dan trigger | 18 tabel dan enam trigger konsisten antara migration/replay, MySQL utama, dan ekspor SQL. Counter AUTO_INCREMENT bukan perbedaan skema. | schema-audit.json |
| Kunci kandidat dan ketergantungan | Rekap memakai (NPM, periode, kode MK, nama komponen); kode kelas melengkapi kunci pada penawaran. Identitas/master dipisah dari penawaran, pengambilan dan nilai. | Bab IV; struktur.sql |
| Rekonstruksi tanpa kehilangan nilai kosong | JOIN menghasilkan 28 baris dari tujuh pengambilan Akhyar, empat komponen tiap kelas; LEFT JOIN mempertahankan NULL. | database.json; manual-calculations.json; sql-import.json |
| CRUD 11 master dan penghapusan berelasi | Tambah/ubah/hapus data bebas relasi diuji; FK RESTRICT dan guard melindungi catatan yang digunakan. | PHPUnit, tes feature |
| Manipulasi ID dan akses antarakun | Kelas dosen, KRS/KHS/presensi mahasiswa, ekspor, dan master dibatasi server. | PHPUnit; browser 403 dalam matriks UI |
| Login berulang, CSRF, rahasia | Percobaan login dibatasi; password gagal tidak di-flash; request tanpa CSRF ditolak 419; runtime/kredensial tidak masuk paket. | PHPUnit; csrf.json; pemeriksaan paket |
| Kursi terakhir | Dua proses: satu diterima dan satu ditolak, dua reservasi akhir pada kapasitas dua. | concurrency.json |
| Pertemuan ganda | Temuan race diperbaiki dengan lock kelas di dalam transaksi; dua proses menghasilkan satu pertemuan dan satu audit. | AcademicController; PHPUnit; concurrency.json |
| Dua admin menghapus satu sama lain | Temuan race diperbaiki dengan lock akun terurut dan pemeriksaan admin terakhir di dalam transaksi; tersisa satu admin. | MasterController; concurrency.json |
| KRS dan benturan jadwal | Draf tidak mereservasi; diajukan/disetujui mereservasi; pengembalian melepaskan kursi. Prodi, periode, SKS, mata kuliah ganda, jadwal dan kapasitas divalidasi. | PHPUnit; browser-flow.txt |
| Nilai NULL/nol, grade, publikasi dan koreksi | Semua batas grade dan nilai tepat di bawah batas diuji. Nilai lengkap diperlukan untuk publikasi; koreksi wajib beralasan; bobot terkunci setelah publikasi pertama. | PHPUnit; browser-flow.txt |
| IPS/IPK dan pengambilan ulang | Akhyar: IPS parsial 3,75 dari 8 SKS, IPK 3,55 dengan pengambilan terbaru yang terbit. Raka: 88,50/A dan IPS/IPK 4,00. | manual-calculations.json; KHS; CSV |
| UI akhir | 64 halaman × 9 lebar × 2 tema = 1.152 kombinasi. Tabel/diagram punya gulir internal; seluruh halaman tidak overflow. | ui-final-summary.json; qa-ui-20261006.json |
| Font dan tombol | Syne geometris lokal pada semua elemen web; hierarki melalui ukuran dan bobot. Tombol aksi memakai SVG dan nama aksesibel. | DESIGN.md; resources; lisensi font; screenshots |
| Kontras, keyboard, gerak dan storage | 128 render terang/gelap; semua sampel teks memenuhi AA. Menu/fokus/scroll keyboard diuji browser; dialog Batal/Escape/fokus/Tab diperiksa pada browser; fallback dan busy juga diperiksa pada VM. | contrast.json; ui-interactions.json; ui-interactions-final.json; dialog-browser.json; master-controls-browser.json; ui-resilience.json |
| Dependensi | shell-quote rentan melalui concurrently diperbaiki dengan override 1.11.0 dan lockfile; audit npm/Composer tanpa advisory, build berhasil. | npm-audit-before.json; npm-audit.json; composer-audit.json |
| Versi sumber dan publik | Sumber kerja dibandingkan dengan paket awal dan salinan publik secara baca-saja; perubahan final dipublikasikan melalui commit baru; tiga commit lama tetap dipertahankan. | source-comparison.json |

Pemeriksaan zoom memakai ruang layout CSS yang setara 125%, 150%, dan 200% dari lebar 1920 (1536/1280/960), bukan menu zoom browser. Pengukuran tiga reload adalah durasi API termasuk komunikasi alat, bukan Lighthouse atau Core Web Vitals. Pengujian cetak mencakup kontrol, handler dan stylesheet; printer fisik tidak digunakan. Klaim kelulusan berlaku pada skenario yang dicatat. Unggah LMS berada di luar pelaksanaan ini. Repo dan deployment Vercel telah diperbarui; bukti online dicatat terpisah dari baseline lokal.

| Deployment dan polish online | HTTPS, login pribadi, TLS Aiven, database terpisah, source-path guard, sticky header, copy akses jelas, scroll halus dan animasi masuk native; seluruh temuan online ditangani. | deployment-final-20261007.json; http-production-20261007.json; ui-navigation-final-20261007.json; workflow-production-20261007.json |
