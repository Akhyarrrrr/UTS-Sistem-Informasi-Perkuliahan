<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->timestamp('first_published_at')->nullable();
        });
        DB::statement('UPDATE kelas SET first_published_at = published_at WHERE published_at IS NOT NULL');
        $checks = [
            'krs_detail' => '(SELECT periode_id FROM krs WHERE id=NEW.krs_id) <> (SELECT periode_id FROM kelas WHERE id=NEW.kelas_id)',
            'nilai_komponen' => '(SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM komponen_nilai WHERE id=NEW.komponen_nilai_id)',
            'presensi' => '(SELECT kelas_id FROM krs_detail WHERE id=NEW.krs_detail_id) <> (SELECT kelas_id FROM pertemuan WHERE id=NEW.pertemuan_id)',
        ];
        foreach ($checks as $table => $condition) {
            DB::unprepared("CREATE TRIGGER validate_{$table}_update BEFORE UPDATE ON {$table} FOR EACH ROW BEGIN IF {$condition} THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Hubungan akademik tidak sesuai'; END IF; END");
        }
    }

    public function down(): void
    {
        foreach (['krs_detail', 'nilai_komponen', 'presensi'] as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS validate_{$table}_update");
        }
        Schema::table('kelas', fn (Blueprint $table) => $table->dropColumn('first_published_at'));
    }
};
