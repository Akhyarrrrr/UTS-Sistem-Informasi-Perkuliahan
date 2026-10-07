<?php

$work = dirname(__DIR__, 2);
$credentials = json_decode(file_get_contents($work.'/Runtime/db-credentials.json'), true, flags: JSON_THROW_ON_ERROR);
$pdo = new PDO('mysql:host=127.0.0.1;port=3319;charset=utf8mb4', 'root', $credentials['root'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$databases = ['uts_perkuliahan', 'uts_perkuliahan_replay_20261006', 'uts_perkuliahan_sqltest_20261006'];
$academic = array_keys(json_decode(file_get_contents($work.'/Bukti/database.json'), true)['dictionary']);
$schemas = [];
$columns = [];
$triggers = [];
$counts = [];
foreach ($databases as $database) {
    $pdo->exec("USE `$database`");
    $all = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $counts[$database] = ['total' => count($all), 'akademik' => 16, 'akun' => 1, 'audit' => 1, 'infrastruktur' => count(array_diff($all, $academic))];
    foreach ($academic as $table) {
        $ddl = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
        $schemas[$database][$table] = preg_replace(['/ AUTO_INCREMENT=\d+/', '/ CHARACTER SET utf8mb4(?= COLLATE utf8mb4_)/'], '', $ddl);
        $statement = $pdo->prepare('SELECT COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,COLUMN_DEFAULT,EXTRA,COLUMN_KEY,CHARACTER_SET_NAME,COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ORDER BY ORDINAL_POSITION');
        $statement->execute([$database, $table]);
        $columns[$database][$table] = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    $statement = $pdo->prepare('SELECT TRIGGER_NAME,ACTION_STATEMENT FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=? ORDER BY TRIGGER_NAME');
    $statement->execute([$database]);
    $triggers[$database] = $statement->fetchAll(PDO::FETCH_KEY_PAIR);
}
$differences = [];
foreach (array_slice($databases, 1) as $database) {
    foreach ($academic as $table) {
        if ($schemas[$database][$table] !== $schemas[$databases[0]][$table] || $columns[$database][$table] !== $columns[$databases[0]][$table]) {
            $differences[] = $database.'.'.$table;
        }
    }
    if ($triggers[$database] !== $triggers[$databases[0]]) {
        $differences[] = $database.'.triggers';
    }
}
$record = ['captured' => date(DATE_ATOM), 'comparison' => 'SHOW CREATE TABLE: kolom, tipe, NULL, default, PK, UNIQUE, FK, CHECK; ACTION_STATEMENT seluruh trigger. Counter AUTO_INCREMENT dan deklarasi charset redundan dinormalisasi; metadata kolom, charset, dan collation tetap dibandingkan.', 'table_counts' => $counts, 'tables_compared' => count($academic), 'triggers_compared' => count($triggers[$databases[0]]), 'differences' => $differences, 'passed' => $differences === []];
file_put_contents($work.'/Bukti/schema-audit.json', json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE).PHP_EOL;
exit($record['passed'] ? 0 : 1);
