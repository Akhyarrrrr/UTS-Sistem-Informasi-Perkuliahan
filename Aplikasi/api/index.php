<?php

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
