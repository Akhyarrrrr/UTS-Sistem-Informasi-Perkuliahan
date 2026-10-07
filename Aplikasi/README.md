# Perkuliahan

Sistem Informasi Perkuliahan untuk UTS Manajemen dan Pemodelan Data, disusun oleh Akhyar, NPM 260820701100010, Kelas A.

Halaman `/` memperkenalkan sistem kepada publik. Ruang akademik memakai login dan pembatasan peran. Semua profil, jadwal, presensi, nilai, dan kebijakan akademik merupakan data simulasi untuk pengujian tugas.

## Status deployment

Proyek Vercel `uts-sistem-informasi-perkuliahan` sudah dibuat pada akun `akhyarrrrr`. Adapter PHP dan build aset telah disiapkan. Deployment produksi dan koneksi MySQL online belum terverifikasi; URL produksi akan dicatat setelah pengujian selesai.

## Peran dan alur akademik

| Peran | Tindakan |
| --- | --- |
| Mahasiswa | Memilih kelas, menyimpan dan mengajukan KRS, melihat jadwal, presensi, KHS, IPS, dan IPK. |
| Dosen | Mengelola pertemuan dan presensi kelas yang diampu, mengisi komponen nilai, menerbitkan serta mengoreksi hasil dengan alasan. |
| Admin | Mengelola 11 master, meninjau KRS, mengatur bobot, serta memeriksa rekap dan riwayat perubahan. |

KRS bergerak dari draf ke diajukan, kemudian disetujui atau dikembalikan. Kapasitas, konflik jadwal, batas SKS, kepemilikan data, dan status publikasi diperiksa sebelum perubahan disimpan. Nilai kosong berarti belum dinilai; nilai nol merupakan hasil penilaian. Ekspor CSV dan cetak mengikuti akses pengguna.

## Arsitektur

Laravel 13, PHP 8.5, Blade, Vite, CSS, dan JavaScript native; font Syne dilayani dari aset lokal. Migration membentuk 26 tabel: 16 tabel akademik, akun, audit, dan delapan tabel infrastruktur. SQL penilaian memuat 18 tabel domain beserta enam trigger.

Pada Vercel, aset `public` disajikan secara statis, sedangkan permintaan aplikasi diteruskan ke `api/index.php`. Runtime komunitas dipin ke `vercel-php@0.9.0`; build memakai Node 22.x. Adapter menempatkan kompilasi Blade dan berkas sementara di `/tmp`. Sesi dan cache memakai MySQL eksternal, antrean sinkron, dan log `stderr`. Migration, seeding, serta pembuatan kunci aplikasi dilakukan terpisah dari build.

## Instalasi lokal

Panduan Windows lengkap beserta skrip pengelolaan MySQL tersedia di [README paket](../README.md). Untuk instalasi manual, sediakan PHP 8.5, Composer 2, Node 22.12+ dalam lini 22, serta MySQL 8.0/8.4, lalu jalankan dari folder ini:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
```

Atur koneksi database kosong pada `.env` dan `DEMO_PASSWORD` acak minimal 16 karakter. Kemudian:

```powershell
php artisan migrate --seed
php artisan db:seed --class=DemonstrationSeeder
php artisan serve --host=127.0.0.1 --port=8088
```

Buka `http://localhost:8088`. Kata sandi dibuat oleh pemilik instalasi dan disimpan secara pribadi. Seeder tidak menyediakan kata sandi bawaan di luar lingkungan tes. Jangan menjalankan `migrate:fresh` pada database yang datanya ingin dipertahankan.

## Konfigurasi Vercel

Hubungkan repo `Akhyarrrrr/UTS-Sistem-Informasi-Perkuliahan`, dengan Root Directory `Aplikasi`, Framework Preset `Other`, Node 22.x, Install Command `npm ci`, Build Command `npm run build`, dan Output Directory `public`. `vercel.json` memblokir akses langsung ke PHP serta berkas tersembunyi sebelum penyajian aset. Hook Composer `vercel` memeriksa kebutuhan ekstensi PHP dan menjalankan penemuan paket Laravel.

Tambahkan nilai berikut melalui Environment Variables Vercel. Buat kunci dan kredensial berbeda untuk production dan preview; jangan menyimpan nilai rahasia di repo. Tabel ini menunjukkan nama dan bentuk nilai, tanpa kredensial:

