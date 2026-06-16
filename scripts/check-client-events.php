<?php

declare(strict_types=1);

$db = __DIR__.'/../database/instances/pruebas-retail.sqlite';
$pdo = new PDO('sqlite:'.$db);

$tables = ['message_queue', 'event_feed_projections', 'event_logs', 'middleware_bus_metrics'];
foreach ($tables as $table) {
    try {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        echo "{$table}: {$count}".PHP_EOL;
        if ($count > 0) {
            $sample = $pdo->query("SELECT * FROM {$table} ORDER BY rowid DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            echo '  last: '.json_encode($sample, JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    } catch (Throwable $e) {
        echo "{$table}: (missing)".PHP_EOL;
    }
}
