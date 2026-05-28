<?php

declare(strict_types=1);

$pdo = new PDO('sqlite:'.__DIR__.'/../database/instances/platform.sqlite');
$rows = $pdo->query(
    'SELECT id, status, progress_current, published, planned_total, error_message FROM simulation_runs ORDER BY created_at DESC LIMIT 5',
)->fetchAll(PDO::FETCH_ASSOC);

foreach ($rows as $row) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE).PHP_EOL;
}
