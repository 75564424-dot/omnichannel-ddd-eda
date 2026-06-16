<?php

declare(strict_types=1);

/**
 * One-time migration: Control/Middleware Simulation -> app/Simulation BC.
 * Run: php scripts/dev/migrate-simulation-bc.php
 */

$root = dirname(__DIR__, 2);

/** @var array<string, array{path: string, ns: string, src?: string}> */
$classMap = [
    'SimulationMessages' => ['path' => 'Domain/ValueObjects/SimulationMessages.php', 'ns' => 'App\\Simulation\\Domain\\ValueObjects'],
    'SimulationRunHandoffStore' => ['path' => 'Application/Services/Handoff/SimulationRunHandoffStore.php', 'ns' => 'App\\Simulation\\Application\\Services\\Handoff'],
    'SimulationRunHandoffSync' => ['path' => 'Application/Services/Handoff/SimulationRunHandoffSync.php', 'ns' => 'App\\Simulation\\Application\\Services\\Handoff'],
    'SimulationWorkerLauncher' => ['path' => 'Application/Services/Worker/SimulationWorkerLauncher.php', 'ns' => 'App\\Simulation\\Application\\Services\\Worker'],
    'SimulationWorkerEnvironmentFactory' => ['path' => 'Application/Services/Worker/SimulationWorkerEnvironmentFactory.php', 'ns' => 'App\\Simulation\\Application\\Services\\Worker'],
    'SimulationWorkerTenantBootstrap' => ['path' => 'Application/Services/Worker/SimulationWorkerTenantBootstrap.php', 'ns' => 'App\\Simulation\\Application\\Services\\Worker'],
    'SimulationRunWorkerMonitor' => ['path' => 'Application/Services/Worker/SimulationRunWorkerMonitor.php', 'ns' => 'App\\Simulation\\Application\\Services\\Worker'],
    'SimulationRunOrchestrator' => ['path' => 'Application/Services/Orchestration/SimulationRunOrchestrator.php', 'ns' => 'App\\Simulation\\Application\\Services\\Orchestration'],
    'SimulationRunQueryService' => ['path' => 'Application/Services/Orchestration/SimulationRunQueryService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Orchestration'],
    'SimulationRunStaleGuard' => ['path' => 'Application/Services/Orchestration/SimulationRunStaleGuard.php', 'ns' => 'App\\Simulation\\Application\\Services\\Orchestration'],
    'SimulationStaleRunReplacer' => ['path' => 'Application/Services/Orchestration/SimulationStaleRunReplacer.php', 'ns' => 'App\\Simulation\\Application\\Services\\Orchestration'],
    'LocalFleetSimulationRunner' => ['path' => 'Application/Services/Orchestration/LocalFleetSimulationRunner.php', 'ns' => 'App\\Simulation\\Application\\Services\\Orchestration'],
    'ClientSiloSimulationExecutor' => ['path' => 'Application/Services/Execution/ClientSiloSimulationExecutor.php', 'ns' => 'App\\Simulation\\Application\\Services\\Execution'],
    'ExecuteSimulationRunOnInstanceService' => ['path' => 'Application/Services/Execution/ExecuteSimulationRunOnInstanceService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Execution'],
    'TenantSimulationAutomationService' => ['path' => 'Application/Services/Execution/TenantSimulationAutomationService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Execution'],
    'SimulationProgressReporter' => ['path' => 'Application/Services/Progress/SimulationProgressReporter.php', 'ns' => 'App\\Simulation\\Application\\Services\\Progress'],
    'SimulationRunControlPlaneClient' => ['path' => 'Application/Services/Progress/SimulationRunControlPlaneClient.php', 'ns' => 'App\\Simulation\\Application\\Services\\Progress'],
    'SimulationRunInternalApiService' => ['path' => 'Application/Services/Progress/SimulationRunInternalApiService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Progress'],
    'SimulationRunCompletionService' => ['path' => 'Application/Services/Progress/SimulationRunCompletionService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Progress'],
    'SimulationRunFailureHandler' => ['path' => 'Application/Services/Progress/SimulationRunFailureHandler.php', 'ns' => 'App\\Simulation\\Application\\Services\\Progress'],
    'SimulationRunMetricsCollector' => ['path' => 'Application/Services/Metrics/SimulationRunMetricsCollector.php', 'ns' => 'App\\Simulation\\Application\\Services\\Metrics'],
    'SimulationInstancePrepareService' => ['path' => 'Application/Services/Prepare/SimulationInstancePrepareService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Prepare'],
    'SimulationTenantSettingsSync' => ['path' => 'Application/Services/Prepare/SimulationTenantSettingsSync.php', 'ns' => 'App\\Simulation\\Application\\Services\\Prepare'],
    'SimulationDiagnosticsReader' => ['path' => 'Application/Services/Prepare/SimulationDiagnosticsReader.php', 'ns' => 'App\\Simulation\\Application\\Services\\Prepare'],
    'InstanceSimulationReadinessService' => ['path' => 'Application/Services/Prepare/InstanceSimulationReadinessService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Prepare'],
    'SimulationRunsResetService' => ['path' => 'Application/Services/Reset/SimulationRunsResetService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Reset'],
    'SimulationPublishScope' => ['path' => 'Application/Services/Runtime/SimulationPublishScope.php', 'ns' => 'App\\Simulation\\Application\\Services\\Runtime', 'src' => 'Middleware'],
    'SimulationQueueDrainer' => ['path' => 'Application/Services/Runtime/SimulationQueueDrainer.php', 'ns' => 'App\\Simulation\\Application\\Services\\Runtime', 'src' => 'Middleware'],
    'SimulationPulseService' => ['path' => 'Application/Services/Runtime/SimulationPulseService.php', 'ns' => 'App\\Simulation\\Application\\Services\\Runtime', 'src' => 'Middleware'],
];

