<?php

declare(strict_types=1);

/**
 * Genera Matriz_Cobertura_Funcional.csv y Matriz_Trazabilidad_Pruebas.csv
 */
$base = dirname(__DIR__, 3);
$csvPath = $base.'/docs/testing/matriz_maestra_casos.csv';

$fh = fopen($csvPath, 'r');
$header = fgetcsv($fh);
$rows = [];
while (($row = fgetcsv($fh)) !== false) {
    $rows[] = $row;
}
fclose($fh);

$procCounts = [];
$procFailed = [];
$procIds = [];

function addProcCount(array &$counts, array &$failed, array &$ids, string $proc, string $id, string $resultado): void
{
    if ($proc === '') {
        return;
    }
    if (! isset($counts[$proc])) {
        $counts[$proc] = 0;
        $failed[$proc] = 0;
        $ids[$proc] = [];
    }
    $counts[$proc]++;
    $ids[$proc][] = $id;
    if ($resultado === 'FALLÓ') {
        $failed[$proc]++;
    }
}

// Mapeo secundario: tests cuya evidencia cubre otro PROC aunque matriz use etiqueta primaria
$secondaryProcByMethod = [
    'CompanySimulationAutomationTest::' => ['PROC-020'],
    'SimulationRunReportTest::' => ['PROC-020'],
    'SimulationRunCancellationTest::' => ['PROC-020'],
    'SimulationInternalApiTest::' => ['PROC-020'],
    'TenantModuleCatalogTest::' => ['PROC-034'],
    'TenantModuleCatalogServiceTest::' => ['PROC-034'],
    'IntegrationAdminApiTest::' => ['PROC-012'],
    'MiddlewarePipelineEndToEndTest::' => ['PROC-002'],
    'SimulateClientCommandTest::' => ['PROC-002'],
];

foreach ($rows as $row) {
    $proc = $row[5] ?? '';
    $id = $row[0] ?? '';
    $caso = $row[1] ?? '';
    $resultado = $row[10] ?? '';

    addProcCount($procCounts, $procFailed, $procIds, $proc, $id, $resultado);

    foreach ($secondaryProcByMethod as $prefix => $extraProcs) {
        if (! str_starts_with($caso, $prefix)) {
            continue;
        }
        foreach ($extraProcs as $extra) {
            addProcCount($procCounts, $procFailed, $procIds, $extra, $id, $resultado);
        }
    }

    // Sync/registry en nombres de método → PROC-002
    if (preg_match('/sync[_]?config|sync_registry|SubscriptionRegistry|TopologyRegistry/i', $caso) === 1) {
        addProcCount($procCounts, $procFailed, $procIds, 'PROC-002', $id, $resultado);
    }
}

$processMeta = [
    'PROC-001' => ['Publicación eventos bus', 'Implementado', 'Alta', 'MP-02'],
    'PROC-002' => ['Sync catálogo → registry', 'Implementado', 'Alta', 'MP-02'],
    'PROC-003' => ['Consulta operativa bus', 'Implementado', 'Alta', 'MP-02'],
    'PROC-004' => ['Observabilidad dashboard', 'Implementado', 'Alta', 'MP-03'],
    'PROC-005' => ['Auth operadores web', 'Implementado', 'Crítica', 'MP-04'],
    'PROC-006' => ['Auth API integradores', 'Implementado', 'Crítica', 'MP-04'],
    'PROC-007' => ['Gestión empresas CP', 'Implementado', 'Alta', 'MP-01'],
    'PROC-008' => ['Provisioning instancia', 'Parcial', 'Alta', 'MP-01'],
    'PROC-009' => ['Simulación cliente E2E', 'Implementado', 'Alta', 'MP-05'],
    'PROC-010' => ['Onboarding instancia', 'Implementado', 'Media', 'MP-01'],
    'PROC-011' => ['Ingress webhooks', 'Parcial', 'Alta', 'MP-08'],
    'PROC-012' => ['Gestión canales/integraciones', 'Implementado', 'Alta', 'MP-08'],
    'PROC-013' => ['Monitoreo y alertas', 'Implementado', 'Alta', 'MP-03'],
    'PROC-014' => ['Retención y purga', 'Implementado', 'Media', 'MP-06'],
    'PROC-015' => ['Incidentes soporte', 'Implementado', 'Alta', 'MP-01'],
    'PROC-016' => ['Validación catálogo CI', 'Implementado', 'Alta', 'MP-05'],
    'PROC-017' => ['Flujo middleware 5 etapas', 'Documental', 'Media', 'MP-08'],
    'PROC-018' => ['Multi-tenancy lógico Fase 3', 'Diferido', 'Baja', 'MP-07'],
    'PROC-019' => ['Portal instancia cliente', 'Implementado', 'Alta', 'MP-09'],
    'PROC-020' => ['Simulación desde CP', 'Implementado', 'Alta', 'MP-01'],
    'PROC-030' => ['Despliegue producción VM', 'Documentado', 'Media', 'MP-06'],
    'PROC-031' => ['Backup y restauración', 'Documentado', 'Media', 'MP-06'],
    'PROC-032' => ['DR Drill', 'Documentado', 'Baja', 'MP-06'],
    'PROC-033' => ['Evaluación aceptación middleware', 'Documentado', 'Media', 'MP-07'],
    'PROC-034' => ['Espejo catálogo CP→Silo', 'Implementado', 'Alta', 'MP-01'],
];

