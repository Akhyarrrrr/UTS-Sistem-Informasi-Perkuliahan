<?php

$work = dirname(__DIR__, 2);
$credentials = json_decode(file_get_contents($work.'/Runtime/db-credentials.json'), true);
$database = 'uts_perkuliahan_sqltest_20261006';
$pdo = new PDO('mysql:host=127.0.0.1;port=3319;charset=utf8mb4', 'root', $credentials['root'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci");
$pdo->exec("USE `$database`");
if ($pdo->query('SHOW TABLES')->fetch()) {
    throw new RuntimeException('Database uji SQL sudah berisi tabel; verifikasi tidak menimpa data.');
}
$schema = file_get_contents($work.'/SQL/struktur.sql');
[$tables, $triggers] = explode('DELIMITER $$', $schema, 2);
$pdo->exec($tables);
foreach (explode('$$', str_replace('DELIMITER ;', '', $triggers)) as $trigger) {
    if (trim($trigger) !== '') {
        $pdo->exec(trim($trigger));
    }
}
$pdo->exec(file_get_contents($work.'/SQL/data-simulasi.sql'));
$counts = [];
foreach ($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
    $counts[$table] = (int) $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
}
$expected = json_decode(file_get_contents($work.'/Bukti/database.json'), true)['counts'];
$foreignKeys = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA='$database' AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);
$orphans = [];
foreach ($foreignKeys as $fk) {
    $query = "SELECT COUNT(*) FROM `{$fk['TABLE_NAME']}` c LEFT JOIN `{$fk['REFERENCED_TABLE_NAME']}` p ON p.`{$fk['REFERENCED_COLUMN_NAME']}`=c.`{$fk['COLUMN_NAME']}` WHERE c.`{$fk['COLUMN_NAME']}` IS NOT NULL AND p.`{$fk['REFERENCED_COLUMN_NAME']}` IS NULL";
    $orphans[$fk['TABLE_NAME'].'.'.$fk['COLUMN_NAME']] = (int) $pdo->query($query)->fetchColumn();
}
$queries = json_decode(file_get_contents($work.'/Bukti/database.json'), true)['queries'];
$reconstruction = $pdo->query($queries['rekonstruksi'])->fetchAll(PDO::FETCH_ASSOC);
$record = ['database' => $database, 'tables' => count($counts), 'counts_match' => ! array_diff_assoc($expected, $counts), 'rows' => $counts, 'foreign_keys_checked' => count($orphans), 'orphans' => $orphans, 'reconstructed_rows' => count($reconstruction), 'triggers' => count($pdo->query('SHOW TRIGGERS')->fetchAll()), 'passed' => ! array_diff_assoc($expected, $counts) && array_sum($orphans) === 0 && count($reconstruction) === 28];
file_put_contents($work.'/Bukti/sql-import.json', json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(array_diff_key($record, array_flip(['rows', 'orphans'])), JSON_PRETTY_PRINT).PHP_EOL;
if (! $record['passed']) {
    exit(1);
}
