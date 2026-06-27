# Sistema de pruebas — omnichannel-ddd-eda (DDD + EDA + middleware)

**Versión documentación:** v1.8 | **Última auditoría:** 2026-06-27

Este directorio documenta la estrategia de validación automática del proyecto **omnichannel-ddd-eda**: capas `tests/Unit`, `tests/Integration`, `tests/Feature`, `tests/E2E`, instrumentos ISO, matrices CSV y trazabilidad con procesos BPMN (`docs/Diagrama_BPMN/`).

---

## Punto de entrada

| Necesidad | Documento |
|-----------|-----------|
| Auditoría completa docs vs código | [00_Auditoria_Testing.md](./00_Auditoria_Testing.md) |
| Informe final con métricas | [Informe_Validacion_Testing.md](./Informe_Validacion_Testing.md) |
| Todos los casos (362 filas) | [matriz_maestra_casos.csv](./matriz_maestra_casos.csv) |
| Cobertura PROC-001…034 | [Matriz_Cobertura_Funcional.md](./Matriz_Cobertura_Funcional.md) |
| Trazabilidad CU→BPMN→Test | [Matriz_Trazabilidad_Pruebas.md](./Matriz_Trazabilidad_Pruebas.md) |
| Funcionalidades retiradas | [Funcionalidades_Obsoletas.md](./Funcionalidades_Obsoletas.md) |
| Instrumentos ISO / checklist | [instrumentos/](./instrumentos/) |

---

## Auditoría y validación (2026-06-27)

| Documento | CSV | Contenido |
|-----------|-----|-----------|
| [00_Auditoria_Testing.md](./00_Auditoria_Testing.md) | — | Comparación 160→362 tests, módulos nuevos, brechas |
| [Informe_Validacion_Testing.md](./Informe_Validacion_Testing.md) | — | Veredicto: **VALIDACIÓN PARCIAL** (2 fallos) |
| [Matriz_Cobertura_Funcional.md](./Matriz_Cobertura_Funcional.md) | [Matriz_Cobertura_Funcional.csv](./Matriz_Cobertura_Funcional.csv) | PROC ↔ tests ↔ brechas |
| [Matriz_Trazabilidad_Pruebas.md](./Matriz_Trazabilidad_Pruebas.md) | [Matriz_Trazabilidad_Pruebas.csv](./Matriz_Trazabilidad_Pruebas.csv) | CU → BPMN → API → TC-xxxx |
| [Funcionalidades_Obsoletas.md](./Funcionalidades_Obsoletas.md) | [Funcionalidades_Obsoletas.csv](./Funcionalidades_Obsoletas.csv) | Historial retirados |

---

## Módulos evolucionados (Feature + CSV)

| Documento | CSV | BPMN |
|-----------|-----|------|
| [feature_control_plane.md](./feature_control_plane.md) | [feature_control_plane.csv](./feature_control_plane.csv) | PROC-007,008,015,020,034 |
| [feature_dashboard_observabilidad.md](./feature_dashboard_observabilidad.md) | [feature_dashboard_observabilidad.csv](./feature_dashboard_observabilidad.csv) | PROC-004,013 |
| [feature_seguridad_identidad.md](./feature_seguridad_identidad.md) | [feature_seguridad_identidad.csv](./feature_seguridad_identidad.csv) | PROC-005,006 |
| [feature_integracion_webhooks.md](./feature_integracion_webhooks.md) | [feature_integracion_webhooks.csv](./feature_integracion_webhooks.csv) | PROC-011,012 |
| [feature_plataforma_fleet_simulacion.md](./feature_plataforma_fleet_simulacion.md) | [feature_plataforma_fleet_simulacion.csv](./feature_plataforma_fleet_simulacion.csv) | PROC-009,010,020 |

---

## Middleware y capas (estratégicos + CSV)

