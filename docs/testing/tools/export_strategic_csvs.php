<?php

declare(strict_types=1);

/**
 * Exporta CSVs estratégicos filtrados desde tests + last_junit.xml
 */
$base = dirname(__DIR__, 3);
$junitPath = $base.'/docs/testing/tools/last_junit.xml';

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
        $key = (string) $tc['class'].'::'.(string) $tc['name'];
        $results[$key] = (isset($tc->failure) || isset($tc->error)) ? 'FALLÓ' : 'PASÓ';
    }

    return $results;
}

function extractTests(string $php): array
{
    $methods = [];
    if (preg_match_all('/\#\[Test\](?:\s*\([^)]*\))?\s*\n(?:\s*\#[^\n]+\s*\n)*\s*public function (\w+)\s*\(/m', $php, $m)) {
        $methods = array_merge($methods, $m[1]);
    }
    if (preg_match_all('/public function (test[A-Za-z0-9_]+)\s*\(/m', $php, $m2)) {
        $methods = array_merge($methods, $m2[1]);
    }

    return array_values(array_unique($methods));
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
        '/Logging/' => 'Logging',
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

function bpmnFrom(string $module, string $class): string
{
    if (str_contains($class, 'Webhook')) {
        return 'PROC-011';
    }
    if (str_contains($class, 'Simulate') || str_contains($class, 'Simulation')) {
        return 'PROC-009';
    }
    if (str_contains($class, 'Catalog') || str_contains($class, 'ValidatePlatform') || str_contains($class, 'ModulesConfig')) {
        return 'PROC-016';
    }
    if (str_contains($class, 'Publisher') || str_contains($class, 'Bus') || str_contains($class, 'Outbox')) {
        return 'PROC-001';
    }
    if (str_contains($class, 'Sync') || str_contains($class, 'Registry') || str_contains($class, 'Subscription')) {
        return 'PROC-002';
    }
    if (str_contains($class, 'ControlApi') || str_contains($class, 'Resilience') || str_contains($class, 'Pipeline')) {
        return 'PROC-003';
    }
    if (str_contains($class, 'Dashboard') || str_contains($class, 'Feed') || str_contains($class, 'Metrics')) {
        return 'PROC-004';
    }

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
        'EventBus' => 'PROC-002',
        'Quality' => 'PROC-016',
        'API' => 'PROC-003',
        'Logging' => 'PROC-013',
    ];

    return $map[$module] ?? 'PROC-001';
}

function collectAllTests(string $base, array $junitResults): array
{
    $all = [];
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
        $classShort = $classMatch[1] ?? '';
        $fqcn = $namespace.'\\'.$classShort;
        $module = moduleFromPath($rel);
        $layer = layerFromRel($rel);
        foreach (extractTests($php) as $method) {
            $key = $fqcn.'::'.$method;
            $resultado = $junitResults[$key] ?? 'PENDIENTE DE VALIDACIÓN';
            $all[] = compact('rel', 'classShort', 'fqcn', 'method', 'module', 'layer', 'resultado', 'key');
        }
    }

    return $all;
}

function writeCsv(string $path, array $headers, array $rows): void
{
    $fp = fopen($path, 'w');
    if ($fp === false) {
        throw new RuntimeException("Cannot write {$path}");
    }
    fputcsv($fp, $headers);
    foreach ($rows as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);
}

function rowFromTest(array $t, int $id, string $doc, string $criterio = ''): array
{
    $resultado = $t['resultado'];
    $paso = $resultado === 'PASÓ' ? 'Sí' : ($resultado === 'FALLÓ' ? 'No' : '');
    $fallo = $resultado === 'FALLÓ' ? 'Sí' : ($resultado === 'PASÓ' ? 'No' : '');

    return [
        sprintf('TC-%04d', $id),
        $t['classShort'].'::'.$t['method'],
        $t['layer'],
        $t['module'],
        bpmnFrom($t['module'], $t['classShort']),
        $criterio !== '' ? $criterio : 'Aserciones PHPUnit en verde',
        $resultado,
        $paso,
        $fallo,
        $resultado === 'FALLÓ' ? 'Ver docs/testing/tools/last_junit.xml' : '',
        'v1.8',
        '2026-06-27',
        'CI / QA',
        $doc,
        $t['fqcn'],
        $t['rel'],
    ];
}

$junitResults = parseJUnit($junitPath);
$all = collectAllTests($base, $junitResults);
$headers = ['ID', 'Caso_Prueba', 'Capa', 'Modulo', 'Proceso_BPMN', 'Criterio', 'Resultado_Obtenido', 'Paso', 'Fallo', 'Observaciones', 'Version', 'Fecha', 'Responsable', 'Documento', 'Clase', 'Archivo'];
$date = '2026-06-27';

