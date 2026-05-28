# Auditoría de la suite — redundancia y limpieza

## 1. Objetivo de la prueba
Registrar decisiones de **limpieza de documentación y pruebas** para evitar duplicación y referencias a flujos que ya no existen en código.

## 2. Alcance
Directorio `docs/testing` y convenciones de nombres; no modifica código de aplicación.

## 3. Flujo probado
Revisión manual (QA lead) de docs frente a `tests/*` y rutas HTTP actuales.

## 4. Datos de entrada
Archivos eliminados o reemplazados (lista abajo), salida de `php vendor/bin/phpunit`.

## 5. Resultado esperado
Un solo conjunto de catálogos auto-generados con prefijo claro; matrices y auditoría breves y actuales.

## 6. Resultado obtenido (si aplica)
**2026-05-03**

- **Eliminados** (obsoletos o redundantes con plantillas desactualizadas):
  - `catalog_unit.md`, `catalog_integration.md`, `catalog_feature.md`, `catalog_e2e.md` — volumen elevado y referencias legacy (ej. flujos omnicanal no presentes en la suite actual).
  - `architecture_validation_matrix.md` — reemplazado por `matrix_validacion_middleware.md` alineado a middleware genérico.
  - `audit_phase1_phase2.md` — fusionado en este documento.
- **Sustituidos por:**
  - `unit_catalogo_autogenerado.md`, `integration_catalogo_autogenerado.md`, `feature_catalogo_autogenerado.md`, `e2e_catalogo_autogenerado.md` (generador actualizado con preámbulo estándar de las 7 secciones).
- **Pruebas nuevas** (código): `ConfigModulesCatalogPresentationTest`, `ClientProductionLikeSimulationTest` bajo `tests/E2E`.
- **Duplicación funcional**: `MiddlewarePipelineEndToEndTest::full_flow_*` y `ClientProductionLikeSimulationTest` comparten familia de comportamiento; se mantiene **E2E** para escenario **multi-tipo de evento** y payload heterogéneo explícito (simulación cliente), y **Feature** para regresión B.2/sync/publish ya consolidada.

## 7. Relación con el middleware (qué valida del sistema)
La auditoría asegura que la documentación de pruebas **no contradice** el rol del middleware y que los escenarios E2E/Feature cubren integración desacoplada sin duplicar asserts triviales.
