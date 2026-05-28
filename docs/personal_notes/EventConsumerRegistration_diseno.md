# Diseño — Integración `EventConsumerRegistrationInterface`

**Audiencia:** implementación de packs/cliente sin tocar el núcleo de la plataforma.  
**Restricciones respetadas:** no sustituye `config/eventbus.php`; no invalida B.2 (sync sigue leyendo `config('eventbus.*')` tras el merge en runtime); sin frameworks externos; simplicidad.

**Contrato actual:**

```php
// App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface
public static function subscriptionCatalog(): array;
// @return array<string, list<array{module:string, listener:class-string, queue:string}>>
```

---

## 1. Diseño técnico

### 1.1 Cómo se descubren las clases

**Recomendación:** lista **explícita** de FQCN en configuración, en lugar de escanear el filesystem o el autoload.

- **Motivo:** determinista, rápido en `boot`, compatible con OPcache y con paquetes Composer opcionales (el pack puede hacer `mergeConfigFrom` sobre una clave compartida).
- **Clave sugerida:** `config/eventbus.php` → nuevo array `'consumer_registrars' => []` **o** archivo dedicado `config/eventbus_registrars.php` devuelto y combinado en el provider (evita tocar demasiadas claves del eventbus si se prefiere separar).

**Flujo de descubrimiento:**

1. Al arrancar la app, el provider lee la lista de clases.
2. Por cada clase: `is_subclass_of($class, EventConsumerRegistrationInterface::class)` (o `class_implements`) y método estático `subscriptionCatalog()`.
3. Si falla la validación → **log + omitir esa clase** (no tumbar la aplicación entera por un pack mal cargado).

**Alternativa opcional (fase posterior):** convención de namespace `App\Packs\*\...` + **solo si** el equipo acepta un comando `php artisan eventbus:discover` que vuelque FQCN en caché/config generado (sigue siendo explícito en runtime).

### 1.2 Dónde se registran (provider)

**Recomendación:** `App\Providers\EventBusIntegrationServiceProvider` (o `PackEventBusServiceProvider`) **registrado en `bootstrap/providers.php`** (o `config/app.php` según versión Laravel del proyecto) **después** de los providers que registran el contenedor del middleware, pero el merge de config debe ocurrir en **`boot()`** lo antes posible.

- **Por qué no solo `AppServiceProvider`:** separa la política “host core” de la “extensión por packs”, orden de `boot` más claro y archivos más pequeños.
- **Por qué sí un provider (y no solo un `composer` script):** Laravel ya centraliza el ciclo de vida; el mismo punto sirve para **fusionar `eventbus.subscriptions`** y para **`Event::listen`**.

Orden sugerido:

1. `register()` del provider de integración: bindings livianos si hace falta.
2. `boot()` temprano:
   - construir catálogo fusionado;
   - `config()->set('eventbus.subscriptions', $merged)`;
   - registrar listeners con `Event::listen`.

`SubscriptionRegistryService` ya lee `config()` en cada llamada; el merge en `boot()` es coherente con publicación HTTP y con **B.2** (`sync-config` verá suscripciones ya fusionadas al ejecutarse en una petición posterior).

### 1.3 Cómo se hace el merge

**Fuente base (sin reemplazar archivo):**

- Partir de `config('eventbus.subscriptions')` tal como viene de `config/eventbus.php` (commits del core).

**Fuente de packs:**

- Para cada `subscriptionCatalog()` devuelto: clave = **string** `event_type`, valor = lista de filas `{ module, listener, queue }`.

**Fusión en `eventbus.subscriptions`:**

La forma **esperada hoy** por `SubscriptionRegistryService` es:

```php
$eventType => [
    ['module' => 'NombreModulo'],
    // ...
]
```

**Propuesta de merge:**

1. Normalizar cada fila del pack a la forma persistida por el core, **conservando** metadatos extra si en el futuro se amplía el esquema; como mínimo:
   - `module` (obligatorio para cola y topology).
   - Opcional en runtime: `listener`, `queue` pueden quedar en la misma fila como claves adicionales **si** el código del core ignora claves desconocidas con `array_column(..., 'module')` — hoy solo usa `module`, así que es seguro.
2. Para cada `event_type`:
   - `$base = $subscriptions[$eventType] ?? [];`
   - `$incoming = ... filas del pack;`
   - **Dedup:** misma pareja `(module, listener)` o, si `listener` vacío en archivo legacy, solo `module` para no duplicar entradas en cola.
   - `$subscriptions[$eventType] = array_values(deduped_merge($base, $incoming));`

3. `config()->set('eventbus.subscriptions', $subscriptions);`

**Producers:** el contrato actual **solo** cubre suscripciones. Los productores del pack siguen pudiendo declararse en `eventbus.php` o en un segundo método estático futuro; fuera del alcance mínimo.

### 1.4 Registro de listeners en el bus Laravel

Además del merge de config:

- Por cada fila con `listener` resolvible:
  - `Event::listen($eventType, [$listenerClass, 'handle'])` **o** `Event::listen($eventType, $listenerClass)` si el proyecto estandariza invocables.
