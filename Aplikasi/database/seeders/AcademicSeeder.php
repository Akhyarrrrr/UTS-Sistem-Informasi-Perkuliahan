<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $plainPassword = config('app.demo_password');
        if (! app()->environment('testing') && (! is_string($plainPassword) || strlen($plainPassword) < 16)) {
            throw new \RuntimeException('DEMO_PASSWORD minimal 16 karakter wajib diatur sebelum membuat akun demonstrasi.');
        }
        if (DB::table('fakultas')->exists()) {
            return;
        }
        DB::transaction(function () use ($plainPassword) {
            $password = Hash::make($plainPassword ?: 'DemoForTesting2026!');
            $account = fn ($name, $email, $role) => DB::table('users')->insertGetId(['name' => $name, 'email' => $email, 'role' => $role, 'password' => $password, 'created_at' => now(), 'updated_at' => now()]);
            $admin = $account('Administrator akademik', 'admin@demo.test', 'admin');
            DB::table('fakultas')->insert([['kode' => 'FMIPA', 'nama' => 'Fakultas Matematika dan Ilmu Pengetahuan Alam'], ['kode' => 'FT', 'nama' => 'Fakultas Teknik']]);
            DB::table('prodi')->insert([['fakultas_id' => 1, 'kode' => 'MKIA', 'nama' => 'Magister Kecerdasan Artifisial', 'batas_sks' => 24], ['fakultas_id' => 2, 'kode' => 'INFO', 'nama' => 'Informatika', 'batas_sks' => 24], ['fakultas_id' => 1, 'kode' => 'MATH', 'nama' => 'Matematika', 'batas_sks' => 24]]);
            $names = ['Akhyar', 'Nadia Rahma', 'Farhan Putra', 'Bella Nabila', 'Rizky Fadli', 'Salsabila Putri', 'Dimas Pratama', 'Aulia Fitri', 'Fauzan Arif', 'Nisa Hanifah', 'Raka Wijaya', 'Intan Safira', 'Yoga Pratama', 'Siti Zahra'];
            foreach ($names as $i => $name) {
                $u = $account($name, $i === 0 ? 'mahasiswa@demo.test' : 'mhs'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT).'@demo.test', 'mahasiswa');
                DB::table('mahasiswa')->insert(['user_id' => $u, 'prodi_id' => $i < 12 ? 1 : ($i === 12 ? 2 : 3), 'npm' => $i === 0 ? '260820701100010' : 'SIM2026'.str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT), 'nama' => $name, 'angkatan' => 2026]);
            }
            foreach (['Dr. Ahmad Fadhli', 'Dr. Siti Marlina', 'Dr. Teuku Zulkifli', 'Dr. Dewi Anggraini'] as $i => $name) {
                $u = $account($name, $i === 0 ? 'dosen@demo.test' : 'dosen'.($i + 1).'@demo.test', 'dosen');
                DB::table('dosen')->insert(['user_id' => $u, 'prodi_id' => 1, 'kode' => 'DSN'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT), 'nama' => $name]);
            }
            $courses = [['MPD301', 'Manajemen dan Pemodelan Data', 3], ['MPE302', 'Metode Penelitian', 2], ['AIK303', 'Kecerdasan Artifisial', 3], ['PSD304', 'Pemrograman untuk Sains Data', 3], ['STA305', 'Statistika Lanjutan', 3], ['BDA306', 'Big Data dan Analitika', 3], ['ETK307', 'Etika Kecerdasan Artifisial', 2]];
            foreach ($courses as $c) {
                DB::table('matakuliah')->insert(['prodi_id' => 1, 'kode' => $c[0], 'nama' => $c[1], 'sks' => $c[2]]);
            }
            DB::table('matakuliah')->insert(['prodi_id' => 2, 'kode' => 'INF101', 'nama' => 'Algoritma dan Pemrograman', 'sks' => 3]);
            DB::table('periode')->insert([['nama' => '2025/2026 Genap', 'tahun_mulai' => 2025, 'semester' => 'Genap', 'krs_mulai' => '2026-01-01', 'krs_selesai' => '2026-02-15', 'aktif' => false], ['nama' => '2026/2027 Ganjil', 'tahun_mulai' => 2026, 'semester' => 'Ganjil', 'krs_mulai' => today()->subDays(7)->toDateString(), 'krs_selesai' => today()->addDays(14)->toDateString(), 'aktif' => true]]);
            foreach ([1, 2] as $period) {
                foreach ([['A', 85, 4], ['B+', 80, 3.5], ['B', 70, 3], ['C+', 65, 2.5], ['C', 60, 2], ['D', 50, 1], ['E', 0, 0]] as $s) {
                    DB::table('skala_nilai')->insert(['periode_id' => $period, 'huruf' => $s[0], 'minimum' => $s[1], 'angka' => $s[2]]);
                }
            }
            DB::table('ruang')->insert([['kode' => 'B0101', 'nama' => 'Ruang B.01.01', 'kapasitas' => 30], ['kode' => 'LAB01', 'nama' => 'Laboratorium komputasi', 'kapasitas' => 24], ['kode' => 'B0102', 'nama' => 'Ruang B.01.02', 'kapasitas' => 30]]);
            $classSpecs = [[1, 2, 1, 'A', 12, 1, '09:00', '11:30', 1], [2, 2, 2, 'A', 12, 2, '09:00', '10:40', 1], [3, 2, 3, 'A', 12, 3, '09:00', '11:30', 1], [4, 2, 1, 'A', 12, 4, '09:00', '11:30', 2], [5, 2, 2, 'A', 12, 5, '09:00', '11:30', 1], [6, 2, 3, 'A', 12, 1, '13:00', '15:30', 3], [7, 2, 4, 'A', 12, 2, '13:00', '14:40', 3], [1, 2, 1, 'B', 3, 4, '13:00', '15:30', 1], [1, 1, 1, 'A', 12, 1, '09:00', '11:30', 1], [3, 1, 3, 'A', 12, 3, '09:00', '11:30', 1], [4, 1, 2, 'A', 12, 4, '09:00', '11:30', 2]];
            foreach ($classSpecs as $c) {
                $id = DB::table('kelas')->insertGetId(['matakuliah_id' => $c[0], 'periode_id' => $c[1], 'dosen_id' => $c[2], 'kode' => $c[3], 'kapasitas' => $c[4]]);
                DB::table('jadwal')->insert(['kelas_id' => $id, 'hari' => $c[5], 'mulai' => $c[6], 'selesai' => $c[7], 'ruang_id' => $c[8], 'mode' => 'Luring']);
                foreach (['Tugas' => 20, 'Kuis' => 10, 'UTS' => 30, 'UAS' => 40] as $label => $weight) {
                    DB::table('komponen_nilai')->insert(['kelas_id' => $id, 'nama' => $label, 'bobot' => $weight]);
                }
            }
            $enroll = function ($student, $period, $classes, $status) {
                $id = DB::table('krs')->insertGetId(['mahasiswa_id' => $student, 'periode_id' => $period, 'status' => $status, 'created_at' => now(), 'updated_at' => now()]);
                foreach ($classes as $c) {
                    DB::table('krs_detail')->insert(['krs_id' => $id, 'kelas_id' => $c]);
                }

                return $id;
            };
            $enroll(1, 2, [1, 2, 3, 4], 'disetujui');
            $enroll(1, 1, [9, 10, 11], 'disetujui');
            $enroll(2, 2, [4, 5, 8], 'diajukan');
            foreach (range(3, 8) as $student) {
                $enroll($student, 2, [1, 2, 3, 4], 'disetujui');
            }
            $enroll(9, 2, [5, 6, 7], 'draf');
            $returned = $enroll(10, 2, [5, 6], 'dikembalikan');
            DB::table('krs')->where('id', $returned)->update(['catatan' => 'Lengkapi pilihan mata kuliah sesuai rencana studi.']);
            foreach (DB::table('krs_detail as d')->join('krs as k', 'k.id', '=', 'd.krs_id')->where('k.status', 'disetujui')->select('d.*', 'k.mahasiswa_id')->get() as $detail) {
                if ($detail->kelas_id === 4) {
                    $c = DB::table('komponen_nilai')->where('kelas_id', 4)->first();
                    DB::table('nilai_komponen')->insert(['krs_detail_id' => $detail->id, 'komponen_nilai_id' => $c->id, 'nilai' => 80]);

                    continue;
                }
                $values = match ($detail->kelas_id) {
                    1 => [80, 90, 85, 95],2 => [74, 80, 70, 78],3 => [85, 90, 88, 90],9 => [70, 75, 72, 80],10 => [80, 85, 82, 84],11 => [70, 75, 72, 80],default => [80, 80, 80, 80]
                };
                foreach (DB::table('komponen_nilai')->where('kelas_id', $detail->kelas_id)->orderBy('id')->get() as $i => $component) {
                    DB::table('nilai_komponen')->insert(['krs_detail_id' => $detail->id, 'komponen_nilai_id' => $component->id, 'nilai' => max(0, $values[$i] - ($detail->mahasiswa_id === 1 ? 0 : ($detail->mahasiswa_id % 5) * 2))]);
                }
            }
            DB::table('kelas')->whereIn('id', [1, 2, 3, 9, 10, 11])->update(['published_at' => now(), 'first_published_at' => now()]);
            foreach ([1, 4] as $class) {
                foreach ([1, 2, 3] as $number) {
                    $meeting = DB::table('pertemuan')->insertGetId(['kelas_id' => $class, 'nomor' => $number, 'tanggal' => today()->subDays(21 - $number * 7), 'topik' => ($class === 1 ? ['Konsep model relasional', 'SQL dan integritas referensial', 'Normalisasi basis data'] : ['Struktur data dan tipe dasar', 'Operasi array dan tabel', 'Pengolahan dataset'])[$number - 1]]);
                    foreach (DB::table('krs_detail as d')->join('krs as k', 'k.id', '=', 'd.krs_id')->where('d.kelas_id', $class)->where('k.status', 'disetujui')->select('d.id', 'k.mahasiswa_id')->get() as $d) {
                        DB::table('presensi')->insert(['pertemuan_id' => $meeting, 'krs_detail_id' => $d->id, 'status' => $d->mahasiswa_id === 1 ? 'Hadir' : (['Hadir', 'Izin', 'Sakit', 'Alpa'][($d->mahasiswa_id + $number) % 4])]);
                    }
                }
            }
            DB::table('activity_log')->insert(['user_id' => $admin, 'aksi' => 'Siapkan data simulasi', 'entitas' => 'sistem', 'detail' => json_encode(['periode_utama' => '2026/2027 Ganjil']), 'created_at' => now()]);
        });
    }
}
