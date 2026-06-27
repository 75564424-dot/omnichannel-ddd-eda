# Instrumento — Calidad (REQ-QA-01, validate-catalog, coverage gate)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente requisitos:** REQ-QA-01, REQ-VAL-01 en [requerimientos.csv](../../Patente/matriz_generada/requerimientos.csv)  
**Matriz evaluation:** [docs/evaluation/08_Matriz_Calidad.csv](../../evaluation/08_Matriz_Calidad.csv) (C24–C26)

## 1. Propósito

Instrumentar los controles de **calidad de software** del plan de calidad: cobertura mínima, validación de catálogo declarativo, contratos API e idempotencia.

## 2. Controles principales

| Control | Requisito | Mecanismo | Estado |
|---------|-----------|-----------|--------|
| Cobertura ≥70% | REQ-QA-01 | `platform:quality-coverage` / `scripts/ci/check-application-coverage.php` | PENDIENTE_VALIDACION en CI |
| validate-catalog | REQ-VAL-01 | `php artisan platform:validate-catalog` + Unit tests | Implementado |
| OpenAPI contract | REQ-API-01 / C24 | `OpenApiContractTest` + `lint-openapi.sh` | Implementado |
| Idempotencia publish | REQ-API-02 / C25 | `IdempotencyKeyPublishTest`, `EventStoreIdempotencyIntegrationTest` | Implementado |
| Matriz casos export | C26 | `export_test_matrix.php` → `matriz_maestra_casos.csv` | Implementado |

## 3. Métricas actuales (2026-06-27)

- **363** métodos test (`README.md`, `last_junit.xml`)
- **2** fallos abiertos (361/363 verdes ≈ 99.4% pass rate)
- Cobertura clover: ejecutar `composer test:coverage` — gate 70% no enforced

## 4. PROC-016 / PROC-033

- **PROC-016** Validación catálogo — B.3 Plan implementación
- **PROC-033** Evaluación aceptación middleware — framework evaluation

## 5. CSV

[Instrumentos_Calidad.csv](./Instrumentos_Calidad.csv)

## 6. Comandos

```bash
php artisan platform:validate-catalog
php scripts/ci/check-application-coverage.php build/coverage/clover.xml 70
php vendor/bin/phpunit tests/Unit/Platform/ValidatePlatformCatalogTest.php
bash scripts/ci/lint-openapi.sh
php docs/testing/tools/export_test_matrix.php
```
