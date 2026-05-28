# Matriz de validación — middleware y documentación

## 1. Objetivo de la prueba
Dar una vista única de **qué criterios de arquitectura** cubre la suite automatizada y cómo se relacionan con la definición de middleware (`docs/Modulos/Modulo_Control_Middleware.md`, `docs/Plan_Desarrollo_Servicio_v0.1/Flujo_Middleware.md`).

## 2. Alcance
Criterios aplicables al repo **omnichannel-ddd-eda** en su estado actual (plataforma genérica de bus + dashboard). No incluye flujos omnicanal legados eliminados del código.

## 3. Flujo probado
No es una prueba ejecutable: es la **trazabilidad** entre requisitos documentales y casos `tests/*`.

## 4. Datos de entrada
Fuentes de verdad: `docs/Modulos`, `docs/Plan_Desarrollo_Modulos_v0.1`, `docs/Plan_Desarrollo_Servicio_v0.1`, `docs/personal_notes` (fases de implementación).

## 5. Resultado esperado
Cada fila de la matriz tiene al menos un test activo que la respalda; ausencias quedan explícitas para backlog de QA.

## 6. Resultado obtenido (si aplica)
Última revisión documental: **2026-05-03**. Suite: ver `README.md` (comando `php vendor/bin/phpunit`).

## 7. Relación con el middleware (qué valida del sistema)
El middleware actúa como **transporte y enrutado**; la matriz verifica que las pruebas no confunden middleware con reglas de negocio y que se validan desacoplamiento, propagación y observabilidad.

---

| Criterio | Significado | Cobertura principal en tests |
|----------|-------------|------------------------------|
| Desacoplamiento productor ↔ consumidor | Contrato por `event_type` y suscripciones; payloads opacos | `Integration/Middleware/EventPublisherServiceIntegrationTest`, `Feature/Middleware/MiddlewarePipelineEndToEndTest`, `E2E/Middleware/ClientProductionLikeSimulationTest` |
| Rol de middleware (sin negocio) | No validación semántica del dominio en publicación | Mismos + payloads heterogéneos en E2E |
| Propagación / bus | Dispatch síncrono en tests + filas `bus_queue_entries` | `EventPublisherServiceIntegrationTest`, `BusTracking*` |
| Config declarativa (`modules_config.json` vía `config/modules.php`) ↔ presentación | Normalización y exclusión de filas inválidas | `Unit/Dashboard/ConfigModulesCatalogPresentationTest` |
| `sync-config` idempotente | Misma topología tras repetición | `MiddlewarePipelineEndToEndTest::post_registry_sync_config_*` |
| API de control | Cola, topología, publicación, consulta de evento | `Feature/Middleware/MiddlewareControlApiTest` |
| Coherencia config ↔ ejecución ↔ API dashboard | Catálogo expuesto tras sync + snapshot | `MiddlewarePipelineEndToEndTest::full_flow_*`, `E2E/.../ClientProductionLikeSimulationTest` |
| Reutilización por cliente | Catálogo y `eventbus.*` reconfigurables por instancia | Escenarios con `config()->set` en Feature/E2E y JSON real en despliegue |
| Matrices B.2 / registro (Plan servicio) | JSON declarativo + sync | `post_registry_sync_config_from_declarative_catalog_only_*`, tests de registro en `MiddlewareControlApiTest` |

---

## Observaciones
- Los catálogos auto-generados (`*_catalogo_autogenerado.md`) detallan **cada método**; esta matriz prioriza **criterios de negocio/arquitectura**.
- Para alineación fina con tablas del `Plan_de_implementacion.md`, actualizar esta matriz cuando cambien nombres de eventos estándar del proyecto.
