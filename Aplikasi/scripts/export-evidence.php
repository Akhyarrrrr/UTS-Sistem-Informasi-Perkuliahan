<?php

use App\Services\Academic;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$root = dirname(__DIR__, 2);
$folder = $root.'/Bukti';
$sqlFolder = $root.'/SQL';
foreach ([$folder, $sqlFolder] as $path) {
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }
}
$pdo = DB::connection()->getPdo();
$database = DB::connection()->getDatabaseName();
$tables = ['users', 'fakultas', 'prodi', 'mahasiswa', 'dosen', 'matakuliah', 'periode', 'ruang', 'kelas', 'jadwal', 'krs', 'krs_detail', 'komponen_nilai', 'nilai_komponen', 'skala_nilai', 'pertemuan', 'presensi', 'activity_log'];
$dictionary = [];
$counts = [];
foreach ($tables as $table) {
    $dictionary[$table] = DB::select('SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ORDER BY ORDINAL_POSITION', [$database, $table]);
    $counts[$table] = DB::table($table)->count();
}
$queries = [
    'rekonstruksi' => "SELECT m.npm,p.nama periode,mk.kode,k.kode kelas,c.nama komponen,c.bobot,n.nilai FROM mahasiswa m JOIN krs r ON r.mahasiswa_id=m.id JOIN periode p ON p.id=r.periode_id JOIN krs_detail d ON d.krs_id=r.id JOIN kelas k ON k.id=d.kelas_id JOIN matakuliah mk ON mk.id=k.matakuliah_id JOIN komponen_nilai c ON c.kelas_id=k.id LEFT JOIN nilai_komponen n ON n.krs_detail_id=d.id AND n.komponen_nilai_id=c.id WHERE m.npm='260820701100010' AND r.status='disetujui' ORDER BY p.tahun_mulai,k.id,c.id",
    'total_sks' => "SELECT m.npm,p.nama periode,SUM(mk.sks) sks FROM krs r JOIN mahasiswa m ON m.id=r.mahasiswa_id JOIN periode p ON p.id=r.periode_id JOIN krs_detail d ON d.krs_id=r.id JOIN kelas k ON k.id=d.kelas_id JOIN matakuliah mk ON mk.id=k.matakuliah_id WHERE m.npm='260820701100010' GROUP BY m.npm,p.nama ORDER BY p.nama",
    'yatim' => 'SELECT (SELECT COUNT(*) FROM krs_detail d LEFT JOIN krs r ON r.id=d.krs_id WHERE r.id IS NULL)+(SELECT COUNT(*) FROM nilai_komponen n LEFT JOIN krs_detail d ON d.id=n.krs_detail_id WHERE d.id IS NULL)+(SELECT COUNT(*) FROM presensi a LEFT JOIN pertemuan p ON p.id=a.pertemuan_id WHERE p.id IS NULL) total_yatim',
    'ketidaksesuaian' => 'SELECT (SELECT COUNT(*) FROM krs_detail d JOIN krs r ON r.id=d.krs_id JOIN kelas k ON k.id=d.kelas_id WHERE r.periode_id<>k.periode_id)+(SELECT COUNT(*) FROM nilai_komponen n JOIN krs_detail d ON d.id=n.krs_detail_id JOIN komponen_nilai c ON c.id=n.komponen_nilai_id WHERE d.kelas_id<>c.kelas_id)+(SELECT COUNT(*) FROM presensi a JOIN krs_detail d ON d.id=a.krs_detail_id JOIN pertemuan p ON p.id=a.pertemuan_id WHERE d.kelas_id<>p.kelas_id) tidak_sesuai',
];
$results = [];
foreach ($queries as $name => $query) {
    $results[$name] = DB::select($query);
}
DB::beginTransaction();
$demoRoom = DB::table('ruang')->insertGetId(['kode' => 'SQL-TEST', 'nama' => 'Ruang uji SQL', 'kapasitas' => 10]);
$updated = DB::update('UPDATE ruang SET kapasitas=12 WHERE id=?', [$demoRoom]);
$read = DB::table('ruang')->find($demoRoom);
$deleted = DB::delete('DELETE FROM ruang WHERE id=?', [$demoRoom]);
DB::rollBack();
$results['update_delete'] = ['updated' => $updated, 'after_update' => $read, 'deleted' => $deleted, 'persisted' => DB::table('ruang')->where('kode', 'SQL-TEST')->exists()];
$evidence = ['captured' => now()->toIso8601String(), 'database' => $database, 'mysql' => DB::selectOne('SELECT VERSION() version')->version, 'laravel' => $app->version(), 'counts' => $counts, 'dictionary' => $dictionary, 'queries' => $queries, 'results' => $results, 'ips' => Academic::transcript(1, 2), 'ipk' => Academic::transcript(1)];
file_put_contents($folder.'/database.json', json_encode($evidence, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
$schema = "-- Struktur aktual sistem UTS; MySQL 8.0.46\nSET NAMES utf8mb4;\n";
foreach ($tables as $table) {
    $schema .= $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1].";\n\n";
}
$schema .= "DELIMITER $$\n";
foreach (DB::select('SHOW TRIGGERS') as $trigger) {
    $definition = $pdo->query("SHOW CREATE TRIGGER `{$trigger->Trigger}`")->fetch(PDO::FETCH_NUM)[2];
    $schema .= preg_replace('/DEFINER=`[^`]+`@`[^`]+`\s+/', '', $definition)."$$\n\n";
}
$schema .= "DELIMITER ;\n";
file_put_contents($sqlFolder.'/struktur.sql', $schema);
$data = "-- Data simulasi; akun sengaja terkunci. Jalankan php scripts/activate-demo.php setelah impor.\nSET NAMES utf8mb4;\n";
foreach ($tables as $table) {
    foreach (DB::table($table)->orderBy('id')->get() as $record) {
        $row = (array) $record;
        if ($table === 'users') {
            $row['password'] = '!akun-demo-perlu-diaktifkan!';
            $row['remember_token'] = null;
        }$cols = implode(',', array_map(fn ($k) => '`'.$k.'`', array_keys($row)));
        $vals = implode(',', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row)));
        $data .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
    }
}
file_put_contents($sqlFolder.'/data-simulasi.sql', $data);
file_put_contents($sqlFolder.'/query-pembuktian.sql', implode(";\n\n", $queries).";\n\n-- Demonstrasi UPDATE/DELETE yang aman dalam transaksi\nSTART TRANSACTION;\nINSERT INTO ruang(kode,nama,kapasitas) VALUES('SQL-TEST','Ruang uji SQL',10);\nUPDATE ruang SET kapasitas=12 WHERE kode='SQL-TEST';\nSELECT kode,kapasitas FROM ruang WHERE kode='SQL-TEST';\nDELETE FROM ruang WHERE kode='SQL-TEST';\nROLLBACK;\n");
echo json_encode(['mysql' => $evidence['mysql'], 'laravel' => $evidence['laravel'], 'counts' => $counts, 'rekonstruksi_baris' => count($results['rekonstruksi']), 'ips' => $evidence['ips']['ip'], 'ipk' => $evidence['ipk']['ip'], 'yatim' => $results['yatim']], JSON_PRETTY_PRINT).PHP_EOL;
