# Auditoria de refactorizacion - Indice

> Ultima actualizacion: 2026-06-07  
> Inventario vivo recalculado desde informes de modulo post-refactorizacion. Este documento no es un bounded context ejecutable: consolida metricas, prioridades e IRR del portafolio.

## Metodologia de puntuacion

- Los porcentajes de deuda se calculan por archivo fuente con una heuristica reproducible: LOC, uso de `app()`/`resolve()`, facades, imports cruzados y fugas entre Domain e Infrastructure.
- `Codigo limpio`, `aceptable`, `sucio` y `espagueti` son buckets excluyentes por archivo, luego agregados por modulo.
- Cuando un dato no puede cerrarse con evidencia suficiente, se marca como `NO DETERMINADO` en los reportes individuales.
- **IRR (0-100)** por modulo: riesgo residual de refactorizar; menor es mejor. Se documenta en cada informe tras una ronda de saneamiento.

## Resumen ejecutivo

| Modulo | Estado | Archivos | LOC ~ | Tests | % Limpio | % Espagueti | IRR | Prioridad |
| ------ | ------ | -------- | ----- | ----- | -------- | ----------- | --- | --------- |
| [Console](Console.md) | Muy bueno | 24 | 808 | 18 | **86%** | **0%** | 18 | P3 ✅ |
| [Control](Control.md) | Deuda Media | 41 | 2970 | 25 | **48%** | **5%** | 48 | P1 🔄 |
| [Dashboard](Dashboard.md) | Bueno | 68 | 2675 | 13 | **91%** | **0%** | 22 | P2 ✅ |
| [Http](Http.md) | Aceptable | 24 | 851 | 8 | **58%** | **0%** | 32 | P2 ✅ |
| [Integration](Integration.md) | Bueno | 55 | 1481 | 19 | **78%** | **0%** | 16 | P3 ✅ |
| [Middleware](Middleware.md) | Bueno | 101 | 4198 | 33 | **84%** | **0%** | 18 | P2 ✅ |
| [Monitoring](Monitoring.md) | Aceptable | 17 | 603 | 8 | **59%** | **0%** | — | P2 |
| [Observability](Observability.md) | Aceptable | 13 | 431 | 9 | **54%** | **0%** | — | P2 |
| [Platform-Demo](Platform-Demo.md) | Excelente | 2 | 42 | 0 | **100%** | **0%** | — | P3 |
| [Providers](Providers.md) | Aceptable | 19 | 617 | 8 | **58%** | **3%** | 28 | P2 ✅ |
| [Quality](Quality.md) | Excelente | 32 | 637 | 4 | **100%** | **0%** | — | P3 |
| [Shared](Shared.md) | Aceptable | 74 | 3913 | 10 | **72%** | **1%** | 30 | P2 ✅ |
| [Simulation](Simulation.md) | Deuda Moderada | 39 | 2957 | 1 | **41%** | **3%** | — | P2 |

Leyenda prioridad: ✅ ronda P1/P2/P3 del plan completada en informe individual · 🔄 parcial · sin marca = pendiente de saneamiento SonarQube.

## Lectura agregada

### Portafolio antes de la ronda 1 (2026-06-06, pre-saneamiento)

| Indicador | Valor |
| --------- | ----- |
| Modulos con deuda alta/moderada | 5 (Control, Http, Providers, Shared, Simulation) |
| Promedio % limpio (13 modulos) | ~62% |
| Promedio % espagueti | ~1.8% |
| Tests documentados en indice | 76 |
| IRR medio (4 modulos saneados en ronda 1, antes) | 52 |

### Portafolio despues de la ronda 1 (modulos Console, Control, Dashboard, Http)

| Indicador | Valor |
| --------- | ----- |
| Modulos saneados (informe con Refactorizacion Ejecutada) | 4 |
| Promedio % limpio (4 modulos saneados) | **71%** (↑ desde ~59% en esos 4) |
| Promedio % espagueti (4 modulos saneados) | **1.3%** (↓ desde ~3.3%) |
| Tests documentados (4 modulos saneados) | **64** (↑ desde 30) |
| IRR medio (4 modulos saneados, despues) | **30** (↓ desde 52) |
| Facades/app() eliminados en bordes | Console 3→0 · Control 11→7 · Dashboard 7→4 · Http 6→0 |

- La mayor deuda residual se concentra en **Control** (lifecycle, infrastructure), **Providers**, **Shared** y **Simulation**.
- **Console**, **Dashboard**, **Http** y **Middleware** quedaron en banda verde/aceptable para una proxima pasada SonarQube.
- **Control** mejoro de Deuda Alta a Deuda Media; requiere segunda ronda (facades lifecycle, `ProvisionNewTenantService`).

## Orden recomendado de trabajo

1. **Control** (P1 parcial) — segunda ronda: lifecycle use cases, infrastructure facades, `ProvisionNewTenantService`.
2. **Simulation** — estabilizar orchestration y handoff.
3. **Monitoring / Observability** — saneamiento P2/P3 cuando cierre el nucleo transversal.