$coverageHeaders = [
    'Proceso_BPMN', 'Nombre_Proceso', 'Macroproceso', 'Estado_Implementacion', 'Criticidad',
    'Tests_Asignados', 'Tests_PASO', 'Tests_FALLO', 'Cobertura', 'Brecha', 'IDs_Representativos', 'Documento_BPMN', 'Fecha',
];

$coverageRows = [];
for ($i = 1; $i <= 34; $i++) {
    $proc = sprintf('PROC-%03d', $i);
    if (! isset($processMeta[$proc])) {
        continue;
    }
    [$name, $state, $crit, $mp] = $processMeta[$proc];
    $total = $procCounts[$proc] ?? 0;
    $failed = $procFailed[$proc] ?? 0;
    $passed = $total - $failed;

    $coverage = 'Sin tests';
    $gap = '';
    if ($total >= 10) {
        $coverage = 'Alta';
    } elseif ($total >= 3) {
        $coverage = 'Media';
    } elseif ($total >= 1) {
        $coverage = 'Parcial';
    } else {
        $gap = 'Sin cobertura PHPUnit';
    }

    if ($proc === 'PROC-017') {
        $gap = 'Proceso documental; sin suite dedicada 5-etapas';
        $coverage = $total > 0 ? 'Indirecta (middleware)' : 'Sin tests';
    }
    if ($proc === 'PROC-018') {
        $gap = 'Multi-tenant lógico diferido Fase 3';
        $coverage = 'Diferido';
    }
    if ($proc === 'PROC-010' && $total < 3) {
        $gap = 'Onboarding cubierto indirectamente vía provisioning/portal';
    }
    if ($proc === 'PROC-012' && $total < 5) {
        $gap = 'Admin API parcial; ampliar CRUD canales y rotación secretos';
    }
    if ($proc === 'PROC-034' && $total < 5) {
        $gap = 'Espejo CP→Silo vía TenantModuleCatalogTest; ampliar integración multi-silo';
    }
    if ($proc === 'PROC-002' && $total > 0 && $gap === '') {
        $gap = 'Cobertura vía sync-config; etiqueta matriz a menudo PROC-001';
    }
    if ($proc === 'PROC-020' && $total > 0 && $gap === '') {
        $gap = 'Etiqueta matriz primaria PROC-009; mapeo secundario PROC-020';
    }
    if (in_array($proc, ['PROC-030', 'PROC-031', 'PROC-032', 'PROC-033'], true)) {
        $gap = 'Solo documentación/runbooks; sin tests automatizados';
        $coverage = 'Documental';
    }
    if ($failed > 0) {
        $gap = ($gap !== '' ? $gap.'; ' : '')."{$failed} test(s) FALLÓ";
    }

    $ids = array_slice($procIds[$proc] ?? [], 0, 5);
    $docNum = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
    $bpmnDoc = "docs/Diagrama_BPMN/{$docNum}_Proceso_*.md";

    $coverageRows[] = [
        $proc, $name, $mp, $state, $crit,
        (string) $total, (string) $passed, (string) $failed, $coverage, $gap,
        implode(';', $ids), $bpmnDoc, '2026-06-27',
    ];
}

// REQ-DYN-01 row
$coverageRows[] = [
    'REQ-DYN-01', 'Métricas dinámicas dashboard', 'MP-03', 'Implementado', 'Alta',
    '8', '8', '0', 'Parcial', 'Series dinámicas unitarias; load tests pendientes',
    'TC-0051;TC-0212;TC-0213', 'docs/Diagrama_BPMN/13_Proceso_Observabilidad_Dashboard.md', '2026-06-27',
];

$out = $base.'/docs/testing/Matriz_Cobertura_Funcional.csv';
$fp = fopen($out, 'w');
fputcsv($fp, $coverageHeaders);
foreach ($coverageRows as $r) {
    fputcsv($fp, $r);
}
fclose($fp);
echo 'Cobertura: '.count($coverageRows)." filas\n";

