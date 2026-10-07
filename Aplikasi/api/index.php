<?php

$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
if (preg_match('~(?:^|/)\.[^/]*|\.php(?:/|$)~i', $requestPath)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Halaman tidak ditemukan.';
    exit;
}

$storage = sys_get_temp_dir().'/perkuliahan';

foreach (['app/private', 'framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    $path = $storage.'/'.$directory;
    if (! is_dir($path) && ! mkdir($path, 0700, true) && ! is_dir($path)) {
        throw new RuntimeException('Direktori sementara aplikasi tidak tersedia.');
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $storage;
$_ENV['VIEW_COMPILED_PATH'] = $storage.'/framework/views';
foreach (['CONFIG', 'EVENTS', 'PACKAGES', 'ROUTES', 'SERVICES'] as $cache) {
    $_ENV['APP_'.$cache.'_CACHE'] = $storage.'/framework/cache/'.strtolower($cache).'.php';
}

require __DIR__.'/../public/index.php';
