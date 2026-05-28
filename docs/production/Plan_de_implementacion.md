# Plan de implementación — Pruebas tipo producción y configuración dinámica de módulos

**Ubicación:** `docs/production/Plan_de_implementacion.md`  
**Versión:** 1.1  
**Fecha:** 2026-05-21 (actualizado)  
**Objetivo:** Cerrar el hueco entre el **estado actual del repositorio** y la capacidad de **configurar productores/consumidores por “cliente”**, **visualizar el flujo** en Middleware y Dashboard, y **simular entorno productivo** sin ambigüedad operativa.

> **Nota v1.1:** Este plan fue complementado por la auditoría de producción 2026-05-21. Ver [Auditoria_Produccion.md](Auditoria_Produccion.md) y los planes individuales en `docs/production/Plan_*.md`. Las secciones marcadas **[ACTUALIZADO]** reflejan el código vigente.

---

## 1. Verificación de readiness (resumen ejecutivo)

### 1.1 ¿Se puede ya probar el Middleware “como producción” con módulos configurables?

| Criterio | Estado | Nota |
|----------|--------|------|
| Publicar eventos vía API (`POST /api/middleware/events/publish`) | **Cumple** | Válido para smoke / carga ligera. |
| Ver cola, métricas, topología, dead letters en `/middleware` | **Cumple** | UI + polling existentes. |
| Ver feed, KPIs configurables, topología declarativa en `/dashboard` | **Cumple** | `dashboard_config.json`, `modules_config.json`. |
| Topología **observada** (productores/consumidores inferidos del tráfico) | **Cumple parcial** | Requiere `eventbus.subscriptions` con módulos consumidores para aristas esperadas + eventos con `event_id`. |
| Registro persistente desde catálogo (`POST /api/middleware/registry/sync-config`) | **Cumple [ACTUALIZADO]** | Sincroniza `eventbus` **y** `modules_config.json` (B.2). |
| Simulación cliente automatizada (fixtures + `platform:simulate-client`) | **Cumple [ACTUALIZADO]** | Fixtures `tests/fixtures/clients/`, runbook producción, smoke scripts, CI nightly. Ver [Plan_SimulacionClientes.md](Plan_SimulacionClientes.md). |
| Configuración **dinámica por cliente** sin redeploy / sin tocar repo | **No cumple** | Hoy predominan **archivos de config** y **providers**; no hay panel ni API de gobierno unificada. |
| Fusión automática de packs vía `EventConsumerRegistrationInterface` | **Cumple parcial [ACTUALIZADO]** | `PackSubscriptionCatalogMerger` implementado; activar demo pack vía `DEMO_PACK_ENABLED=true` o `consumer_registrars`. |

**Conclusión:**  
- **Listo** para **pruebas controladas en laboratorio** y **rehearsal staging**: fixtures versionados + `platform:simulate-client`, runbook [Runbook_Simulacion_Cliente.md](Runbook_Simulacion_Cliente.md), checklist pre-GO.  
- **No listo** como producto “el cliente configura módulos en runtime” sin trabajo adicional descrito en las fases siguientes.

---

## 2. Estado actual técnico (inventario fidedigno)

### 2.1 Dos fuentes de verdad parcialmente solapadas

| Fuente | Qué gobierna | Quién la consume |
|--------|----------------|------------------|
| `config/eventbus.php` | Suscripciones in-process, umbrales, colas | Middleware (tracking `QueueEntry.consumers`), sync al registry, tests. |
| `config/modules/modules_config.json` | Productores/suscriptores **declarativos** para UI | Dashboard (`EventFlowTopology`, catálogo API `GET /api/dashboard/modules/catalog`). |

**Riesgo:** un operador puede configurar productores en JSON del dashboard pero olvidar `eventbus.subscriptions` (o al revés) y ver **diagramas incoherentes** entre “declarado” vs “observado” vs “enrutado”.

### 2.2 Qué ya muestra el flujo

- **Middleware** (`/middleware`): topología API `GET /api/middleware/topology` con `config` + `observed` + conexiones cuando hay tráfico y suscripciones cargadas. Botón “Añadir módulos configurados” → `sync-config`.
- **Dashboard** (`/dashboard`): topología desde catálogo de módulos + métricas desde feed/bus según JSON.

### 2.3 Contrato de integración declarativo

- `App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface::subscriptionCatalog()` — **implementado [ACTUALIZADO]** vía `PackSubscriptionCatalogMerger` y `EventBusIntegrationServiceProvider`. Demo pack en `app/Platform/Demo/`.

### 2.4 Base de datos middleware (2026-05-21)

- Esquema completo omnicanal migrado — ver `docs/architecture/middleware_database_*.md`
- Runtime activo: `message_queue`, `dead_letter_queue`, `event_feed_projections`, `observability_metrics`, `registered_modules`
- Tablas sin código aún: `event_store`, `integrations`, `webhooks`, `workflows` — ver [Plan_Middleware.md](Plan_Middleware.md), [Plan_Integraciones.md](Plan_Integraciones.md)

---

## 3. Definición de “listo para pruebas tipo producción” (criterios de salida)

Se considera **alcanzado** cuando:

1. **Runbook único** (en docs) describe cómo “dar de alta” un cliente simulado: pasos ordenados y archivos/artefactos tocados.
2. **Un solo flujo coherente**:
   - declaración de productores/consumidores (elegir: JSON unificado **o** eventbus + mirror automático a UI), y  
   - verificación en Middleware **y** Dashboard sin contradicciones documentadas.
3. **Fusión de catálogo** desde packs (o desde un JSON de tenant) sin editar `AppServiceProvider` para cada cliente.
4. **Pruebas automatizadas** (feature/integration) que cubran: catálogo cargado → sync → publish → topología observada + snapshot dashboard.