// 1. unit_configuracion_catalogo_declarativo.csv
$unitCatalogFilter = static fn (array $t): bool => str_starts_with($t['rel'], 'tests/Unit/')
    && preg_match('/(Catalog|ModulesConfig|ValidatePlatform|TenantCatalog|PackSubscription|TopologyRegistry|TopologySnapshot|TenantModuleCatalog)/', $t['rel'].$t['classShort']) === 1;
$rows = [];
$id = 0;
foreach (array_filter($all, $unitCatalogFilter) as $t) {
    $id++;
    $rows[] = rowFromTest($t, $id, 'unit_configuracion_catalogo_declarativo.md', 'Catálogo declarativo normalizado y coherente');
}
writeCsv($base.'/docs/testing/unit_configuracion_catalogo_declarativo.csv', $headers, $rows);
echo "unit_configuracion: {$id}\n";

// 2. feature_api_middleware_control.csv
$rows = [];
$id = 0;
foreach (array_filter($all, static fn ($t) => str_starts_with($t['rel'], 'tests/Feature/Middleware/')) as $t) {
    $id++;
    $rows[] = rowFromTest($t, $id, 'feature_api_middleware_control.md', 'API HTTP control middleware operativa');
}
writeCsv($base.'/docs/testing/feature_api_middleware_control.csv', $headers, $rows);
echo "feature_middleware: {$id}\n";

// 3. integration_flujo_eventos_bus.csv
$intFilter = static fn ($t) => str_starts_with($t['rel'], 'tests/Integration/Middleware/')
    || str_starts_with($t['rel'], 'tests/Integration/Dashboard/')
    || str_starts_with($t['rel'], 'tests/Integration/Logging/');
$rows = [];
$id = 0;
foreach (array_filter($all, $intFilter) as $t) {
    $id++;
    $rows[] = rowFromTest($t, $id, 'integration_flujo_eventos_bus.md', 'Flujo publicación → bus → observabilidad');
}
writeCsv($base.'/docs/testing/integration_flujo_eventos_bus.csv', $headers, $rows);
echo "integration_bus: {$id}\n";

// 4. e2e_simulacion_cliente.csv
$rows = [];
$id = 0;
foreach (array_filter($all, static fn ($t) => str_starts_with($t['rel'], 'tests/E2E/')) as $t) {
    $id++;
    $rows[] = rowFromTest($t, $id, 'e2e_simulacion_cliente.md', 'Simulación cliente multi-evento E2E');
}
writeCsv($base.'/docs/testing/e2e_simulacion_cliente.csv', $headers, $rows);
echo "e2e: {$id}\n";

// 5. priority_tests_matrix.csv
$priorityMap = [
    'Tests\\Feature\\Security\\PlatformApiAuthenticationTest' => ['Auth middleware API', 'PROC-006', 'Seguridad'],
    'Tests\\Feature\\Integration\\WebhookIngressTest' => ['Webhook signature validation', 'PROC-011', 'Integration'],
    'Tests\\Integration\\Middleware\\EventStoreIdempotencyIntegrationTest' => ['event_store append idempotency', 'PROC-001', 'Middleware'],
    'Tests\\Feature\\Platform\\PurgePlatformRetentionTest' => ['Retention purge command', 'PROC-014', 'Platform'],
    'Tests\\Feature\\Middleware\\ResilienceApiTest' => ['Idempotent publish HTTP 200', 'PROC-003', 'Middleware'],
    'Tests\\Unit\\Platform\\ValidatePlatformCatalogTest' => ['validate-catalog command', 'PROC-016', 'Platform'],
];
$prioHeaders = ['ID', 'Test_Prioritario', 'Clase', 'Archivo', 'Proceso_BPMN', 'Modulo', 'Resultado_Obtenido', 'Estado_CI', 'Documento', 'Fecha'];
$prioRows = [];
$pid = 0;
foreach ($priorityMap as $fqcn => [$label, $bpmn, $mod]) {
    $classTests = array_filter($all, static fn ($t) => $t['fqcn'] === $fqcn);
    if ($classTests === []) {
        $pid++;
        $prioRows[] = [sprintf('PRIO-%02d', $pid), $label, $fqcn, '(clase no encontrada)', $bpmn, $mod, 'PENDIENTE DE VALIDACIÓN', 'No verificado', 'priority_tests_matrix.md', $date];
        continue;
    }
    foreach ($classTests as $t) {
        $pid++;
        $prioRows[] = [sprintf('PRIO-%02d', $pid), $label.' → '.$t['method'], $t['fqcn'], $t['rel'], $bpmn, $mod, $t['resultado'], $t['resultado'] === 'PASÓ' ? 'CI verde' : ($t['resultado'] === 'FALLÓ' ? 'CI rojo' : 'No verificado'), 'priority_tests_matrix.md', $date];
    }
}
writeCsv($base.'/docs/testing/priority_tests_matrix.csv', $prioHeaders, $prioRows);
echo "priority: {$pid}\n";

