# Fase C — Registro dinámico de consumidores (extensibilidad)

**Ámbito:** integración de packs sin editar el núcleo de la plataforma mediante `EventConsumerRegistrationInterface` y merge en runtime de `eventbus.subscriptions`.  
**Relacionado:** Fase B.2 (sync ampliado) sigue leyendo `config('eventbus.subscriptions')` **después** del merge; el archivo `config/eventbus.php` permanece como base versionada.

---

## 1. Problema inicial

Hasta B.2, las suscripciones in-process que alimentan cola, metadatos de consumidores y `sync-config` dependían de **`subscriptions` declaradas a mano** en `config/eventbus.php` (o de duplicar esa intención operativa). Cada nuevo consumidor de un pack “cliente” implicaba:

- tocar configuración central del host;
- riesgo de divergencia frente al catálogo declarativo (`modules_config.json`);
- fricción para equipos que distribuyen lógica en paquetes Composer aislados.

Faltaba un **contrato estable** y un **punto único de bootstrap** que materializara esas suscripciones sin ramificar el core por cada listener.

---

## 2. Solución

### Uso del interface

El contrato existente **`App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface`** define un único método estático:

- `subscriptionCatalog(): array`  
- Forma esperada: `event_type => [ ['module' => string, 'listener' => class-string, 'queue' => string], ... ]`  
  (campos opcionales según fila; `module` es el mínimo para alinear con el registry y la cola.)

Una clase por pack (o por bounded context) implementa el interface y centraliza **qué** eventos consume y **qué** clase Laravel escucha cada tipo.

### Registro automático

1. **`config('eventbus.consumer_registrars')`** — lista de FQCN a procesar (por defecto vacía; sin coste si no hay packs).
2. **`EventBusIntegrationServiceProvider`** (registrado en `bootstrap/providers.php`) en **`boot()`**:
   - instancia **`PackSubscriptionCatalogMerger`**;
   - fusiona cada `subscriptionCatalog()` con las `subscriptions` ya cargadas desde `eventbus.php`;
   - actualiza **`config()->set('eventbus.subscriptions', …)`**;
   - registra **`Event::listen($eventType, $listenerClass)`** cuando `listener` apunta a una clase existente.

No se reemplaza `eventbus.php`: el merge es **aditivo** y el archivo sigue siendo la línea base en repositorio.

---

## 3. Flujo

```text
Pack (Composer / código cliente)
    │
    │  Clase implements EventConsumerRegistrationInterface
    │  + FQCN listado en eventbus.consumer_registrars
    ▼
EventBusIntegrationServiceProvider::boot
    │
    ├─► PackSubscriptionCatalogMerger
    │         • valida clase / interface
    │         • merge seguro + dedup (module + listener por event_type)
    │         • config()->set('eventbus.subscriptions', …)
    │
    └─► Event::listen(evento, listener)
                │
                ▼
        Laravel Event dispatcher
                │
                ▼
        Middleware / publish / tracking
        (SubscriptionRegistryService, cola, topology, POST …/registry/sync-config)
```

**Cadena perceptible:** el pack aporta catálogo; el host materializa config y listeners; el middleware y las APIs existentes consumen la **misma** `config('eventbus.subscriptions')` unificada.

---

## 4. Beneficios

| Beneficio | Descripción |
|-----------|-------------|
| **Extensibilidad** | Nuevos consumidores se añaden con una clase + una entrada de configuración, sin abrir el core. |
| **Desacoplamiento** | El pack conoce sus tipos y listeners; el host solo aplica el contrato y el merge. |
| **Escalabilidad operativa** | Varios packs pueden coexistir; el merger deduplica filas equivalentes en un mismo arranque. |
| **Alineación con B.2** | `sync-config` y publicación ven suscripciones ya fusionadas en runtime, coherente con registry + JSON declarativo cuando el operador mantiene nombres/`event_type` alineados. |

---

## 5. Ejemplo — pack demo

**Ubicación de referencia en el repo:**

- `App\Platform\Demo\DemoPackEventConsumers` — implementa `subscriptionCatalog()` y declara el tipo `Platform.Demo.Pack` con módulo `DemoPack` y listener `DemoPackListener`.
- `App\Platform\Demo\DemoPackListener` — listener mínimo (`handle`) para pruebas locales.

**Activación (solo laboratorio):** en `config/eventbus.php`, añadir el FQCN a `consumer_registrars`:

```php
'consumer_registrars' => [
    App\Platform\Demo\DemoPackEventConsumers::class,
],
```

Tras `config:clear` si aplica y reinicio del proceso PHP, el bus tendrá la suscripción y el listener registrado sin editar manualmente el array `subscriptions` del mismo archivo.

---

## 6. Limitaciones

| Tema | Detalle |
|------|---------|
| **Orden de carga** | El merge ocurre en `boot()` del `EventBusIntegrationServiceProvider`. Debe ejecutarse en un momento en el que la aplicación ya haya cargado `config/eventbus.php`; el orden actual en `bootstrap/providers.php` (tras `AppServiceProvider`) es el esperado. Cambiar el orden sin análisis puede afectar otros `boot()` que lean suscripciones. |
| **`config:cache`** | Los valores cacheados solo reflejan el PHP estático; el merge en runtime sigue aplicándose al arrancar workers con bootstrap completo. Equipos que dependan solo del archivo cacheado para auditoría deben complementar con revisión de `consumer_registrars` o exportación documentada. |
| **Campo `queue` en el contrato** | La fila puede incluir `queue` con fines documentales o futuros; la integración actual **no** traduce automáticamente ese string a colas Laravel: los listeners pueden usar `ShouldQueue` y la configuración de colas estándar si se desea ejecución asíncrona. |
| **Conflictos de nombres** | Dos packs que usen el mismo `module` y `listener` para el mismo `event_type` se deduplican; dos `listener` distintos para el mismo módulo y evento generan dos filas (válido). Conflictos semánticos (mismo nombre de módulo, responsabilidades distintas) son convención de diseño, no los resuelve el merger. |
| **JSON inválido / packs rotos** | Clases inexistentes, interfaces incorrectas o `subscriptionCatalog()` que lanza excepción se omiten con log; no debe tumbar el arranque. Un pack mal configurado puede pasar desapercibido si no se revisan logs. |
| **Doble `boot` manual** | Llamar dos veces a la lógica de registro en el mismo ciclo de vida podría duplicar `Event::listen` para el mismo par evento/listener según cómo se invoque; el flujo normal es un solo `boot()` por petición/worker. |

---

## Referencias de código

| Concepto | Ruta |
|----------|------|
| Contrato | `app/Shared/Contracts/EventBus/EventConsumerRegistrationInterface.php` |
| Merger | `app/Shared/EventBus/PackSubscriptionCatalogMerger.php` |
| Provider | `app/Providers/EventBusIntegrationServiceProvider.php` |
| Config | `config/eventbus.php` → `consumer_registrars` |
| Registro de provider | `bootstrap/providers.php` |
| Demo | `app/Platform/Demo/` |

---

*Documento base para evolución modular (más packs, descubrimiento asistido, política de precedencia o exportación del catálogo efectivo).*
