# Matriz — tests prioritarios (Plan Calidad)

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [priority_tests_matrix.csv](./priority_tests_matrix.csv)

---

## Objetivo

Consolidar los **6 tests prioritarios** definidos en Plan Calidad / framework de evaluación, con trazabilidad a clases PHPUnit, procesos BPMN y estado CI real (2026-06-27).

## Alcance

- Seis capacidades críticas de plataforma: auth API, webhooks, idempotencia event store, retención, publish idempotente HTTP, validación catálogo.
- **18 filas** en CSV (métodos individuales por clase prioritaria).
- CI: `.github/workflows/ci.yml`.

## Precondiciones

- PHPUnit ejecutado con JUnit exportado.
- Variables de entorno de test según `phpunit.xml`.
- Para webhook/auth: flags de plataforma configurables en tests.

## Postcondiciones

- Cada prioridad PRIO-01…PRIO-06 tiene cobertura verificable en verde (salvo regresiones globales no relacionadas).
- Matriz enlazada a evaluación en `docs/evaluation/`.

## Casos

| # | Test prioritario | Clase | Proceso BPMN | Resultado agregado |
|---|------------------|-------|--------------|-------------------|
| 1 | Auth middleware API | `PlatformApiAuthenticationTest` | PROC-006 | PASÓ |
| 2 | Webhook signature validation | `WebhookIngressTest` | PROC-011 | PASÓ |
| 3 | event_store append idempotency | `EventStoreIdempotencyIntegrationTest` | PROC-001 | PASÓ |
| 4 | Retention purge command | `PurgePlatformRetentionTest` | PROC-014 | PASÓ |
| 5 | Idempotent publish HTTP 200 | `ResilienceApiTest` | PROC-003 | PASÓ |
| 6 | validate-catalog command | `ValidatePlatformCatalogTest` | PROC-016 | PASÓ |

Detalle método a método: [priority_tests_matrix.csv](./priority_tests_matrix.csv).

## Criterios de aceptación

- Los 6 bloques prioritarios con `Estado_CI = CI verde` en CSV.
- Auth API rechaza requests sin credenciales cuando auth habilitada.
- Webhook rechaza firma inválida.
- Segundo append event_store no duplica fila.
- Purge respeta tablas permitidas y retención.
- Replay idempotency-key retorna misma respuesta HTTP 200.
- validate-catalog falla en CI si JSON inválido.

## Resultados

**2026-06-27:** Los **6 tests prioritarios (18 métodos agregados)** — **PASÓ**.

Suite global: 363 tests, 2 fallos en Identity/Platform seeding **no pertenecen** a esta matriz prioritaria.

Comandos de verificación individual:

```bash
php vendor/bin/phpunit --filter PlatformApiAuthenticationTest
php vendor/bin/phpunit --filter WebhookIngressTest
php vendor/bin/phpunit --filter EventStoreIdempotencyIntegrationTest
php vendor/bin/phpunit --filter PurgePlatformRetentionTest
php vendor/bin/phpunit --filter ResilienceApiTest
php vendor/bin/phpunit --filter ValidatePlatformCatalogTest
```

## Observaciones

- Prioridades alineadas con `docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md`.
- Load test k6 (100 eps) es complemento no-PHPUnit; ver `load/README.md`.
- API v1 contract tests (`OpenApiContractTest`) son prioritarios operativos pero fuera de la lista histórica de 6.

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| Desactivar auth en prod sin tests | Bypass seguridad |
| Webhook secret rotado sin test | Ingress rechazado |
| Purge en tabla no allowlisted | Pérdida datos |
| Catálogo roto mergeado | CI verde local, fallo prod |

## Dependencias

- `.github/workflows/ci.yml`
- `docs/evaluation/05_Matriz_Seguridad.csv`
- `docs/evaluation/08_Matriz_Calidad.csv`
- `docs/production/` runbooks retención y auth

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV prioridades | `priority_tests_matrix.csv` |
| Matriz maestra | `matriz_maestra_casos.csv` |
| JUnit | `docs/testing/tools/last_junit.xml` |
| Framework evaluación | `docs/evaluation/Middleware_Acceptance_Evaluation_Framework.md` |

## Componentes

| Prioridad | Componente |
|-----------|------------|
| Auth API | `AuthenticatePlatformApi`, Sanctum M2M |
| Webhook | `WebhookSignatureVerifier`, `ReceiveWebhookUseCase` |
| Idempotencia store | `EventStoreIdempotencyIntegrationTest` |
| Retención | `PurgePlatformRetentionCommand` |
| Resiliencia HTTP | Idempotency-Key middleware publish |
| Catálogo CI | `ValidatePlatformCatalogCommand` |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-005 Auth web | [14_Proceso_Autenticacion_Operadores_Web.md](../Diagrama_BPMN/14_Proceso_Autenticacion_Operadores_Web.md) |
| PROC-006 Auth API | [15_Proceso_Autenticacion_API_Integradores.md](../Diagrama_BPMN/15_Proceso_Autenticacion_API_Integradores.md) |
| PROC-011 Webhooks | [20_Proceso_Ingress_Webhooks_Integraciones.md](../Diagrama_BPMN/20_Proceso_Ingress_Webhooks_Integraciones.md) |
| PROC-014 Retención | [23_Proceso_Retencion_Purga_Datos.md](../Diagrama_BPMN/23_Proceso_Retencion_Purga_Datos.md) |
| PROC-016 Validación catálogo | [25_Proceso_Validacion_Catalogo_CI.md](../Diagrama_BPMN/25_Proceso_Validacion_Catalogo_CI.md) |
| PROC-001 Publicación | [10_Proceso_Publicacion_Eventos_Bus.md](../Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md) |

Macroproceso seguridad: [04_Macroproceso_Seguridad_Acceso.md](../Diagrama_BPMN/04_Macroproceso_Seguridad_Acceso.md).
