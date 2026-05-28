# Estrategia de pruebas — pre-producción (plataforma bus + middleware + dashboard)

**Rol:** guía para QA Lead / equipo antes de liberar a producción real (modelo **Fase D: instancia por cliente**).  
**Base:** Fase A (runbook), B.2 (`sync-config` + `modules.catalog`), C (`consumer_registrars` + interface), D (silo por despliegue).

---

## 1. Tipos de pruebas necesarias

### 1.1 Unitarias (sí aplican)

| Qué | Objetivo | Cobertura esperada |
|-----|----------|-------------------|
| **Merger de catálogo C** (`PackSubscriptionCatalogMerger`) | Merge idempotente, deduplicación, skips seguros | Alta en reglas de merge (ya cubierto en tests; mantener al cambiar contrato). |
| **Servicios de dominio puros** (value objects, normalizaciones) | Regresión rápida sin I/O | Según evolución del core; priorizar lo que alimenta cola/registry. |

**No** sustituyen validar HTTP ni UI.

### 1.2 Integración (API + BD)

| Qué | Objetivo |
|-----|----------|
| **Middleware Control API** | `sync-config`, `publish`, queue, topology, metrics, event-by-id |
| **Dashboard API** (si expone catálogo/métricas críticas) | Catálogo coherente con JSON cargado |
| **Flujo config → sync → publish → lectura en BD** | `middleware_registered_modules`, `bus_queue_entries`, feed si aplica |

**Herramienta:** PHPUnit Feature/Integration + `RefreshDatabase` (patrón actual del repo).

**Cobertura esperada:** todos los endpoints usados en runbook + al menos un caso **con** `consumer_registrars` simulado (config runtime) por release mayor.

### 1.3 End-to-end (E2E)

| Qué | Objetivo |
|-----|----------|
| **Smoke automatizado ligero** | `POST /registry/sync-config` → `POST /events/publish` → `GET /queue` o event lookup → código HTTP y `success` |
| **E2E navegador (opcional / recomendado pre-prod)** | `/middleware` y `/dashboard` cargan, reflejan tráfico reciente tras publicar eventos de prueba |

**Herramientas:** script curl/PowerShell en CI; Playwright/Cypress si el equipo ya los usa (no obligatorio día uno).

**Cobertura esperada:** mínimo **un** E2E API por entorno (staging); E2E UI al menos **manual** checklist hasta automatizar.

### 1.4 Pruebas manuales controladas

| Qué | Cuándo |
|-----|--------|
| Runbook completo por **instancia** (Fase D) | Cada primer despliegue cliente y tras cambios de config mayores |
| Validación visual topología / KPIs | Cuando cambia `dashboard_config.json` o `modules_config.json` |
| Prueba restore backup | Antes de declarar DR “ok” |

Usar checklist explícita (anexo al runbook).

---

## 2. Flujos críticos a validar

### 2.1 Configuración de módulos

| Paso | Validación |
|------|------------|
| `modules_config.json` válido | JSON válido; filas con `id`/`name` según reglas del catálogo |
| Carga en app | `GET /api/dashboard/modules/catalog` (o equivalente) refleja productores/suscriptores esperados |
| Coherencia con bus | Tipos de evento declarados alineados con `eventbus.php` / packs C (evitar “solo JSON”) |


### 2.2 `sync-config` (B.2)

| Paso | Validación |
|------|------------|
| Ejecución idempotente | Dos `POST …/registry/sync-config` consecutivos sin error; contadores coherentes (o BD sin duplicados lógicos) |
| Fusiona `eventbus` + declarativo | Tras editar solo JSON: registry refleja módulos; tras editar suscripciones: consumidores en filas esperadas |
| Con packs (C) | Con `consumer_registrars` configurado, `sync-config` + publish muestran `consumers` acordes en cola cuando el tipo está suscrito |

### 2.3 Emisión de eventos

| Paso | Validación |
|------|------------|
| **Publish HTTP** | 422 si falta campo; 201 con payload mínimo válido |
| **Comando demo** (`platform:demo-dashboard-events`, etc.) | No excepción; feed/KPIs según `dashboard_config.json` si tipos coinciden |
| `event_id` presente | Tracking y feed no omiten filas por falta de id |

### 2.4 Procesamiento de consumidores (C)

| Paso | Validación |
|------|------------|
| Listener registrado | Tras dispatch/publicación del `event_type`, el listener del pack se ejecuta (log, side-effect de prueba, o spy en test) |
| Sin doble ejecución por merge duplicado | Un solo listener por par evento+clase tras boot |

### 2.5 Visualización Middleware + Dashboard

