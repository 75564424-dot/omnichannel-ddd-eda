# Informe de Cumplimiento — [FASE 4] Implementación Frontend
## Runbook v1.5 — Gestión del Ciclo de Vida Operativo de Tenants

**Fecha de finalización:** 2026-05-30
**Ejecutado por:** Arquitecto de Software Senior / Tech Lead / Revisor Técnico
**Fuente de verdad:** Repositorio `omnichannel-ddd-eda` + Runbook_v1.5_Gestion_Ciclo_Vida_Tenants.md + Informe Fase 3
**Estado del informe:** ✅ **FASE 4 COMPLETADA SATISFACTORIAMENTE**

---

## 1. Resumen Ejecutivo (Qué se hizo)

Se completó al 100% la **Fase 4: Implementación Frontend** del Runbook v1.5, entregando todos los componentes visuales y de interacción definidos en la especificación.

### Análisis previo a implementación (Paso 1 y 2)

Antes de escribir cualquier código se realizó:

1. **Lectura completa del Runbook v1.5** — sección §5 Fase 4 y matriz UX de §3.3.
2. **Verificación de dependencias de Fase 3** — confirmado que `tenant.lifecycle`, `tenant.status` y `tenant.actions_available` son expuestos por `TenantPresentationService::toSummary()` vía `TenantLifecyclePolicy::inferLifecycle()` y `TenantLifecycleOrchestrator::lifecycleStatus()`.
3. **Verificación de rutas backend** — confirmado que `lifecycle.start`, `lifecycle.suspend` y `lifecycle.restore` están registradas en `routes/control.php`.
4. **Hallazgo crítico identificado** — el middleware `EnsureTenantOperationalStatus` de Fase 3 renderiza una página HTML nativa (no Inertia) para silos suspendidos. El componente `Tenant/Suspended.vue` se crea de todas formas según lo exige el Runbook §3.2, y queda preparado para ser activado en Fase 5 (Integración).
5. **Confirmación de alcance** — nada de la Fase 4 requería modificar backend, middleware, rutas ni servicios existentes; solo componentes Vue.

### Entregables completados

1. **`Companies/Show.vue` actualizado** — reemplazados los dos botones legacy (`Suspender` / `Activar`) por tres acciones contextuales (`Levantar servicio`, `Suspender servicio`, `Restaurar servicio`) gobernadas por `tenant.actions_available`.
2. **Modales de confirmación** — implementados para las acciones destructivas (Suspender) y de restauración (Restaurar), con texto informativo claro para el operador SaaS.
3. **Badge de estado lifecycle** — indicador visual reactivo en la cabecera de `Show.vue` que refleja `running / suspended / provisioned / stopped`.
4. **`Companies/Index.vue` actualizado** — columna "Estado" reemplazada por badge de lifecycle coloreado, consumiendo el campo `lifecycle` ya presente en el listado de tenants.
5. **`Tenant/Suspended.vue` creado** — página Inertia para silos suspendidos, con mensaje institucional del Runbook §3.2 y diseño coherente con el sistema de diseño del proyecto.

---

## 2. Archivos Modificados

| # | Archivo | Tipo de cambio | Descripción |
|---|---------|---------------|-------------|
| 1 | `resources/js/Pages/Control/Companies/Show.vue` | Modificado | Reemplazo completo del bloque de acciones: dos botones legacy → tres acciones contextuales + modal + badge lifecycle. Scripts y estilos actualizados en consecuencia. |
| 2 | `resources/js/Pages/Control/Companies/Index.vue` | Modificado | Columna "Estado" convertida en badge lifecycle coloreado. Añadidas funciones helper `lifecycleBadgeLabel()` y `lifecycleBadgeClass()`. |

---

## 3. Archivos Nuevos

| # | Archivo | Descripción |
|---|---------|-------------|
| 1 | `resources/js/Pages/Tenant/Suspended.vue` | Página Inertia de suspensión de silo cliente. Muestra el mensaje institucional requerido por §3.2 del Runbook, diseñada sin formulario de login. Compatible con el sistema de diseño oscuro del proyecto. |