| Documento | CSV |
|-----------|-----|
| [matrix_validacion_middleware.md](./matrix_validacion_middleware.md) | [matrix_validacion_middleware.csv](./matrix_validacion_middleware.csv) |
| [audit_suite_redundancia.md](./audit_suite_redundancia.md) | [audit_suite_redundancia.csv](./audit_suite_redundancia.csv) |
| [unit_configuracion_catalogo_declarativo.md](./unit_configuracion_catalogo_declarativo.md) | [unit_configuracion_catalogo_declarativo.csv](./unit_configuracion_catalogo_declarativo.csv) |
| [feature_api_middleware_control.md](./feature_api_middleware_control.md) | [feature_api_middleware_control.csv](./feature_api_middleware_control.csv) |
| [integration_flujo_eventos_bus.md](./integration_flujo_eventos_bus.md) | [integration_flujo_eventos_bus.csv](./integration_flujo_eventos_bus.csv) |
| [e2e_simulacion_cliente.md](./e2e_simulacion_cliente.md) | [e2e_simulacion_cliente.csv](./e2e_simulacion_cliente.csv) |
| [priority_tests_matrix.md](./priority_tests_matrix.md) | [priority_tests_matrix.csv](./priority_tests_matrix.csv) |
| [load/README.md](./load/README.md) | [load/README.csv](./load/README.csv) |

---

## Instrumentos de medición

| Instrumento | CSV |
|-------------|-----|
| [instrumentos/Checklist_PreDespliegue.md](./instrumentos/Checklist_PreDespliegue.md) | [Checklist_PreDespliegue.csv](./instrumentos/Checklist_PreDespliegue.csv) |
| [instrumentos/Matriz_Riesgos_Testing.md](./instrumentos/Matriz_Riesgos_Testing.md) | [Matriz_Riesgos_Testing.csv](./instrumentos/Matriz_Riesgos_Testing.csv) |
| [instrumentos/Matriz_Evidencias.md](./instrumentos/Matriz_Evidencias.md) | [Matriz_Evidencias.csv](./instrumentos/Matriz_Evidencias.csv) |
| [instrumentos/ISO_25010_Instrumentos.md](./instrumentos/ISO_25010_Instrumentos.md) | [ISO_25010_Instrumentos.csv](./instrumentos/ISO_25010_Instrumentos.csv) |
| [instrumentos/ISO_29119_Instrumentos.md](./instrumentos/ISO_29119_Instrumentos.md) | [ISO_29119_Instrumentos.csv](./instrumentos/ISO_29119_Instrumentos.csv) |
| [instrumentos/Instrumentos_Middleware.md](./instrumentos/Instrumentos_Middleware.md) | [Instrumentos_Middleware.csv](./instrumentos/Instrumentos_Middleware.csv) |
| [instrumentos/Instrumentos_Seguridad.md](./instrumentos/Instrumentos_Seguridad.md) | [Instrumentos_Seguridad.csv](./instrumentos/Instrumentos_Seguridad.csv) |
| [instrumentos/Instrumentos_Observabilidad.md](./instrumentos/Instrumentos_Observabilidad.md) | [Instrumentos_Observabilidad.csv](./instrumentos/Instrumentos_Observabilidad.csv) |
| [instrumentos/Instrumentos_Calidad.md](./instrumentos/Instrumentos_Calidad.md) | [Instrumentos_Calidad.csv](./instrumentos/Instrumentos_Calidad.csv) |

---

## Catálogos auto-generados (una ficha por método)

| Archivo | Carpeta de tests |
|---------|------------------|
| [unit_catalogo_autogenerado.md](./unit_catalogo_autogenerado.md) | `tests/Unit` (200 métodos) |
| [integration_catalogo_autogenerado.md](./integration_catalogo_autogenerado.md) | `tests/Integration` (21 métodos) |
| [feature_catalogo_autogenerado.md](./feature_catalogo_autogenerado.md) | `tests/Feature` (139 métodos) |
| [e2e_catalogo_autogenerado.md](./e2e_catalogo_autogenerado.md) | `tests/E2E` (2 métodos) |

---

## Clasificación de pruebas

| Tipo | Suite / documento |
|------|-------------------|
| Unitarias | `tests/Unit`, `unit_catalogo_autogenerado.md` |
| Integración | `tests/Integration`, `integration_flujo_eventos_bus.md` |
| Middleware | `feature_api_middleware_control.md`, `Instrumentos_Middleware` |
| API | `tests/Feature/Api/`, OpenApiContractTest |
| Base de datos | Integration tenant seeding, event_store idempotency |
| Arquitectura | `matrix_validacion_middleware.md` |
| Seguridad | `feature_seguridad_identidad.md`, `Instrumentos_Seguridad` |
| Observabilidad | `feature_dashboard_observabilidad.md`, `Instrumentos_Observabilidad` |
| Rendimiento | `load/k6_publish_sustained.js` — **PENDIENTE DE VALIDACIÓN** |
| IA | Sin suite automatizada (PROC-033 manual en `docs/evaluation/`) |
| Funcionales | `tests/Feature` |
| No funcionales | ISO 25010, resiliencia, retención |
| Aceptación | E2E + checklist pre-GO |
| Regresión | CI `.github/workflows/ci.yml` |
| Smoke | `HealthEndpointTest`, canary publish |
| End-to-End | `tests/E2E`, `e2e_simulacion_cliente.md` |

