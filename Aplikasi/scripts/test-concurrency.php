<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\MasterController;
use App\Models\User;
use App\Services\Academic;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->loadEnvironmentFrom('.env.testing');
$app->make(Kernel::class)->bootstrap();

if (DB::connection()->getDatabaseName() !== 'uts_perkuliahan_test') {
    throw new RuntimeException('Database khusus tes wajib.');
}
if (in_array($argv[1] ?? '', ['worker', 'meeting-worker', 'admin-worker'], true)) {
    if ($argv[1] === 'meeting-worker') {
        Auth::setUser(User::where('email', 'dosen@demo.test')->firstOrFail());
    }
    if ($argv[1] === 'admin-worker') {
        Auth::setUser(User::findOrFail((int) $argv[3]));
    }
    while (microtime(true) < (float) $argv[4]) {
        usleep(1000);
    }
    try {
        if ($argv[1] === 'worker') {
            Academic::submitKrs((int) $argv[2], (int) $argv[3]);
        } elseif ($argv[1] === 'meeting-worker') {
            $request = Request::create('/kelas/4/pertemuan', 'POST', ['nomor' => 32, 'tanggal' => '2026-10-06', 'topik' => 'Uji pertemuan serentak']);
            $request->setUserResolver(fn () => Auth::user());
            (new AcademicController)->createMeeting($request, 4);
        } else {
            $request = Request::create('/admin/users/'.$argv[2], 'DELETE');
            $route = (new Route('DELETE', '/admin/users/{id}', []))->name('master.users.destroy');
            $request->setRouteResolver(fn () => $route);
            (new MasterController)->destroy($request, (int) $argv[2]);
        }
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
function workers(string $kind, array $pairs): array
{
    $start = microtime(true) + 1;
    $processes = [];
    $outputs = [];
    foreach ($pairs as [$id,$student]) {
        $pipes = [];
        $process = proc_open([PHP_BINARY, __FILE__, $kind, (string) $id, (string) $student, (string) $start], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, dirname(__DIR__));
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

    return $outputs;
}
$outputs = workers('worker', [[$one, 11], [$two, 12]]);
$reserved = Academic::reserved(5);
$success = $outputs === ['diterima', 'ditolak'] && $reserved === 2;
$result = ['uji' => 'dua pengajuan untuk satu kursi tersisa', 'hasil' => $outputs, 'kapasitas' => 2, 'kursi_awal' => 1, 'kursi_akhir' => $reserved, 'lulus' => $success, 'database' => DB::connection()->getDatabaseName()];
$meetingOutputs = workers('meeting-worker', [[4, 0], [4, 0]]);
$meetingCount = DB::table('pertemuan')->where('kelas_id', 4)->where('nomor', 32)->count();
$result['pertemuan_serentak'] = ['hasil' => $meetingOutputs, 'baris' => $meetingCount, 'lulus' => $meetingOutputs === ['diterima', 'ditolak'] && $meetingCount === 1];
$secondAdmin = DB::table('users')->insertGetId(['name' => 'Admin konkurensi', 'email' => 'concurrent-admin@demo.test', 'role' => 'admin', 'password' => '!akun-tes-terkunci!']);
$adminOutputs = workers('admin-worker', [[$secondAdmin, 1], [1, $secondAdmin]]);
$adminCount = DB::table('users')->where('role', 'admin')->count();
$result['admin_terakhir'] = ['hasil' => $adminOutputs, 'admin_tersisa' => $adminCount, 'lulus' => $adminOutputs === ['diterima', 'ditolak'] && $adminCount === 1];
$success = $success && $result['pertemuan_serentak']['lulus'] && $result['admin_terakhir']['lulus'];
$result['lulus'] = $success;
$evidence = dirname(__DIR__, 2).'/Bukti';
if (! is_dir($evidence)) {
    mkdir($evidence, 0777, true);
}
file_put_contents($evidence.'/concurrency.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($success ? 0 : 1);
