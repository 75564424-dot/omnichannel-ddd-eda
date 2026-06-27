# Matriz de cobertura funcional — PROC-001 a PROC-034

**Versión:** v1.9 | **Fecha:** 2026-06-24 | **CSV:** [Matriz_Cobertura_Funcional.csv](./Matriz_Cobertura_Funcional.csv)  
**Fuente tests:** [matriz_maestra_casos.csv](./matriz_maestra_casos.csv)  
**Mapa BPMN:** [00_Mapa_Procesos.md](../Diagrama_BPMN/00_Mapa_Procesos.md)

---

## 1. Objetivo

Mapear cada proceso BPMN (PROC-001…PROC-034) y requisito dinámico **REQ-DYN-01** a la cobertura de pruebas PHPUnit, indicando brechas explícitas.

## 2. Leyenda de cobertura

| Nivel | Criterio |
|-------|----------|
| **Alta** | ≥10 tests asignados |
| **Media** | 3–9 tests |
| **Parcial** | 1–2 tests |
| **Sin tests** | 0 tests en matriz |
| **Documental** | Solo runbooks/docs |
| **Diferido** | No implementado (PROC-018) |

## 3. Resumen por macroproceso

| Macroproceso | Procesos | Tests totales | Cobertura agregada |
|--------------|----------|---------------|-------------------|
| MP-01 Plataforma SaaS | 007,008,010,015,020,034 | ~90 | Alta |
| MP-02 Middleware | 001,002,003,017 | ~85 | Alta PROC-001–003; PROC-017 indirecta |
| MP-03 Observabilidad | 004,013 | ~71 | Alta |
| MP-04 Seguridad | 005,006 | ~25 | Alta |
| MP-05 Calidad | 009,016 | ~45 | Alta |
| MP-06 Operaciones | 014,030,031,032 | ~8 | Parcial (solo PROC-014) |
| MP-07 Gobernanza | 033,018 | 0–1 | Documental / Diferido |
| MP-08 Integración | 011,012,017 | ~24 | Parcial PROC-011/012 |
| MP-09 Portal | 019 | ~9 | Media |

## 4. Tabla PROC-001 → PROC-034

| Proceso | Nombre | Tests | PASÓ | FALLO | Cobertura | Brecha principal |
|---------|--------|-------|------|-------|-----------|------------------|
| PROC-001 | Publicación eventos | 42+ | 42+ | 0 | Alta | — |
| PROC-002 | Sync registry | 18+ | 18+ | 0 | Alta | — |
| PROC-003 | Consulta operativa bus | 22+ | 22+ | 0 | Alta | — |
| PROC-004 | Dashboard observabilidad | 45+ | 45+ | 0 | Alta | REQ-DYN-01 load parcial |
| PROC-005 | Auth operadores web | 16+ | 16+ | 0 | Media | — |
| PROC-006 | Auth API integradores | 9+ | 9+ | 0 | Media | — |
| PROC-007 | Gestión empresas CP | 12+ | 12+ | 0 | Alta | — |
| PROC-008 | Provisioning instancia | 14+ | 14+ | 0 | Media | Fleet VM real |
| PROC-009 | Simulación E2E | 35+ | 35+ | 0 | Alta | — |
| PROC-010 | Onboarding instancia | 2 | 2 | 0 | Parcial | E2E onboarding |
| PROC-011 | Ingress webhooks | 20+ | 20+ | 0 | Media | Rate limit / replay |
| PROC-012 | Canales integraciones | 1 | 1 | 0 | Parcial | CRUD ampliado |
| PROC-013 | Monitoreo alertas | 8+ | 8+ | 0 | Media | Canal alerta externo |
| PROC-014 | Retención purga | 5+ | 5+ | 0 | Media | — |
| PROC-015 | Incidentes soporte | 4+ | 4+ | 0 | Media | — |
| PROC-016 | Validación catálogo CI | 15+ | 15+ | 0 | Alta | — |
| PROC-017 | Flujo 5 etapas | 0 | 0 | 0 | Indirecta | Sin suite dedicada |
| PROC-018 | Multi-tenant lógico | 0 | 0 | 0 | Diferido | Fase 3 backlog |
| PROC-019 | Portal instancia | 9+ | 9+ | 0 | Media | — |
| PROC-020 | Simulación desde CP | 15+ | 15+ | 0 | Alta | — |
| PROC-030 | Despliegue VM | 0 | 0 | 0 | Documental | Manual ops |
| PROC-031 | Backup | 0 | 0 | 0 | Documental | Manual ops |
| PROC-032 | DR Drill | 0 | 0 | 0 | Documental | Manual ops |
| PROC-033 | Evaluación aceptación | 0 | 0 | 0 | Documental | docs/evaluation |
| PROC-034 | Espejo CP→Silo | 2+ | 2+ | 0 | Parcial | Multi-silo concurrente |
| REQ-DYN-01 | Métricas dinámicas | 8 | 8 | 0 | Parcial | k6 load pendiente |

> Conteos exactos por proceso en [Matriz_Cobertura_Funcional.csv](./Matriz_Cobertura_Funcional.csv).

## 5. Brechas prioritarias

| ID | Brecha | Impacto | Acción |
|----|--------|---------|--------|
| GAP-01 | PROC-017 sin tests 5-etapas | Medio | Mapear a MiddlewarePipeline E2E o doc-only |
| GAP-02 | PROC-012 cobertura mínima | Medio | Tests rotación secretos, disable |
| GAP-03 | PROC-030–032 sin automatización | Bajo ops | Checklist manual pre-GO |
| GAP-04 | REQ-DYN-01 load | Alto perf | Ejecutar k6 LOAD-01 |
| GAP-05 | PROC-034 integración | Medio | Test espejo multi-instancia |

## 6. Fallos resueltos (2026-06-24)

| Proceso | Test ID | Corrección |
|---------|---------|------------|
| PROC-005 | TC-0070 | `PlatformDatabaseReadiness` — SQLite `:memory:` |
| PROC-011 | TC-0161 | Idem — resolución `tenant_id` en cola |

## 7. Referencias documentales BPMN

Cada fila CSV incluye columna `Documento_BPMN` apuntando a `docs/Diagrama_BPMN/NN_Proceso_*.md`.

Validación cruzada: [99_Validacion_Brechas.md](../Diagrama_BPMN/99_Validacion_Brechas.md), [Matriz_Trazabilidad_BPMN.md](../Diagrama_BPMN/Matriz_Trazabilidad_BPMN.md).

## 8. Regeneración

```bash
php docs/testing/tools/generate_strategic_matrices.php
php docs/testing/tools/export_test_matrix.php
```