// 6. matrix_validacion_middleware.csv — criterios arquitectura → tests representativos
$criteria = [
    ['CRIT-01', 'Desacoplamiento productor-consumidor', 'PROC-001', 'EventPublisherServiceIntegrationTest', 'tests/Integration/Middleware/EventPublisherServiceIntegrationTest.php'],
    ['CRIT-02', 'Rol middleware sin lógica de negocio', 'PROC-001', 'ClientProductionLikeSimulationTest', 'tests/E2E/Middleware/ClientProductionLikeSimulationTest.php'],
    ['CRIT-03', 'Propagación bus y cola', 'PROC-001', 'BusTrackingDirectDispatchIntegrationTest', 'tests/Integration/Middleware/BusTrackingDirectDispatchIntegrationTest.php'],
    ['CRIT-04', 'Config declarativa ↔ presentación', 'PROC-016', 'ConfigModulesCatalogPresentationTest', 'tests/Unit/Dashboard/ConfigModulesCatalogPresentationTest.php'],
    ['CRIT-05', 'sync-config idempotente', 'PROC-002', 'MiddlewarePipelineEndToEndTest', 'tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php'],
    ['CRIT-06', 'API control cola/topología/publish', 'PROC-003', 'MiddlewareControlApiTest', 'tests/Feature/Middleware/MiddlewareControlApiTest.php'],
    ['CRIT-07', 'Coherencia config-ejecución-dashboard', 'PROC-004', 'MiddlewarePipelineEndToEndTest', 'tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php'],
    ['CRIT-08', 'Reutilización por cliente/instancia', 'PROC-009', 'MultiClientFixtureSimulationTest', 'tests/E2E/Middleware/MultiClientFixtureSimulationTest.php'],
    ['CRIT-09', 'Idempotencia event_store', 'PROC-001', 'EventStoreIdempotencyIntegrationTest', 'tests/Integration/Middleware/EventStoreIdempotencyIntegrationTest.php'],
    ['CRIT-10', 'Registro suscripciones y bus', 'PROC-002', 'SubscriptionRegistryAndBusRegistrationIntegrationTest', 'tests/Integration/Middleware/SubscriptionRegistryAndBusRegistrationIntegrationTest.php'],
    ['CRIT-11', 'Outbox relay', 'PROC-001', 'OutboxRelayIntegrationTest', 'tests/Integration/Middleware/OutboxRelayIntegrationTest.php'],
    ['CRIT-12', 'Resiliencia publish/idempotency-key', 'PROC-003', 'ResilienceApiTest', 'tests/Feature/Middleware/ResilienceApiTest.php'],
    ['CRIT-13', 'Tenant operacional en middleware', 'PROC-018', 'EnsureTenantOperationalStatusTest', 'tests/Feature/Middleware/EnsureTenantOperationalStatusTest.php'],
    ['CRIT-14', 'Observabilidad dashboard desde bus', 'PROC-004', 'PlatformPingObservedByDashboardIntegrationTest', 'tests/Integration/Dashboard/PlatformPingObservedByDashboardIntegrationTest.php'],
    ['CRIT-15', 'Validación catálogo CI', 'PROC-016', 'ValidatePlatformCatalogTest', 'tests/Unit/Platform/ValidatePlatformCatalogTest.php'],
];
$critHeaders = ['ID', 'Criterio', 'Proceso_BPMN', 'Clase_Representativa', 'Archivo', 'Tests_Cubiertos', 'Resultado_Agregado', 'Observaciones', 'Documento', 'Fecha'];
$critRows = [];
foreach ($criteria as [$cid, $crit, $bpmn, $class, $file]) {
    $classTests = array_filter($all, static fn ($t) => $t['classShort'] === $class);
    $passed = 0;
    $failed = 0;
    $pending = 0;
    foreach ($classTests as $t) {
        if ($t['resultado'] === 'PASÓ') {
            $passed++;
        } elseif ($t['resultado'] === 'FALLÓ') {
            $failed++;
        } else {
            $pending++;
        }
    }
    $total = count($classTests);
    $agg = $failed > 0 ? 'FALLÓ' : ($total > 0 && $passed === $total ? 'PASÓ' : ($pending === $total ? 'PENDIENTE DE VALIDACIÓN' : 'PASÓ'));
    $critRows[] = [$cid, $crit, $bpmn, $class, $file, (string) $total, $agg, $failed > 0 ? 'Revisar fallos en clase representativa' : '', 'matrix_validacion_middleware.md', $date];
}
writeCsv($base.'/docs/testing/matrix_validacion_middleware.csv', $critHeaders, $critRows);
echo "matrix: ".count($critRows)."\n";

