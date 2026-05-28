# Fase D — Arquitectura por cliente y camino a producción

**Propósito:** documento **único de referencia** para la Fase D: decisión, diseño, impacto, plan, beneficios y riesgos. Complementa detalle en `Fase_D_blueprint_instancia_por_cliente.md` y `Fase_D_plan_implementacion.md`.

**Audiencia:** arquitectura, operaciones, PM y desarrollo que llevarán la plataforma a **producción real** con varios clientes.

---

## 1. Decisión estratégica

### 1.1 Modelo elegido

**Instancia por cliente** (silo desplegable):

- Cada cliente dispone de **su propia instancia** de aplicación (proceso PHP, configuración, secretos).
- **Base de datos dedicada** por instancia (mismo esquema de migraciones en todas).
- **No** se adopta, en esta fase, **multi-tenant lógico** dentro de una sola aplicación Laravel (sin partición por `tenant_id` en tablas ni suscripciones por request).

### 1.2 Por qué no multi-tenant en una sola instancia (resumen)

| Motivo | Implicación |
|--------|-------------|
| El bus y B.2/C fusionan configuración **al arranque del proceso** | Multi-tenant exigiría tenant resolution y catálogos **por tenant** en runtime — refactor amplio. |
| Riesgo de fuga de datos entre tenants | Con BD y despliegue dedicados, el aislamiento es **físico** y más fácil de auditar. |
| Estado actual del producto | Encaja con archivos `modules_config.json`, `eventbus.php` y `consumer_registrars` **por despliegue**, sin rediseñar el núcleo. |

**Híbrido futuro:** si el negocio exige mayor densidad, se puede **re-evaluar** multi-tenant con diseño explícito; este documento **no** lo asume.

---

## 2. Diseño técnico

### 2.1 Arquitectura lógica

```text
                    ┌─────────────────────────────────────┐
                    │ Cliente A (instancia desplegable)      │
                    │  • .env + APP_URL propios              │
                    │  • modules_config.json (catálogo UI)   │
                    │  • eventbus.php + consumer_registrars│
                    │  • BD dedicada                          │
                    │  • /middleware + /dashboard → solo A   │
                    └─────────────────────────────────────┘

                    ┌─────────────────────────────────────┐
                    │ Cliente B (otra instancia)             │
                    │  (misma imagen / mismo repo, otra cfg)│
                    └─────────────────────────────────────┘
```

- **Identidad del cliente:** implícita en **perímetro de despliegue** (URL, red, secreto), no obligatoriamente en una tabla `tenants` dentro de la app.
- **Trazabilidad:** opcional `PLATFORM_CLIENT_SLUG` / `APP_NAME` en `.env` para logs y APM; convenciones de `event_type` y `origin` (§2.3).

### 2.2 Componentes y encaje con fases anteriores

| Componente | Comportamiento en Fase D |
|------------|-------------------------|
| **`modules_config.json`** | Uno por instancia (o futuro path por env). Alimenta `config('modules.catalog')` → Dashboard + input a **B.2** en `sync-config`. |
| **`config/eventbus.php`** | Base versionada; `subscriptions` / `producers` + **`consumer_registrars`** (Fase C). Merge en `boot` → `config('eventbus.subscriptions')` efectiva. |
| **B.2 `sync-config`** | Sin cambio de contrato: persiste registry desde **eventbus efectivo** + catálogo declarativo de **esa** instancia. |
| **Fase C** | Solo se listan registrars de packs **contratados** en ese despliegue. |
| **Publicación HTTP** | Igual que hoy; `event_type` debe existIR en suscripciones si se quieren consumidores en cola. |
| **Middleware / Dashboard** | Una instancia = una vista operativa; “varios clientes” = **varias instancias/URLs**. |

### 2.3 Naming recomendado (eventos e integraciones)

| Campo | Guía |
|-------|------|
| `event_type` | Estable, jerárquico; prefijo de marca o cliente si los eventos salen del silo (`Acme.Orders.Placed`). |
| `origin` | Canal o módulo emisor (`POS`, `PartnerAPI`, …). |
| `payload` | Incluir `event_id`, `occurred_at`; metadatos opcionales solo para correlación externa, **no** para ACL multi-tenant en app. |

### 2.4 Base de datos y esquema

- **Una BD por cliente** (recomendado).
- **Mismas migraciones** en todas las instancias; sin requisito de `tenant_id` en el modelo instancia-por-cliente.
- **Backup / restore** siempre **etiquetados** por cliente para evitar mezclar dumps.

### 2.5 Flujo operativo end-to-end

```text
Deploy (por cliente) → inyecta .env + JSON + config eventbus/registrars
        → migrate
        → arranque: modules.php + EventBusIntegrationServiceProvider (C)
        → opcional: config:clear
        → POST …/registry/sync-config (B.2)
        → smoke publish
        → uso de /middleware y /dashboard
```

---

## 3. Impacto en el sistema actual

