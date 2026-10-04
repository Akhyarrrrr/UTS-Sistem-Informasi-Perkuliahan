<?php

$runtime = dirname(__DIR__, 2).'/Runtime';
$credentials = json_decode(file_get_contents($runtime.'/db-credentials.json'), true);
$pdo = new PDO('mysql:host=127.0.0.1;port=3319', 'root', $credentials['root']);
$pdo->exec('SHUTDOWN');
echo "MySQL UTS dihentikan dengan aman.\n";
