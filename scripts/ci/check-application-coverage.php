<?php

declare(strict_types=1);

/**
 * Verifica cobertura mínima en capas Application (Middleware + Dashboard + Shared/Platform).
 * Uso: php scripts/ci/check-application-coverage.php build/coverage/clover.xml 70
 */
if ($argc < 3) {
    fwrite(STDERR, "Usage: php {$argv[0]} <clover.xml> <min-percent>\n");
    exit(1);
}

$cloverPath = $argv[1];
$minPercent = (float) $argv[2];

if (! is_readable($cloverPath)) {
    fwrite(STDERR, "Clover file not readable: {$cloverPath}\n");
    exit(1);
}

$xml = simplexml_load_file($cloverPath);
if ($xml === false) {
    fwrite(STDERR, "Unable to parse clover XML: {$cloverPath}\n");
    exit(1);
}

$prefixes = [
    'App\\Middleware\\Application\\',
    'App\\Dashboard\\Application\\',
    'App\\Integration\\Application\\',
    'App\\Monitoring\\Application\\',
    'App\\Observability\\Application\\',
    'App\\Shared\\Platform\\',
];

$statements = 0;
$covered = 0;

foreach ($xml->project->file as $file) {
    $path = str_replace('\\', '/', (string) $file['name']);
    if (! str_contains($path, '/Application/') && ! str_contains($path, '/Shared/Platform/')) {
        continue;
    }

    $matchesLayer = false;
    foreach ($prefixes as $prefix) {
        $needle = str_replace('\\', '/', $prefix);
        if (str_contains($path, $needle)) {
            $matchesLayer = true;
            break;
        }
    }

    if (! $matchesLayer) {
        continue;
    }

    foreach ($file->line as $line) {
        if ((string) $line['type'] !== 'stmt') {
            continue;
        }
        $statements++;
        if ((int) $line['count'] > 0) {
            $covered++;
        }
    }
}

if ($statements === 0) {
    fwrite(STDERR, "No Application-layer statements found in clover report.\n");
    exit(1);
}

$percent = ($covered / $statements) * 100;
$formatted = number_format($percent, 2);

fwrite(STDOUT, "Application layer coverage: {$formatted}% ({$covered}/{$statements} statements)\n");

if ($percent + 0.0001 < $minPercent) {
    fwrite(STDERR, "Coverage gate failed: {$formatted}% < {$minPercent}%\n");
    exit(1);
}

fwrite(STDOUT, "Coverage gate passed (>= {$minPercent}%).\n");
exit(0);
