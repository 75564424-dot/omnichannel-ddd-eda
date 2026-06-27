# Diagrama BPMN — Índice de documentación

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Alcance:** Procesos operativos, macroprocesos y artefactos de trazabilidad de la plataforma omnichannel DDD + EDA.

**Evidencia base:** `docs/Patente/matriz_generada/procesos.csv`, `docs/Diagrama_BPMN/00_Mapa_Procesos.md`.

---

## Cómo usar este índice

| Necesidad | Documento |
|-----------|-----------|
| Visión global y jerarquía | [00_Mapa_Procesos.md](00_Mapa_Procesos.md) |
| Trazabilidad REQ/ADR/API | [Matriz_Trazabilidad_BPMN.md](Matriz_Trazabilidad_BPMN.md) |
| Brechas y funcionalidad no cubierta | [99_Validacion_Brechas.md](99_Validacion_Brechas.md) |
| Ficha de un proceso | Tabla §Procesos abajo |
| Macroproceso de negocio | Tabla §Macroprocesos |

**Convención nombres:** `{NN}_Proceso_{Nombre}.md` — NN correlativo; PROC-{ID} en encabezado del documento.

**Plantilla completa por ficha:** Descripción, Objetivo, Alcance, Actores, Entradas, Salidas, Reglas de negocio, Precondiciones, Postcondiciones, Flujo principal, Flujos alternativos, Excepciones, Eventos, Dependencias, Riesgos, Indicadores, Relación con otros procesos, Componentes, Documentación relacionada, Trazabilidad, Mermaid, BPMN Mapping.

---

## Macroprocesos (MP-01 → MP-09)

| ID | Documento | Procesos hijos principales |
|----|-----------|----------------------------|
| MP-01 | [01_Macroproceso_Gestion_Plataforma_SaaS.md](01_Macroproceso_Gestion_Plataforma_SaaS.md) | PROC-007, 008, 010, 015, 020, 034 |
| MP-02 | [02_Macroproceso_Operacion_Middleware_Eventos.md](02_Macroproceso_Operacion_Middleware_Eventos.md) | PROC-001, 002, 003, 017 |
| MP-03 | [03_Macroproceso_Observabilidad_Monitoreo.md](03_Macroproceso_Observabilidad_Monitoreo.md) | PROC-004, 013 |
| MP-04 | [04_Macroproceso_Seguridad_Acceso.md](04_Macroproceso_Seguridad_Acceso.md) | PROC-005, 006 |
| MP-05 | [05_Macroproceso_Calidad_Validacion.md](05_Macroproceso_Calidad_Validacion.md) | PROC-009, 016, 033 |
| MP-06 | [06_Macroproceso_Operaciones_Infraestructura.md](06_Macroproceso_Operaciones_Infraestructura.md) | PROC-014, 030, 031, 032 |
| MP-07 | [07_Macroproceso_Gobernanza_Evolucion.md](07_Macroproceso_Gobernanza_Evolucion.md) | PROC-033, 018 |
| MP-08 | [08_Macroproceso_Integracion_Omnicanal.md](08_Macroproceso_Integracion_Omnicanal.md) | PROC-011, 012, 017 |
| MP-09 | [09_Macroproceso_Portal_Cliente.md](09_Macroproceso_Portal_Cliente.md) | PROC-019 |

---

## Procesos operativos (PROC-001 → PROC-034)