| Área | Impacto en código | Impacto operativo |
|------|-------------------|-------------------|
| **B.2** | **Nulo** | Sync se ejecuta **por instancia**; más despliegues ⇒ más ejecuciones a gobernar. |
| **Fase C** | **Nulo** | Lista `consumer_registrars` **por contrato**; revisar que no se copien packs de otro cliente. |
| **eventbus** | **Nulo** | Convenciones de naming y documentación; merge sigue siendo global **al proceso**. |
| **Middleware** | **Nulo** | N URLs/N paneles; posible inventario “fleet” externo. |
| **Dashboard** | **Nulo** | Mismo riesgo de siempre: alinear JSON con `eventbus`/packs por **cliente**. |

**Conclusión:** Fase D es principalmente **modelo de despliegue y gobierno**; **no** exige reescribir el núcleo del bus para el primer salto a producción multi-cliente.

**Referencias de análisis:** evaluación de riesgos e impacto previa (sync, C, aislamiento, drift) debe mantenerse viva en reuniones de release; regla de oro: **una instancia no comparte BD con otra.**

---

## 4. Plan de implementación

Se adopta un **camino en cuatro oleadas** (incremental, controlado). Detalle de tareas, dependencias, riesgos y entregables: **`docs/personal_notes/Fase_D_plan_implementacion.md`**.

| Oleada | Objetivo |
|--------|----------|
| **1 — Preparación** | Documentación, runbook despliegue, convenciones, ficha de artefactos por cliente, RACI. |
| **2 — Adaptación mínima** | Pipeline parametrizado, instancia piloto, smoke post-deploy, restore etiquetado, retro. |
| **3 — Soporte completo** | GitOps/IaC, inventario fleet, automatización opcional de sync, formación, `platform:validate-catalog` (B.3) si aplica. |
| **4 — Optimización** | JSON por volumen/ruta si hace falta, observabilidad unificada, DR, hardening, revisión coste/multi-tenant. |

**Diagrama de dependencias:** Fase 1 → Fase 2 → Fase 3 → Fase 4 (la Fase 4 depende de carga real o varios clientes).

**Runbook operativo base:** `docs/personal_notes/Runbook_cliente_simulado.md` (extender con sección “Despliegue por cliente” según plan D1.2).

---

## 5. Beneficios

| Beneficio | Descripción |
|-----------|-------------|
| **Producción sin rediseño del core** | Reutiliza B.2 y C tal como están. |
| **Aislamiento fuerte** | Datos y config separados por instancia; blast radius acotado. |
| **Escalado horizontal claro** | Más clientes ⇒ más instancias; patrones Cloud estándar. |
| **Cumplimiento y explicabilidad** | “Este cliente vive en X URL y BD Y” es simple de defender ante auditoría. |
| **Evolutivo** | Posibilidad de fleet, validate-catalog y observabilidad sin bloquear el primer piloto. |

---

## 6. Riesgos

| Riesgo | Gravedad | Mitigación |
|--------|-----------|------------|
| **Drift** de versión o config entre clientes | Media | Inventario + política de upgrades; GitOps por cliente. |
| **Sobrecarga operativa** (N instancias) | Media | Automatizar smoke, sync opcional post-deploy, formación. |
| **Malentendido de aislamiento** (mezclar dos clientes en una BD) | Alta | Checklist infra, etiquetas de backup, formación. |
| **`config:cache`** vs JSON montado en caliente | Media | Procedimiento de rebuild de cache o evitar cache donde el JSON sea dinámico. |
| **JSON / registrars incorrectos** en un deploy | Media | PR/revisión por plantilla; validación automática futura (B.3). |
| **Expectativa de “un panel para todos los clientes”** dentro de esta app | Baja–media | Documentar: fleet es herramienta **externa** o fase posterior; Middleware actual es **por instancia**. |

---

## 7. Documentos relacionados

| Documento | Contenido |
|-----------|-----------|
| `Fase_D_blueprint_instancia_por_cliente.md` | Diseño detallado (gestión cliente, módulos, bus, BD, UI). |
| `Fase_D_plan_implementacion.md` | Tareas D1.x–D4.x, dependencias, entregables. |
| `Fase_C_extensibilidad.md` | Registro dinámico de consumidores (C). |
| `B2_sync_ampliado.md` / `B2_validacion.md` | Sync extendido (B.2). |
| `Runbook_cliente_simulado.md` | Procedimiento operativo local y extensible a despliegue. |
| `Plan_Desarrollo_Servicio_v0.1/Plan_de_implementacion.md` | Plan maestro del servicio; enlazar Fase D si procede. |

---

## 8. Criterio de éxito hacia “producción real”

- Al menos **una** instancia no local con pipeline reproducible, BD dedicada, smoke verde y **sync-config** acordado.
- **Procedimiento escrito** de alta de cliente y restore por instancia.
- Equipo alineado: **instancia = cliente** hasta nueva decisión explícita sobre multi-tenant.

---

*Fase D — arquitectura cliente / producción. Documento maestro; mantener sincronizado con el blueprint y el plan operativo al evolucionar el parque de despliegues.*
