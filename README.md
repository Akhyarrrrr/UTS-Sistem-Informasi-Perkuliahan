# Sistem Informasi Perkuliahan · UTS Akhyar

NPM 260820701100010 · Kelas A · Manajemen dan Pemodelan Data

Aplikasi Laravel 13 / PHP 8.5 / MySQL 8.0. Semua profil, nilai, jadwal, dan kebijakan akademik adalah data simulasi; identitas penyusun dipakai pada laporan dan satu profil demonstrasi.

## Instalasi pertama di Windows

1. Ekstrak seluruh ZIP ke folder yang dapat ditulis. Pertahankan susunan `Aplikasi`, `SQL`, `Bukti`, dan skrip pada tingkat yang sama.
2. Sediakan PHP 8.5 dengan PDO MySQL, mbstring, tokenizer, openssl, fileinfo, XML, dan zip; Composer 2; Node.js 22.12+ atau 24; serta MySQL 8.0. Port 8088 dan 3319 harus tersedia. Aplikasi telah diuji pada PHP 8.5.0, Node 24.18.0, dan MySQL 8.0.46.
3. Buka PowerShell di folder `Sistem_Informasi_Perkuliahan`, yaitu folder hasil ekstraksi yang memuat `install.ps1`:

```powershell
powershell -ExecutionPolicy Bypass -File .\check.ps1
powershell -ExecutionPolicy Bypass -File .\install.ps1
powershell -ExecutionPolicy Bypass -File .\start.ps1
```

Jika lokasi MySQL berbeda:

```powershell
powershell -ExecutionPolicy Bypass -File .\install.ps1 -MySqlBin 'D:\tools\mysql\bin'
powershell -ExecutionPolicy Bypass -File .\start.ps1 -MySqlBin 'D:\tools\mysql\bin'
```

Instalasi memasang dependensi dari `composer.lock` dan `package-lock.json`, membuat instans MySQL khusus, mengatur `.env`, menjalankan migration/seeder, lalu membangun aset. Internet diperlukan untuk mengunduh dependensi pada instalasi pertama. Font dan aset aplikasi dilayani secara lokal setelah build. Tidak diperlukan Docker.

Buka **http://localhost:8088** untuk halaman pengenalan publik. Login mengarahkan pengguna ke ruang kerja sesuai peran; halaman pengenalan tetap dapat diakses sesudah login. Akun dan kata sandi demo yang dibuat instalasi berada dalam `Runtime/akses-demo.txt`. Tiga akun utama: `admin@demo.test`, `dosen@demo.test`, dan `mahasiswa@demo.test`; semua akun simulasi memakai kata sandi instalasi yang sama. Akun Raka Wijaya untuk hasil demonstrasi: `mhs11@demo.test`. Kata sandi lokal tidak disertakan dalam ZIP.

Skrip tidak menggunakan MySQL utama komputer. Data berada di `Runtime/mysql-data`, pada port 3319 dengan koneksi loopback. Aplikasi memakai `uts_perkuliahan`; tes memakai `uts_perkuliahan_test`. Jangan memindahkan atau menghapus Runtime saat layanan masih berjalan.

## Menjalankan berikutnya

```powershell
powershell -ExecutionPolicy Bypass -File .\start.ps1
powershell -ExecutionPolicy Bypass -File .\stop.ps1
```

`stop.ps1` menghentikan layanan dan mempertahankan data. Jangan menjalankan `migrate:fresh` pada database aplikasi yang ingin dipertahankan. Seeder hanya membuat data awal ketika belum ada fakultas; pengulangan instalasi tidak mengganti catatan yang sudah ada.

## Demo yang tersedia

- Admin: persetujuan KRS Nadia Rahma; CRUD master; aturan SKS dan skala; rekap dan riwayat.
- Dosen utama: MPD301 A/B, PSD304 A, serta PDB308 A. PSD304 mempunyai nilai yang belum lengkap untuk demonstrasi validasi.
- Akhyar: KRS 11 SKS, 8 SKS bernilai terbit, IPS sementara 3,75, IPK 3,55. Satu nilai belum terbit.
- Raka Wijaya: PDB308, presensi Hadir, nilai 88,50/A, IPS 4,00. `DemonstrationSeeder` mereproduksi hasil akhir demonstrasi browser; audit menyebut sumber seeder secara jelas.
- `mhs12@demo.test`: belum memiliki KRS dan dapat dipakai untuk mencoba alur baru. Mata kuliah dengan nilai terbit tidak menerima peserta baru. Gunakan kelas B atau kelas lain yang belum terbit.

Admin membuat/meninjau kelas, mahasiswa menyimpan dan mengajukan KRS, admin menyetujui atau mengembalikan dengan alasan, dosen mencatat pertemuan/presensi dan nilai, lalu menerbitkan nilai. Nilai kosong berarti belum dinilai. Koreksi hasil terbit membutuhkan alasan; bobot tetap dikunci setelah terbit pertama.

## Pengujian

```powershell
cd Aplikasi
php artisan test --compact --log-junit ../Bukti/phpunit.xml
php scripts/test-concurrency.php
npm run build
php scripts/export-evidence.php
```

