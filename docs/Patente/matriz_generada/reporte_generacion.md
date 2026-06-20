# Reporte de generación — Matriz integral de trazabilidad

**Fecha de generación:** 2026-06-10  
**Ubicación salida:** `docs/matriz_generada/`  
**Repositorio:** `platform/event-bus-core` (omnichannel-ddd-eda)  
**Commits analizados:** 37 (`git rev-list --all --count`)  
**Rama activa al generar:** `main`

---

## Documentos analizados

### ADRs (9)
- `docs/production/ADR_001_instancia_por_cliente.md` … `ADR_009_opentelemetry_distributed_tracing.md`

### Planes de desarrollo y producción
- `docs/Plan_Desarrollo_Servicio_v0.1/` — Flujo_Middleware.md, Arquitectura_EDA.md, DDD_en_la_arquitectura.md, Matriz_Plan_Desarrollo.csv
- `docs/Plan_Desarrollo_Modulos_v0.1/` — Plan_Modulo_Control_Middleware.md (v2.0), Plan_Modulo_Dashboard_General.md (v2.0), README.md
- `docs/production/Plan_*.md` — 16 planes (Seguridad, APIs, Middleware, Integraciones, Tenants, etc.)
- `docs/production/Plan_de_implementacion.md` (v1.1)
- `docs/production/Reporte_Implementacion.md`
- `docs/production/Auditoria_Produccion.md`

### Módulos y arquitectura
- `docs/Modulos/Modulo_Control_Middleware.md`, `Modulo_Dashboard_General.md` (legacy)
- `docs/architecture/` — er_diagram, middleware_database_dictionary, middleware_database_architecture

### Runbooks (8)
- `docs/production/Runbook_*.md` (5)
- `docs/monitoring/Runbook_Alertas.md`
- `docs/personal_notes/Runbook_cliente_simulado.md`, `Runbook_Fase_A_cliente_simulado_local.md`

### Testing y API
- `docs/testing/` — 13 archivos markdown + load/
- `docs/api/openapi.yaml`, `BREAKING_CHANGE_POLICY.md`, `postman_collection.json`

### Referencia y obsoletos
- `docs/Analisis_v0.1/` — 18 documentos de investigación
- `docs/DC_Mockups_obsoletos(NOusar)/` — 5 fichas marcadas NOusar
- `docs/Mokcups_v1.0/`, `docs/Mokcups_v2.0/` — mockups HTML

### Configuración y manifiestos
- `composer.json`, `package.json`, `phpunit.xml`
- `config/platform.php`, `eventbus.php`, `saas_catalog.php`, `platform_roles.php`, etc.

**Total archivos en `docs/`:** 158 (conteo filesystem 2026-06-10)

---

## Carpetas analizadas

| Carpeta | Propósito evidenciado | Archivos aprox. |
|---------|----------------------|-----------------|
| `app/` | Bounded contexts DDD (14 carpetas top-level) | 311 PHP |
| `config/` | Configuración plataforma eventbus módulos | 22 PHP + JSON |
| `database/migrations/` | Esquema BD middleware y observabilidad | 31 |
| `database/seeders/` | Seed tenant instancia | evidenciado en Reporte_Implementacion |
| `routes/` | web, control, api, console | 4 |
| `resources/js/` | UI Inertia Vue (14 páginas) | PENDIENTE_VALIDACION conteo total |
| `tests/` | Unit Feature Integration E2E | 81 PHP + e2e-ui |
| `scripts/` | CI ops local-dev | 14 |
| `docs/` | Documentación planes ADRs runbooks | 158 |
| `deploy/` | PENDIENTE_VALIDACION | NO_EVIDENCIADO en análisis profundo |

---

## Archivos analizados

| Categoría | Cantidad | Fuente |
|-----------|----------|--------|
| Archivos totales repositorio | 17193 | `Get-ChildItem -Recurse -File` (incluye vendor/node_modules si presentes) |
| Archivos `docs/` | 158 | filesystem |
| Archivos PHP `app/` | 311 | filesystem |
| Archivos PHP `tests/` | 81 | filesystem |
| Migraciones | 31 | `database/migrations/` |
| Comandos Artisan propios | 14+2 | `app/Console/Commands/` + Monitoring commands |
| Páginas Vue | 14 | `resources/js/Pages/**/*.vue` |
| CSV generados matriz | 12 | `docs/matriz_generada/` |

---

## Versiones identificadas

| Versión / hito | Evidencia | Estado |
|----------------|-----------|--------|
| beta | Commit inicial `b175b8d` mensaje "version beta" | git log |
| v0.1 | Carpetas `Plan_Desarrollo_*_v0.1`, README módulos | documentación |
| v1.1 | `Plan_de_implementacion.md` Versión 1.1 | documentación |
| v1.7 | Commit `6500034` "version 1.7" | git log |
| v2.0 | Planes módulo Middleware y Dashboard | documentación |
| Fase 6 | Commits `35cd726`, `6eb0b5c` | git log |
| Ramas feature/v1.5, v1.6, v1.7 | `git branch -a` | existen; sin tags git |
| Tags git | `git tag -l` vacío | NO_EVIDENCIADO releases formales |

---

## Dependencias identificadas

### Runtime PHP (`composer.json`)
- PHP ^8.2, Laravel ^11.0, inertiajs/inertia-laravel ^3.0, laravel/sanctum 4.0, opis/json-schema 2.3, ramsey/uuid ^4.7

### Desarrollo PHP
- pestphp/pest ^3.0, phpstan/phpstan ^1.10, laravel/pint ^1.0, mockery/mockery ^1.6

