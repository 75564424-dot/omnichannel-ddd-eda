# Unit — configuración y catálogo declarativo

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [unit_configuracion_catalogo_declarativo.csv](./unit_configuracion_catalogo_declarativo.csv)

---

## Objetivo

Garantizar que la capa de **configuración declarativa** (`config/modules/modules_config.json` vía `config/modules.php`, catálogos por instancia en `config/modules/instances/*/`) se normaliza, valida y presenta de forma predecible, coherente con dashboard, event bus y control plane.

## Alcance

- **24 métodos** Unit en clases relacionadas con catálogo (ver CSV).
- Clases principales: `ConfigModulesCatalogPresentationTest`, `ValidatePlatformCatalogTest`, `TenantModuleCatalogServiceTest`, `PackSubscriptionCatalogMergerTest`, `TopologyRegistryConfigMapperTest`, `ModulesConfigPathTest`, `TenantCatalogRuntimeConfiguratorTest`.
- Sin HTTP ni base de datos persistente (tests puros o mocks).

## Precondiciones

- `config/modules/modules_config.json` válido según esquema del proyecto.
- Autoload Composer actualizado.
- PHPUnit suite Unit disponible.

## Postcondiciones

- Filas inválidas omitidas en presentación; tipos de evento deduplicados.
- Comando `platform:validate-catalog` cubierto por tests Unit.
- Rutas de instancia (`ModulesConfigPath`) resueltas correctamente para silos locales.

## Casos

| Clase | Métodos | Resultado agregado |
|-------|---------|-------------------|
| `ConfigModulesCatalogPresentationTest` | 2 | PASÓ |
| `ValidatePlatformCatalogTest` | 4 | PASÓ |
| `TenantModuleCatalogServiceTest` | 1 | PASÓ |
| `PackSubscriptionCatalogMergerTest` | 1 | PASÓ |
| `TopologyRegistryConfigMapperTest` | 1 | PASÓ |
| `TopologySnapshotMergerTest` | 1 | PASÓ |
| `ModulesConfigPathTest` | 1 | PASÓ |
| `TenantCatalogRuntimeConfiguratorTest` | 1 | PASÓ |
| `TenantCatalogSampleEventBuilderTest` | 1 | PASÓ |
| `ClientDashboardMetricsCatalogServiceTest` | 2 | PASÓ |
| `ClientDashboardModulesConfigurationTest` | 1 | PASÓ |
| `ClientDashboardModulesServiceTest` | 2 | PASÓ |
| `ClientInstancePortalServiceTest` | 2 | PASÓ |
| `TenantModuleCatalogTest` (Feature, ref.) | — | Ver Feature Control |

Listado completo método a método: [unit_configuracion_catalogo_declarativo.csv](./unit_configuracion_catalogo_declarativo.csv).

## Criterios de aceptación

- Solo filas válidas en `producers`/`subscribers` del catálogo de presentación.
- Listas de `event_types` sin duplicados ni strings vacíos.
- Defaults de middleware y mensaje de contacto acorde a `modules.php`.
- Validación de catálogo rechaza JSON malformado o referencias rotas.

## Resultados

**2026-06-27:** 24/24 métodos **PASÓ** (JUnit, suite Unit 200/200 verde).

Comando:

```bash
php vendor/bin/phpunit --testsuite Unit --filter "Catalog|ModulesConfig|ValidatePlatform|TenantCatalog|PackSubscription|Topology"
```

## Observaciones

- `ConfigModulesCatalogDataProvider` es la implementación bajo test en `ConfigModulesCatalogPresentationTest`.
- Catálogo por tenant en silo: `config/modules/instances/{slug}/modules_config.json` (ej. `pruebas`).
- Espejo CP→Silo (PROC-034) se valida en Feature `TenantModuleCatalogTest`, no en este alcance Unit.

## Riesgos

| Riesgo | Mitigación |
|--------|------------|
| JSON de instancia desincronizado del CP | Tests Feature `TenantModuleCatalogTest` |
| Cambio de esquema sin actualizar validador | `ValidatePlatformCatalogTest` en CI |
| Presentación oculta errores de config | Tests de filas inválidas explícitos |

## Dependencias

- `config/modules.php`, `config/modules/modules_config.json`
- `docs/architecture/data_dictionary.md` (entidades catálogo)
- `docs/Diagrama_BPMN/11_Proceso_Sincronizacion_Catalogo_Registry.md`
- `docs/Diagrama_BPMN/34_Proceso_Espejo_Catalogo_CP_Silo.md`

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV casos Unit catálogo | `unit_configuracion_catalogo_declarativo.csv` |
| Catálogo Unit completo | `unit_catalogo_autogenerado.md` |
| JUnit | `docs/testing/tools/last_junit.xml` |

## Componentes

| Componente | Responsabilidad |
|------------|-----------------|
| `ConfigModulesCatalogDataProvider` | Normalización presentación dashboard |
| `ValidatePlatformCatalogCommand` | Gate CI catálogo |
| `TenantModuleCatalogService` | Carga catálogo tenant/instancia |
| `PackSubscriptionCatalogMerger` | Fusión suscripciones event bus |
| `TopologyRegistryConfigMapper` | Mapeo registry ↔ config |
| `TenantCatalogRuntimeConfigurator` | Runtime reconfig por tenant |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-002 Sync catálogo registry | [11_Proceso_Sincronizacion_Catalogo_Registry.md](../Diagrama_BPMN/11_Proceso_Sincronizacion_Catalogo_Registry.md) |
| PROC-016 Validación catálogo CI | [25_Proceso_Validacion_Catalogo_CI.md](../Diagrama_BPMN/25_Proceso_Validacion_Catalogo_CI.md) |
| PROC-034 Espejo CP→Silo | [34_Proceso_Espejo_Catalogo_CP_Silo.md](../Diagrama_BPMN/34_Proceso_Espejo_Catalogo_CP_Silo.md) |
| PROC-019 Portal cliente | [28_Proceso_Portal_Instancia_Cliente.md](../Diagrama_BPMN/28_Proceso_Portal_Instancia_Cliente.md) |

Evaluación: `docs/evaluation/03_Matriz_Integracion.csv` (criterio C4 catálogo).
