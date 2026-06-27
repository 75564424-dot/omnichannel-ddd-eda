# Informe de validación de testing — métricas finales

**Versión:** v1.8 | **Fecha:** 2026-06-27  
**Ejecutor:** CI / QA local  
**Comando:** `php vendor/bin/phpunit` (01:20, Memory 96 MB)

---

## 1. Resumen ejecutivo

La suite de pruebas del proyecto **omnichannel-ddd-eda** fue auditada y documentada tras la evolución de módulos desde la línea base **2026-05-22** (160 métodos). El estado actual comprende **363 tests PHPUnit** con **361 pasando** y **2 fallos documentados** que bloquean validación completa de PROC-005 (portal) y seeding multi-tenant (PROC-011).

**Veredicto:** **VALIDACIÓN PARCIAL** — apto para desarrollo continuo; **no apto para release productivo** sin resolver TC-0070 y TC-0161.

---

## 2. Métricas globales

| Métrica | Valor |
|---------|-------|
| Tests PHPUnit ejecutados | **363** |
| Métodos en matriz maestra | **362** |
| Assertions | **1.198** |
| Pasaron | **361** (99,45%) |
| Fallaron | **2** (0,55%) |
| Errores | 0 |
| Skipped | 0 |
| Tiempo ejecución | 80,2 s |
| Fecha ejecución | 2026-06-27 |

### Desglose por suite

| Suite | Métodos | Ejecutados | PASÓ | FALLÓ |
|-------|---------|------------|------|-------|
| Unit | 200 | 200 | 200 | 0 |
| Integration | 21 | 21 | 20 | **1** |
| Feature | 139 | 141* | 139 | **1** |
| E2E | 2 | 2 | 2 | 0 |

\*PHPUnit puede contar data providers adicionales vs métodos estáticos.

---

## 3. Cobertura por bounded context

| Módulo | Casos CSV | Documento | BPMN principal | Estado |
|--------|-----------|-----------|----------------|--------|
| Middleware | 80+ | `feature_api_middleware_control.md` | PROC-001–003 | OK |
| Control Plane | 65 | `feature_control_plane.md` | PROC-007,008,015,020,034 | OK |
| Dashboard/Obs | 71 | `feature_dashboard_observabilidad.md` | PROC-004,013 | OK |
| Security/Identity | 25 | `feature_seguridad_identidad.md` | PROC-005,006 | **1 fallo** |
| Integration | 24 | `feature_integracion_webhooks.md` | PROC-011,012 | **1 fallo** |
| Platform/Fleet | 59 | `feature_plataforma_fleet_simulacion.md` | PROC-009,010,020 | OK |
| Quality | 5+ | `unit_configuracion_catalogo_declarativo.md` | PROC-016 | OK |
| E2E | 2 | `e2e_simulacion_cliente.md` | PROC-009 | OK |

---

## 4. Cobertura BPMN (PROC-001 → PROC-034)

| Categoría | Procesos | Con tests | Sin tests |
|-----------|----------|-----------|-----------|
| Implementados | 22 | 20 | 2 (010 parcial, 034 parcial) |
| Parciales doc | 2 (011,008) | 2 | 0 |
| Documentales ops | 4 (030–033) | 0 | 4 |
| Diferidos | 1 (018) | 0 | 1 |

Detalle completo: [Matriz_Cobertura_Funcional.csv](./Matriz_Cobertura_Funcional.csv)

### Procesos sin cobertura automatizada

- PROC-030 Despliegue VM
- PROC-031 Backup
- PROC-032 DR Drill
- PROC-033 Evaluación aceptación (framework manual en `docs/evaluation/`)
- PROC-018 Multi-tenancy lógico (diferido)

### Brechas parciales

| ID | Brecha | Severidad |
|----|--------|-----------|
| REQ-DYN-01 | Métricas dinámicas — load k6 no ejecutado | Media |
| PROC-017 | Flujo 5 etapas — solo documental | Baja |
| PROC-012 | Admin canales — 1 test CRUD | Media |
| PROC-034 | Espejo CP→Silo — sin multi-silo stress | Media |

---

## 5. Fallos activos (detalle)

### TC-0070 — OperatorLoginTest

```
Tests\Feature\Identity\OperatorLoginTest::operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled

Expected: http://localhost/login
Actual:   http://localhost/dashboard
```

- **Proceso:** PROC-005
- **Incidencia:** INC-613e3b
- **Impacto:** Aislamiento portal multi-tenant
- **Documento:** [feature_seguridad_identidad.md](./feature_seguridad_identidad.md)

### TC-0161 — InstanceTenantSeedingIntegrationTest