---

## 4. Decisiones Técnicas Tomadas

### D1: Gobernar visibilidad de acciones exclusivamente vía `tenant.actions_available`
El array `actions_available` ya es computado por el backend (`TenantLifecycleOrchestrator::lifecycleStatus`) según la `TenantLifecyclePolicy`. El frontend no reimplementa reglas de transición — solo consume el array. Esto garantiza que la lógica de negocio vive exclusivamente en el Domain (Fase 3) y el frontend actúa como capa de presentación pura.

### D2: Modal de confirmación inline con Teleport
Se implementó el modal con `<Teleport to="body">` para evitar problemas de z-index y stacking context dentro del layout. No se introdujo una librería modal externa — implementación mínima con estado reactivo local.

### D3: Rutas `lifecycle/suspend` y `lifecycle/restore` (no legacy)
Las nuevas acciones del frontend llaman a las rutas `/lifecycle/suspend` y `/lifecycle/restore` (Fase 3), no a las rutas legacy `/suspend` y `/activate`. Las rutas legacy se conservan en el backend para retrocompatibilidad (Fase 3 no las eliminó).

### D4: `Tenant/Suspended.vue` como componente Inertia independiente
El componente se ubica en `resources/js/Pages/Tenant/Suspended.vue` (directorio nuevo `Tenant/`), siguiendo la convención de Inertia donde el path del archivo refleja el nombre de la vista. Recibe `tenant_name` como prop opcional. No incluye layout ni nav — es una página de bloqueo completa.

### D5: Badge de lifecycle sin lógica de negocio
Las funciones `lifecycleBadgeLabel` y `lifecycleBadgeClass` en ambos componentes son mapeos puramente visuales. No toman decisiones sobre qué acciones mostrar — eso lo hace el backend.

---

## 5. Riesgos Introducidos

| # | Riesgo | Probabilidad | Impacto | Mitigación |
|---|--------|--------------|---------|------------|
| 1 | `tenant.actions_available` ausente en tenants pre-v1.5 sin lifecycle | Baja | Bajo | El operador `?.includes()` en todos los `v-if` previene errores JS. Si el array es `null/undefined`, ningún botón se muestra. |
| 2 | `tenant.lifecycle` ausente en el listado de Index | Baja | Bajo | `lifecycleBadgeLabel` tiene fallback `tenant.status ?? tenant.lifecycle ?? '—'`. |
| 3 | `Tenant/Suspended.vue` no activado via Inertia (middleware usa HTML nativo) | Media | Bajo | Documentado en §8 Hallazgos. El componente existe y está correcto. Requiere wiring en Fase 5. |

---

## 6. Riesgos Mitigados

| # | Riesgo (del Runbook §7) | Cómo se mitigó |
|---|-------------------------|----------------|
| 1 | UX confusa entre «Activar» legacy y «Restaurar» | Los botones legacy fueron completamente eliminados de la UI. La nueva UX muestra exclusivamente las tres acciones contextuales según la matriz §3.3. |
| 2 | Operador puede activar flag BD sin levantar proceso (Hallazgo 7 del Runbook) | El botón "Activar" genérico fue reemplazado por "Restaurar servicio" que invoca el use case `RestoreTenantServiceUseCase`, el cual coordina levantar el proceso si corresponde. |
| 3 | Acciones visibles incorrectamente según estado | Las acciones se rigen exclusivamente por `actions_available` computado por `TenantLifecyclePolicy` en backend. El frontend no tiene lógica de transición propia. |

---

## 7. Compatibilidad Retroactiva

La compatibilidad retroactiva está **100% garantizada** por las siguientes razones:

1. **Props no eliminados**: `Show.vue` sigue consumiendo exactamente las mismas props (`tenant`, `deployment`, `health`, `plans`, `modules`, `roles`) que ya enviaba `CompanyShowPageService`. Se añadieron `lifecycle` y `actions_available` en Fase 3 — el componente ahora los usa.