| Paso | Validación |
|------|------------|
| **Middleware** | Cola con filas nuevas; topología coherente con tráfico + config; métricas sin error 500 |
| **Dashboard** | Catálogo declarativo = JSON; feed/KPIs reaccionan si eventos y config lo permiten |
| **Sin mezcla de clientes (D)** | En staging/prod: una URL/instancia = un juego de datos; no usar mismo browser profile para dos clientes reales sin limpiar sesión si hay riesgo de confusión humana |

---

## 3. Riesgos a cubrir — mapa prueba ↔ riesgo

| Riesgo | Cómo se cubre |
|--------|----------------|
| **Inconsistencia JSON vs `eventbus` / packs** | Tests de integración con `config()->set` + (futuro) `platform:validate-catalog`; manual: matriz en runbook |
| **Pérdida de eventos** | Publish 201 + GET event-by-id + fila en cola `PROCESADO` (o flujo documentado); logs sin skip de `event_id` |
| **Duplicados en registry/suscripciones** | Unit merger + test dedup; doble sync manual |
| **Fallo de consumidor (excepción en listener)** | Test o staging: listener que falla → dead letter o log según diseño actual; verificar que el bus no queda inconsistente sin documentar |
| **JSON inválido en disco (silencioso)** | Manual/control: lint JSON en CI; prueba de archivo corrupto en staging |
| **Regresión B.2/C** | Suite CI verde + subconjunto “smoke” largo antes de tag |
| **`config:cache`** desalineado | Prueba manual: cambiar config, cachear, verificar necesidad de `config:clear` documentado |

---

## 4. Lista de pruebas (inventario ejecutable)

### 4.1 Automatizadas (mantener en CI)

- [ ] Suite PHPUnit completa (unit + feature + integration).
- [ ] Casos existentes: `sync-config`, publish, registry, topology (según `MiddlewareControlApiTest` y afines).
- [ ] Casos `PackSubscriptionCatalogMerger` (C).
- [ ] (Recomendado) Test feature: `consumer_registrars` con fixture que registre listener y aserte efecto o config merge (si no existe, añadir en iteración próxima).

### 4.2 Staging / pre-prod (por instancia cliente)

- [ ] Migraciones aplicadas en BD **dedicada**.
- [ ] `POST /api/middleware/registry/sync-config` → `success: true`.
- [ ] `POST /api/middleware/events/publish` con tipo suscrito → fila en cola con consumers esperados.
- [ ] Evento demo alineado con `dashboard_config.json` → KPI/serie visible si aplica.
- [ ] `/middleware` y `/dashboard` sin error 500; datos coherentes tras pasos anteriores.

### 4.3 Manuales (checklist mínima)

- [ ] Runbook Fase A seguido en el entorno objetivo.
- [ ] Ficha Fase D: `.env`, JSON, `consumer_registrars`, versión imagen/commit correctos.
- [ ] Restore de backup en entorno aislado (etiqueta cliente).

---

## 5. Cobertura esperada (resumen)

| Capa | Mínimo aceptable pre-prod | Ideal |
|------|---------------------------|--------|
| Unit | Merger C + piezas críticas sin I/O | + reglas puras nuevas |
| Integración API | Todos los endpoints operativos del runbook + sync B.2 + publish | + fixture pack C end-to-end |
| E2E API | Smoke post-deploy automatizado | Incluir topología |
| E2E UI | Checklist manual | Playwright en smoke nightly |
| Manual | Primera instancia cliente + DR | Cada release mayor |

---

## 6. Criterios de éxito — listo para producción

El sistema se considera **listo para producción** (por **instancia/cliente**) cuando se cumple **simultáneamente**:

1. **CI verde** en el commit/imagen desplegada (sin tests ignorados que oculten regresión conocida).
2. **Staging** (o piloto) con la **misma** clase de artefactos que prod: BD dedicada, JSON y `consumer_registrars` del contrato, sin datos cruzados de otro cliente.
3. **Smoke automatizado o manual firmado:** migrate → sync-config → publish → verificación cola/topología + pantallas `/middleware` y `/dashboard`.
4. **Runbook y rollback** conocidos por el turno que despliega (orden `config:clear`, sync, reinicio si aplica).
5. **Observabilidad mínima:** logs accesibles, identificación de instancia (`APP_URL`, slug opcional); alertas acordadas para 5xx y fallos de cola si hay.
6. **Backup/restore** probado o ventana aceptada por negocio si aún no hay DR completo (riesgo explícito).

**No es obligatorio** para el primer go-live: E2E UI automatizado, siempre que el checklist manual esté ejecutado y firmado.

---

## 7. Artefactos recomendados

| Artefacto | Uso |
|-----------|-----|
| Checklist pre-prod (este doc + runbook) | Cada release |
| Log de ejecución smoke (fecha, versión, operador) | Auditoría |
| Tabla “qué versión en qué cliente” (fleet) | Fase D |

---

*Estrategia práctica pre-producción — alinear con `Fase_D_arquitectura_cliente.md` y `Runbook_cliente_simulado.md`.*