/**
 * @return list<string>
 */
function simulationSourceCandidates(string $root, string $class, string $layer): array
{
    if ($layer === 'Middleware') {
        return [
            "{$root}/app/Middleware/Application/Services/Simulation/{$class}.php",
            "{$root}/app/Middleware/Application/Services/{$class}.php",
        ];
    }

    return [
        "{$root}/app/Control/Application/Services/Simulation/{$class}.php",
        "{$root}/app/Control/Application/Services/{$class}.php",
    ];
}

function simulationResolveSource(string $root, string $class, string $layer): ?string
{
    foreach (simulationSourceCandidates($root, $class, $layer) as $candidate) {
        if (is_readable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function simulationDetectNamespace(string $content): ?string
{
    if (preg_match('/^namespace\s+([^;]+);/m', $content, $matches) === 1) {
        return trim($matches[1]).'\\';
    }

    return null;
}

/**
 * @param array<string, array{path: string, ns: string, src?: string}> $classMap
 */
function simulationFixImports(string $content, string $currentFqcn, array $classMap): string
{
    $currentNs = substr($currentFqcn, 0, (int) strrpos($currentFqcn, '\\') + 1);
    $uses = [];

    foreach ($classMap as $class => $meta) {
        $targetFqcn = $meta['ns'].'\\'.$class;
        if ($targetFqcn === $currentFqcn || str_starts_with($targetFqcn, $currentNs)) {
            continue;
        }

        if (preg_match('/(?<![\\\\\w])'.preg_quote($class, '/').'(?![\\\\\w])/', $content) !== 1) {
            continue;
        }

        $uses[$targetFqcn] = $class;
    }

    if ($uses === []) {
        return $content;
    }

    ksort($uses);

    $useLines = '';
    foreach ($uses as $fqcn => $alias) {
        $useLines .= "use {$fqcn};\n";
    }

    return preg_replace(
        '/(namespace\s+[^;]+;\s*\n)/',
        "$1\n{$useLines}",
        $content,
        1,
    ) ?? $content;
}

/** @var array<string, string> */
$fqcnMap = [];
foreach ($classMap as $class => $meta) {
    $fqcnMap[$meta['ns'].'\\'.$class] = $class;
}

foreach ($classMap as $class => $meta) {
    $layer = ($meta['src'] ?? 'Control') === 'Middleware' ? 'Middleware' : 'Control';
    $src = simulationResolveSource($root, $class, $layer);
    if ($src === null) {
        fwrite(STDERR, "Missing source for {$class}\n");
        exit(1);
    }

    $dest = "{$root}/app/Simulation/{$meta['path']}";
    $destDir = dirname($dest);
    if (! is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $content = file_get_contents($src);
    $oldNs = simulationDetectNamespace($content);
    if ($oldNs === null) {
        fwrite(STDERR, "Could not detect namespace in {$src}\n");
        exit(1);
    }

    $content = preg_replace(
        '/^namespace\s+[^;]+;/m',
        'namespace '.$meta['ns'].';',
        $content,
        1,
    ) ?? $content;

    $currentFqcn = $meta['ns'].'\\'.$class;
    $content = simulationFixImports($content, $currentFqcn, $classMap);

    file_put_contents($dest, $content);
    echo "Wrote {$dest}\n";
}

/** @var array<string, string> $replacements */
$replacements = [];
foreach ($classMap as $class => $meta) {
    $newFqcn = $meta['ns'].'\\'.$class;
    $replacements["App\\Control\\Application\\Services\\Simulation\\{$class}"] = $newFqcn;
    $replacements["App\\Control\\Application\\Services\\{$class}"] = $newFqcn;
    $replacements["App\\Middleware\\Application\\Services\\Simulation\\{$class}"] = $newFqcn;
    $replacements["App\\Middleware\\Application\\Services\\{$class}"] = $newFqcn;
}

$scanDirs = [
    "{$root}/app",
    "{$root}/tests",
    "{$root}/routes",
    "{$root}/bootstrap",
    "{$root}/scripts",
];

foreach ($scanDirs as $dir) {
    if (! is_dir($dir)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($it as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        if (str_contains($path, DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR)) {
            continue;
        }
        if (str_contains($path, 'migrate-simulation-bc.php')) {
            continue;
        }
        if (str_contains($path, DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Simulation'.DIRECTORY_SEPARATOR)) {
            continue;
        }

        $content = file_get_contents($path);
        $original = $content;
        foreach ($replacements as $from => $to) {
            $content = str_replace($from, $to, $content);
        }

        if ($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated refs in {$path}\n";
        }
    }
}

foreach ($classMap as $class => $meta) {
    $layer = ($meta['src'] ?? 'Control') === 'Middleware' ? 'Middleware' : 'Control';
    foreach (simulationSourceCandidates($root, $class, $layer) as $src) {
        if (is_file($src)) {
            unlink($src);
            echo "Deleted {$src}\n";
        }
    }
}

echo "Migration complete.\n";
