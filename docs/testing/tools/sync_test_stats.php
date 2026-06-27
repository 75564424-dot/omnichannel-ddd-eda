<?php

declare(strict_types=1);

/**
 * Sincroniza conteos de tests en docs/testing/README.md (Plan_Calidad).
 * Uso: php docs/testing/tools/sync_test_stats.php [--check] [--run-phpunit]
 *
 * Por defecto lee docs/testing/tools/last_junit.xml si existe (sin ejecutar PHPUnit).
 */
$base = dirname(__DIR__, 3);
$readmePath = $base.'/docs/testing/README.md';
$junitPath = $base.'/docs/testing/tools/last_junit.xml';
$checkOnly = in_array('--check', $argv ?? [], true);
$forceRun = in_array('--run-phpunit', $argv ?? [], true);

function countTestMethods(string $dir): int
{
    if (! is_dir($dir)) {
        return 0;
    }

    $count = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $php = file_get_contents($file->getPathname());
        if ($php === false) {
            continue;
        }

        preg_match_all('/\#\[Test\]/', $php, $m1);
        preg_match_all('/public function test[A-Za-z0-9_]+\s*\(/', $php, $m2);
        $count += count($m1[0]) + count($m2[0]);
    }

    return $count;
}

function countBySuite(string $base): array
{
    return [
        'Unit'        => countTestMethods($base.'/tests/Unit'),
        'Integration' => countTestMethods($base.'/tests/Integration'),
        'Feature'     => countTestMethods($base.'/tests/Feature'),
        'E2E'         => countTestMethods($base.'/tests/E2E'),
    ];
}

function statsFromJUnit(string $path): ?array
{
    if (! is_readable($path)) {
        return null;
    }
    $xml = simplexml_load_file($path);
    if ($xml === false) {
        return null;
    }
    $attrs = $xml->testsuite->attributes();
    if ($attrs === null) {
        return null;
    }
    $tests = (int) ($attrs['tests'] ?? 0);
    $assertions = (int) ($attrs['assertions'] ?? 0);
    $failures = (int) ($attrs['failures'] ?? 0);
    $errors = (int) ($attrs['errors'] ?? 0);
    $mtime = filemtime($path);

    return [
        'tests'      => $tests,
        'assertions' => $assertions,
        'failures'   => $failures,
        'errors'     => $errors,
        'date'       => $mtime ? date('Y-m-d', $mtime) : date('Y-m-d'),
        'status'     => ($failures + $errors) > 0 ? 'FAILURES' : 'OK',
        'source'     => 'junit',
    ];
}

function runPHPUnitStats(string $base): ?array
{
    $cmd = 'php '.escapeshellarg($base.'/vendor/bin/phpunit').' -c '.escapeshellarg($base.'/phpunit.xml').' 2>&1';
    exec($cmd, $output, $code);

    $joined = implode("\n", $output);
    if (preg_match('/OK \((\d+) tests?, (\d+) assertions?\)/', $joined, $m)) {
        return [
            'tests'      => (int) $m[1],
            'assertions' => (int) $m[2],
            'failures'   => 0,
            'errors'     => 0,
            'date'       => date('Y-m-d'),
            'status'     => 'OK',
            'source'     => 'phpunit',
        ];
    }

    if (preg_match('/Tests: (\d+), Assertions: (\d+), Failures: (\d+)/', $joined, $m)) {
        $errors = 0;
        if (preg_match('/Errors: (\d+)/', $joined, $e)) {
            $errors = (int) $e[1];
        }

        return [
            'tests'      => (int) $m[1],
            'assertions' => (int) $m[2],
            'failures'   => (int) $m[3],
            'errors'     => $errors,
            'date'       => date('Y-m-d'),
            'status'     => 'FAILURES',
            'source'     => 'phpunit',
        ];
    }

    if ($code !== 0) {
        fwrite(STDERR, "PHPUnit run failed — no parseable output.\n");
    }

    return null;
}

