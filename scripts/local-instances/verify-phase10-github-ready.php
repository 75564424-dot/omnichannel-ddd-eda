<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

function check(string $label, bool $ok): void
{
    global $failures;
    echo ($ok ? 'PASS' : 'FAIL') . " {$label}\n";
    if (! $ok) {
        $failures[] = $label;
    }
}

// Filesystem
check('fs-no-env-client', count(glob($root . '/.env.client-*')) === 0);
check('fs-no-tenant-test-sqlite', count(glob($root . '/database/instances/tenant-test-*.sqlite*')) === 0);
check('fs-no-legacy-sqlite', count(array_filter(
    glob($root . '/database/instances/*.sqlite*') ?: [],
    fn ($p) => preg_match('/(acme-retail|pruebas-retail|lifecycle-test|unaprueba)/', $p)
)) === 0);
check('fs-no-wal-shm', count(glob($root . '/database/instances/*.sqlite-shm') ?: []) === 0
    && count(glob($root . '/database/instances/*.sqlite-wal') ?: []) === 0);
check('fs-no-modules-instances', ! is_dir($root . '/config/modules/instances') || count(glob($root . '/config/modules/instances/*')) === 0);
check('fs-no-handoffs', count(glob($root . '/storage/app/simulation-handoff/*')) === 0);
check('fs-no-simulation-pulse', ! is_file($root . '/storage/app/simulation-pulse.json'));
check('fs-no-launchers', count(glob($root . '/storage/app/simulation-launchers/*')) === 0);
check('fs-no-simulation-logs', count(glob($root . '/storage/logs/simulation-*')) === 0);

// Registry
$registryFile = $root . '/deploy/local-instances/fleet-registry.json';
$registry = is_file($registryFile) ? json_decode((string) file_get_contents($registryFile), true) : ['instances' => []];
check('registry-empty', ($registry['instances'] ?? []) === []);

// Control Plane DB
$db = $root . '/database/instances/platform.sqlite';
if (! is_file($db)) {
    check('cp-db-exists', false);
} else {
    $pdo = new PDO('sqlite:' . $db);
    $tenants = $pdo->query('SELECT slug FROM tenants')->fetchAll(PDO::FETCH_COLUMN);
    $badTenants = array_filter($tenants, fn ($s) => $s !== 'platform' || preg_match('/tenant-test-|acme-retail|pruebas-retail|lifecycle-test|unaprueba/', (string) $s));
    check('cp-only-platform-tenant', $tenants === ['platform']);
    check('cp-no-tenant-test', ! preg_grep('/tenant-test-/', $tenants));
    $runs = (int) $pdo->query('SELECT COUNT(*) FROM simulation_runs')->fetchColumn();
    check('cp-no-simulation-runs', $runs === 0);
    $orphanUsers = (int) $pdo->query(
        'SELECT COUNT(*) FROM users u LEFT JOIN tenants t ON u.tenant_id = t.id WHERE u.tenant_id IS NOT NULL AND t.id IS NULL'
    )->fetchColumn();
    check('cp-no-orphan-users', $orphanUsers === 0);
}

// Git status runtime artifacts (modified, not staged deletions)
$git = shell_exec('git status --short 2>&1') ?? '';
$runtimePatterns = [
    '/^\\s*M\\s+storage\\/logs\\//',
    '/^\\s*M\\s+storage\\/framework\\/views\\//',
    '/^\\s*M\\s+database\\/instances\\/.*\\.(sqlite-shm|sqlite-wal)$/',
    '/^\\s*M\\s+public\\/build\\//',
    '/^\\?\\?\\s+storage\\/logs\\//',
    '/^\\?\\?\\s+storage\\/framework\\/views\\//',
];
$runtimeHits = [];
foreach (explode("\n", trim($git)) as $line) {
    foreach ($runtimePatterns as $pattern) {
        if (preg_match($pattern, $line)) {
            $runtimeHits[] = trim($line);
        }
    }
}
check('git-no-runtime-modified', $runtimeHits === []);
if ($runtimeHits !== []) {
    echo '  runtime hits: ' . implode('; ', $runtimeHits) . "\n";
}

echo "\nSummary: " . count($failures) . " failures\n";
exit(count($failures) > 0 ? 1 : 0);
