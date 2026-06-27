# Feature — Dashboard, Observabilidad y Monitoreo

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [feature_dashboard_observabilidad.csv](./feature_dashboard_observabilidad.csv)  
**Fuente IDs:** [matriz_maestra_casos.csv](./matriz_maestra_casos.csv)

---

## 1. Objetivo

Documentar pruebas que validan la **proyección observacional** del middleware (dashboard SSE/REST), **métricas dinámicas**, **export Prometheus**, **correlación de trazas** y **evaluación de alertas** programadas.

## 2. Alcance BPMN

| Proceso | Documento BPMN | Capa validada |
|---------|----------------|---------------|
| PROC-004 | [13_Proceso_Observabilidad_Dashboard.md](../Diagrama_BPMN/13_Proceso_Observabilidad_Dashboard.md) | Feed, métricas, nodos, activación LIVE |
| PROC-013 | [22_Proceso_Monitoreo_Alertas_Plataforma.md](../Diagrama_BPMN/22_Proceso_Monitoreo_Alertas_Plataforma.md) | Prometheus, canary, alertas scheduler |
| PROC-016 | [25_Proceso_Validacion_Catalogo_CI.md](../Diagrama_BPMN/25_Proceso_Validacion_Catalogo_CI.md) | Presentación catálogo módulos |

## 3. Carpetas de tests

| Capa | Ruta |
|------|------|
| Feature Dashboard | `tests/Feature/Dashboard/` |
| Feature Observability | `tests/Feature/Observability/` |
| Feature Monitoring | `tests/Feature/Monitoring/` |
| Unit Dashboard | `tests/Unit/Dashboard/` |
| Unit Observability | `tests/Unit/Observability/` |
| Integration Dashboard | `tests/Integration/Dashboard/` |
| Integration Observability | `tests/Integration/Observability/` |

## 4. Clases representativas

### DashboardEndpointsTest — 17 métodos (PROC-004)

IDs matriz TC-0047–TC-0063. Cubre:

| Método | Endpoint / comportamiento |
|--------|---------------------------|
| `idle_dashboard_feed_reconciles_syncing_middleware_snapshot_online` | Reconciliación feed idle |
| `recent_dashboard_feed_entries_skip_syncing_reconciliation` | Feed reciente sin reconciliar |
| `get_dashboard_metrics_returns_global_counters_shape` | `GET /api/dashboard/metrics` |
| `get_dashboard_metrics_catalog_returns_metrics_and_event_envelope` | Catálogo métricas |
| `get_dashboard_metric_series_returns_chart_payload` | Series temporales (REQ-DYN-01) |
| `get_dashboard_metric_series_returns_404_for_unknown_metric` | Validación métrica desconocida |
| `get_dashboard_modules_catalog_returns_normalized_topology_payload` | Topología módulos |
| `get_dashboard_events_feed_returns_list_wrapper` | Feed eventos |
| `get_dashboard_snapshot_returns_aggregate_payload` | Snapshot agregado |
| `get_dashboard_metrics_flow_returns_diagram_payload` | Diagrama flujo |
| `get_dashboard_daily_series_respects_days_cap` | Serie diaria con límite |
| `get_dashboard_nodes_returns_nested_payload` | Nodos anidados |
| `refresh_node_returns_updated_status_snapshot` | Refresh nodo |
| `patch_middleware_events_updates_flag_and_restores_default` | Activación LIVE |
| `patch_middleware_events_validates_boolean` | Validación PATCH |
| `patch_middleware_events_rejects_unknown_node` | Nodo inexistente |
| `get_dashboard_nodes_and_bus_endpoints_respond` | Smoke nodos + bus |

### ModuleActivationGateServiceTest (PROC-004 / simulación)

| Método | ID | Validación |
|--------|-----|------------|
| `simulation_blocked_when_middleware_inactive` | TC-0215 | Gate simulación |
| `simulation_blocked_when_all_producers_inactive` | TC-0216 | Gate productores |

### Observabilidad (PROC-013)

| Clase | IDs | Rol |
|-------|-----|-----|
| `PrometheusMetricsEndpointTest` | TC-0117, TC-0118 | `/metrics` formato Prometheus |
| `CorrelationIdMiddlewareTest` | TC-0115, TC-0116 | Header `X-Correlation-Id` |
| `PrometheusTextRendererTest` | (Unit) | Render líneas métricas |
| `TraceContextTest` | (Unit) | Contexto traza |
| `TraceLogsPipelineIntegrationTest` | TC-0159 | Pipeline publish→trace→feed |

### Monitoreo (PROC-013)

| Clase | IDs | Rol |
|-------|-----|-----|
| `EvaluateMonitoringAlertsCommandTest` | TC-0113, TC-0114 | `platform:monitoring-evaluate` |
| `CanaryPublishCommandTest` | TC-0112 | `platform:canary-publish` |

### Integración arquitectura dashboard

| Clase | ID | Rol |
|-------|-----|-----|
| `PlatformPingObservedByDashboardIntegrationTest` | TC-0144 | Ping → feed |
| `GetGlobalMetricsUsesDashboardRepositoriesTest` | TC-0143 | Límite capas |
| `DashboardFeedListenersDependencyBoundaryTest` | TC-0142 | Frontera dependencias |

## 5. Resultado obtenido (2026-06-27)

| Métrica | Valor |
|---------|-------|
| Casos en CSV | 71 |
| DashboardEndpointsTest | 17/17 PASÓ |
| ModuleActivationGateServiceTest | 2/2 PASÓ |
| Prometheus + Monitoring Feature | 5/5 PASÓ |
| Fallos módulo | 0 |
| REQ-DYN-01 load | **Parcial** — series unitarias OK; k6 pendiente ([load/README.md](./load/README.md)) |

## 6. Brechas

- **REQ-DYN-01**: métricas dinámicas cubiertas en Unit/Feature; pruebas de carga sostenida no ejecutadas en CI.
- Alertas PROC-013: evaluación comando cubierta; integración con canal externo (PagerDuty/email) no automatizada.

## 7. Ejecución

```bash
php vendor/bin/phpunit tests/Feature/Dashboard/DashboardEndpointsTest.php
php vendor/bin/phpunit tests/Unit/Dashboard/ModuleActivationGateServiceTest.php
php vendor/bin/phpunit tests/Feature/Observability/
php vendor/bin/phpunit tests/Feature/Monitoring/
```

## 8. Trazabilidad BPMN

Macroproceso MP-03: [03_Macroproceso_Observabilidad_Monitoreo.md](../Diagrama_BPMN/03_Macroproceso_Observabilidad_Monitoreo.md).  
Filas CU-DASH-01, CU-DASH-02, CU-OBS-01, CU-OBS-02 en [Matriz_Trazabilidad_Pruebas.csv](./Matriz_Trazabilidad_Pruebas.csv).