### Frontend (`package.json`)
- vue ^3.4.21, @inertiajs/vue3 ^3.0, vite ^5.2.0, axios ^1.6.4, tailwindcss ^3.4.1, @playwright/test ^1.49.0

### Dependencias arquitectónicas internas (30 registros)
Ver `dependencias.csv` — incluye acoplamientos Dashboard→Middleware, Control→Tenant, Integration→Bus, etc.

---

## Datos faltantes

| Dato | Motivo |
|------|--------|
| IDs formales RF/RNF | No existen en documentación; se usaron REQ-C*, REQ-O*, REQ-ADR*, REQ-RST* |
| Archivo BPMN `.bpmn` | Búsqueda glob: solo `phpunit.xml`; flujos reconstruidos desde markdown y código |
| `docs/matriz de control de versiones/control_versiones - FLUJO_BPMN.csv` | Referenciado en índice docs; archivo no encontrado en workspace al generar |
| Tags / releases GitHub | `git tag -l` sin resultados |
| Ramas origen/destino exactas en merges 3-4 | Sin merge commits para feature/v1.7→main |
| Estado SSE O5 en producción | StreamLiveEventsUseCase en plan; implementación UI PENDIENTE_VALIDACION |
| Módulos analytics, security_audit, multi_channel UI | Solo catálogo `saas_catalog.php`; implementación parcial o NO_EVIDENCIADO |
| Fechas de commits en docs sin hash | Documentos con fecha interna sin correlación commit |
| LocalFleet / ProvisionNewTenant | NO_EVIDENCIADO en código commitado analizado (grep sin resultados) |

---

## Riesgos encontrados

1. **Divergencia fuentes de verdad:** `eventbus.php` vs `modules_config.json` — documentado en `Plan_de_implementacion.md` §2.1; mitigado parcialmente por `sync-config` y `platform:validate-catalog`.
2. **Documentación 5 etapas vs implementación 2 etapas:** `Flujo_Middleware.md` vs `Plan_Middleware.md` problema #5.
3. **Legacy retail en docs:** `Modulos/` y mockups obsoletos vs core agnóstico — riesgo de scope creep en interpretación.
4. **Config dinámica runtime:** Estado explícito "No cumple" — producto no listo para autogestión cliente.
5. **Datos legacy contaminados:** Commit `6eb0b5c` menciona contaminación histórica silos.
6. **Seguridad cloud producción:** `Auditoria_Produccion.md` indica aspectos críticos no listos; ADR-002/003 enterprise diferidos.
7. **Historial Git limitado:** 37 commits; trazabilidad funcional depende fuertemente de documentación no siempre ligada a hash.
8. **Sin tags de release:** Dificulta control de versiones formal.

---

## Nivel de confianza por tabla

| Tabla | Nivel | Justificación |
|-------|-------|---------------|
| `procesos.csv` | **Alto** | Basado en rutas, comandos, servicios y ADRs verificados en código |
| `actividades_bpmn.csv` | **Medio** | Mezcla actividades implementadas (código) y documentales (Flujo_Middleware 5 etapas) |
| `flujo_bpmn.csv` | **Medio** | Gateways inferidos donde código no nombra nodos BPMN explícitos |
| `requerimientos.csv` | **Alto** | Extraídos de C1-C5, O1-O5, ADRs y planes con rutas citadas |
| `control_versiones.csv` | **Medio** | Commits verificados; entradas documentales sin hash marcadas NO_EVIDENCIADO |
| `ai_dlc.csv` | **Medio** | Estados Producción inferidos de Plan_de_implementacion y Release_decision_QA |
| `dependencias.csv` | **Alto** | composer.json, package.json y acoplamientos verificados en providers/rutas |
| `merges.csv` | **Bajo** | Solo 2 merge commits; ramas destino PENDIENTE_VALIDACION |
| `historial.csv` | **Medio** | Timeline git completa (37 commits) + eventos documentales sin hash |
| `artefactos.csv` | **Medio** | Muestra representativa (60/158 docs); no catálogo exhaustivo |
| `pmv.csv` | **Alto** | `saas_catalog.php` + evidencia código por módulo |
| `dashboard.csv` | **Alto** | Conteos verificados por script PowerShell sobre CSV generados |

---

## Archivos generados

| Archivo | Registros (datos) |
|---------|-------------------|
| `procesos.csv` | 20 |
| `actividades_bpmn.csv` | 32 |
| `flujo_bpmn.csv` | 31 |
| `requerimientos.csv` | 37 |
| `control_versiones.csv` | 20 |
| `ai_dlc.csv` | 60 |
| `dependencias.csv` | 30 |
| `merges.csv` | 4 |
| `historial.csv` | 24 |
| `artefactos.csv` | 60 |
| `pmv.csv` | 12 |
| `dashboard.csv` | 22 |
| **Total conocimiento** | **330** |

---

## Metodología

1. Prioridad fuentes: código implementado > Git > ADRs > runbooks > planes > markdown.
2. En conflicto documentación vs código: prevalece implementación (ej. core sin dominios retail).
3. Campos no determinables: `PENDIENTE_VALIDACION` o `NO_EVIDENCIADO`.
4. Sin invención de IDs RF/RNF inexistentes en el repositorio.

---

## Próximos pasos sugeridos (no ejecutados)

- Completar archivo BPMN formal o recuperar CSV en `docs/matriz de control de versiones/`
- Crear tags git por hitos v1.7 y releases
- Validar manualmente SSE O5 y módulos analytics/security_audit/multi_channel
- Ampliar `artefactos.csv` a catálogo exhaustivo de los 158 archivos `docs/`
