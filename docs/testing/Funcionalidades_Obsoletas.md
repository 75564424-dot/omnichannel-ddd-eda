# Funcionalidades obsoletas — historial de retiro

**Versión:** v1.8 | **Fecha:** 2026-06-27 | **CSV:** [Funcionalidades_Obsoletas.csv](./Funcionalidades_Obsoletas.csv)

---

## 1. Objetivo

Conservar **historial** de funcionalidades, documentos y métricas de testing retiradas o diferidas, para evitar reintroducir referencias incorrectas en QA y arquitectura.

## 2. Alcance

Elementos eliminados o obsoletos desde la auditoría **2026-05-03** y la línea base documental **2026-05-22**, contrastados con la suite actual (**363 PHPUnit**, junio 2026).

---

## 3. Dominios retail omnicanal en core (retirados)

### Inventario y Pedidos

| Aspecto | Detalle |
|---------|---------|
| **Estado** | Retirado del core Laravel |
| **Fecha retiro código/tests** | 2026-05-03 |
| **Motivo** | ADR-001: middleware agnóstico; dominios retail son contextos del **integrador externo** |
| **Tests eliminados** | Clases `Inventario.*`, `Pedido.*`, eventos `Inventario.Events.*`, `Pedido.Events.*` |
| **Reemplazo** | Fixtures `Platform.*` en simulación; catálogo declarativo JSON por tenant |
| **Evidencia** | [00_Mapa_Procesos.md](../Diagrama_BPMN/00_Mapa_Procesos.md) — "no implementa dominios retail" |
| **ID auditoría** | OBS-01, OBS-02, AUD-09 |

El generador `generate_test_catalogs.php` aún mapea paths legacy a labels `Inventario`/`Pedidos` solo como **convención de clasificación** en catálogos; no implica código de dominio en runtime.

---

## 4. Documentos de catálogo legacy (retirados 2026-05-03)

| ID | Documento | Volumen aprox. | Sustituto |
|----|-----------|----------------|-----------|
| OBS-03 | `catalog_unit.md` | Alto | `unit_catalogo_autogenerado.md` |
| OBS-04 | `catalog_integration.md` | Alto | `integration_catalogo_autogenerado.md` |
| OBS-05 | `catalog_feature.md` | Alto | `feature_catalogo_autogenerado.md` |
| OBS-06 | `catalog_e2e.md` | Medio | `e2e_catalogo_autogenerado.md` |
| OBS-07 | `architecture_validation_matrix.md` | Medio | `matrix_validacion_middleware.md` |

**Problema:** referencias a flujos omnicanal retail y plantillas desactualizadas; conteo **160 tests** congelado en mayo 2026.

---

## 5. Multi-tenancy lógico Fase 3 (diferido)

| Aspecto | Detalle |
|---------|---------|
| **Proceso BPMN** | PROC-018 — [27_Proceso_Multi_Tenancy_Logico_Fase3.md](../Diagrama_BPMN/27_Proceso_Multi_Tenancy_Logico_Fase3.md) |
| **Estado** | **Diferido** — no implementado en core |
| **Modelo vigente** | Silo Laravel dedicado por cliente (`:8001+`), control plane `:8000` |
| **Tests relacionados** | `InstanceDeploymentServiceTest::cross_tenant_portal_allowed_when_multi_tenant_flag_enabled` valida **flag** de portal, no tenancy lógico en BD compartida |
| **ID** | OBS-08 |

---

## 6. Métrica obsoleta: 160 tests (2026-05-22)

| Métrica histórica | Valor actual | Fuente |
|-------------------|--------------|--------|
| 160 métodos | 362 métodos / 363 PHPUnit | `README.md`, `matriz_maestra_casos.csv` |
| Sin fallos documentados | Suite en verde | 364/364 PASÓ (2026-06-24) |

Incremento principal: módulos Control (+65 casos), Dashboard (+71), Platform (+59), API v1, Security/Identity.

---

## 7. Flujos E2E omnicanal legacy

| Flujo | Estado | Reemplazo |
|-------|--------|-----------|
| InboundOrder / Stock sync retail | Retirado | `ClientProductionLikeSimulationTest` multi-evento |
| Catálogos `catalog_e2e.md` por dominio | Retirado | `e2e_simulacion_cliente.md` + E2E auto-generado |

ID: OBS-10.

---

## 8. Qué NO reintroducir

1. Tests de dominio Inventario/Pedidos en `tests/Feature` del core.
2. Documentos `catalog_*.md` manuales paralelos a auto-generados.
3. Asunción de multi-tenant lógico en SQLite compartido (PROC-018).
4. Métrica "160 tests" en README o matrices sin fecha de actualización.

---

## 9. Historial de decisiones

| Fecha | Decisión | Documento |
|-------|----------|-----------|
| 2026-05-03 | Eliminar catálogos legacy y fusionar auditoría fases | `audit_suite_redundancia.md` |
| 2026-05-22 | Última snapshot catálogos auto-gen antes evolución Control | `*_catalogo_autogenerado.md` |
| 2026-06-27 | Documentación modular por bounded context + matrices estratégicas | Este documento + CSV |

---

## 10. CSV completo

Ver [Funcionalidades_Obsoletas.csv](./Funcionalidades_Obsoletas.csv) — 10 filas OBS-01…OBS-10 con columnas: ID, Funcionalidad, Tipo, Estado, Fecha_Retiro, Reemplazo, Evidencia, Documento_Historico, Observaciones.
