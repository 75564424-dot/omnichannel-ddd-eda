# Fase D — Blueprint técnico (instancia por cliente)

**Estrategia base:** **una instancia desplegable por cliente** (silo de aplicación + base de datos dedicada + secretos propios). No se diseña aquí un multi-tenant lógico dentro de una única JVM/PHP-FPM compartiendo la misma base con partición por `tenant_id`.

**Compatibilidad:** conserva **Fase B.2** (`sync-config` fusiona `eventbus` + `modules.catalog`) y **Fase C** (`consumer_registrars` + `EventConsumerRegistrationInterface`) sin exigir cambios de contrato en el código actual; el blueprint describe **cómo operar y nombrar** a nivel de plataforma.

---

## 1. Gestión de clientes

### 1.1 Identificación

| Nivel | Mecanismo | Rol |
|-------|-----------|-----|
| **Operativo / negocio** | Identificador estable del cliente (`client_slug`, `client_id` interno, contrato, etc.) | Facturación, soporte, trazabilidad en observabilidad **externa** (logs agregados, APM tags). |
| **Runtime de la instancia** | **No hace falta** un modelo `Tenant` en base si cada despliegue sirve a un solo cliente | La “identidad del cliente” es **implícita** en el despliegue. |
| **Trazas y eventos** | Prefijos en `event_type` y/o `origin` (ver §3) | Correlación humana y búsquedas sin cruzar datos entre clientes en la misma BD. |

**Decisión clave:** el tenant **no** se resuelve por cabecera HTTP dentro de la app; se resuelve **por el perímetro del despliegue** (URL dedicada, VPC, cluster). Esto mantiene B.2/C como hoy (merge global **por proceso**).

### 1.2 Configuración

- **`.env` por instancia:** `APP_URL`, credenciales de BD, colas, `APP_KEY`, opcionalmente `PLATFORM_CLIENT_SLUG`, `PLATFORM_CLIENT_LABEL` (solo metadatos / logs).
- **Artefactos versionados o entregados por canal seguro:**
  - `config/modules/modules_config.json` **específico del cliente** (en repo privado del cliente, volumen montado, o secret manager que genera archivo en deploy).
  - `config/eventbus.php` puede seguir siendo **base común** + overrides mínimos por cliente (GitOps) o solo `consumer_registrars` apuntando a packs del cliente.
- **Fase C:** lista `consumer_registrars` incluye solo los packs contratados para **ese** despliegue.

**Incremental:** primer paso sin producto “panel multi-cliente”; segundo paso opcional: pipeline Helm/terraform que inyecta JSON + env desde un CRM interno.

---

## 2. Configuración de módulos por cliente

### 2.1 Separación de `modules_config.json`

| Enfoque | Descripción |
|---------|-------------|
| **Recomendado (simple)** | Un archivo **`modules_config.json` por instancia** en disco (ruta fija `config/modules/modules_config.json` en el artefacto de ese cliente). No hay varios JSON en un solo runtime. |
| **Incremental (mismo binario)** | Variable de entorno opcional futura `MODULES_CONFIG_PATH` apuntando al JSON del cliente; `config/modules.php` lee esa ruta si está definida — **no obligatorio en v1** si cada build/despliegue ya empaqueta el JSON correcto. |

Cada cliente tiene su propio **catálogo declarativo** (productores, suscriptores, mensaje de contacto) **sin** ramificar código PHP del core.

### 2.2 `sync-config`

- **Sin cambio conceptual:** `POST /api/middleware/registry/sync-config` sigue leyendo `config('eventbus.*')` y `config('modules.catalog')` **de esa instancia**.
- **Procedimiento por cliente:** tras actualizar JSON y/o `eventbus` / registrars, `config:clear` si aplica → **sync-config** → publicar eventos de prueba.
- **Automatización opcional:** job post-deploy (curl interno) que llama a `sync-config` una vez levantada la instancia.

**B.2** permanece válido: el registry refleja la combinación **eventbus + declarativo** del **único** cliente atendido por el proceso.

---

## 3. EventBus adaptado

### 3.1 Separación de eventos entre clientes

Los eventos **no coexisten en la misma base de la instancia** con otro cliente: la separación es **física** (otra instancia = otro silo). Dentro de la instancia el bus sigue siendo **global** al proceso (como ahora).

Si en el futuro hubiera **varias instancias** del mismo software generando métricas en un lake compartido, la separación lógica viene de:

- **etiquetas de despliegue** en observabilidad (`client_slug`);
- **nombres de evento** y **origin** explícitos (abaixo).

### 3.2 Naming recomendado

| Campo | Convención | Ejemplo |
|-------|------------|---------|
| **`event_type`** | Jerárquico, estable, con **prefijo de cliente o marca** cuando los eventos puedan exportarse fuera del silo | `AcmeOrders.Order.Placed`, `Platform.Demo.Measurement` (demo interno) |
| **`origin`** (publish API) | Canal o módulo emisor humano | `POS`, `PartnerAPI`, `Acme.WebCheckout` |
| **`payload`** | Incluir `event_id`, `occurred_at`; opcional `tenant_hint` = slug **solo** para trazas cruzadas en integraciones externas, no para ACL dentro de la app monocliente |

