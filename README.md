# Sistem Informasi Perkuliahan

UTS Manajemen dan Pemodelan Data, Kelas A — Akhyar, NPM 260820701100010.

[Aplikasi online](https://uts-sistem-informasi-perkuliahan.vercel.app/) · [Repositori](https://github.com/Akhyarrrrr/UTS-Sistem-Informasi-Perkuliahan)

Aplikasi Laravel 13, PHP 8.5, dan MySQL untuk data master, penawaran kelas, KRS, presensi, penilaian, KHS, IPS/IPK, serta riwayat perubahan. Nama, jadwal, nilai, dan aturan akademik merupakan simulasi tugas. Akun online diberikan secara pribadi.

## Instalasi lokal di Windows

Sediakan PHP 8.5 beserta ekstensi yang disyaratkan Composer, termasuk PDO MySQL; Composer 2; Node.js 22.x minimal 22.12; dan MySQL 8.0. Port 3319 dan 8088 harus tersedia. Dependensi pertama kali diunduh melalui internet.

Clone repositori, lalu buka PowerShell di folder yang memuat `install.ps1`:

```powershell
git clone https://github.com/Akhyarrrrr/UTS-Sistem-Informasi-Perkuliahan.git
cd UTS-Sistem-Informasi-Perkuliahan
powershell -ExecutionPolicy Bypass -File .\install.ps1
powershell -ExecutionPolicy Bypass -File .\start.ps1
```

Jika lokasi MySQL berbeda, tambahkan `-MySqlBin 'D:\tools\mysql\bin'` pada perintah instalasi dan mulai. Lokasi bawaan adalah `C:\Program Files\MySQL\MySQL Server 8.0\bin`.

Buka [http://localhost:8088](http://localhost:8088). Instalasi memasang dependensi dari lockfile, membuat database, menyiapkan `.env` dan `.env.testing`, menjalankan migration/seeder, serta membangun aset. Font dan aset aplikasi dilayani secara lokal sesudah build.

Layanan memakai MySQL khusus pada `127.0.0.1:3319`. Database aplikasi adalah `uts_perkuliahan`; database tes adalah `uts_perkuliahan_test`. Direktori data, akses demo, dan log berada di **`Catatan_Pribadi/Layanan_Lokal` pada folder induk repositori**, sehingga tidak menjadi bagian kode publik. Contoh: repositori `D:\UTS\Pengerjaan` memakai `D:\UTS\Catatan_Pribadi\Layanan_Lokal`.

Email akun utama: `admin@demo.test`, `dosen@demo.test`, `mahasiswa@demo.test` (Akhyar), dan `mhs12@demo.test` (Intan). Kata sandi lokal dibuat saat instalasi dan dapat dibaca pada `Catatan_Pribadi/Layanan_Lokal/akses-demo.txt`. Kata sandi tidak disertakan dalam repositori.

## Menjalankan dan menghentikan

```powershell
powershell -ExecutionPolicy Bypass -File .\start.ps1
powershell -ExecutionPolicy Bypass -File .\stop.ps1
```

Penghentian mempertahankan data. Hentikan layanan sebelum memindahkan direktori data. Jangan menjalankan `migrate:fresh` pada database utama yang ingin dipertahankan; perintah itu menghapus tabel. Seeder membuat data awal ketika belum ada fakultas dan mempertahankan data yang sudah ada.

## Urutan demonstrasi

1. **Admin:** periksa periode, katalog, kelas, jadwal, skala nilai, profil, dan akun.
2. **Mahasiswa:** simpan pilihan KRS sebagai draf, lalu ajukan. Draf tidak memesan kursi; pengajuan dan persetujuan memesan kursi.
3. **Admin:** kembalikan pengajuan dengan alasan atau setujui. Pengembalian membuka revisi dan melepaskan kursi.
4. **Dosen:** buka kelas yang diampu, buat pertemuan, catat presensi, simpan nilai komponen, lalu terbitkan nilai lengkap dengan bobot 100%.
5. **Mahasiswa:** baca jadwal, presensi, KHS, IPS/IPK; unduh CSV atau gunakan cetak.
6. **Dosen dan admin:** koreksi nilai terbit dengan alasan, terbitkan ulang, lalu periksa riwayat. Bobot tetap terkunci sesudah publikasi pertama.

Pada instalasi seeder baru, Intan belum memiliki KRS. Contoh MPD301 B memakai 3 SKS dan bobot Tugas/Kuis/UTS/UAS 20/10/30/40%. Nilai 80/90/85/95 menghasilkan 88,50/A; koreksi UAS ke 96 menghasilkan 88,90/A dan IPS/IPK 4,00. Pada aplikasi online dan snapshot SQL, alur Intan sudah selesai. Untuk mempraktikkan perubahan dari awal, gunakan lokal dengan profil dan kelas yang belum selesai.

Akhyar mempunyai 11 SKS pilihan, 8 SKS terbit, IPS sementara 3,75, dan IPK 3,55. Nilai kosong berarti belum dinilai. Hanya hasil terbit masuk KHS/IPS; IPK memakai pengambilan terbaru yang sudah terbit untuk tiap mata kuliah.

## Struktur dan pengujian

| Lokasi | Isi |
|---|---|
| `Aplikasi/app`, `routes`, `resources` | Controller, aturan akademik, route, dan antarmuka |
| `Aplikasi/database` | Migration, batasan integritas, dan seeder |
| `Aplikasi/tests` | Pengujian peran, CRUD, KRS, penilaian, dan deployment |
| `Aplikasi/scripts` | Konfigurasi lokal, penghentian MySQL, dan aktivasi akun snapshot |
| `SQL` | DDL, data simulasi, dan query pembuktian |
| `install.ps1`, `start*.ps1`, `stop.ps1` | Instalasi dan pengelolaan layanan lokal |

Dari folder `Aplikasi`:

```powershell
php artisan test --compact
npm ci
npm run build
```

Tes memiliki pemeriksaan nama database khusus agar tidak dijalankan pada database utama. Pemeriksaan 9 Oktober 2026 menghasilkan 39 tes dan 318 asersi lulus. Build aset berhasil. Konkurensi kursi terakhir, nomor pertemuan, dan admin terakhir juga telah diperiksa melalui dua proses bersamaan.

## SQL

`struktur.sql` memuat 18 tabel akademik/akun/audit dan enam trigger; `data-simulasi.sql` memuat keadaan akhir demonstrasi; `query-pembuktian.sql` memuat JOIN, agregasi, pemeriksaan hubungan, serta operasi transaksi. Database aplikasi lengkap memiliki 26 tabel, termasuk infrastruktur Laravel.

Jalur instalasi utama menggunakan migration dan seeder. Untuk memakai snapshot pada **database UTS kosong**, jalankan konfigurasi lokal, kemudian `php artisan migrate --force` tanpa seeder; impor **hanya** `SQL/data-simulasi.sql` dengan klien MySQL ke port 3319. Jangan menimpa data utama. Akun snapshot dikunci dan dapat diaktifkan secara lokal dari folder `Aplikasi` melalui:

```powershell
php scripts/activate-demo.php
```

Jangan mengimpor `struktur.sql` di atas tabel migration. DDL mandiri tidak menyertakan tabel sesi/cache Laravel, sehingga migration infrastruktur tetap diperlukan sebelum menjalankan aplikasi.

## Konfigurasi online

Root proyek Vercel adalah `Aplikasi`; Node 22.x membangun aset dan `vercel-php@0.9.0` menjalankan `api/index.php`. Database online memakai Aiven MySQL dengan TLS. Sesi dan cache berada pada database, sedangkan berkas sementara fungsi berada di `/tmp`. Kunci aplikasi, kredensial database, dan akun demonstrasi disimpan secara pribadi. Build tidak menjalankan migration atau seeder; perubahan database dilakukan terpisah.

Gangguan HTTP 500 sebelumnya berkaitan dengan layanan atau koneksi Aiven yang terputus. Pemilik proyek menyambungkan kembali Aiven secara manual pada 9 Oktober 2026 dan mengonfirmasi aplikasi kembali dapat diakses. Jika gangguan berulang, periksa kondisi Aiven dan pesan exception pada Vercel Logs.
