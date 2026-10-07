# Pemeriksaan dan bukti UTS

Status penyerahan: **aplikasi produksi Ready; paket lokal siap ditinjau**. Seluruh 50 butir Delivery Gate lulus; tidak ada temuan terbuka dalam cakupan pemeriksaan.

Akhyar · 260820701100010 · Kelas A. Audit final: 6-7 Oktober 2026. Acuan adalah enam bagian soal UTS asli; database, ERD, SQL, dan normalisasi berbobot 40%. Pemetaan lengkap ada pada MATRIKS_UTS.md.

## Hasil yang diperiksa

| Bagian | Hasil | Bukti |
|---|---|---|
| Laravel dari ZIP terpasang | 35 tes, 285 assertion; tidak ada kegagalan | phpunit-final-20261007.xml |
| Transaksi bersamaan | Kursi terakhir, nomor pertemuan, admin terakhir: tiga skenario lulus | concurrency.json |
| Skema dan SQL | 18 tabel ekspor, enam trigger, 23 FK; skema utama/replay/impor konsisten | schema-audit.json; sql-import.json |
| Normalisasi dan perhitungan | UNF-3NF sesuai skema; tujuh pengambilan Akhyar menjadi 28 baris JOIN; nilai kosong dipertahankan; IPS 3,75 dan IPK 3,55 sesuai perhitungan manual | Bab IV; database.json; manual-calculations.json |
| Demonstrasi tiga peran | KRS dikembalikan, direvisi, disetujui; presensi Hadir; nilai kosong ditolak; hasil 88,50/A diterbitkan, dikoreksi, lalu terlihat pada KHS | replay.json; browser-flow.txt; screenshots/04-10 |
| UI | 64 target halaman, sembilan lebar, dua tema: 1.152 keadaan; ukuran render dicocokkan dengan ukuran yang diminta | ui-final-summary.json; qa-ui-20261006.json, label verified- |
| Kontras | 128 pasangan halaman/tema, 6.972 sampel teks; minimum 5,187:1; seluruh sampel memenuhi AA | contrast.json; contrast-rendered-final.json |
| Kontrol master dan dialog | 11 keluarga kontrol diuji pada browser; Cancel/Escape, fokus Tab/Shift+Tab, penolakan hapus berelasi dan CRUD ruang uji tanpa relasi lulus; 18 keadaan layout dialog dan dua tema kontras lulus | master-controls-browser.json; dialog-browser.json; contrast-dialog.json |
| Keyboard dan ketahanan | Fokus menu/Escape, gulir tabel, password, validasi; storage gagal, pengurangan gerak, confirmation/busy/repeated-submit/pageshow diperiksa | ui-interactions.json; ui-resilience.json; verify-ui.mjs |
| Aset dan console | Preview tiga peran dimuat; tidak ada pesan error/warn pada pencatatan browser final | ui-interactions-final.json; browser-console.json |
| Dependensi | npm dan Composer tanpa advisory setelah perbaikan shell-quote 1.11.0 | npm-audit.json; composer-audit.json |
| Persistensi | Hash 17 tabel akademik/audit tetap sama sesudah restart layanan | persistence.json |
| Versi | Riwayat tiga commit lama dipertahankan; sumber aplikasi final disinkronkan ke repo dan diuji pada preview sebelum produksi | deployment-final-20261007.json; source-comparison.json |
| PDF dan paket | Hasil inspeksi PDF terikat pada hash; ZIP diekstrak, CRC/rahasia/kesamaan sumber diperiksa; SHA-256 tersedia pada manifest | pdf-review.json; package-checks.json; manifest-paket.json |

## Perbaikan yang diselesaikan

Saya menyatukan pemeriksaan pertemuan, insert, dan audit dalam transaksi yang mengunci kelas. Perlindungan admin terakhir dipindahkan ke transaksi dengan penguncian akun terurut. Jadwal yang sudah dipakai oleh reservasi terlindungi saat penghapusan. Regresi dan pengujian dua proses menyertai perbaikan tersebut.

Halaman / menjadi halaman publik bernama home. Dashboard dan tindakan akademik tetap dibatasi autentikasi serta peran. Tampilan memakai Syne lokal pada seluruh teks, angka, tabel, dan kontrol; Source Sans 3 dan Source Serif 4 dihapus dari dependensi serta build. Tombol aksi menggunakan SVG dengan aria-label, tooltip, dan nama untuk pembaca layar. Navigasi mempertahankan nama halaman agar orientasi tetap jelas.

Laporan ditulis sebagai tindakan saya secara formal. Format mengikuti skripsi S1: Times New Roman 12 pt, A4, spasi 1,5, margin kiri 3,5 cm dan sisi lainnya 3 cm; enam bab, daftar otomatis, caption, ERD, kamus data, dan cuplikan kode sesuai implementasi final. Screenshot diambil ulang setelah revisi font.

## Metode dan batas pemeriksaan

Lebar utama: 320, 390, 768, 1024, 1440, 1920 CSS pixel. Ruang layout setara zoom 125%, 150%, 200% dari lebar 1920 diuji pada 1536, 1280, 960; menu zoom browser tidak diubah. Kesalahan awal target viewport ditemukan, diperbaiki, lalu seluruh matriks diulang dengan assertion ukuran aktual. Hanya label verified- menjadi bukti UI akhir; catatan sebelumnya disimpan sebagai riwayat pemeriksaan.