// 7. audit_suite_redundancia.csv
$auditHeaders = ['ID', 'Elemento', 'Tipo', 'Estado', 'Decision', 'Reemplazo', 'Observaciones', 'Fecha', 'Documento'];
$auditRows = [
    ['AUD-01', 'catalog_unit.md', 'Documento', 'Obsoleto', 'Eliminado', 'unit_catalogo_autogenerado.md', 'Referencias legacy omnicanal', $date, 'audit_suite_redundancia.md'],
    ['AUD-02', 'catalog_integration.md', 'Documento', 'Obsoleto', 'Eliminado', 'integration_catalogo_autogenerado.md', 'Volumen elevado desactualizado', $date, 'audit_suite_redundancia.md'],
    ['AUD-03', 'catalog_feature.md', 'Documento', 'Obsoleto', 'Eliminado', 'feature_catalogo_autogenerado.md', 'Plantillas desactualizadas', $date, 'audit_suite_redundancia.md'],
    ['AUD-04', 'catalog_e2e.md', 'Documento', 'Obsoleto', 'Eliminado', 'e2e_catalogo_autogenerado.md', 'Flujos omnicanal no presentes', $date, 'audit_suite_redundancia.md'],
    ['AUD-05', 'architecture_validation_matrix.md', 'Documento', 'Obsoleto', 'Reemplazado', 'matrix_validacion_middleware.md', 'Alineado middleware genérico', $date, 'audit_suite_redundancia.md'],
    ['AUD-06', 'audit_phase1_phase2.md', 'Documento', 'Obsoleto', 'Fusionado', 'audit_suite_redundancia.md', 'Historial fases 1-2', $date, 'audit_suite_redundancia.md'],
    ['AUD-07', 'Conteo 160 tests (2026-05-22)', 'Métrica', 'Obsoleto', 'Actualizado', '362 métodos / 363 PHPUnit', 'README y matriz maestra v1.7+', $date, 'audit_suite_redundancia.md'],
    ['AUD-08', 'MiddlewarePipelineEndToEndTest vs ClientProductionLikeSimulationTest', 'Duplicación funcional', 'Vigente', 'Coexistencia justificada', 'Feature=regresión B.2; E2E=multi-tipo', 'Sin asserts triviales duplicados', $date, 'audit_suite_redundancia.md'],
    ['AUD-09', 'Flujos omnicanal legacy (Inventario/Pedido)', 'Feature código', 'Obsoleto', 'No en suite', 'Platform.* fixtures', 'ADR-001 silos por cliente', $date, 'audit_suite_redundancia.md'],
    ['AUD-10', 'matriz_maestra_casos.csv', 'Artefacto', 'Vigente', 'Fuente maestra', 'export_test_matrix.php', '362 filas con junit', $date, 'audit_suite_redundancia.md'],
    ['AUD-11', 'InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed', 'Test', 'Vigente', 'PASÓ tras fix PlatformDatabaseReadiness :memory:', 'Corregido INC-e36025', 'Fix 2026-06-24', $date, 'audit_suite_redundancia.md'],
    ['AUD-12', 'OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled', 'Test', 'Vigente', 'PASÓ tras fix PlatformDatabaseReadiness :memory:', 'Corregido INC-613e3b', 'Fix 2026-06-24', $date, 'audit_suite_redundancia.md'],
];
writeCsv($base.'/docs/testing/audit_suite_redundancia.csv', $auditHeaders, $auditRows);
echo "audit: ".count($auditRows)."\n";

// 8. load/README.csv
$loadHeaders = ['ID', 'Escenario', 'Herramienta', 'Endpoint', 'Throughput', 'Duracion_s', 'Umbral_p95_ms', 'Umbral_error_pct', 'Resultado_Obtenido', 'Observaciones', 'Documento', 'Fecha'];
$loadRows = [
    ['LOAD-01', 'Publish sustained throughput', 'k6', 'POST /api/middleware/events/publish', '100 eps', '60', '2000', '5', 'PENDIENTE DE VALIDACIÓN', 'Requiere APP_URL y middleware en ejecución; no en PHPUnit', 'load/README.md', $date],
    ['LOAD-02', 'Publish baseline smoke', 'k6', 'POST /api/middleware/events/publish', '10 eps', '30', '2000', '5', 'PENDIENTE DE VALIDACIÓN', 'Workflow quality-load.yml workflow_dispatch', 'load/README.md', $date],
];
writeCsv($base.'/docs/testing/load/README.csv', $loadHeaders, $loadRows);
echo "load: ".count($loadRows)."\n";

echo "Done.\n";
