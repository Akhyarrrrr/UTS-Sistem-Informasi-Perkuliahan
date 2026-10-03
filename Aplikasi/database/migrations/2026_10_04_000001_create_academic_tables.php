<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->enum('role', ['admin', 'dosen', 'mahasiswa'])->default('mahasiswa'));
        Schema::create('fakultas', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 12)->unique();
            $t->string('nama', 120);
        });
        Schema::create('prodi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('fakultas_id')->constrained('fakultas')->restrictOnDelete();
            $t->string('kode', 12)->unique();
            $t->string('nama', 120);
            $t->unsignedTinyInteger('batas_sks')->default(24);
        });
        Schema::create('mahasiswa', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $t->foreignId('prodi_id')->constrained('prodi')->restrictOnDelete();
            $t->string('npm', 20)->unique();
            $t->string('nama', 100);
            $t->unsignedSmallInteger('angkatan');
        });
        Schema::create('dosen', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained('users')->restrictOnDelete();
            $t->foreignId('prodi_id')->constrained('prodi')->restrictOnDelete();
            $t->string('kode', 20)->unique();
            $t->string('nama', 100);
        });
        Schema::create('matakuliah', function (Blueprint $t) {
            $t->id();
            $t->foreignId('prodi_id')->constrained('prodi')->restrictOnDelete();
            $t->string('kode', 20)->unique();
            $t->string('nama', 120);
            $t->unsignedTinyInteger('sks');
        });
        Schema::create('periode', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 60)->unique();
            $t->unsignedSmallInteger('tahun_mulai');
            $t->enum('semester', ['Ganjil', 'Genap']);
            $t->date('krs_mulai');
            $t->date('krs_selesai');
            $t->boolean('aktif')->default(false);
        });
        Schema::create('ruang', function (Blueprint $t) {
            $t->id();
            $t->string('kode', 20)->unique();
            $t->string('nama', 100);
            $t->unsignedSmallInteger('kapasitas');
        });
        Schema::create('kelas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('matakuliah_id')->constrained('matakuliah')->restrictOnDelete();
            $t->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $t->foreignId('dosen_id')->constrained('dosen')->restrictOnDelete();
            $t->string('kode', 12);
            $t->unsignedSmallInteger('kapasitas');
            $t->timestamp('published_at')->nullable();
            $t->unique(['matakuliah_id', 'periode_id', 'kode']);
        });
        Schema::create('jadwal', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $t->foreignId('ruang_id')->nullable()->constrained('ruang')->restrictOnDelete();
            $t->unsignedTinyInteger('hari');
            $t->time('mulai');
            $t->time('selesai');
            $t->enum('mode', ['Luring', 'Daring'])->default('Luring');
            $t->string('tautan', 255)->nullable();
        });
        Schema::create('krs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('mahasiswa_id')->constrained('mahasiswa')->restrictOnDelete();
            $t->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $t->enum('status', ['draf', 'diajukan', 'disetujui', 'dikembalikan'])->default('draf');
            $t->text('catatan')->nullable();
            $t->timestamps();
            $t->unique(['mahasiswa_id', 'periode_id']);
        });
        Schema::create('krs_detail', function (Blueprint $t) {
            $t->id();
            $t->foreignId('krs_id')->constrained('krs')->restrictOnDelete();
            $t->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $t->unique(['krs_id', 'kelas_id']);
        });
        Schema::create('komponen_nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $t->string('nama', 30);
            $t->decimal('bobot', 5, 2);
            $t->unique(['kelas_id', 'nama']);
        });
        Schema::create('nilai_komponen', function (Blueprint $t) {
            $t->id();
            $t->foreignId('krs_detail_id')->constrained('krs_detail')->restrictOnDelete();
            $t->foreignId('komponen_nilai_id')->constrained('komponen_nilai')->restrictOnDelete();
            $t->decimal('nilai', 5, 2)->nullable();
            $t->unique(['krs_detail_id', 'komponen_nilai_id']);
        });
        Schema::create('skala_nilai', function (Blueprint $t) {
            $t->id();
            $t->foreignId('periode_id')->constrained('periode')->restrictOnDelete();
            $t->string('huruf', 2);
            $t->decimal('minimum', 5, 2);
            $t->decimal('angka', 3, 2);
            $t->unique(['periode_id', 'huruf']);
            $t->unique(['periode_id', 'minimum']);
        });
        Schema::create('pertemuan', function (Blueprint $t) {
            $t->id();
            $t->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $t->unsignedTinyInteger('nomor');
            $t->date('tanggal');
            $t->string('topik', 160);
            $t->unique(['kelas_id', 'nomor']);
        });
        Schema::create('presensi', function (Blueprint $t) {
            $t->id();
            $t->foreignId('pertemuan_id')->constrained('pertemuan')->restrictOnDelete();
            $t->foreignId('krs_detail_id')->constrained('krs_detail')->restrictOnDelete();
            $t->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa']);
            $t->unique(['pertemuan_id', 'krs_detail_id']);
        });
        Schema::create('activity_log', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('aksi', 80);
            $t->string('entitas', 50);
            $t->unsignedBigInteger('record_id')->nullable();
            $t->json('detail')->nullable();
            $t->timestamp('created_at')->useCurrent();
        });
        foreach ([
            'prodi' => ['batas_sks BETWEEN 1 AND 30'], 'matakuliah' => ['sks BETWEEN 1 AND 6'],
            'periode' => ['krs_mulai <= krs_selesai'], 'ruang' => ['kapasitas > 0'], 'kelas' => ['kapasitas > 0'],
            'jadwal' => ['hari BETWEEN 1 AND 7', 'mulai < selesai', "(mode = 'Daring' OR ruang_id IS NOT NULL)"],
            'komponen_nilai' => ['bobot > 0 AND bobot <= 100'], 'nilai_komponen' => ['nilai IS NULL OR nilai BETWEEN 0 AND 100'],
            'skala_nilai' => ['minimum BETWEEN 0 AND 100', 'angka BETWEEN 0 AND 4'], 'pertemuan' => ['nomor BETWEEN 1 AND 32'],
        ] as $table => $checks) {
            foreach ($checks as $i => $check) {
                DB::statement("ALTER TABLE `$table` ADD CONSTRAINT `chk_{$table}_{$i}` CHECK ($check)");
            }
        }
        DB::unprepared("CREATE TRIGGER check_krs_detail_insert BEFORE INSERT ON krs_detail FOR EACH ROW BEGIN IF (SELECT periode_id FROM kelas WHERE id=NEW.kelas_id) <> (SELECT periode_id FROM krs WHERE id=NEW.krs_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Periode kelas harus sama dengan KRS'; END IF; END");
        DB::unprepared("CREATE TRIGGER check_nilai_insert BEFORE INSERT ON nilai_komponen FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM komponen_nilai WHERE id=NEW.komponen_nilai_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Komponen nilai harus berasal dari kelas peserta'; END IF; END");
        DB::unprepared("CREATE TRIGGER check_presensi_insert BEFORE INSERT ON presensi FOR EACH ROW BEGIN IF (SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM pertemuan WHERE id=NEW.pertemuan_id) THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Presensi harus berasal dari kelas peserta'; END IF; END");
    }

    public function down(): void
    {
        foreach (['presensi', 'pertemuan', 'skala_nilai', 'nilai_komponen', 'komponen_nilai', 'krs_detail', 'krs', 'jadwal', 'kelas', 'ruang', 'periode', 'matakuliah', 'dosen', 'mahasiswa', 'prodi', 'fakultas', 'activity_log'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
