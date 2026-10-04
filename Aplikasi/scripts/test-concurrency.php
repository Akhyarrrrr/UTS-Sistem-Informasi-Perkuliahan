<?php

use App\Services\Academic;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDatabaseName() !== 'uts_perkuliahan_test') {
    throw new RuntimeException('Database khusus tes wajib.');
}
if (($argv[1] ?? '') === 'worker') {
    while (microtime(true) < (float) $argv[4]) {
        usleep(1000);
    }
    try {
        Academic::submitKrs((int) $argv[2], (int) $argv[3]);
        echo 'diterima';
    } catch (ValidationException) {
        echo 'ditolak';
    }
    exit;
}
Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true]);
DB::table('kelas')->where('id', 5)->update(['kapasitas' => 2]);
$one = Academic::saveKrs(11, 2, [5]);
$two = Academic::saveKrs(12, 2, [5]);
$start = microtime(true) + 1;
$processes = [];
$outputs = [];
foreach ([[$one, 11], [$two, 12]] as [$id,$student]) {
    $pipes = [];
    $process = proc_open([PHP_BINARY, __FILE__, 'worker', (string) $id, (string) $student, (string) $start], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
    $processes[] = [$process, $pipes];
}
foreach ($processes as [$process,$pipes]) {
    $outputs[] = trim(stream_get_contents($pipes[1]));
    $errors = stream_get_contents($pipes[2]);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    if (proc_close($process) !== 0 || $errors) {
        throw new RuntimeException($errors);
    }
}
sort($outputs);
$reserved = Academic::reserved(5);
$success = $outputs === ['diterima', 'ditolak'] && $reserved === 2;
$result = ['uji' => 'dua pengajuan untuk satu kursi tersisa', 'hasil' => $outputs, 'kapasitas' => 2, 'kursi_awal' => 1, 'kursi_akhir' => $reserved, 'lulus' => $success, 'database' => DB::connection()->getDatabaseName()];
$evidence = dirname(__DIR__, 2).'/Bukti';
if (! is_dir($evidence)) {
    mkdir($evidence, 0777, true);
}
file_put_contents($evidence.'/concurrency.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($success ? 0 : 1);
