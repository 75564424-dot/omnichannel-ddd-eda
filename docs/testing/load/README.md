# Prueba de carga — publish sustained throughput

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [README.csv](./README.csv) | **Plan:** Plan Calidad Fase 2 | **Herramienta:** [k6](https://k6.io/)

---

## Objetivo

Validar **100 eventos/segundo sostenidos** en `POST /api/middleware/events/publish` como baseline de capacidad del middleware en silo cliente, complementando tests PHPUnit (funcionalidad, no rendimiento).

## Alcance

- Script k6: `load/k6_publish_sustained.js`
- Escenarios LOAD-01 (100 eps / 60 s) y LOAD-02 (smoke 10 eps / 30 s).
- **No forma parte de PHPUnit** — resultados `PENDIENTE DE VALIDACIÓN` hasta ejecución manual o workflow.

## Precondiciones

- k6 instalado (`choco install k6` en Windows / `brew install k6` en macOS).
- Middleware en ejecución (`APP_URL` apuntando a silo activo).
- Auth API deshabilitada **o** `PLATFORM_API_KEY` configurada para header `X-API-Key`.
- Catálogo sincronizado (`sync-config`) y tenant operacional.
- Sync previo de registry recomendado en entorno de carga dedicado.

## Postcondiciones

- Umbrales k6 cumplidos: `http_req_failed` < 5%, `http_req_duration` p95 < 2000 ms.
- Resultados registrados en CSV y artefacto CI si aplica.
- Sin degradación persistente en cola o DLQ post-prueba.

## Casos

| ID | Escenario | Throughput | Duración | Resultado |
|----|-----------|------------|----------|-----------|
| LOAD-01 | Publish sustained throughput | 100 eps | 60 s | PENDIENTE DE VALIDACIÓN |
| LOAD-02 | Publish baseline smoke | 10 eps | 30 s | PENDIENTE DE VALIDACIÓN |

Detalle: [README.csv](./README.csv).

## Criterios de aceptación

| Métrica | Umbral |
|---------|--------|
| `http_req_failed` | < 5% |
| `http_req_duration` p95 | < 2000 ms |
| Throughput objetivo LOAD-01 | ≥ 100 eps configurados |
| Códigos HTTP publish | Predominio 2xx |

## Resultados

**2026-06-27:** Escenarios de carga **no ejecutados** en la corrida PHPUnit documentada. Estado: **PENDIENTE DE VALIDACIÓN**.

PHPUnit (funcional): 363 tests, 361 OK — no sustituye prueba de carga.

### Ejecución local

```bash
export APP_URL=http://127.0.0.1:8000
export PLATFORM_LOAD_TEST_EPS=100
export PLATFORM_LOAD_TEST_DURATION=60
bash scripts/ci/run-k6-load-test.sh
```

### Variables opcionales

| Variable | Default | Descripción |
|----------|---------|-------------|
| `PLATFORM_LOAD_TEST_EPS` | 100 | Eventos por segundo |
| `PLATFORM_LOAD_TEST_DURATION` | 60 | Segundos de prueba |
| `PLATFORM_API_KEY` | — | Header `X-API-Key` si auth habilitada |
| `APP_URL` | — | Base URL del silo bajo prueba |

## Observaciones

- Workflow CI: `.github/workflows/quality-load.yml` — `workflow_dispatch` y schedule semanal.
- Entorno prod/VM: ver `docs/production/` y [30_Proceso_Despliegue_Produccion_VM.md](../Diagrama_BPMN/30_Proceso_Despliegue_Produccion_VM.md).
- Tests sync en PHPUnit no modelan contención SQLite/concurrency bajo carga; k6 es necesario para baseline real.

## Riesgos

| Riesgo | Impacto |
|--------|---------|
| No ejecutar load test | Sobreestimar capacidad 100 eps |
| SQLite en prod bajo carga | Contención vs PostgreSQL documentado |
| Auth mal configurada en script | 401 masivo, falso negativo |
| Cola async sin workers | Acumulación vs publish HTTP 200 |

## Dependencias

- `docs/testing/load/k6_publish_sustained.js`
- `scripts/ci/run-k6-load-test.sh`
- `.github/workflows/quality-load.yml`
- `docs/evaluation/04_Guia_Instrumentos_Medicion.md`
- Middleware PROC-001 publish endpoint operativo

## Evidencias

| Artefacto | Ubicación |
|-----------|-----------|
| CSV escenarios load | `load/README.csv` |
| Script k6 | `load/k6_publish_sustained.js` |
| Runner CI | `scripts/ci/run-k6-load-test.sh` |
| JUnit (funcional, no load) | `docs/testing/tools/last_junit.xml` |

## Componentes

| Componente | Rol en carga |
|------------|--------------|
| `POST /api/middleware/events/publish` | Endpoint bajo estrés |
| `EventPublisherService` | Pipeline publicación |
| `bus_queue_entries` | Persistencia cola |
| k6 VUs | Generación tráfico sostenido |
| Prometheus `/metrics` | Observación post-prueba (opcional) |

## Trazabilidad BPMN

| Proceso | Documento |
|---------|-----------|
| PROC-001 Publicación eventos | [10_Proceso_Publicacion_Eventos_Bus.md](../Diagrama_BPMN/10_Proceso_Publicacion_Eventos_Bus.md) |
| PROC-013 Monitoreo | [22_Proceso_Monitoreo_Alertas_Plataforma.md](../Diagrama_BPMN/22_Proceso_Monitoreo_Alertas_Plataforma.md) |
| PROC-030 Deploy VM | [30_Proceso_Despliegue_Produccion_VM.md](../Diagrama_BPMN/30_Proceso_Despliegue_Produccion_VM.md) |
| PROC-033 Evaluación aceptación | [33_Proceso_Evaluacion_Aceptacion_Middleware.md](../Diagrama_BPMN/33_Proceso_Evaluacion_Aceptacion_Middleware.md) |

Evaluación: `docs/evaluation/06_Matriz_Operacion.csv`, instrumentos en `docs/evaluation/04_Guia_Instrumentos_Medicion.md`.
