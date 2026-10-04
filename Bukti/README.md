# Bukti pengujian aplikasi

Pemeriksaan aplikasi dilakukan pada **4 Oktober 2026**. Berkas berikut berasal dari pengujian versi final dan paket hasil ekstraksi; tanggal pelaksanaan asli dipertahankan.

| Pemeriksaan | Hasil | Bukti |
|---|---|---|
| Laravel/PHPUnit | 32 tes lulus, 248 assertion | [phpunit.xml](phpunit.xml) |
| Kursi terakhir | Dua proses: satu diterima, satu ditolak; reservasi akhir 2 dari kapasitas 2 | [concurrency.json](concurrency.json) |
| CSRF | POST tanpa token ditolak dengan HTTP 419 | [csrf.json](csrf.json) |
| Nilai dan IPS/IPK | MPD301 88,50/A; MPE302 75,00/B; IPS parsial Akhyar 3,75 dan IPK 3,55 | [manual-calculations.json](manual-calculations.json) |
| Snapshot SQL | 18 tabel, 6 trigger, 23 FK, jumlah baris cocok, tanpa baris yatim | [sql-import.json](sql-import.json) |
| Rekonstruksi relasional | Query JOIN, kamus kolom, jumlah baris, dan keluaran SQL | [database.json](database.json) |
| Persistensi | Hash catatan akademik sebelum dan sesudah restart sama | [persistence.json](persistence.json) |
| Antarmuka | Desktop, tablet, ponsel, layout setara zoom 125%/150%, kontras AA pada pasangan yang diperiksa, dan keyboard menu | [qa-ui.json](qa-ui.json) |
| Pemasangan ulang | Dependensi, migration/seeder, build, dan alur ketiga peran lulus pada database replay | [replay.json](replay.json) |
| Ekspor KHS | CSV benar-benar diunduh dari browser | [khs-browser.csv](khs-browser.csv) |
| KHS demonstrasi | Nilai Raka 88,50/A dan IPS 4,00 | [replay-khs.jpg](replay-khs.jpg) |
| Lingkungan | Spesifikasi komputer dan versi software | [spesifikasi.json](spesifikasi.json) |

Tes mencakup CRUD seluruh 11 jenis master; login/logout; akses peran dan manipulasi ID; penghapusan berelasi; aturan KRS; nilai kosong dan rentang nilai; ambang grade; bobot dan publikasi; koreksi dengan alasan; pengulangan mata kuliah; presensi; serta perlindungan pembuka formula CSV.

## Catatan metode

Pengujian utama memakai `uts_perkuliahan_test`. Import SQL memakai `uts_perkuliahan_sqltest`. Demonstrasi pemasangan ulang memakai `uts_perkuliahan_replay` pada port Laravel 8089; port penggunaan normal aplikasi adalah 8088.

Pemeriksaan zoom menggunakan viewport CSS 1536 × 720 dan 1280 × 600, setara ruang layout zoom 125% dan 150% pada lebar 1920 piksel. Shortcut zoom Chrome melalui API tidak mengubah viewport/DPR. Pemeriksaan cetak mencakup tombol, handler `window.print()`, dan stylesheet; pencetakan ke perangkat fisik tidak dilakukan.

Seluruh peserta, nilai, dan aturan akademik adalah simulasi. Deployment hosting dan pengumpulan LMS belum dilakukan. Bukti kompilasi laporan disimpan bersama paket LaTeX, terpisah dari repo aplikasi.

## Pemeriksaan README publik

- **PASS, isi:** jumlah tes dan hasil fungsi merujuk berkas bukti pada tabel di atas; data demonstrasi diberi label simulasi.
- **PASS, tautan dan gambar:** tautan internal menunjuk berkas yang disertakan; gambar KHS berasal dari pengujian browser asli.
- **PASS, panduan:** perintah clone, folder kerja, prasyarat, port, akun lokal, dan database tes sesuai skrip yang disertakan.
- **PASS, penulisan:** README menjelaskan pekerjaan tiap peran dan cara menjalankan sistem tanpa klaim keunggulan atau testimoni rekaan.