| PROC | Nombre | Estado | Documento |
|------|--------|--------|-----------|
| PROC-001 | Publicación eventos al bus | Implementado | [10_Proceso_Publicacion_Eventos_Bus.md](10_Proceso_Publicacion_Eventos_Bus.md) |
| PROC-002 | Sincronización catálogo → registry | Implementado | [11_Proceso_Sincronizacion_Catalogo_Registry.md](11_Proceso_Sincronizacion_Catalogo_Registry.md) |
| PROC-003 | Consulta operativa del bus | Implementado | [12_Proceso_Consulta_Operativa_Bus.md](12_Proceso_Consulta_Operativa_Bus.md) |
| PROC-004 | Observabilidad dashboard | Implementado | [13_Proceso_Observabilidad_Dashboard.md](13_Proceso_Observabilidad_Dashboard.md) |
| PROC-005 | Autenticación operadores web | Implementado | [14_Proceso_Autenticacion_Operadores_Web.md](14_Proceso_Autenticacion_Operadores_Web.md) |
| PROC-006 | Autenticación API integradores | Implementado | [15_Proceso_Autenticacion_API_Integradores.md](15_Proceso_Autenticacion_API_Integradores.md) |
| PROC-007 | Gestión empresas control plane | Implementado | [16_Proceso_Gestion_Empresas_Control_Plane.md](16_Proceso_Gestion_Empresas_Control_Plane.md) |
| PROC-008 | Provisioning nueva instancia | Parcial | [17_Proceso_Provisioning_Nueva_Instancia.md](17_Proceso_Provisioning_Nueva_Instancia.md) |
| PROC-009 | Simulación cliente E2E | Implementado | [18_Proceso_Simulacion_Cliente_E2E.md](18_Proceso_Simulacion_Cliente_E2E.md) |
| PROC-010 | Onboarding instancia por cliente | Implementado | [19_Proceso_Onboarding_Instancia_Cliente.md](19_Proceso_Onboarding_Instancia_Cliente.md) |
| PROC-011 | Ingress webhooks integraciones | Parcial | [20_Proceso_Ingress_Webhooks_Integraciones.md](20_Proceso_Ingress_Webhooks_Integraciones.md) |
| PROC-012 | Gestión canales e integraciones | Implementado | [21_Proceso_Gestion_Canales_Integraciones.md](21_Proceso_Gestion_Canales_Integraciones.md) |
| PROC-013 | Monitoreo y alertas plataforma | Implementado | [22_Proceso_Monitoreo_Alertas_Plataforma.md](22_Proceso_Monitoreo_Alertas_Plataforma.md) |
| PROC-014 | Retención y purga datos | Implementado | [23_Proceso_Retencion_Purga_Datos.md](23_Proceso_Retencion_Purga_Datos.md) |
| PROC-015 | Gestión incidentes soporte | Implementado | [24_Proceso_Gestion_Incidentes_Soporte.md](24_Proceso_Gestion_Incidentes_Soporte.md) |
| PROC-016 | Validación catálogo CI | Implementado | [25_Proceso_Validacion_Catalogo_CI.md](25_Proceso_Validacion_Catalogo_CI.md) |
| PROC-017 | Flujo middleware 5 etapas (doc) | Documental | [26_Proceso_Flujo_Middleware_5_Etapas.md](26_Proceso_Flujo_Middleware_5_Etapas.md) |
| PROC-018 | Multi-tenancy lógico Fase 3 | Diferido | [27_Proceso_Multi_Tenancy_Logico_Fase3.md](27_Proceso_Multi_Tenancy_Logico_Fase3.md) |
| PROC-019 | Portal instancia cliente web | Implementado | [28_Proceso_Portal_Instancia_Cliente.md](28_Proceso_Portal_Instancia_Cliente.md) |
| PROC-020 | Simulación desde control plane | Implementado | [29_Proceso_Simulacion_Desde_Control_Plane.md](29_Proceso_Simulacion_Desde_Control_Plane.md) |
| PROC-030 | Despliegue producción VM | Documentado | [30_Proceso_Despliegue_Produccion_VM.md](30_Proceso_Despliegue_Produccion_VM.md) |
| PROC-031 | Backup y restauración | Documentado | [31_Proceso_Backup_Restauracion.md](31_Proceso_Backup_Restauracion.md) |
| PROC-032 | DR Drill | Documentado | [32_Proceso_DR_Drill.md](32_Proceso_DR_Drill.md) |
| PROC-033 | Evaluación aceptación middleware | Documentado | [33_Proceso_Evaluacion_Aceptacion_Middleware.md](33_Proceso_Evaluacion_Aceptacion_Middleware.md) |
| PROC-034 | Espejo catálogo CP→Silo | Implementado | [34_Proceso_Espejo_Catalogo_CP_Silo.md](34_Proceso_Espejo_Catalogo_CP_Silo.md) |

---

## Documentos transversales

| Documento | Propósito |
|-----------|-----------|
| [00_Mapa_Procesos.md](00_Mapa_Procesos.md) | Mapa general, catálogo, dependencias, actores |
| [Matriz_Trazabilidad_BPMN.md](Matriz_Trazabilidad_BPMN.md) | Trazabilidad proceso ↔ DDD ↔ API ↔ evaluación |
| [99_Validacion_Brechas.md](99_Validacion_Brechas.md) | Brechas, requisitos sin proceso, PMV pendientes |
| [README.md](README.md) | Este índice |