---

## Regenerar documentación

Tras añadir o modificar tests:

```bash
# 1. Ejecutar suite y capturar resultados JUnit
php vendor/bin/phpunit --log-junit docs/testing/tools/last_junit.xml

# 2. Regenerar catálogos Markdown por capa
php docs/testing/tools/generate_test_catalogs.php

# 3. Exportar matriz maestra CSV (362 casos TC-xxxx)
php docs/testing/tools/export_test_matrix.php

# 4. Regenerar CSVs estratégicos desde JUnit
php docs/testing/tools/export_strategic_csvs.php

# 5. Regenerar matrices de cobertura y módulos
php docs/testing/tools/generate_strategic_matrices.php

# 6. Sincronizar estadísticas en este README
composer test:stats
```

---

## Ejecución

```bash
php vendor/bin/phpunit
php vendor/bin/phpunit --testsuite Unit
php vendor/bin/phpunit --testsuite Integration
php vendor/bin/phpunit --testsuite Feature
php vendor/bin/phpunit --testsuite E2E
composer test
```

---

## Entorno de pruebas (`phpunit.xml`)

- `QUEUE_CONNECTION=sync` — determinismo en CI
- `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`
- `PLATFORM_DEPLOYMENT_MODE=instance_per_client`
- `PLATFORM_API_AUTH_ENABLED=false` (tests habilitan auth por test cuando aplica)

---

## Resultado real (auto-sincronizado)

- **Fecha:** 2026-06-27  
- **Comando:** `php vendor/bin/phpunit`  
- **Resultado:** **FALLÓ** (363 tests, 1198 assertions, 2 failures, 0 errors)

### Desglose por suite (métodos de test)

- **Unit:** 200 métodos `#[Test]` — 200 PASÓ
- **Integration:** 21 métodos `#[Test]` — 20 PASÓ, **1 FALLÓ**
- **Feature:** 139 métodos `#[Test]` — 138 PASÓ, **1 FALLÓ**
- **E2E:** 2 métodos `#[Test]` — 2 PASÓ

### Fallos activos (no inventados — evidencia JUnit)

| ID | Test | Incidencia |
|----|------|------------|
| TC-0070 | `OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled` | Redirect `/login` esperado, `/dashboard` obtenido |
| TC-0161 | `InstanceTenantSeedingIntegrationTest::message_queue_persists_tenant_id_after_seed` | `tenant_id` null en `message_queue` |

> Actualizado por `php docs/testing/tools/sync_test_stats.php` — ejecutar tras cambios (`composer test:stats`).

---

## Estadísticas carpeta

| Métrica | Valor |
|---------|-------|
| Archivos Markdown | 38+ |
| Archivos CSV | 25+ |
| Casos en matriz maestra | 362 |
| Instrumentos | 9 pares MD+CSV |
| Documentos estratégicos modulares | 18 |

---

## Observaciones

- Las pruebas **no** añaden reglas de negocio al middleware: observan rutas HTTP, contratos de evento, persistencia de trazas y coherencia con el catálogo declarativo.
- Los tipos `Platform.*` en tests son **ejemplos**; en despliegue se alinean con `config/eventbus.php` y `modules_config.json`.
- Resultados marcados **PENDIENTE DE VALIDACIÓN** cuando no hay evidencia de ejecución (p. ej. k6 load tests).
- Dominios retail (Inventario, Pedidos) **retirados del core** — ver [Funcionalidades_Obsoletas.md](./Funcionalidades_Obsoletas.md).

---

## Referencias

- [docs/Diagrama_BPMN/](../Diagrama_BPMN/) — Procesos PROC-001…034
- [docs/architecture/](../architecture/) — Blueprint, ERD, diccionario datos
- [docs/evaluation/](../evaluation/) — Framework aceptación middleware
- [docs/production/](../production/) — Runbooks, ADRs, planes
