<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! $app->isLocal() || ! str_starts_with(DB::connection()->getDatabaseName(), 'uts_perkuliahan')) {
    fwrite(STDERR, 'Hanya untuk database UTS lokal.'.PHP_EOL);
    exit(1);
}
$password = config('app.demo_password');
if (! is_string($password) || strlen($password) < 16) {
    fwrite(STDERR, 'Jalankan configure-local.php terlebih dahulu.'.PHP_EOL);
    exit(1);
}
DB::table('users')->where('email', 'like', '%@demo.test')->update(['password' => Hash::make($password)]);
echo 'Akun simulasi diaktifkan menggunakan kredensial instalasi lokal.'.PHP_EOL;