// Trazabilidad
$trace = [
    ['CU-CP-01', 'Gestionar empresas tenant', 'PROC-007', 'TenantOperatorsScopedTest', 'GET /control/companies', 'TC-0031;TC-0032', 'feature_control_plane.md'],
    ['CU-CP-02', 'Provisionar nueva instancia', 'PROC-008', 'ProvisionNewTenantInputMapper', 'POST /control/companies', 'TC-0198;TC-0199;TC-0026', 'feature_control_plane.md'],
    ['CU-CP-03', 'Simular desde control plane', 'PROC-020', 'CompanySimulationAutomationTest', 'POST /control/companies/{id}/simulate', 'TC-0015;TC-0018', 'feature_control_plane.md'],
    ['CU-CP-04', 'Espejar catálogo CP→Silo', 'PROC-034', 'TenantModuleCatalogService', 'PUT /control/tenants/{id}/catalog', 'TC-0028;TC-0029', 'feature_control_plane.md'],
    ['CU-CP-05', 'Gestionar incidentes soporte', 'PROC-015', 'ClientSupportReportTest', 'POST /portal/support-report', 'TC-0012;TC-0013;TC-0014', 'feature_control_plane.md'],
    ['CU-DASH-01', 'Consultar feed dashboard', 'PROC-004', 'DashboardEndpointsTest', 'GET /api/dashboard/events/feed', 'TC-0054;TC-0047', 'feature_dashboard_observabilidad.md'],
    ['CU-DASH-02', 'Activar módulos LIVE', 'PROC-004', 'ModuleActivationGateServiceTest', 'PATCH /api/dashboard/nodes/{id}/middleware-events', 'TC-0215;TC-0216', 'feature_dashboard_observabilidad.md'],
    ['CU-OBS-01', 'Exportar métricas Prometheus', 'PROC-013', 'PrometheusMetricsEndpointTest', 'GET /metrics', 'TC-0117;TC-0118', 'feature_dashboard_observabilidad.md'],
    ['CU-OBS-02', 'Evaluar alertas plataforma', 'PROC-013', 'EvaluateMonitoringAlertsCommandTest', 'php artisan platform:monitoring-evaluate', 'TC-0113;TC-0114', 'feature_dashboard_observabilidad.md'],
    ['CU-SEC-01', 'Login operador web', 'PROC-005', 'OperatorLoginTest', 'POST /login', 'TC-0068;TC-0070', 'feature_seguridad_identidad.md'],
    ['CU-SEC-02', 'Autorización por rol', 'PROC-005', 'RoleBasedAuthorizationTest', 'POST /api/middleware/sync-config', 'TC-0076;TC-0078', 'feature_seguridad_identidad.md'],
    ['CU-SEC-03', 'Auth API integradores', 'PROC-006', 'PlatformApiAuthenticationTest', 'POST /api/middleware/events/publish', 'TC-0133;TC-0135', 'feature_seguridad_identidad.md'],
    ['CU-INT-01', 'Recibir webhook firmado', 'PROC-011', 'WebhookIngressTest', 'POST /api/integrations/webhooks/{channel}', 'TC-0085;TC-0086', 'feature_integracion_webhooks.md'],
    ['CU-INT-02', 'Administrar canales', 'PROC-012', 'IntegrationAdminApiTest', 'POST /api/integrations/channels', 'TC-0083', 'feature_integracion_webhooks.md'],
    ['CU-PLT-01', 'Simular cliente CLI', 'PROC-009', 'SimulateClientCommandTest', 'php artisan platform:simulate-client', 'TC-0128;TC-0129', 'feature_plataforma_fleet_simulacion.md'],
    ['CU-PLT-02', 'Gestionar flota local', 'PROC-009', 'LocalFleetRegistryTest', 'deploy/local-instances/fleet-registry.json', 'TC-0324;TC-0349', 'feature_plataforma_fleet_simulacion.md'],
    ['CU-BUS-01', 'Publicar evento al bus', 'PROC-001', 'MiddlewareControlApiTest', 'POST /api/middleware/events/publish', 'TC-0035;TC-0036', 'feature_api_middleware_control.md'],
    ['CU-BUS-02', 'Sync registry', 'PROC-002', 'MiddlewarePipelineEndToEndTest', 'POST /api/middleware/sync-config', 'TC-0037', 'matrix_validacion_middleware.md'],
    ['CU-BUS-03', 'Consultar cola bus', 'PROC-003', 'MiddlewareControlApiTest', 'GET /api/middleware/queue', 'TC-0003;TC-0010', 'feature_api_middleware_control.md'],
    ['CU-PRT-01', 'Acceder portal tenant', 'PROC-019', 'TenantPortalRoutingTest', 'GET /t/{slug}/dashboard', 'TC-0037;TC-0041', 'feature_control_plane.md'],
    ['CU-QLT-01', 'Validar catálogo CI', 'PROC-016', 'ValidatePlatformCatalogTest', 'php artisan platform:validate-catalog', 'TC-0332;TC-0335', 'unit_configuracion_catalogo_declarativo.md'],
    ['CU-OPS-01', 'Purgar retención datos', 'PROC-014', 'PurgePlatformRetentionTest', 'php artisan platform:purge-retention', 'TC-0121;TC-0122', 'feature_plataforma_fleet_simulacion.md'],
    ['CU-MT-01', 'Multi-tenant lógico', 'PROC-018', '(diferido)', 'N/A', 'TC-0321', 'Funcionalidades_Obsoletas.md'],
    ['CU-DOC-01', 'Flujo 5 etapas middleware', 'PROC-017', '(documental)', 'N/A', 'Indirecto PROC-001', 'Matriz_Cobertura_Funcional.md'],
];