Tiga reload cache hangat pada 1440 x 1000 tanpa throttling menghasilkan durasi API 233, 204, 181 ms, seperti dicatat pada laporan. Pemeriksaan ulang build final menghasilkan 216, 235, 188 ms dengan DOM selesai dan Syne dimuat; keduanya dicatat terpisah pada home-performance.json. Durasi mencakup komunikasi alat, bukan Navigation Timing, Lighthouse, atau Core Web Vitals. Pengujian cetak mencakup kontrol, handler, dan stylesheet A4; printer fisik tidak digunakan. Konfirmasi diperiksa melalui browser nyata. Pengiriman berulang dan fallback juga diuji dengan mengeksekusi sumber JavaScript pada VM yang terkontrol. Pemeriksaan akun tidak mengubah kata sandi pengguna; tindakan CRUD berulang per baris memakai handler bersama yang sama.

Database utama dilindungi backup offline sebelum audit. Tes, impor SQL, dan replay memakai database khusus. Instalasi replay memakai drive pendek yang dipetakan ke hasil ekstraksi lokal untuk menghindari batas path Windows. Kredensial, .env aktif, Runtime, vendor, dan node_modules tidak disertakan dalam ZIP; instalasi memasang dependensi dari lockfile.

Paket disediakan untuk tinjauan lokal. Repo GitHub diperbarui dan aplikasi telah diterapkan pada Vercel. Unggah LMS, pengujian beban, serta audit penetrasi khusus tidak dilakukan. Kelulusan berlaku pada skenario dan lingkungan yang dicatat; tidak ada temuan terbuka dalam cakupan tersebut.

## Finalisasi deployment dan UI online, 7 Oktober 2026

Produksi: https://uts-sistem-informasi-perkuliahan.vercel.app/ . PHP 8.5.2 melalui vercel-php@0.9.0, Node 22.x, Laravel 13.34.0, Aiven MySQL 8.4.8 gratis di Bangalore; region fungsi bom1. Production/preview/test terpisah, TLS dengan CA terverifikasi, akun aplikasi DML pada database masing-masing. Kredensial disimpan di Runtime pribadi.

| Pemeriksaan tambahan | Hasil | Bukti |
|---|---|---|
| Suite final MySQL 8.4 | 39 tes, 318 asersi, nol error/kegagalan; kemudian tiga konkurensi lulus | phpunit-aiven-final-20261007.xml; concurrency-aiven-final-20261007.json |
| Skema dan TLS | 26 tabel berjalan, 18 domain, 23 FK, 13 CHECK, enam trigger; metadata sama, CA salah/DDL/akses lintas database ditolak | schema-aiven-20261007.json |
| HTTP preview/produksi | 28 kontrol per lingkungan; status dan isi respons diperiksa | http-preview-20261007.json; http-production-20261007.json |
| Alur produksi | Intan: KRS dikembalikan/diajukan ulang/disetujui; presensi Hadir; nilai 0 dibedakan dari kosong; publikasi ditolak sebelum lengkap; koreksi UAS 96 menghasilkan 88,90/A, 3 SKS, IPS/IPK 4,00; CSV aktual cocok | workflow-production-20261007.json; khs-production-intan.csv |
| Persistensi online | Sesi dan catatan studi tetap ada setelah redeployment; adapter memakai sesi database setelah proses PHP baru | ui-navigation-final-20261007.json; vercel-adapter-local-20261007.json |
| UI online | 963 keadaan audit online dan 90 pemeriksaan tambahan setelah animasi final; kedua tema, enam lebar utama dan tiga lebar setara zoom; tidak ada overflow halaman, tombol tanpa nama, atau gambar terlihat gagal | ui-online-20261007.json; ui-navigation-final-20261007.json |
| Navigasi | Sticky header, jarak anchor, scroll halus, perpindahan halaman, menu ponsel, Shift+Tab/Escape; console final tanpa error/warn pada pencatatan tab baru | ui-navigation-final-20261007.json; browser-console-online-20261007.json |
| Screenshot | Capture produksi asli dengan akun simulasi; login tanpa kredensial; home dan mobile final disimpan terpisah | screenshots-online/ |

Tiga reload online cache hangat pada 1440×1000: 747, 706, 704 ms. Ini durasi komunikasi alat, bukan Core Web Vitals. Pemeriksaan zoom memakai lebar CSS setara; zoom menu browser tidak diubah. Cetak diperiksa melalui kontrol, handler native, dan stylesheet A4; printer fisik tidak digunakan. Gangguan DNS/koneksi pada percobaan awal suite ditangani dengan verifikasi jaringan dan menjalankan ulang suite utuh; TLS tidak dinonaktifkan. Laporan hasil gagal awal disimpan sebagai catatan pribadi, bukan hasil lulus final.

Temuan runtime root/document root, respons 404 yang masih membawa PHP, dan error transisi lintas dokumen telah diperbaiki pada bagian bersama. Pengujian final memeriksa isi respons serta navigasi kembali. Tidak ada temuan terbuka dalam cakupan yang dicatat. Aiven gratis mempunyai batas satu CPU/1 GB RAM/1 GB disk/76 koneksi, kemungkinan dimatikan saat tidak aktif, dan tanpa SLA. Tidak ada layanan berbayar yang diaktifkan.