Hasta cumplir (1)–(4), las pruebas siguen siendo **laboratorio con intervención manual**.

---

## 4. Plan por fases (trabajo restante)

### Fase A — Documentación operativa inmediata (sin código obligatorio)

| ID | Tarea | Entregable |
|----|--------|------------|
| A.1 | Runbook “Cliente simulado” | Sección en este plan o archivo `docs/.../Runbook_cliente_simulado.md`: orden exacto (editar JSON → `config:clear` si aplica → `sync-config` → `platform:emit-mock` / publish HTTP → verificar URLs). |
| A.2 | Matriz “fuente de verdad” | Tabla oficial: qué archivo manda para **enrutamiento** vs **solo UI**. |
| A.3 | Alinear docs viejos | Nota en `docs/Modulos/*.md` o en `Plan_Desarrollo_Modulos_v0.1/README.md` apuntando a este runbook si hay divergencia. |

**Indicador de cierre A:** Un integrador puede repetir la simulación siguiendo solo documentación.

---

### Fase B — Unificar o enlazar catálogo declarativo con `eventbus` (código)

| ID | Tarea | Entregable |
|----|--------|------------|
| B.1 | **Opción recomendada:** al cargar la app, si existe `modules_config.json`, **derivar o validar** contra `eventbus.subscriptions` / `producers` (o generar uno desde otro con comando `artisan`). | Menos divergencia manual. |
| B.2 | **Opción alternativa:** `POST /api/middleware/registry/sync-config` lea **también** `modules_config.json` además de `eventbus` para poblar el registry. | Un solo clic tras editar JSON. |
| B.3 | Comando `php artisan platform:validate-catalog` | Falla CI si declarado ≠ suscrito (reglas definidas). |

**Indicador de cierre B:** Tras editar un solo lugar (o ejecutar un sync explícito), Middleware y Dashboard reflejan el mismo grafo declarativo.

---

### Fase C — Registrar packs de cliente (`EventConsumerRegistrationInterface`)

| ID | Tarea | Entregable |
|----|--------|------------|
| C.1 | En `AppServiceProvider` (o provider dedicado), **descubrir** clases que implementan `EventConsumerRegistrationInterface` y **merge** seguro en `eventbus.subscriptions` en `boot()`. | Integración sin tocar el core por cada listener. |
| C.2 | Ejemplo `tests/fixtures` o paquete demo con una implementación mínima | Modelo para “cliente”. |

**Indicador de cierre C:** Añadir un pack = solo PHP + provider; sin duplicar arrays en config a mano.

---

### Fase D — Capa de “tenant / cliente” (opcional, solo si el negocio lo exige ya)

| ID | Tarea | Entregable |
|----|--------|------------|
| D.1 | Diseño: ¿multi-tenant en una instancia o **instancia por cliente**? | ADR breve en `docs/`. |
| D.2 | Si multi-tenant: namespacing de `event_type`, prefijos de `origin`, o tablas por `tenant_id`. | Fuera del alcance del core actual; planificar. |

**Indicador de cierre D:** Decisión explícita; si “instancia por cliente”, Fase D puede posponerse.

---

### Fase E — QA y observabilidad de pruebas

| ID | Tarea | Entregable |
|----|--------|------------|
| E.1 | Suite de pruebas “cliente A”: config mínima + N eventos + assert topología | Test de integración/feature. |
| E.2 | Checklist manual para demo a negocio | Anexo en docs. |

**Indicador de cierre E:** CI verde + checklist firmado internamente.

---

## 5. Validación explícita solicitada (formato control de calidad)

| Pregunta | Resultado | Validación |
|----------|-----------|------------|
| ¿Podemos pasar ya a desarrollar **pruebas tipo producción** solo con lo existente? | **Parcial** | **Condicional:** sí en laboratorio con edición de config y procedimiento; **no** como autogestión por cliente final. |
| ¿Configuración dinámica productor/consumidor **solo con instrucciones** al estilo “cliente real”? | **No cumple** aún | **Rechazado** hasta Fase A + al menos B.1/B.2 + C.1 según ambición. |
| ¿Flujo visible Middleware + Dashboard? | **Cumple** con datos coherentes | **Aprobado** si las fuentes están alineadas manualmente. |

---

## 6. Próximo paso recomendado (actualizado v1.1)

B.2 y C están implementados. Prioridades inmediatas según [Auditoria_Produccion.md](Auditoria_Produccion.md):

1. **Seguridad:** auth + rate limiting ([Plan_Seguridad.md](Plan_Seguridad.md))
2. **Cloud:** Docker + CI + `.env.example` ([Plan_Cloud.md](Plan_Cloud.md), [Plan_CI_CD.md](Plan_CI_CD.md))
3. **Simulación:** consolidar runbooks + staging ([Plan_SimulacionClientes.md](Plan_SimulacionClientes.md))
4. **Pendiente B.3:** `platform:validate-catalog`

---

## 7. Referencias en el repositorio

- Contrato de packs: `app/Shared\Contracts\EventBus/EventConsumerRegistrationInterface.php`
- Catálogo UI: `config/modules/modules_config.json`, `config/modules.php`
- Bus: `config/eventbus.php`
- Sync registry: `POST /api/middleware/registry/sync-config`
- Plan de módulos (producto): `docs/Plan_Desarrollo_Modulos_v0.1/README.md`
- **Auditoría producción:** [Auditoria_Produccion.md](Auditoria_Produccion.md)

---

## 8. Mapa de planes de producción (2026-05-21)

Ver índice completo en [Auditoria_Produccion.md](Auditoria_Produccion.md) — 17 planes individuales `Plan_*.md`.

---

*Documento histórico complementado por auditoría producción 2026-05-21. Regularizar antes de refactor masivo.*
