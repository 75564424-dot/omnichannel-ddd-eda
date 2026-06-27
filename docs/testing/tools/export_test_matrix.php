<?php

declare(strict_types=1);

/**
 * Exporta matriz maestra de casos de prueba a CSV.
 * Uso: php docs/testing/tools/export_test_matrix.php [--junit=path]
 */
$base = dirname(__DIR__, 3);
$junitPath = $base.'/docs/testing/tools/last_junit.xml';

foreach ($argv ?? [] as $arg) {
    if (str_starts_with($arg, '--junit=')) {
        $junitPath = substr($arg, 8);
    }
}

function moduleFromPath(string $rel): string
{
    $map = [
        '/Middleware/' => 'Middleware',
        '/Dashboard/' => 'Dashboard',
        '/Control/' => 'Control',
        '/Integration/' => 'Integration',
        '/Observability/' => 'Observability',
        '/Monitoring/' => 'Monitoring',
        '/Platform/' => 'Platform',
        '/Simulation/' => 'Simulation',
        '/Security/' => 'Security',
        '/Identity/' => 'Identity',
        '/EventBus/' => 'EventBus',
        '/Quality/' => 'Quality',
        '/Api/' => 'API',
        '/Health/' => 'Health',
        '/Logging/' => 'Logging',
        '/Console/' => 'Console',
        '/Providers/' => 'Providers',
        '/Http/' => 'Http',
        '/Shared/' => 'Shared',
    ];
    foreach ($map as $needle => $module) {
        if (str_contains($rel, $needle)) {
            return $module;
        }
    }

    return 'Transversal';
}

function layerFromRel(string $rel): string
{
    if (str_starts_with($rel, 'tests/Unit/')) {
        return 'Unitaria';
    }
    if (str_starts_with($rel, 'tests/Integration/')) {
        return 'Integración';
    }
    if (str_starts_with($rel, 'tests/Feature/')) {
        return 'Funcional';
    }
    if (str_starts_with($rel, 'tests/E2E/')) {
        return 'End-to-End';
    }

    return 'Desconocida';
}

function classifyTest(string $layer, string $module, string $class): string
{
    if (str_contains($class, 'Security') || str_contains($class, 'Auth') || str_contains($class, 'Login')) {
        return 'Seguridad';
    }
    if (str_contains($class, 'Prometheus') || str_contains($class, 'Correlation') || str_contains($class, 'Trace')) {
        return 'Observabilidad';
    }
    if (str_contains($class, 'OpenApi') || str_contains($class, 'Api') || str_contains($class, 'Idempotency')) {
        return 'API';
    }
    if ($module === 'Middleware') {
        return 'Middleware';
    }
    if ($layer === 'End-to-End') {
        return 'End-to-End';
    }
    if ($layer === 'Unitaria') {
        return 'Unitaria';
    }
    if ($layer === 'Integración') {
        return 'Integración';
    }

    return 'Funcional';
}

function bpmnFromModule(string $module, string $class): string
{
    $map = [
        'Middleware' => 'PROC-001',
        'Dashboard' => 'PROC-004',
        'Control' => 'PROC-007',
        'Integration' => 'PROC-011',
        'Observability' => 'PROC-013',
        'Monitoring' => 'PROC-013',
        'Platform' => 'PROC-009',
        'Simulation' => 'PROC-009',
        'Security' => 'PROC-006',
        'Identity' => 'PROC-005',
        'EventBus' => 'PROC-001',
        'Quality' => 'PROC-016',
        'API' => 'PROC-003',
    ];
    if (str_contains($class, 'Webhook')) {
        return 'PROC-011';
    }
    if (str_contains($class, 'Simulate') || str_contains($class, 'Simulation')) {
        return 'PROC-009';
    }
    if (str_contains($class, 'Purge') || str_contains($class, 'Retention')) {
        return 'PROC-014';
    }
    if (str_contains($class, 'Catalog') || str_contains($class, 'ValidatePlatform')) {
        return 'PROC-016';
    }
    if (str_contains($class, 'TenantLifecycle') || str_contains($class, 'Provisioning')) {
        return 'PROC-008';
    }
    if (str_contains($class, 'Portal') || str_contains($class, 'InstancePortal')) {
        return 'PROC-019';
    }
    if (str_contains($class, 'Incident') || str_contains($class, 'Support')) {
        return 'PROC-015';
    }

    return $map[$module] ?? 'PROC-001';
}

function priorityFrom(string $layer, string $module): string
{
    if (in_array($module, ['Middleware', 'Control', 'Security', 'Identity'], true)) {
        return 'Alta';
    }
    if ($layer === 'End-to-End') {
        return 'Alta';
    }

    return 'Media';
}

function criticalityFrom(string $module): string
{
    return in_array($module, ['Middleware', 'Control', 'Security'], true) ? 'Crítica' : 'Alta';
}

