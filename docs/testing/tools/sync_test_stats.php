<?php

declare(strict_types=1);

/**
 * Sincroniza conteos de tests en docs/testing/README.md (Plan_Calidad).
 * Uso: php docs/testing/tools/sync_test_stats.php [--check]
 */
$base = dirname(__DIR__, 3);
$readmePath = $base.'/docs/testing/README.md';
$checkOnly = in_array('--check', $argv ?? [], true);

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

function runPHPUnitStats(string $base): ?array
{
    $cmd = 'php '.escapeshellarg($base.'/vendor/bin/phpunit').' -c '.escapeshellarg($base.'/phpunit.xml').' 2>&1';
    exec($cmd, $output, $code);

    if ($code !== 0) {
        fwrite(STDERR, "PHPUnit run failed — using static method counts only.\n");

        return null;
    }

    $joined = implode("\n", $output);
    if (preg_match('/OK \((\d+) tests?, (\d+) assertions?\)/', $joined, $m)) {
        return [
            'tests'      => (int) $m[1],
            'assertions' => (int) $m[2],
            'failures'   => 0,
            'errors'     => 0,
            'date'       => date('Y-m-d'),
            'status'     => 'OK',
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
        ];
    }

    return null;
}

if (! is_readable($readmePath)) {
    fwrite(STDERR, "README not found: {$readmePath}\n");
    exit(1);
}

$suites = countBySuite($base);
$staticTotal = array_sum($suites);
$runtime = runPHPUnitStats($base);

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

$replacement = <<<MD
## Resultado real (auto-sincronizado)

- **Fecha:** {$date}  
- **Comando:** \`composer test\` / \`php vendor/bin/phpunit\`  
{$assertionLine}

### Desglose por suite (métodos de test)

{$suiteBlock}

> Actualizado por \`php docs/testing/tools/sync_test_stats.php\` — ejecutar tras añadir tests o en CI (\`composer test:stats\`).
MD;

$pattern = '/## Resultado real \([^)]+\).*?(?=\n## Observaciones)/s';
if (! preg_match($pattern, $readme)) {
    $pattern = '/## Resultado real \(última verificación documentada\).*?(?=## Observaciones)/s';
}

if ($checkOnly) {
    $ok = preg_match('/\*\*Resultado:\*\* OK \((\d+) tests?, (\d+) assertions?\)/', $readme, $documented)
        && (int) $documented[1] === $tests
        && (int) $documented[2] === ($assertions ?? -1);

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

$newReadme = preg_replace($pattern, $replacement."\n\n", $readme, 1);
if ($newReadme === null || $newReadme === $readme) {
    fwrite(STDERR, "Could not update README stats section.\n");
    exit(1);
}

file_put_contents($readmePath, $newReadme);
fwrite(STDOUT, "Updated {$readmePath} — {$tests} tests documented.\n");