```
message_queue_persists_tenant_id_after_seed

Expected tenant_id: 102be7b0-f31a-4220-933d-af811faa0a9b
Actual tenant_id:   null
```

- **Proceso:** PROC-011 / PROC-010
- **Incidencia:** INC-e36025
- **Impacto:** Trazabilidad tenant en cola post-seed
- **Documento:** [feature_integracion_webhooks.md](./feature_integracion_webhooks.md)

---

## 6. Pruebas de carga

| ID | Escenario | Resultado | Documento |
|----|-----------|-----------|-----------|
| LOAD-01 | Publish 100 eps / 60s | PENDIENTE DE VALIDACIÓN | [load/README.csv](./load/README.csv) |
| LOAD-02 | Smoke 10 eps / 30s | PENDIENTE DE VALIDACIÓN | [load/README.csv](./load/README.csv) |

Herramienta: k6 (`docs/testing/load/k6_publish_sustained.js`). Requiere middleware en ejecución.

---

## 7. Criterios arquitectura middleware

Matriz CRIT-01…CRIT-15 en [matrix_validacion_middleware.csv](./matrix_validacion_middleware.csv):

| Criterio | Resultado agregado |
|----------|-------------------|
| CRIT-01 Desacoplamiento | PASÓ |
| CRIT-04 Config declarativa | PASÓ |
| CRIT-06 API control | PASÓ |
| CRIT-09 Idempotencia event_store | PASÓ |
| CRIT-13 Tenant operacional | PASÓ (PROC-018 doc) |
| CRIT-15 Validación catálogo | PASÓ |

15/15 criterios con evidencia; CRIT-13 referencia PROC-018 documental.

---

## 8. Evolución vs 2026-05-22

| Indicador | Antes | Ahora | Δ |
|-----------|-------|-------|---|
| Métodos | 160 | 362 | +126% |
| Módulos documentados | 4 suites genéricas | 10 fichas modulares | +6 |
| IDs trazables | Parcial | 362 TC-xxxx | Completo |
| Fallos conocidos | 0 doc | 2 doc | Transparencia |

Auditoría: [00_Auditoria_Testing.md](./00_Auditoria_Testing.md)

---

## 9. Artefactos generados

| Artefacto | Filas / estado |
|-----------|----------------|
| `matriz_maestra_casos.csv` | 362 |
| `feature_control_plane.csv` | 65 |
| `feature_dashboard_observabilidad.csv` | 71 |
| `feature_seguridad_identidad.csv` | 25 |
| `feature_integracion_webhooks.csv` | 24 |
| `feature_plataforma_fleet_simulacion.csv` | 59 |
| `Matriz_Cobertura_Funcional.csv` | 26 |
| `Matriz_Trazabilidad_Pruebas.csv` | 24 |
| `Funcionalidades_Obsoletas.csv` | 10 |
| `last_junit.xml` | 363 testcases |

---

## 10. Conclusiones y recomendaciones

### Conclusiones

1. La suite creció **126%** con cobertura sólida de Control Plane, Dashboard, Platform/Fleet y API v1.
2. Middleware core (PROC-001–003) mantiene cobertura alta Feature + Integration + E2E.
3. **2 fallos** impiden certificar PROC-005 portal isolation y seeding tenant en cola.
4. Operaciones infra (PROC-030–032) y load (REQ-DYN-01) permanecen fuera de PHPUnit.

### Recomendaciones pre-release

| Prioridad | Acción |
|-----------|--------|
| P0 | Corregir TC-0070 y TC-0161 |
| P1 | Ejecutar LOAD-01 en staging |
| P2 | Ampliar PROC-012 y PROC-034 |
| P3 | Checklist manual PROC-030–032 |

### Firma validación

| Rol | Estado | Fecha |
|-----|--------|-------|
| QA automatizado | PARCIAL (2 fallos) | 2026-06-27 |
| Arquitectura middleware | OK (CRIT-01–15) | 2026-06-27 |
| Control Plane | OK módulo | 2026-06-27 |
| Release productivo | **BLOQUEADO** hasta P0 | — |

---

## 11. Referencias

- [00_Auditoria_Testing.md](./00_Auditoria_Testing.md)
- [Matriz_Cobertura_Funcional.md](./Matriz_Cobertura_Funcional.md)
- [Matriz_Trazabilidad_Pruebas.md](./Matriz_Trazabilidad_Pruebas.md)
- [Funcionalidades_Obsoletas.md](./Funcionalidades_Obsoletas.md)
- [00_Mapa_Procesos.md](../Diagrama_BPMN/00_Mapa_Procesos.md)