- **Cola (`queue`):** si el string no es `sync` y el listener implementa `ShouldQueue`, alinear con `config('eventbus.queues')` o documentar que el pack usa la conexión por defecto. Opción simple inicial: ignorar `queue` en v1 y ejecutar síncronos como hoy el core en listeners de plataforma; en v2 mapear explícitamente.

**Relación con listeners existentes:** `BusTrackingListener` / `ModuleObservationListener` están en wildcard `*`; los listeners de negocio del pack son **adicionales** por `event_type` y no los sustituyen.

---

## 2. Flujo de ejecución

```text
php-fpm / artisan / queue-worker
        │
        ▼
bootstrap providers
        │
        ├─► MiddlewareServiceProvider::register (SubscriptionRegistryService, etc.)
        │
        ▼
EventBusIntegrationServiceProvider::boot
        │
        ├─► Leer lista FQCN + subscriptionCatalog() de cada pack
        ├─► Merge determinístico → config()->set('eventbus.subscriptions', …)
        ├─► Event::listen(eventType, listener) por cada fila válida
        │
        ▼
HTTP request / job
        │
        ├─► POST /events/publish → EventPublisherService → config('eventbus.subscriptions') [ya fusionado]
        │
        ├─► POST /registry/sync-config → SyncConfiguredModulesToRegistryUseCase
        │       • eventbus (incl. merge packs)
        │       • modules.catalog (B.2)
        │       → middleware_registered_modules
        │
        └─► GET /api/middleware/topology, /middleware UI → reflejan módulos + tráfico
```

**Paréntesis operativo:** tras desplegar un pack nuevo, si el registry debe reflejar módulos solo vía archivo, el operador sigue ejecutando `sync-config`; con el merge activo, **las suscripciones ya están en config en runtime**, así que cola y topology técnica pueden alinearse sin editar `eventbus.php` a mano (salvo política del equipo de exigir también commit en archivo para auditoría).

---

## 3. Decisiones clave

| Decisión | Razón |
|----------|--------|
| **Provider dedicado** | Orden de `boot` explícito, responsabilidad única, menor riesgo de mezclar con `AppServiceProvider` del host. |
| **Lista explícita de FQCN** | Sin escaneo; comportamiento predecible en producción; packs opcionales no rompen el arranque si no están en la lista. |
| **Dedup por `(event_type, module)` + `listener`** | Evita filas duplicadas en `subscriptions` y registros dobles de `Event::listen`. |
| **Errores: fail-soft por pack** | Un pack defectuoso no debe caer toda la plataforma; registrar `Log::warning` / `report()` con contexto (`class`, `event_type`). |
| **No reemplazar `eventbus.php`** | Sigue siendo la “línea base” versionada; el merge es **aditivo** en runtime (y opcionalmente exportable a archivo en pipeline si se requiere trazabilidad). |

---

## 4. Riesgos

| Riesgo | Mitigación |
|--------|------------|
| **Sobrecarga en `boot`** | Listas cortas; catálogos estáticos; sin reflection pesada; evitar I/O. |
| **Conflictos de nombres (`module`)** | Documentar prefijos por pack (`ClientAcme.Orders`); dedup no elimina dos módulos distintos con mismo nombre si el operador los declaró así — convención y code review. |
| **Orden de carga** | Documentar que `EventBusIntegrationServiceProvider` debe ejecutarse después de que exista la config base; antes de listeners que se disparan en el mismo ciclo de boot poco frecuente — en la práctica, publicar ocurre tras boot completo. |
| **Doble registro de `Event::listen`** | Guard interno en provider (array `$registeredListeners`) o comprobar antes de registrar. |
| **config:cache** | Si se usa `php artisan config:cache`, el merge en `boot()` **sí** se aplica al runtime en workers que arrancan de nuevo; valores solo-cacheados del archivo pueden quedar desactualizados respecto al merge — equipo debe saber que los packs dependen del provider de merge, no solo del PHP estático cacheado. |
| **Divergencia archivo vs runtime** | Quien audita solo `eventbus.php` no ve packs; mitigar con export/documentación o tests que aserten `config('eventbus.subscriptions')` tras boot. |

---

## 5. Contrato para implementadores de pack (resumen)

1. Clase en el paquete del cliente: `implements EventConsumerRegistrationInterface` (o trait + clase final estática según estilo del equipo; el contrato exige `static subscriptionCatalog()`).
2. Devolver solo entradas válidas: `event_type` no vacío, `module` y `listener` con clase existente.
3. Registrar el FQCN en la lista de descubrimiento del host (config merge o PR en el repo del cliente).
4. Opcional: `ServiceProvider` del pack que registre bindings propios **antes** del boot del bus si los listeners tienen dependencias.

---

## 6. Relación con B.2

- **Sync ampliado** sigue leyendo `eventbus.subscriptions` **vía `config()`** y `modules.catalog`.  
- Tras el merge de packs en `boot()`, **ambos caminos** ven las mismas suscripciones en memoria para esa ejecución.  
- No se requiere cambio en `SyncConfiguredModulesToRegistryUseCase` para “soportar packs” si el merge ocurre antes del primer uso.

---

*Diseño orientado a implementación en Laravel — listo para bajar a tareas de fase C (provider, merge, tests de integración, actualización de plan de implementación).*