2. **Tenants sin `actions_available`**: El guard `?.includes()` hace que ningún botón lifecycle aparezca si el backend no envía el array. El tenant seguirá mostrándose correctamente.

3. **Tenants sin `lifecycle` en Index**: `lifecycleBadgeLabel` tiene fallback a `tenant.status`, por lo que tenants pre-v1.5 mostrarán `active` / `suspended` como antes, solo que con formato badge coloreado.

4. **Rutas legacy preservadas en backend**: Las rutas `POST /{tenant}/suspend` y `POST /{tenant}/activate` siguen activas en `routes/control.php`. Si algún script o test externo las invoca, seguirán funcionando.

5. **Sin cambios en servicios, controladores, modelos ni middleware**: Todos los cambios son exclusivamente en componentes Vue del frontend.

---

## 8. Checklist de Cumplimiento (vs. Runbook §5 Fase 4)

| Requisito Runbook | Cumple | Evidencia |
|-------------------|--------|-----------|
| Actualizar `Companies/Show.vue` — tres acciones contextuales | ✅ Sí | Botones `Levantar servicio`, `Suspender servicio`, `Restaurar servicio` con `v-if` sobre `tenant.actions_available` |
| Crear `Tenant/Suspended.vue` | ✅ Sí | `resources/js/Pages/Tenant/Suspended.vue` — página sin login, mensaje institucional del §3.2 |
| Indicadores de estado en `Companies/Index.vue` | ✅ Sí | Badge lifecycle coloreado reemplaza columna `status` plana |
| Confirmaciones modales (Suspender / Restaurar) | ✅ Sí | `<Teleport to="body">` modal con texto informativo y botón de cancelación |
| Feedback post-Levantar | ✅ Sí | Flash message `'Instancia de empresa levantada correctamente.'` retornado por el controlador backend y renderizado por el bloque de flash existente en `Show.vue` |
| Acciones visibles según matriz §3.3 | ✅ Sí | `provisioned+active` → Levantar; `running+active` → Suspender; `suspended` → Restaurar |
| Página suspensión sin formulario login | ✅ Sí | `Tenant/Suspended.vue` no contiene `<form>`, ni campos de autenticación |
| Respetar diseño oscuro existente del proyecto | ✅ Sí | Todos los estilos usan tokens del sistema (`#0b0e11`, `#e1fdff`, `#b9cacb`, etc.) |
| No modificar rutas existentes | ✅ Sí | Ninguna ruta PHP fue modificada |
| No modificar backend, servicios ni middleware | ✅ Sí | Solo archivos Vue |
| Compatibilidad retroactiva | ✅ Sí | Guards `?.` en todos los nuevos accesos; fallbacks en badges |
| Compatible Windows y Linux | ✅ Sí | Componentes Vue — no tienen dependencias de OS |
| No introducir deuda técnica nueva | ✅ Sí | Sin dependencias externas nuevas; lógica mínima necesaria |

---

## 9. Verificación de Seguridad

| Verificación | Estado | Evidencia |
|-------------|--------|-----------|
| Rutas existentes no rotas | ✅ OK | `routes/control.php` no modificado |
| Provisioning no afectado | ✅ OK | Ningún componente de provisioning tocado |
| Autenticación no afectada | ✅ OK | `Login.vue` y `AuthenticateOperatorUseCase` intactos |
| Multi-tenant no afectado | ✅ OK | Cambios son presentacionales en el control plane |
| Control plane no afectado | ✅ OK | Solo se modificaron vistas Vue del CP; ningún servicio ni ruta |
| Instancias ya provisionadas no afectadas | ✅ OK | Los silos no reciben cambios de código; solo el CP visual |

---

## 10. Hallazgos Fuera de Alcance

### Hallazgo F4-01 — `Tenant/Suspended.vue` no está activo vía Inertia en el middleware

**Descripción:** El middleware `EnsureTenantOperationalStatus` (creado en Fase 3) renderiza la página de suspensión como HTML nativo crudo (`$this->suspendedHtmlView()`), no como una vista Inertia. El componente `Tenant/Suspended.vue` existe y es correcto, pero requiere que el middleware sea modificado para usar `Inertia::render('Tenant/Suspended', ['tenant_name' => ...])` en lugar del HTML estático.

