<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! str_starts_with(DB::connection()->getDatabaseName(), 'uts_perkuliahan')) {
    throw new RuntimeException('Hanya untuk database UTS lokal.');
}
$password = env('DEMO_PASSWORD');
if (! $password) {
    throw new RuntimeException('Jalankan configure-local.php terlebih dahulu.');
}
DB::table('users')->where('email', 'like', '%@demo.test')->update(['password' => Hash::make($password)]);
echo 'Akun simulasi diaktifkan menggunakan kredensial instalasi lokal.'.PHP_EOL;