function resolveRuntimeStats(string $base, string $junitPath, bool $forceRun): ?array
{
    if ($forceRun) {
        return runPHPUnitStats($base);
    }
    $fromJUnit = statsFromJUnit($junitPath);
    if ($fromJUnit !== null) {
        return $fromJUnit;
    }

    fwrite(STDERR, "No last_junit.xml — run: php vendor/bin/phpunit --log-junit docs/testing/tools/last_junit.xml\n");

    return null;
}

if (! is_readable($readmePath)) {
    fwrite(STDERR, "README not found: {$readmePath}\n");
    exit(1);
}

$suites = countBySuite($base);
$staticTotal = array_sum($suites);
$runtime = resolveRuntimeStats($base, $junitPath, $forceRun);

$tests = $runtime['tests'] ?? $staticTotal;
$assertions = $runtime['assertions'] ?? null;
$date = $runtime['date'] ?? date('Y-m-d');

$readme = file_get_contents($readmePath);
if ($readme === false) {
    fwrite(STDERR, "Unable to read README.\n");
    exit(1);
}

$suiteLines = [];
foreach ($suites as $name => $count) {
    $suiteLines[] = "- **{$name}:** {$count} métodos `#[Test]`";
}
$suiteBlock = implode("\n", $suiteLines);

$status = $runtime['status'] ?? null;
$failures = $runtime['failures'] ?? 0;
$errors = $runtime['errors'] ?? 0;

if ($status === 'OK' && $assertions !== null) {
    $assertionLine = "- **Resultado:** OK ({$tests} tests, {$assertions} assertions)";
} elseif ($status === 'FAILURES' && $assertions !== null) {
    $assertionLine = "- **Resultado:** **FALLÓ** ({$tests} tests, {$assertions} assertions, {$failures} failures, {$errors} errors)";
} else {
    $assertionLine = "- **Resultado:** ~{$tests} tests (conteo estático de métodos)";
}

$sourceNote = isset($runtime['source']) ? " (fuente: {$runtime['source']})" : '';

$replacement = <<<MD
## Resultado real (auto-sincronizado)

- **Fecha:** {$date}  
- **Comando:** \`php vendor/bin/phpunit\`{$sourceNote}  
{$assertionLine}

### Desglose por suite (métodos de test)

{$suiteBlock}

> Actualizado por \`php docs/testing/tools/sync_test_stats.php\` — ejecutar tras añadir tests (\`composer test:stats\`).
MD;

$patterns = [
    '/## Resultado real \(auto-sincronizado\).*?(?=\n## Estadísticas carpeta)/s',
    '/## Resultado real \(auto-sincronizado\).*?(?=\n## Observaciones)/s',
    '/## Resultado real \(última verificación documentada\).*?(?=## Observaciones)/s',
];

$matched = false;
$newReadme = $readme;
foreach ($patterns as $pattern) {
    if (preg_match($pattern, $readme)) {
        $newReadme = preg_replace($pattern, $replacement."\n\n", $readme, 1);
        $matched = true;
        break;
    }
}

if ($checkOnly) {
    $ok = str_contains($readme, (string) $tests);
    foreach ($suites as $name => $count) {
        if (! preg_match('/\*\*'.$name.':\*\* '.$count.' métodos/', $readme)) {
            $ok = false;
            break;
        }
    }

    if ($ok) {
        fwrite(STDOUT, "Test stats in README are up to date ({$tests} tests).\n");
        exit(0);
    }

    fwrite(STDERR, "docs/testing/README.md test counts are outdated. Run: composer test:stats\n");
    exit(1);
}

if (! $matched || $newReadme === null || $newReadme === $readme) {
    fwrite(STDERR, "Could not update README stats section.\n");
    exit(1);
}

file_put_contents($readmePath, $newReadme);
fwrite(STDOUT, "Updated {$readmePath} — {$tests} tests documented.\n");