function parseJUnit(string $path): array
{
    if (! is_readable($path)) {
        return [];
    }
    $xml = simplexml_load_file($path);
    if ($xml === false) {
        return [];
    }
    $results = [];
    foreach ($xml->xpath('//testcase') as $tc) {
        $class = (string) $tc['class'];
        $method = (string) $tc['name'];
        $key = $class.'::'.$method;
        $failed = isset($tc->failure) || isset($tc->error);
        $results[$key] = $failed ? 'FALLÓ' : 'PASÓ';
    }

    return $results;
}

function extractTests(string $php): array
{
    $methods = [];
    if (preg_match_all('/\#\[Test\](?:\s*\([^)]*\))?\s*\n(?:\s*\#[^\n]+\s*\n)*\s*public function (\w+)\s*\(/m', $php, $m)) {
        $methods = array_merge($methods, $m[1]);
    }
    if (preg_match_all('/public function (test[A-Za-z0-9_]+)\s*\([^)]*\)\s*:\s*void/m', $php, $m2)) {
        $methods = array_merge($methods, $m2[1]);
    }

    return array_values(array_unique($methods));
}

$junitResults = parseJUnit($junitPath);
$date = date('Y-m-d');
$version = 'v1.7';
$rows = [];
$id = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base.'/tests', FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php' || ! str_ends_with($file->getFilename(), 'Test.php')) {
        continue;
    }
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($base) + 1));
    $php = file_get_contents($file->getPathname());
    if ($php === false) {
        continue;
    }
    preg_match('/namespace\s+([^;]+);/', $php, $nsMatch);
    preg_match('/class\s+(\w+)/', $php, $classMatch);
    $namespace = $nsMatch[1] ?? '';
    $classShort = $classMatch[1] ?? basename($file->getFilename(), '.php');
    $fqcn = $namespace.'\\'.$classShort;
    $module = moduleFromPath($rel);
    $layer = layerFromRel($rel);
    $tipo = classifyTest($layer, $module, $classShort);
    $bpmn = bpmnFromModule($module, $classShort);

    foreach (extractTests($php) as $method) {
        $id++;
        $testId = sprintf('TC-%04d', $id);
        $key = $fqcn.'::'.$method;
        $resultado = $junitResults[$key] ?? 'PENDIENTE DE VALIDACIÓN';
        $paso = $resultado === 'PASÓ' ? 'Sí' : ($resultado === 'FALLÓ' ? 'No' : '');
        $fallo = $resultado === 'FALLÓ' ? 'Sí' : ($resultado === 'PASÓ' ? 'No' : '');

        $rows[] = [
            'ID' => $testId,
            'Caso_Prueba' => $classShort.'::'.$method,
            'Tipo' => $tipo,
            'Capa' => $layer,
            'Modulo' => $module,
            'Proceso_BPMN' => $bpmn,
            'Prioridad' => priorityFrom($layer, $module),
            'Criticidad' => criticalityFrom($module),
            'Estado' => 'Vigente',
            'Resultado_Esperado' => 'Aserciones PHPUnit en verde',
            'Resultado_Obtenido' => $resultado,
            'Paso' => $paso,
            'Fallo' => $fallo,
            'Observaciones' => $resultado === 'FALLÓ' ? 'Ver junit '.$rel : '',
            'Incidencias' => $resultado === 'FALLÓ' ? 'INC-'.substr(md5($key), 0, 6) : '',
            'Version' => $version,
            'Fecha' => $date,
            'Responsable' => 'CI / QA',
            'Comentarios' => '',
            'Riesgos' => '',
            'Dependencias' => $rel,
            'Documentacion' => 'docs/testing/'.$layer.'_catalogo_autogenerado.md',
            'Clase' => $fqcn,
            'Archivo' => $rel,
        ];
    }
}

$headers = array_keys($rows[0] ?? []);
$outPath = $base.'/docs/testing/matriz_maestra_casos.csv';
$fp = fopen($outPath, 'w');
if ($fp === false) {
    fwrite(STDERR, "Cannot write {$outPath}\n");
    exit(1);
}
fputcsv($fp, $headers);
foreach ($rows as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

$passed = count(array_filter($rows, fn ($r) => $r['Resultado_Obtenido'] === 'PASÓ'));
$failed = count(array_filter($rows, fn ($r) => $r['Resultado_Obtenido'] === 'FALLÓ'));
$pending = count(array_filter($rows, fn ($r) => $r['Resultado_Obtenido'] === 'PENDIENTE DE VALIDACIÓN'));

fwrite(STDOUT, "Exported {$outPath}\n");
fwrite(STDOUT, "Total: ".count($rows)." | PASÓ: {$passed} | FALLÓ: {$failed} | PENDIENTE: {$pending}\n");
