# Pemeriksaan dan bukti UTS

Status penyerahan: **siap ditinjau secara lokal**. Seluruh 50 butir Delivery Gate lulus; tidak ada temuan terbuka dalam cakupan pemeriksaan.

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
| Versi | Sumber dibandingkan dengan paket awal dan checkout publik; checkout publik tetap bersih dan tidak diubah | source-comparison.json |
| PDF dan paket | Hasil inspeksi PDF terikat pada hash; ZIP diekstrak, CRC/rahasia/kesamaan sumber diperiksa; SHA-256 tersedia pada manifest | pdf-review.json; package-checks.json; manifest-paket.json |

## Perbaikan yang diselesaikan

Saya menyatukan pemeriksaan pertemuan, insert, dan audit dalam transaksi yang mengunci kelas. Perlindungan admin terakhir dipindahkan ke transaksi dengan penguncian akun terurut. Jadwal yang sudah dipakai oleh reservasi terlindungi saat penghapusan. Regresi dan pengujian dua proses menyertai perbaikan tersebut.

Halaman / menjadi halaman publik bernama home. Dashboard dan tindakan akademik tetap dibatasi autentikasi serta peran. Tampilan memakai Syne lokal pada seluruh teks, angka, tabel, dan kontrol; Source Sans 3 dan Source Serif 4 dihapus dari dependensi serta build. Tombol aksi menggunakan SVG dengan aria-label, tooltip, dan nama untuk pembaca layar. Navigasi mempertahankan nama halaman agar orientasi tetap jelas.

Laporan ditulis sebagai tindakan saya secara formal. Format mengikuti skripsi S1: Times New Roman 12 pt, A4, spasi 1,5, margin kiri 3,5 cm dan sisi lainnya 3 cm; enam bab, daftar otomatis, caption, ERD, kamus data, dan cuplikan kode sesuai implementasi final. Screenshot diambil ulang setelah revisi font.

## Metode dan batas pemeriksaan

Lebar utama: 320, 390, 768, 1024, 1440, 1920 CSS pixel. Ruang layout setara zoom 125%, 150%, 200% dari lebar 1920 diuji pada 1536, 1280, 960; menu zoom browser tidak diubah. Kesalahan awal target viewport ditemukan, diperbaiki, lalu seluruh matriks diulang dengan assertion ukuran aktual. Hanya label verified- menjadi bukti UI akhir; catatan sebelumnya disimpan sebagai riwayat pemeriksaan.

Tiga reload cache hangat pada 1440 x 1000 tanpa throttling menghasilkan durasi API 233, 204, 181 ms, seperti dicatat pada laporan. Pemeriksaan ulang build final menghasilkan 216, 235, 188 ms dengan DOM selesai dan Syne dimuat; keduanya dicatat terpisah pada home-performance.json. Durasi mencakup komunikasi alat, bukan Navigation Timing, Lighthouse, atau Core Web Vitals. Pengujian cetak mencakup kontrol, handler, dan stylesheet A4; printer fisik tidak digunakan. Konfirmasi diperiksa melalui browser nyata. Pengiriman berulang dan fallback juga diuji dengan mengeksekusi sumber JavaScript pada VM yang terkontrol. Pemeriksaan akun tidak mengubah kata sandi pengguna; tindakan CRUD berulang per baris memakai handler bersama yang sama.

Database utama dilindungi backup offline sebelum audit. Tes, impor SQL, dan replay memakai database khusus. Instalasi replay memakai drive pendek yang dipetakan ke hasil ekstraksi lokal untuk menghindari batas path Windows. Kredensial, .env aktif, Runtime, vendor, dan node_modules tidak disertakan dalam ZIP; instalasi memasang dependensi dari lockfile.

Paket ini untuk tinjauan lokal. Unggah LMS, push GitHub, deployment Vercel, pengujian beban produksi, serta audit penetrasi produksi tidak dilakukan. Kelulusan berlaku pada skenario dan lingkungan yang dicatat; tidak ada temuan terbuka dalam cakupan tersebut.