$traceHeaders = ['CU_ID', 'Caso_Uso', 'Proceso_BPMN', 'Servicio_Clase', 'API_Comando', 'Tests_ID', 'Documento_Prueba', 'Fecha'];
$traceOut = $base.'/docs/testing/Matriz_Trazabilidad_Pruebas.csv';
$fp = fopen($traceOut, 'w');
fputcsv($fp, $traceHeaders);
foreach ($trace as $r) {
    $r[] = '2026-06-27';
    fputcsv($fp, $r);
}
fclose($fp);
echo 'Trazabilidad: '.count($trace)." filas\n";

// Funcionalidades obsoletas CSV
$obsoleteHeaders = ['ID', 'Funcionalidad', 'Tipo', 'Estado', 'Fecha_Retiro', 'Reemplazo', 'Evidencia_Eliminacion', 'Documento_Historico', 'Observaciones'];
$obsolete = [
    ['OBS-01', 'Dominio Inventario retail en core', 'Bounded context', 'Retirado', '2026-05-03', 'Fixtures Platform.* agnósticos', 'Tests Inventario.* eliminados', 'audit_suite_redundancia.md AUD-09', 'ADR-001 silos por cliente'],
    ['OBS-02', 'Dominio Pedidos retail en core', 'Bounded context', 'Retirado', '2026-05-03', 'Eventos ejemplo integrador externo', 'Tests Pedido.* eliminados', 'audit_suite_redundancia.md AUD-09', 'Middleware sin lógica negocio'],
    ['OBS-03', 'catalog_unit.md', 'Documento testing', 'Retirado', '2026-05-03', 'unit_catalogo_autogenerado.md', 'Eliminado del repo', 'audit_suite_redundancia.csv AUD-01', '160 tests baseline obsoleto'],
    ['OBS-04', 'catalog_integration.md', 'Documento testing', 'Retirado', '2026-05-03', 'integration_catalogo_autogenerado.md', 'Eliminado del repo', 'AUD-02', ''],
    ['OBS-05', 'catalog_feature.md', 'Documento testing', 'Retirado', '2026-05-03', 'feature_catalogo_autogenerado.md', 'Eliminado del repo', 'AUD-03', ''],
    ['OBS-06', 'catalog_e2e.md', 'Documento testing', 'Retirado', '2026-05-03', 'e2e_catalogo_autogenerado.md', 'Eliminado del repo', 'AUD-04', ''],
    ['OBS-07', 'architecture_validation_matrix.md', 'Documento testing', 'Retirado', '2026-05-03', 'matrix_validacion_middleware.md', 'Reemplazado', 'AUD-05', ''],
    ['OBS-08', 'Multi-tenancy lógico Fase 3', 'Proceso BPMN', 'Diferido', '2026-06-27', 'Silos físicos por tenant', 'PROC-018 documental', '27_Proceso_Multi_Tenancy_Logico_Fase3.md', 'Sin implementación en core'],
    ['OBS-09', 'Conteo 160 tests (2026-05-22)', 'Métrica QA', 'Obsoleto', '2026-06-27', '363 PHPUnit / 362 métodos', 'README.md actualizado', 'AUD-07', '+202 tests nuevos'],
    ['OBS-10', 'Flujos omnicanal InboundOrder/Stock', 'Feature E2E', 'Retirado', '2026-05-03', 'ClientProductionLikeSimulationTest', 'No en tests/', 'e2e_simulacion_cliente.md', 'Platform.* genérico'],
];
$obsOut = $base.'/docs/testing/Funcionalidades_Obsoletas.csv';
$fp = fopen($obsOut, 'w');
fputcsv($fp, $obsoleteHeaders);
foreach ($obsolete as $r) {
    fputcsv($fp, $r);
}
fclose($fp);
echo 'Obsoletas: '.count($obsolete)." filas\n";
