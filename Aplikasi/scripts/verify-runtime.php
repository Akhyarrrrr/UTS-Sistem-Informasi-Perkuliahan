<?php
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use App\Services\Academic;
require dirname(__DIR__).'/vendor/autoload.php';$app=require dirname(__DIR__).'/bootstrap/app.php';$app->make(Kernel::class)->bootstrap();
$evidence=dirname(__DIR__,2).'/Bukti';
$state=[];
foreach(['fakultas','prodi','mahasiswa','dosen','matakuliah','periode','ruang','kelas','jadwal','krs','krs_detail','komponen_nilai','nilai_komponen','skala_nilai','pertemuan','presensi','activity_log'] as $table)$state[$table]=DB::table($table)->orderBy('id')->get()->all();
$hash=hash('sha256',json_encode($state));
if(($argv[1]??'')==='capture'){
    file_put_contents($evidence.'/persistence.json',json_encode(['sebelum_restart'=>$hash,'database'=>DB::connection()->getDatabaseName()],JSON_PRETTY_PRINT));
    echo 'Snapshot data akademik sebelum restart tersimpan.'.PHP_EOL;
}else{
    $record=json_decode(file_get_contents($evidence.'/persistence.json'),true);$record['sesudah_restart']=$hash;$record['lulus']=$record['sebelum_restart']===$hash;
    file_put_contents($evidence.'/persistence.json',json_encode($record,JSON_PRETTY_PRINT));
    echo json_encode($record,JSON_PRETTY_PRINT).PHP_EOL;
    if(!$record['lulus'])exit(1);
}
if(($argv[1]??'')==='http'){
    $context=stream_context_create(['http'=>['method'=>'POST','header'=>'Content-Type: application/x-www-form-urlencoded','content'=>'email=admin%40demo.test&password=invalid','ignore_errors'=>true]]);
    file_get_contents('http://localhost:8088/login',false,$context);
    $status=$http_response_header[0]??'';
    file_put_contents($evidence.'/csrf.json',json_encode(['tanpa_token'=>$status,'lulus'=>str_contains($status,'419')],JSON_PRETTY_PRINT));
    echo $status.PHP_EOL;
}
