# Validación de brechas — Diagrama BPMN

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Propósito:** Consolidar brechas entre documentación BPMN, matriz de patent (`docs/Patente/matriz_generada/`) e implementación real. Solo evidencia registrada — sin procesos inventados.

**Fuentes primarias:** `procesos.csv`, `requerimientos.csv`, `actividades_bpmn.csv`, `pmv.csv`, `reporte_generacion.md`, `historial.csv`, `00_Mapa_Procesos.md`.

---

## Resumen ejecutivo

| Categoría | Cantidad | Severidad predominante |
|-----------|----------|------------------------|
| Procesos documentales / diferidos | 2 | Media (PROC-017, PROC-018) |
| Procesos implementados parcialmente | 3 | Alta (PROC-008, PROC-011, PMV-004) |
| Requisitos sin proceso operativo | 4+ | Alta |
| ADRs / capacidades diferidas | 6+ | Media |
| Artefactos / trazabilidad faltantes | 3 | Baja–Media |
| Módulos catálogo sin implementación | 3 | Media |

---

## 1. Brechas por proceso BPMN

### 1.1 Procesos con estado no completo

| ID | Proceso | Estado matriz | Brecha | Evidencia |
|----|---------|---------------|--------|-----------|
| PROC-008 | Provisioning nueva instancia | implementado parcial | LocalFleet/ProvisionNewTenant NO_EVIDENCIADO; orden ACT-019→ACT-020 PENDIENTE_VALIDACION | procesos.csv; flujo_bpmn FLU-018; reporte_generacion datos faltantes |
| PROC-011 | Ingress webhooks | implementado parcial | Tablas webhooks existen; cobertura producción parcial | procesos.csv; pmv PMV-004 |
| PROC-017 | Flujo middleware 5 etapas | documentado no implementado completo | Pipeline doc 5 etapas vs impl 2 etapas | procesos.csv; REQ-FLOW-01; reporte_generacion R2 |
| PROC-018 | Multi-tenancy lógico Fase 3 | diferido | ADR-001 Fase 3 no implementada | procesos.csv; REQ-MT-01 |

### 1.2 Procesos runbook (PROC-030–032)

| ID | Proceso | Estado | Brecha |
|----|---------|--------|--------|
| PROC-030 | Deploy VM | Documentado | No automatizado CI/CD completo; K8s fuera runbook VM |
| PROC-031 | Backup/restore | Documentado | K8s cronjob PENDIENTE_VALIDACION workspace |
| PROC-032 | DR Drill | Documentado | Sin tags git releases; escenario C requiere K8s enterprise |

### 1.3 Procesos sin brecha crítica documentada

PROC-001, 002, 003, 004, 005, 006, 007, 009, 010, 012, 013, 014, 015, 016, 019, 020, 034 — estado implementado según `procesos.csv` (salvo observaciones menores en notas).

---

## 2. Brechas por requisito (REQ)

| Requisito | Descripción | Estado | Brecha | Proceso relacionado |
|-----------|-------------|--------|--------|---------------------|
| REQ-DYN-01 | Config dinámica sin redeploy | no cumple | **Sin proceso operativo** | — |
| REQ-FLOW-01 | Pipeline 5 etapas | documentado parcial | PROC-017 gap | PROC-017 |
| REQ-MT-01 | Multi-tenant lógico Fase 3 | diferido | PROC-018 no implementado | PROC-018 |
| REQ-O5 | SSE feed en vivo | PENDIENTE_VALIDACION | UI SSE verificar | PROC-004 |
| REQ-C4 | Registry declarativo | implementado parcial | Divergencia eventbus vs JSON | PROC-002, PROC-016 |
| REQ-O4 | Topología observada | implementado parcial | Plan §1.1 parcial | PROC-004 |
| REQ-INT-01 | Integraciones | implementado parcial | PMV-004 | PROC-011, PROC-012 |
| REQ-QA-01 | Cobertura ≥70% | PENDIENTE_VALIDACION | CI coverage script existe | PROC-033 |
| REQ-SEC-03 | Headers seguridad | PENDIENTE_VALIDACION | Middleware existe | PROC-005, 006 |
| REQ-API-02 | Idempotency-Key | PENDIENTE_VALIDACION | Config menciona | PROC-001 |

---

## 3. Brechas por actividad BPMN

| Actividad | Proceso | Estado | Brecha |
|-----------|---------|--------|--------|
| ACT-028 | PROC-017 | NO_EVIDENCIADO en código | Tópicos retail Inventario/Pedido no en core |
| ACT-027 | PROC-017 | documentado no completo | Transform/enriquecimiento no en core |
| ACT-029 | PROC-005 | diferido | OAuth2 enterprise ADR-002 |
| ACT-019 | PROC-008 | implementado parcial | Provisioning CP incompleto fleet |
| ACT-021→005 secuencia | PROC-009 | PENDIENTE_VALIDACION | Secuencia interna simulate-client |

---

## 4. Brechas por PMV (módulos producto)

| PMV | Módulo | Estado | Brecha |
|-----|--------|--------|--------|
| PMV-004 | integrations | implementado parcial | Tablas sin uso completo Plan §2.4 |
| PMV-005 | analytics | parcial | Sin UI dedicada NO_EVIDENCIADO |
| PMV-006 | security_audit | parcial | Panel audit NO_EVIDENCIADO |
| PMV-007 | multi_channel | NO_EVIDENCIADO | Solo catálogo saas_catalog |
| PMV-002 | dashboard | implementado | O5 SSE PENDIENTE_VALIDACION |
| PMV-012 | config_dinamica_runtime | pendiente | REQ-DYN-01 No cumple |