| Variabel | Konfigurasi |
| --- | --- |
| `APP_NAME` | `Sistem Informasi Perkuliahan` |
| `APP_ENV` | `production` |
| `VERCEL_PHP_DOCROOT` | `public`, agar router PHP memakai document root aplikasi. |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Kunci acak tetap yang dibuat sekali untuk lingkungan tersebut. |
| `APP_URL` | URL HTTPS lingkungan yang benar. |
| `APP_LOCALE`, `APP_FALLBACK_LOCALE` | `id` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE` | Host, port, dan database dari layanan MySQL. |
| `DB_USERNAME`, `DB_PASSWORD` | Akun aplikasi dengan izin operasi data pada database lingkungan tersebut. |
| `MYSQL_ATTR_SSL_CA` | Path CA layanan, misalnya `certs/aiven-ca.pem`, di luar `public`. |
| `SESSION_DRIVER`, `CACHE_STORE` | `database` |
| `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY` | `true` |
| `SESSION_SAME_SITE` | `lax` |
| `SESSION_PATH`, `SESSION_DOMAIN` | `/`, `null` |
| `QUEUE_CONNECTION` | `sync` |
| `LOG_CHANNEL`, `LOG_LEVEL` | `stderr`, `warning` |
| `MAIL_MAILER`, `BROADCAST_CONNECTION` | `log` |

Vercel menyediakan `VERCEL` dan `VERCEL_URL`. Middleware mempercayai proxy platform untuk HTTPS serta alamat IP klien, lalu memeriksa host terhadap URL aplikasi dan deployment. CA harus tersedia dalam bundle fungsi. PDO memverifikasi sertifikat server; koneksi online tidak boleh memakai opsi yang menonaktifkan verifikasi TLS.

Pisahkan database production, preview, dan tes. Akun migration memerlukan izin DDL untuk tabel, indeks, constraint, dan trigger; akun aplikasi hanya `SELECT`, `INSERT`, `UPDATE`, serta `DELETE` pada database masing-masing. Gunakan akun migration hanya untuk pemasangan atau perubahan skema. `DEMO_PASSWORD` diperlukan saat seeding awal; kata sandi online harus baru dan dicatat dalam berkas runtime pribadi.

Sebelum peluncuran, cocokkan hasil migration dengan skema aktual, isi data simulasi, dan periksa koneksi menggunakan akun aplikasi. Uji branch preview terlebih dahulu. Bangun produksi dari commit yang sama dengan environment production, lalu verifikasi HTTPS, `/up`, login, alur akademik, sesi setelah redeploy, serta perlindungan berkas. Pilih region fungsi setelah lokasi database diketahui.

## Pengujian

Siapkan `.env.testing` dengan `APP_ENV=testing`, koneksi MySQL khusus, dan `DB_DATABASE=uts_perkuliahan_test`. Guard pengujian menolak nama database lain. Jalankan secara berurutan:

```powershell
php artisan test --compact --log-junit ../Bukti/phpunit.xml
php scripts/test-concurrency.php
php vendor/bin/pint --test
npm run build
```

Suite pada 7 Oktober 2026 lulus: 38 tes dan 294 asersi pada MySQL lokal 8.0.46 serta Aiven MySQL 8.4.8. Tiga tes konkurensi lulus secara berurutan pada kedua database tes. Probe adapter lokal memverifikasi halaman publik, login, KHS, dan sesi database yang bertahan setelah proses PHP dimulai ulang. Audit Aiven mencocokkan seluruh metadata kolom, indeks, constraint, serta enam trigger dengan skema lokal; akun aplikasi ditolak saat mencoba DDL dan akses database lingkungan lain. Pengujian browser produksi masih berlangsung.

Skrip konkurensi membuat ulang hanya database tes, kemudian memeriksa kursi terakhir, nomor pertemuan yang sama, serta penghapusan dua admin serentak. Jangan menjalankan suite Laravel dan skrip konkurensi bersamaan. Bukti tambahan berada dalam folder `Bukti` pada paket utama.

## Backup dan rollback

Simpan dump database, commit sumber, konfigurasi environment, serta kunci aplikasi dalam penyimpanan pribadi sebelum migration. Dump harus mencakup trigger dan memakai snapshot transaksi untuk tabel InnoDB. Untuk pemulihan, impor ke database UTS kosong, verifikasi skema serta jumlah baris, lalu arahkan aplikasi ke database yang dipulihkan. Jangan menimpa database proyek lain.

Rollback deployment Vercel mengembalikan versi kode; tindakan tersebut tidak mengembalikan data MySQL. Gunakan deployment produksi sebelumnya yang sudah diuji dan pastikan skemanya masih kompatibel. Jika perubahan skema memerlukan pemulihan database, lakukan pemulihan secara terpisah dari backup yang sesuai dan periksa alur login serta KHS kembali. Simpan `APP_KEY` lingkungan yang sama selama rollback.

## Batas layanan gratis

Aiven MySQL gratis menyediakan satu CPU, RAM 1 GB, penyimpanan 1 GB, dan batas 76 koneksi. Setiap organisasi hanya dapat memiliki satu MySQL gratis. Layanan dapat dimatikan sementara karena tidak aktif dan tidak mempunyai SLA atau dukungan seperti paket berbayar. Tidak ada peningkatan layanan berbayar otomatis dalam prosedur deployment ini.

Rujukan konfigurasi: [runtime PHP komunitas](https://github.com/vercel-community/php), [Vercel runtimes](https://vercel.com/docs/functions/runtimes), [Laravel deployment](https://laravel.com/docs/13.x/deployment), [Aiven MySQL Free Tier](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier), dan [rollback Vercel](https://vercel.com/docs/deployments/rollback-a-deployment).