---

## Orden de lectura sugerido

### Flujo operativo certificado (onboarding → simulación)

1. [17_Proceso_Provisioning_Nueva_Instancia.md](17_Proceso_Provisioning_Nueva_Instancia.md) — PROC-008  
2. [34_Proceso_Espejo_Catalogo_CP_Silo.md](34_Proceso_Espejo_Catalogo_CP_Silo.md) — PROC-034  
3. [19_Proceso_Onboarding_Instancia_Cliente.md](19_Proceso_Onboarding_Instancia_Cliente.md) — PROC-010  
4. [11_Proceso_Sincronizacion_Catalogo_Registry.md](11_Proceso_Sincronizacion_Catalogo_Registry.md) — PROC-002  
5. [25_Proceso_Validacion_Catalogo_CI.md](25_Proceso_Validacion_Catalogo_CI.md) — PROC-016  
6. [18_Proceso_Simulacion_Cliente_E2E.md](18_Proceso_Simulacion_Cliente_E2E.md) — PROC-009  
7. [10_Proceso_Publicacion_Eventos_Bus.md](10_Proceso_Publicacion_Eventos_Bus.md) — PROC-001  
8. [13_Proceso_Observabilidad_Dashboard.md](13_Proceso_Observabilidad_Dashboard.md) — PROC-004  

### Middleware e integraciones

- [12_Proceso_Consulta_Operativa_Bus.md](12_Proceso_Consulta_Operativa_Bus.md) — PROC-003  
- [20_Proceso_Ingress_Webhooks_Integraciones.md](20_Proceso_Ingress_Webhooks_Integraciones.md) — PROC-011  
- [21_Proceso_Gestion_Canales_Integraciones.md](21_Proceso_Gestion_Canales_Integraciones.md) — PROC-012  
- [26_Proceso_Flujo_Middleware_5_Etapas.md](26_Proceso_Flujo_Middleware_5_Etapas.md) — PROC-017 (documental)

### Operaciones e infraestructura

- [22_Proceso_Monitoreo_Alertas_Plataforma.md](22_Proceso_Monitoreo_Alertas_Plataforma.md) — PROC-013  
- [23_Proceso_Retencion_Purga_Datos.md](23_Proceso_Retencion_Purga_Datos.md) — PROC-014  
- [30_Proceso_Despliegue_Produccion_VM.md](30_Proceso_Despliegue_Produccion_VM.md) — PROC-030  
- [31_Proceso_Backup_Restauracion.md](31_Proceso_Backup_Restauracion.md) — PROC-031  
- [32_Proceso_DR_Drill.md](32_Proceso_DR_Drill.md) — PROC-032  

---

## Fuentes matriz patent

| Archivo | Contenido |
|---------|-----------|
| [procesos.csv](../Patente/matriz_generada/procesos.csv) | Catálogo PROC-001–020 |
| [actividades_bpmn.csv](../Patente/matriz_generada/actividades_bpmn.csv) | ACT-001–032 |
| [flujo_bpmn.csv](../Patente/matriz_generada/flujo_bpmn.csv) | FLU-001–031 |
| [requerimientos.csv](../Patente/matriz_generada/requerimientos.csv) | REQ-C*, REQ-O*, REQ-ADR*, etc. |
| [dependencias.csv](../Patente/matriz_generada/dependencias.csv) | DEP-001–030 |
| [artefactos.csv](../Patente/matriz_generada/artefactos.csv) | ADRs, runbooks, planes |
| [pmv.csv](../Patente/matriz_generada/pmv.csv) | Módulos producto |
| [reporte_generacion.md](../Patente/matriz_generada/reporte_generacion.md) | Metodología y riesgos |

---

## Evaluación y gobernanza

- [33_Proceso_Evaluacion_Aceptacion_Middleware.md](33_Proceso_Evaluacion_Aceptacion_Middleware.md) — PROC-033  
- [docs/evaluation/README_Evaluacion.md](../evaluation/README_Evaluacion.md) — Framework 8 dominios  

---

## Estadísticas carpeta

| Métrica | Valor |
|---------|-------|
| Macroprocesos | 9 |
| Procesos fichados | 25 |
| Documentos transversales | 4 |
| **Total archivos .md** | **38** |

---

*Índice generado 2026-06-27 — alineado con matriz_generada y 00_Mapa_Procesos.md*
