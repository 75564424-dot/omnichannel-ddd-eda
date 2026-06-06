<?php

declare(strict_types=1);

$path = dirname(__DIR__, 2) . '/database/instances/platform.sqlite';
if (! is_file($path)) {
    fwrite(STDERR, "platform.sqlite not found\n");
    exit(1);
}

$pdo = new PDO('sqlite:' . $path);
$pdo->exec('PRAGMA wal_checkpoint(TRUNCATE)');
echo "checkpoint ok\n";