**Impacto:** La página de suspensión funciona correctamente con el HTML nativo. Sin embargo, la versión Inertia permitiría compartir el sistema de diseño global del frontend (fuentes Vite, CSS compilado), comportarse como SPA, y ser más mantenible.

**Fase correspondiente:** Fase 5 — Integración.

**Recomendación:** En Fase 5, modificar `EnsureTenantOperationalStatus::handle()` para usar `Inertia::render('Tenant/Suspended', ['tenant_name' => $tenant->name])` en la rama web, con la dependencia de que el asset bundle esté disponible en el silo suspendido (evaluar si los assets de Vite se sirven desde `/build/`).

---

### Hallazgo F4-02 — Columna "Estado" en Index aún expone `can_simulate` / `simulate_block_reason` con semántica pre-v1.5

**Descripción:** `CompanyListingService::tenantsForIndex()` ya expone `lifecycle` (via `TenantPresentationService::toSummary()`), pero la columna de simulación en `Index.vue` muestra opciones deshabilitadas basadas en `can_simulate` sin correlación visual con el lifecycle. Un tenant en estado `provisioned` (sin silo levantado) aparece deshabilitado en simulación, pero la razón no es visible junto al badge.

**Impacto:** Operatividad reducida — el operador puede no entender por qué no puede simular un tenant que parece "provisionado".

**Fase correspondiente:** Fase 6 — Pruebas (validación UX).

**Recomendación:** En Fase 6, considerar agregar tooltip o nota junto al selector de simulación que explique la relación lifecycle ↔ elegibilidad de simulación.

---

## 11. Trabajo Detectado de Fase Posterior (No Ejecutado)

| Tarea detectada | Fase correspondiente | Motivo de no ejecutar |
|----------------|---------------------|----------------------|
| Activar `Tenant/Suspended.vue` vía Inertia en el middleware | Fase 5 — Integración | Requiere modificar middleware de Fase 3; fuera del alcance del frontend puro |
| Actualizar `instances:serve` para modo supervisor | Fase 5 — Integración | Tarea de infraestructura Node.js, no de componentes Vue |
| Suite de pruebas E2E Playwright para suspensión | Fase 6 — Pruebas | Requiere entorno de integración completo |

---

## Resumen del estado de archivos entregados

```
NUEVOS:
  resources/js/Pages/Tenant/
  └── Suspended.vue                          ✅ CREADO

MODIFICADOS:
  resources/js/Pages/Control/Companies/
  ├── Show.vue                               ✅ MODIFICADO
  │     - Botones legacy suspend/activate → eliminados
  │     - 3 acciones contextuales lifecycle → añadidas
  │     - Modal confirmación (Suspender / Restaurar) → añadido
  │     - Badge lifecycle en cabecera → añadido
  │     - Scripts: funciones legacy → eliminadas; lógica lifecycle → añadida
  │     - Estilos: btn-start, btn-restore, btn-ghost, badges → añadidos
  └── Index.vue                              ✅ MODIFICADO
        - Columna status plana → badge lifecycle coloreado
        - lifecycleBadgeLabel() y lifecycleBadgeClass() → añadidas

SIN MODIFICAR (correctamente fuera de alcance):
  app/Http/Middleware/EnsureTenantOperationalStatus.php
  app/Control/Application/Services/Tenants/TenantLifecycleOrchestrator.php
  app/Control/Domain/Policies/TenantLifecyclePolicy.php
  routes/control.php
  app/Control/Interfaces/Http/Controllers/CompanyController.php
  bootstrap/app.php
  config/platform.php
```

---

*Informe generado al completar Fase 4. La implementación frontend está lista para ser integrada en Fase 5 (Integración) donde se conectará el supervisor de procesos Node, se actualizará el middleware para usar Inertia, y se sincronizará la documentación de despliegue.*
