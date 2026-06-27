# Feature — Control Plane (TenantLifecycle, Provisioning, Simulation, Catalog, Portal, Incidents)

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [feature_control_plane.csv](./feature_control_plane.csv)  
**Fuente IDs:** [matriz_maestra_casos.csv](./matriz_maestra_casos.csv)

---

## 1. Objetivo

Documentar las pruebas **Feature** y **Unit** del módulo **Control Plane** que validan gobierno SaaS de tenants: ciclo de vida, provisioning, simulación desde CP, catálogo técnico, portal amigable e incidentes de soporte.

## 2. Alcance BPMN

| Proceso | Documento BPMN | Dominio funcional |
|---------|----------------|-------------------|
| PROC-007 | [16_Proceso_Gestion_Empresas_Control_Plane.md](../Diagrama_BPMN/16_Proceso_Gestion_Empresas_Control_Plane.md) | Empresas, operadores scoped |
| PROC-008 | [17_Proceso_Provisioning_Nueva_Instancia.md](../Diagrama_BPMN/17_Proceso_Provisioning_Nueva_Instancia.md) | Lifecycle start/suspend/restore |
| PROC-015 | [24_Proceso_Gestion_Incidentes_Soporte.md](../Diagrama_BPMN/24_Proceso_Gestion_Incidentes_Soporte.md) | Reportes cliente + panel incidentes |
| PROC-020 | [29_Proceso_Simulacion_Desde_Control_Plane.md](../Diagrama_BPMN/29_Proceso_Simulacion_Desde_Control_Plane.md) | Automatización simulación desde índice empresas |
| PROC-034 | [34_Proceso_Espejo_Catalogo_CP_Silo.md](../Diagrama_BPMN/34_Proceso_Espejo_Catalogo_CP_Silo.md) | Espejo catálogo CP→silo local |

Procesos relacionados en CSV: PROC-016 (validación catálogo), PROC-019 (portal routing).

## 3. Carpetas de tests

| Capa | Ruta |
|------|------|
| Feature | `tests/Feature/Control/` |
| Unit | `tests/Unit/Control/` |
| Transversal Feature | `tests/Feature/TenantLifecycleTest.php` |
| Integración | `tests/Integration/Platform/TenantLifecycleIntegrationFlowTest.php` |

## 4. Clases representativas

### TenantLifecycle (PROC-008)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `TenantLifecycleEndpointsTest` | 2 | TC-0026, TC-0027 |
| `TenantLifecycleTest` | 4 | TC-0138–TC-0141 |
| `TenantLifecyclePolicyTest` | 5 | TC-0200–TC-0204 |
| `TenantLifecycleIntegrationFlowTest` | 1 | TC-0162 |

### Provisioning (PROC-007/008)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `ProvisionNewTenantInputMapperTest` | 2 | TC-0198, TC-0199 |
| `ProvisionNewTenantResultPresenterTest` | 2 | TC-0185, TC-0186 |
| `ProvisionNewTenantFleetFallbackHandlerTest` | 1 | TC-0197 |
| `TenantOperatorDeploymentGuardTest` | 1 | TC-0030 |

### Simulation (PROC-009/020)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `CompanySimulationAutomationTest` | 4 | TC-0015–TC-0018 |
| `SimulationRunReportTest` | 3 | TC-0023–TC-0025 |
| `SimulationRunCancellationTest` | 2 | TC-0021, TC-0022 |
| `SimulationInternalApiTest` | 1 | TC-0020 |
| `SimulationRunHandoffStoreTest` | (Unit) | TC-0187+ |
| `SimulationRunFailureHandlerTest` | (Unit) | — |

### Catalog (PROC-016/034)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `TenantModuleCatalogTest` | 2 | TC-0028, TC-0029 |
| `TenantModuleCatalogServiceTest` | (Unit) | TC-0176+ |
| `ClientDashboardModulesServiceTest` | (Unit) | — |

### Portal (PROC-019)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `TenantPortalRoutingTest` | 9 | TC-0037–TC-0045 |
| `ClientInstancePortalServiceTest` | (Unit) | — |

### Incidents (PROC-015)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `ClientSupportReportTest` | 3 | TC-0012–TC-0014 |
| `ControlIncidentsBusStatusTest` | 1 | TC-0019 |
| `ClientIncidentReportPresenterTest` | (Unit) | — |

### Operators / Empresas (PROC-007)

| Clase | Métodos | IDs matriz |
|-------|---------|------------|
| `TenantOperatorsScopedTest` | 6 | TC-0031–TC-0036 |

## 5. Datos de entrada típicos

- Usuario `saas_admin` autenticado en host control plane (`:8000`).
- Tenant con slug, lifecycle (`active`/`suspended`), `deployment_settings` y catálogo explícito en JSON.
- Fleet registry local (`deploy/local-instances/fleet-registry.json`) para espejo PROC-034.
- Simulación: tenant con catálogo válido vs tenant sin catálogo (rechazo esperado).

## 6. Resultado esperado

- Endpoints lifecycle responden 200/403 según rol y estado tenant.
- Provisioning persiste tenant y guía despliegue cuando fleet no disponible.
- Simulación desde CP crea `SimulationRun`, progreso vía API interna y reporte al completar.
- Catálogo guardado en CP se refleja en silo cuando existe instancia local.
- Portal `/t/{slug}/*` redirige a silo o devuelve 503/404 según estado.
- Incidentes: operador cliente envía reporte; admin SaaS responde con notificación.

## 7. Resultado obtenido (2026-06-27)

| Métrica | Valor |
|---------|-------|
| Casos en CSV | 65 |
| PHPUnit Feature Control | 11 clases, ~35 métodos |
| PHPUnit Unit Control | 15 clases, ~30 métodos |
| Fallos en módulo | 0 directos (fallos globales en Identity/Platform) |
| Estado agregado | **PASÓ** (361/363 suite global) |

### Brechas

- PROC-008 provisioning E2E fleet completo: parcial (fallback documentado, sin VM real PROC-030).
- PROC-034: cobertura centrada en `TenantModuleCatalogTest`; falta integración multi-silo concurrente.

## 8. Relación con middleware

El Control Plane **no publica eventos de negocio**; gobierna tenants, catálogo declarativo y simulaciones que **invocan** PROC-001 vía workers/handoff. Los tests validan contratos HTTP/Inertia y políticas de lifecycle que **bloquean** middleware en tenant suspendido (PROC-008 → middleware).

## 9. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Control/
php vendor/bin/phpunit tests/Unit/Control/
php vendor/bin/phpunit tests/Feature/TenantLifecycleTest.php
```

## 10. Trazabilidad

Ver [Matriz_Trazabilidad_Pruebas.csv](./Matriz_Trazabilidad_Pruebas.csv) filas CU-CP-01…CU-CP-05, CU-PRT-01.