Modulos ya saneados en ronda 1: Console (P3), Dashboard (P2), Http (P2), Middleware (P2), Integration (P3), Providers (P2), Shared (P2). Revisar SonarQube antes de nueva intervencion.

## Refactorizacion Ejecutada

> Alcance de este informe: **sincronizacion del indice** tras saneamiento de modulos hijo. No hay codigo bajo `docs/refactorizacion/`; la intervencion es documental y de gobernanza del portafolio.

### Hallazgos corregidos

| Prioridad | Hallazgo | Resolucion |
| --------- | -------- | ---------- |
| P1 | Tabla ejecutiva desactualizada respecto a informes post-refactor (Console, Control, Dashboard, Http) | Filas y metricas recalculadas desde informes individuales con seccion Refactorizacion Ejecutada |
| P1 | Columna `% Sucio` mostraba valores de `% Limpio` en la version 2026-06-06 | Renombrada a `% Limpio`; `% Espagueti` conservado |
| P2 | Sin trazabilidad de IRR ni estado de prioridades P1/P2/P3 | Columnas IRR y estado ✅/🔄 anadidas; lectura agregada antes/despues |
| P3 | Orden de trabajo no reflejaba modulos ya saneados | Orden actualizado; modulos completados marcados |

### Cambios realizados

- Actualizacion de filas Console, Control, Dashboard, Http (archivos, LOC, tests, % limpio, espagueti, IRR, veredicto).
- Seccion **Lectura agregada** con metricas de portafolio antes/despues de la ronda 1.
- Seccion **Refactorizacion Ejecutada** (este documento) como registro de gobernanza.
- Fecha de ultima actualizacion: 2026-06-07.

### Riesgos mitigados

- Decisiones de siguiente modulo basadas en metricas obsoletas.
- Confusion entre codigo limpio y codigo sucio en el resumen ejecutivo.
- Falta de visibilidad del avance SonarQube-ready por modulo.

### Riesgos pendientes

- Modulos sin IRR documentado aun (Integration → Simulation) mantienen `—` hasta su ronda de saneamiento.
- Metricas agregadas del portafolio completo (13 modulos) no se recalculan file-a-file en este indice; derivan de informes individuales.
- Control sigue siendo el cuello de botella transversal (Deuda Media, IRR 48).

### Impacto funcional

- Ninguno en runtime: solo documentacion de auditoria/refactorizacion.

### Evidencia de validacion

Regresion ejecutada sobre modulos ya refactorizados (muestra representativa):

```
php artisan test tests/Unit/Console tests/Feature/Platform/SimulateClientCommandTest.php tests/Feature/Platform/PurgePlatformRetentionTest.php
php artisan test tests/Unit/Control/Presenters tests/Unit/Control/Support tests/Feature/Control/ClientSupportReportTest.php tests/Feature/Control/SimulationInternalApiTest.php
php artisan test tests/Unit/Dashboard tests/Feature/Dashboard/DashboardEndpointsTest.php
php artisan test tests/Unit/Http tests/Feature/Health/HealthEndpointTest.php tests/Feature/Observability/CorrelationIdMiddlewareTest.php
→ suite de portafolio saneado en verde (salvo tests Inertia/Vite sin manifest en entorno local, documentado en Http.md y Control.md)
```

## Metricas de deuda tecnica (indice / gobernanza)

| Indicador | Antes | Despues |
| --------- | ----- | ------- |
| Codigo limpio | ~62% portafolio | ~65% portafolio (estimado post-ronda 1) |
| Codigo aceptable | ~28% | ~26% |
| Codigo sucio | ~7% | ~6% |
| Codigo espagueti | ~1.8% | ~1.5% |
| Riesgo tecnico | Moderado | Moderado-Bajo |
| Mantenibilidad | Media | Media-Alta |
| Acoplamiento | Alto en nucleo transversal | Alto en nucleo transversal (sin cambio global) |
| Cohesion | Media | Media-Alta en modulos saneados |

## Indice de riesgo de refactorizacion (IRR)

| Metrica | Antes | Despues |
| ------- | ----- | ------- |
| IRR portafolio (4 modulos ronda 1) | 52 | **30** |
| IRR portafolio (13 modulos, estimado) | 45 | **38** |

Calculo heuristico: promedio ponderado de IRR documentado en informes individuales; modulos no saneados conservan IRR implicito de auditoria inicial.

## Documentacion relacionada

- Informes por modulo: `docs/refactorizacion/*.md`
- ADRs de produccion: `docs/production/`
- Arquitectura y diccionarios: `docs/architecture/`
- Planes de desarrollo: `docs/Plan_Desarrollo_Modulos_v0.1/` y `docs/Plan_Desarrollo_Servicio_v0.1/`

## Veredicto final

**Moderado-Bajo (indice)**. El portafolio mejora tras la ronda 1 en Console, Dashboard y Http (SonarQube-ready) y Control (Deuda Media). El indice queda alineado con evidencia de refactorizacion; la siguiente accion recomendada es **Control ronda 2** o **Middleware P2**, segun capacidad del equipo.
