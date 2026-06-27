# Feature — Plataforma, Fleet Local y Simulación Cliente

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [feature_plataforma_fleet_simulacion.csv](./feature_plataforma_fleet_simulacion.csv)  
**Fuente IDs:** [matriz_maestra_casos.csv](./matriz_maestra_casos.csv)

---

## 1. Objetivo

Documentar pruebas de **comandos plataforma**, **flota local (LocalFleet)**, **simulación cliente** (`platform:simulate-client`) y automatización desde Control Plane.

## 2. Alcance BPMN

| Proceso | Documento BPMN | Enfoque |
|---------|----------------|---------|
| PROC-009 | [18_Proceso_Simulacion_Cliente_E2E.md](../Diagrama_BPMN/18_Proceso_Simulacion_Cliente_E2E.md) | CLI simulate-client, fixtures |
| PROC-010 | [19_Proceso_Onboarding_Instancia_Cliente.md](../Diagrama_BPMN/19_Proceso_Onboarding_Instancia_Cliente.md) | Contexto tenant instancia |
| PROC-020 | [29_Proceso_Simulacion_Desde_Control_Plane.md](../Diagrama_BPMN/29_Proceso_Simulacion_Desde_Control_Plane.md) | Panel simulación empresas |

## 3. Carpetas de tests

| Capa | Ruta |
|------|------|
| Feature Platform | `tests/Feature/Platform/` |
| Feature Control (simulación) | `tests/Feature/Control/CompanySimulationAutomationTest.php` |
| Unit Platform | `tests/Unit/Platform/` |
| Unit Simulation | `tests/Unit/Simulation/` |
| Unit Shared Platform | `tests/Unit/Shared/Platform/` |
| E2E | `tests/E2E/Middleware/` |

## 4. Clases representativas

### SimulateClientCommandTest (PROC-009)

| ID | Método | Validación |
|----|--------|------------|
| TC-0128 | `simulate_retailco_publishes_events_and_syncs_registry` | Fixture retailco + sync |
| TC-0129 | `simulate_acmepos_fixture_is_valid` | Fixture acmepos |
| TC-0130 | `simulate_unknown_slug_fails` | Slug desconocido → error |

### CompanySimulationAutomationTest (PROC-020)

| ID | Método | Validación |
|----|--------|------------|
| TC-0015 | `saas_admin_can_run_simulation_from_companies_index` | POST simulación CP |
| TC-0016 | `control_plane_marks_tenants_without_explicit_catalog_as_not_simulatable` | Gate catálogo |
| TC-0017 | `companies_index_includes_simulation_panel_props` | Props Inertia panel |
| TC-0018 | `simulation_post_is_rejected_when_tenant_has_no_explicit_catalog` | Rechazo 422 |

### LocalFleet (PROC-009/010)

| Clase | ID | Validación |
|-------|-----|------------|
| `LocalFleetRegistryTest` | TC-0324 | Alloc puerto + upsert slug |
| `LocalFleetAdminCredentialsResolverTest` | TC-0349 | Credenciales admin fleet |
| `LocalInstanceEnvironmentLoaderTest` | TC-0325 | Carga `.env` instancia |
| `InstanceDeploymentServiceTest` | TC-0320, TC-0321 | Binding tenant + multi-tenant flag |
| `InstanceTenantContextTest` | TC-0322, TC-0323 | Contexto slug/deployment |

### Planificación simulación (Unit)

| Clase | IDs | Rol |
|-------|-----|-----|
| `ClientSimulationPublishPlanTest` | TC-0317–TC-0319 | Plan burst/per-minute |
| `ClientSimulationDeadlineTest` | TC-0315 | Cap por duración |
| `ClientSimulationFixedCountTest` | TC-0316 | Count fijo |
| `SimulateClientOrchestratorTest` | — | Orquestador CLI |
| `SimulationTenantEligibilityCheckerTest` | — | Elegibilidad tenant |

### Comandos operación plataforma

| Clase | IDs | Comando |
|-------|-----|---------|
| `CleanEnvironmentCommandTest` | TC-0119, TC-0120 | Limpieza entorno |
| `ResetLocalEnvironmentCommandTest` | TC-0124–TC-0127 | Reset local + purge tenants |
| `PurgePlatformRetentionTest` | TC-0121–TC-0123 | PROC-014 retención |

### E2E (PROC-009)

| Clase | IDs | Rol |
|-------|-----|-----|
| `ClientProductionLikeSimulationTest` | TC-0001 | Multi-evento productivo |
| `MultiClientFixtureSimulationTest` | TC-0002 | Todos fixtures versionados |

## 5. Resultado obtenido (2026-06-27)

| Métrica | Valor |
|---------|-------|
| Casos en CSV | 59 |
| SimulateClientCommandTest | 3/3 PASÓ |
| CompanySimulationAutomationTest | 4/4 PASÓ |
| LocalFleet unit | PASÓ |
| E2E simulación | 2/2 PASÓ |
| Fallos módulo | 0 directos |

## 6. Brechas

- PROC-010 onboarding: cobertura indirecta vía `InstanceTenantContext` y seeders; sin flujo E2E onboarding documentado.
- Despliegue fleet real (PROC-030): no automatizado en PHPUnit.
- Load tests simulación sostenida: ver [load/README.csv](./load/README.csv) — PENDIENTE.

## 7. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Platform/SimulateClientCommandTest.php
php vendor/bin/phpunit tests/Feature/Control/CompanySimulationAutomationTest.php
php vendor/bin/phpunit tests/Unit/Platform/LocalFleet/
php vendor/bin/phpunit tests/E2E/
```

## 8. Trazabilidad

CU-PLT-01, CU-PLT-02, CU-OPS-01 en [Matriz_Trazabilidad_Pruebas.csv](./Matriz_Trazabilidad_Pruebas.csv).