Tes Laravel mempunyai guard nama database khusus. Skrip konkurensi **membuat ulang hanya `uts_perkuliahan_test`**, lalu menjalankan dua proses PHP untuk kursi terakhir, duplikasi pertemuan, serta perlindungan admin terakhir. Jangan menjalankan tes Laravel dan skrip konkurensi bersamaan karena keduanya memakai database tes yang sama.

## Snapshot SQL dan pemulihan

- `SQL/struktur.sql`: struktur aktual, indeks, FK, CHECK, dan trigger.
- `SQL/data-simulasi.sql`: snapshot akhir demonstrasi. Kata sandi/remember token asli tidak diekspor; akun dikunci sampai diaktifkan secara lokal.
- `SQL/query-pembuktian.sql`: JOIN, agregasi SKS, pemeriksaan baris yatim, serta UPDATE/DELETE transaksi.
- `Bukti/database.json`: tipe kolom, jumlah baris, query, keluaran SQL, IPS/IPK, dan waktu snapshot.

Jalur utama instalasi adalah migration + seeder. Untuk memulihkan snapshot pada **database kosong UTS**, jalankan konfigurasi lokal, lalu `php artisan migrate --force` tanpa `--seed`. Setelah semua tabel kosong dibuat, impor **hanya `data-simulasi.sql`** memakai klien MySQL yang mengarah ke port 3319. Jangan mengimpor `struktur.sql` di atas tabel yang sudah dibuat migration. Selanjutnya jalankan:

```powershell
php scripts/activate-demo.php
```

File struktur disediakan untuk penilaian DDL dan pembuatan tabel akademik pada database kosong tanpa migration. Jika memilih jalur DDL mandiri, tabel infrastruktur Laravel (sessions/cache/locks) juga perlu dibuat dari migration bawaan sebelum aplikasi dijalankan.

## Hosting

Proyek `uts-sistem-informasi-perkuliahan` telah dibuat pada akun Vercel `akhyarrrrr`, dengan Root Directory `Aplikasi` dan Node 22.x. Konfigurasi menggunakan `vercel-php@0.9.0`, entry point `api/index.php`, build `npm ci`/`npm run build`, serta aset statis dalam `public`. Deployment produksi dan koneksi MySQL online belum terverifikasi.

Panduan environment, pemisahan database dan izin akun, TLS, pengujian preview, backup, rollback, serta batas Aiven gratis tersedia dalam [README aplikasi](Aplikasi/README.md). Sesi dan cache online memakai database; berkas sementara serta kompilasi Blade memakai `/tmp`. Kata sandi dan kunci aplikasi disimpan secara pribadi. Migration dan seeding dilakukan terpisah dari build.

## Laporan

Laporan menggunakan XeLaTeX, Times New Roman 12 pt, A4, spasi 1,5, dan margin L3,5/R3/T3/B3 cm. Dari paket sumber LaTeX, jalankan tiga kali:

```powershell
xelatex -interaction=nonstopmode -halt-on-error laporan_uts.tex
```

Font Times New Roman dan Consolas harus tersedia. Semua gambar dan cuplikan kode yang dipakai laporan sudah ada dalam ZIP LaTeX; tidak perlu menjalankan `prepare-evidence.py` untuk kompilasi sumber final.

## Audit 6-7 Oktober 2026

Audit lokal awal meluluskan 35 tes Laravel dengan 285 asersi serta perbandingan DDL/metadata 18 tabel dan enam trigger antara database utama, hasil migration baru, dan impor SQL. Setelah persiapan Vercel, suite meluluskan 38 tes dengan 294 asersi dan tiga tes konkurensi pada MySQL lokal 8.0.46 serta Aiven MySQL 8.4.8 pada 7 Oktober 2026. Bukti: `Bukti/phpunit-vercel-local-20261007.xml`, `Bukti/phpunit-aiven-20261007.xml`, `Bukti/concurrency-vercel-local-20261007.json`, `Bukti/concurrency-aiven-20261007.json`, `Bukti/schema-aiven-20261007.json`, dan `Bukti/vercel-adapter-local-20261007.json`.

Lihat `Bukti/PEMERIKSAAN_DAN_BUKTI.md`, `Bukti/MATRIKS_UTS.md`, serta `Bukti/DELIVERY_GATE.md` untuk audit lokal sebelumnya. Pemeriksaan online masih berlangsung; PDF dan ZIP dalam `Hasil` tetap menjadi baseline lokal sampai paket akhir diregenerasikan dari sumber yang sudah diuji. Salinan GitHub belum diperbarui.

Untuk replay dengan database khusus pada instans UTS yang sudah berjalan:

```powershell
powershell -ExecutionPolicy Bypass -File .\install.ps1 -Replay -ReplayDatabase uts_perkuliahan_replay_20261006
cd Aplikasi
php artisan serve --host=127.0.0.1 --port=8089
```

Replay memakai database berbeda dari `uts_perkuliahan`. Pilih database replay kosong untuk pemeriksaan instalasi baru. Kredensial tetap disediakan secara lokal oleh konfigurasi; jangan memasukkan `Runtime` ke paket publik.

Pada Windows, ekstrak paket ke direktori pendek (misalnya D:\UTS) untuk menghindari batas panjang path saat Composer mengekstrak dependensi. Uji replay menggunakan pemetaan drive pendek ke salinan lokal yang terpisah.