---

## 5. ADRs y capacidades diferidas (sin proceso propio)

| ADR / Tema | Estado | Brecha | Gateway/Nota |
|------------|--------|--------|--------------|
| ADR-002 OAuth2/IdP | Propuesto/diferido | ACT-029 PROC-005 | Fase 3 enterprise |
| ADR-003 SSO/LDAP | Propuesto/diferido | PROC-005, PROC-007 | — |
| ADR-005 Particionamiento event_store | Propuesto POC | PROC-014 retención only | — |
| ADR-006 Sagas compensación | Propuesto/diferido | **Sin proceso BPMN** | — |
| ADR-007 Workflow Temporal/Camunda | parcial | In-process only | PROC-001 parcial |
| Dominios retail (Inventario, Pedidos…) | Documental externo | Consumidores externos | PROC-017 referencia |
| WebSockets tiempo real | No implementado | SSE cubre contrato | Certificación limitaciones |
| Config dinámica runtime | No cumple | **Sin proceso operativo** | REQ-DYN-01 |

---

## 6. Brechas infraestructura y trazabilidad

| Elemento | Estado | Evidencia |
|----------|--------|-----------|
| Archivo BPMN `.bpmn` formal | NO_EVIDENCIADO | reporte_generacion datos faltantes |
| `docs/matriz de control de versiones/` CSV | Archivo ausente | historial HIST-024 |
| Tags git releases | NO_EVIDENCIADO | reporte_generacion; git tag -l vacío |
| Deploy Grafana/Prometheus activo | NO_EVIDENCIADO código | DEP-026 dependencias.csv |
| Ramas feature merge destino | PENDIENTE_VALIDACION | merges.csv baja confianza |
| LocalFleet / ProvisionNewTenant | NO_EVIDENCIADO commit | reporte_generacion |

---

## 7. Funcionalidad documentada sin proceso BPMN dedicado

| Funcionalidad | Documentación | Brecha |
|---------------|---------------|--------|
| Sagas y compensación | ADR-006 | Sin PROC-* |
| Config dinámica productor/consumidor | Plan_de_implementacion §1.1 | Sin PROC-* |
| OAuth2/SSO enterprise | ADR-002, ADR-003 | Gateway en PROC-005 only |
| Módulo analytics UI | saas_catalog PMV-005 | Sin PROC-* |
| Módulo multi_channel | PMV-007 | Sin PROC-* |
| Security audit panel | PMV-006 | Sin PROC-* |
| Dominios retail BC internos | Flujo_Middleware, mockups obsoletos | PROC-017 documental only |

---

## 8. Riesgos consolidados (reporte_generacion)

1. Divergencia `eventbus.php` vs `modules_config.json` — mitigado parcial PROC-002, PROC-016.
2. Documentación 5 etapas vs 2 etapas — PROC-017.
3. Legacy retail docs vs core agnóstico — interpretación incorrecta.
4. Config dinámica runtime No cumple — REQ-DYN-01.
5. Datos legacy silos contaminados — commit HIST-010.
6. Seguridad cloud producción — ADR-002/003 diferidos.
7. Historial Git limitado (37 commits) — trazabilidad doc.
8. Sin tags release — control versiones formal.

---

## 9. Cobertura documental BPMN

| Área | Procesos | Cobertura doc BPMN |
|------|----------|-------------------|
| matriz_generada PROC-001–020 | 20 | Alta — fichas 10–29 (excepto gaps) |
| PROC-030–033 | 4 | Alta — runbooks + evaluation |
| Macroprocesos MP-01–09 | 9 | Alta — 01–09 |
| PROC-034 | 1 | Alta — 34 |
| Validación brechas | 1 | Este documento |
| README índice | 1 | README.md |

**Total fichas proceso:** 25 (PROC-001–020, 030–033, 034) + 9 macro + mapa + matriz + brechas + README.

---

## 10. Acciones recomendadas (derivadas matrices, no ejecutadas)

| Prioridad | Acción | Matriz / Proceso |
|-----------|--------|------------------|
| P0 | Mantener PROC-016 validate-catalog en CI | REQ-VAL-01 |
| P1 | Cerrar gap REQ-FLOW-01 o reclasificar PROC-017 como referencia only | 11_Matriz_Evolucion |
| P1 | Completar PROC-011 cobertura producción webhooks | PMV-004 |
| P2 | Recuperar CSV control versiones BPMN (HIST-024) | Gobernanza |
| P2 | Ejecutar PROC-032 DR drill trimestral | Operación |
| P3 | Decidir ADR Fase 3 multi-tenant vs mantener instance_per_client | PROC-018 |
| P3 | Implementar REQ-DYN-01 o documentar explícitamente out-of-scope | Producto |

---

## Trazabilidad

| Elemento | Evidencia |
|----------|-----------|
| Brechas procesos | `docs/Patente/matriz_generada/procesos.csv` |
| Brechas requisitos | `docs/Patente/matriz_generada/requerimientos.csv` |
| Brechas PMV | `docs/Patente/matriz_generada/pmv.csv` |
| Riesgos | `docs/Patente/matriz_generada/reporte_generacion.md` §Riesgos |
| HIST-024 | `docs/Patente/matriz_generada/historial.csv` |
| Mapa brechas | `docs/Diagrama_BPMN/00_Mapa_Procesos.md` §Brechas |

---

*Fin del documento 99_Validacion_Brechas*
