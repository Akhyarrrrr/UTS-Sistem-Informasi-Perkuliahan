<?php

$base = dirname(__DIR__);
$runtime = dirname($base, 2).'/Catatan_Pribadi/Layanan_Lokal';
$settings = getopt('', ['database:']);
$database = $settings['database'] ?? 'uts_perkuliahan';
if (! preg_match('/^uts_perkuliahan(?:_replay(?:_[0-9]{8})?)?$/', $database)) {
    throw new RuntimeException('Nama database instalasi tidak diizinkan.');
}
if (! is_dir($runtime)) {
    mkdir($runtime, 0777, true);
}
$path = $runtime.'/db-credentials.json';
$credentials = is_file($path) ? json_decode(file_get_contents($path), true) : ['root' => '', 'app' => bin2hex(random_bytes(20)), 'demo' => bin2hex(random_bytes(8)).'Aa7!'];
$pdo = new PDO('mysql:host=127.0.0.1;port=3319;charset=utf8mb4', 'root', $credentials['root'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach ([$database, 'uts_perkuliahan_test'] as $db) {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
}
$quoted = $pdo->quote($credentials['app']);
$pdo->exec("CREATE USER IF NOT EXISTS 'uts_app'@'localhost' IDENTIFIED BY $quoted");
foreach ([$database, 'uts_perkuliahan_test'] as $db) {
    $pdo->exec("GRANT ALL PRIVILEGES ON `$db`.* TO 'uts_app'@'localhost'");
}
if ($credentials['root'] === '') {
    $credentials['root'] = bin2hex(random_bytes(24));
    $pdo->exec("ALTER USER 'root'@'localhost' IDENTIFIED BY ".$pdo->quote($credentials['root']));
}
file_put_contents($path, json_encode($credentials, JSON_PRETTY_PRINT));
$env = file_get_contents(is_file($base.'/.env') ? $base.'/.env' : $base.'/.env.example');
if (! preg_match('/^APP_KEY=base64:/m', $env)) {
    $env = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=base64:'.base64_encode(random_bytes(32)), $env);
}
$values = ['APP_NAME' => '"Sistem Informasi Perkuliahan"', 'APP_ENV' => 'local', 'APP_DEBUG' => 'false', 'APP_URL' => 'http://localhost:8088', 'APP_LOCALE' => 'id', 'APP_FALLBACK_LOCALE' => 'id', 'DB_CONNECTION' => 'mysql', 'DB_HOST' => '127.0.0.1', 'DB_PORT' => '3319', 'DB_DATABASE' => 'uts_perkuliahan', 'DB_USERNAME' => 'uts_app', 'DB_PASSWORD' => $credentials['app'], 'SESSION_DRIVER' => 'database', 'CACHE_STORE' => 'database', 'QUEUE_CONNECTION' => 'sync', 'DEMO_PASSWORD' => $credentials['demo']];
$values['DB_DATABASE'] = $database;
foreach ($values as $key => $value) {
    $pattern = '/^#? ?'.preg_quote($key, '/').'=.*/m';
    $env = preg_match($pattern, $env) ? preg_replace($pattern, $key.'='.$value, $env) : $env."\n$key=$value";
}
file_put_contents($base.'/.env', $env);
file_put_contents($base.'/.env.testing', preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=uts_perkuliahan_test', str_replace(['APP_ENV=local', 'APP_DEBUG=false'], ['APP_ENV=testing', 'APP_DEBUG=true'], $env)));
file_put_contents($runtime.'/akses-demo.txt', "Akun demonstrasi lokal\nAdmin: admin@demo.test\nDosen: dosen@demo.test\nMahasiswa: mahasiswa@demo.test\nKata sandi akun demo: ".$credentials['demo']."\nData merupakan simulasi.\n");
echo "Konfigurasi lokal siap. Kredensial disimpan di Catatan_Pribadi/Layanan_Lokal, di luar repositori.\n";