**Decisión clave:** convención documentada para equipos de integración; **no** hace falta un bus “multi-tenant” en código mientras se mantenga instancia por cliente.

### 3.3 Fase C en este modelo

- Cada instancia registra **solo** los `consumer_registrars` de ese contrato.
- Los `event_type` declarados en packs deben **alinear** con los que el productor publica y con `modules_config.json` / `eventbus.php` para evitar la divergencia ya descrita en el plan de implementación.

---

## 4. Base de datos

### 4.1 Separación por cliente

| Opción | Uso |
|--------|-----|
| **Recomendado** | **Base de datos dedicada** (o cluster gestionado con BD lógica propia) por instancia cliente. Mismo **esquema** de migraciones Laravel en todas las instancias. |
| **Migraciones** | Un único árbol `database/migrations`; versión de schema homogénea; datos **nunca** compartidos entre clientes al nivel aplicación. |

### 4.2 Esquema

- **Sin columna `tenant_id` obligatoria** en tablas actuales (`middleware_registered_modules`, `bus_queue_entries`, feeds, etc.) en el modelo **instancia por cliente**.
- Si más adelante se migrara a multi-tenant, recién entonces se evaluaría `tenant_id` o RLS; **fuera de alcance** de este blueprint.

**Backup / DR:** políticas por instancia; blast radius acotado.

---

## 5. Middleware + Dashboard

### 5.1 Reflejo de “múltiples clientes”

| Realidad del modelo | Implicación en UI |
|--------------------|-------------------|
| Una instancia = un cliente | **`/middleware` y `/dashboard` muestran solo ese cliente.** No se requiere selector de tenant en la UI. |
| “Varios clientes” para el proveedor SaaS | Son **varias URLs / varios despliegues**; el equipo interno usa **una consola de ops** (externa al repo actual) que lista instancias y enlaces, **o** varios bookmarks. |

### 5.2 Evolución incremental (opcional)

- **Panel interno “fleet”** (futuro): inventario de instancias, versión desplegada, último `sync-config`, estado del bus — **no** sustituye al Middleware por instancia.
- **Branding:** `APP_NAME` / tema por cliente vía env sin tocar el núcleo del bus.

---

## 6. Flujo de ejecución (vista end-to-end)

```text
Pipeline de despliegue (por cliente)
    │
    ├─► Inyecta .env + modules_config.json + ajustes eventbus/registrars
    │
    ▼
Arranque PHP (una instancia)
    │
    ├─► config/modules.php carga el JSON del cliente
    ├─► EventBusIntegrationServiceProvider merge (Fase C) en config subscriptions
    │
    ▼
Operador / smoke
    │
    ├─► POST /api/middleware/registry/sync-config  (Fase B.2)
    ├─► POST /api/middleware/events/publish (pruebas)
    │
    ▼
Usuarios del cliente
    │
    └─► /middleware  +  /dashboard  (vista única cliente)
```

---

## 7. Decisiones técnicas clave (resumen)

1. **Tenant = despliegue**, no fila en base dentro de la app monolítica actual.  
2. **Config por instancia:** JSON + env + lista C acotada al contrato.  
3. **Eventos:** convención de nombres + `origin` para integraciones y observabilidad federada futura.  
4. **BD:** dedicada, mismo schema; sin partición lógica obligatoria en tablas.  
5. **UI:** sin multi-tenant en pantalla; escala **horizontal de instancias**.

---

## 8. Qué se mantiene vs qué evoluciona

| Se mantiene | Evoluciona (fuera del core si es posible) |
|-------------|-------------------------------------------|
| Contrato B.2 (sync dual fuente), C (interface + merger + provider) | Procesos de GitOps / CI por cliente |
| `eventbus.php` como base, merge en boot | Cantidad de artefactos y secretos por despliegue |
| APIs actuales de middleware y dashboard | Runbook y plantillas de “alta de cliente” |
| Esquema único de migraciones | Monitoreo agregado “fleet” (producto aparte) |

---

## 9. Nivel de esfuerzo (orden de magnitud)

| Iniciativa | Esfuerzo |
|------------|----------|
| Primera instancia productiva cliente (env + JSON + deploy + sync) | **Bajo–medio** (sobre todo operación) |
| Parametrizar pipeline reutilizable (mismo imagen, configs distintas) | **Medio** |
| Panel fleet + auditoría central | **Medio–alto** (nuevo productoh) |

---

## 10. Nota si en el futuro se desviara a multi-tenant

Este blueprint **no** aplica tal cual: haría falta tenant resolution, suscripciones por tenant, `tenant_id` en datos y tests de no fuga. La estrategia instancia-por-cliente **reduce** el acoplamiento hasta que el negocio exija densidad multi-tenant.

---

*Blueprint Fase D — instancia por cliente; base para implementación incremental sin romper B.2 ni C.*
