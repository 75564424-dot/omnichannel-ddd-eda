# ADR-011: Routing Amigable Multi-Tenant — Migración desde Puertos

**Estado:** Aceptado  
**Fecha:** 2026-06-02  
**Decisores:** Arquitectura de plataforma  
**Runbook relacionado:** `docs/Plan_Desarrollo_Serviciov1.6/Runbook_v1.6_Estabilizacion_Operativa_y_Routing_MultiTenant.md`  
**ADR relacionado:** [ADR-001](ADR_001_instancia_por_cliente.md), [ADR-010](ADR_010_tenant_lifecycle_management.md)  
**Fase:** v1.6 Fases 6–7

---

## Contexto

### Routing actual (por puertos)

El sistema de desarrollo local implementa **una instancia Laravel por cliente** (ADR-001). Cada silo tiene su propio proceso PHP con una URL basada en puerto:

```
http://127.0.0.1:8000   → Control plane (gestión SaaS)
http://127.0.0.1:8001   → Silo Acme Retail     (PLATFORM_CLIENT_SLUG=acme-retail)
http://127.0.0.1:8002   → Silo Pruebas Retail  (PLATFORM_CLIENT_SLUG=pruebas-retail)
http://127.0.0.1:8003   → Silo Lifecycle Test  (PLATFORM_CLIENT_SLUG=lifecycle-test)
```

Cada silo conoce su identidad por `PLATFORM_CLIENT_SLUG` en su propio `.env`. La sesión es independiente por proceso.

### Problema operativo

Las URLs basadas en puertos son inviables en producción:
- Requieren conocer el número de puerto de cada cliente
- Son incompatibles con balanceadores de carga estándar (HTTP/80, HTTPS/443)
- Dificultan la generación de links, redirecciones y configuración de cookies

### Infraestructura existente relevante

La codebase ya tiene los ganchos necesarios para routing multi-tenant:

| Componente | Ubicación | Función |
|---|---|---|
| `allowsMultiTenantPortalLogin()` | `DatabaseInstanceTenantContext` | Flag para resolver tenant por sesión (no por PLATFORM_CLIENT_SLUG) |
| `bindPortalTenantFromSession()` | `DatabaseInstanceTenantContext` | Fija `portal_tenant_id` en sesión |
| `portal_tenant_id` | Session | Tenant activo en modo multi-tenant |
| `PLATFORM_INSTANCE_URL_TEMPLATE` | `config/platform.php` | Template de URL para subdominios |
| `EnsureTenantOperationalStatus` | Middleware global | Bloquea acceso si tenant suspendido |
| `EnsureControlPlaneHost` | Middleware de ruta | Solo permite `/control/*` en el CP |

---

## Decisión

### Modelo de migración en dos etapas

#### Etapa 1 — Routing por ruta (Estado intermedio, Fase 7 de v1.6)

URL objetivo: `http://127.0.0.1:8000/{slug}/login`

El control plane actúa como **reverse proxy lógico**: recibe la petición, extrae el slug de la ruta, resuelve el tenant, y sirve la vista correspondiente cargando el contexto del silo destino.

**No rompe el routing por puertos:** los silos siguen funcionando en sus puertos individuales. Las rutas `/{slug}/*` son un overlay adicional.

**No implementa instancia compartida:** cada silo sigue siendo una instancia Laravel independiente con su propio proceso y BD. El routing path-based es solo presentación.

#### Etapa 2 — Routing por subdominio (Estado futuro, fuera de v1.6)

URL objetivo: `https://{slug}.middleware.example.com/login`

Requiere DNS y configuración de servidor web (nginx/caddy). Usa `PLATFORM_INSTANCE_URL_TEMPLATE` para generar URLs.

---

## Diseño de la Etapa 1 — Routing por ruta

### 1. Nuevo middleware: `ResolveTenantFromRoutePath`

Responsabilidades:
1. Extraer `{slug}` del segmento de ruta
2. Verificar que el tenant existe en la tabla `tenants` del CP
3. Verificar que el tenant tiene un silo provisionado (`settings.deployment.local_instance`)
4. Verificar que el tenant está `active` (no suspendido)
5. Llamar a `DatabaseInstanceTenantContext::bindPortalTenantFromSession($tenantId)` para fijar el contexto
6. Proceder con la request hacia el silo correspondiente

Contrato:
```php
namespace App\Http\Middleware;

final class ResolveTenantFromRoutePath
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('tenant_slug'); // parámetro de ruta
        $tenant = TenantModel::query()->where('slug', $slug)->first();

        if ($tenant === null) {
            abort(404, "Tenant '{$slug}' no encontrado.");
        }

        if ($tenant->status !== 'active') {
            abort(503, "El servicio de '{$slug}' está temporalmente suspendido.");
        }

        $localInstance = $tenant->settings['deployment']['local_instance'] ?? null;
        if ($localInstance === null) {
            abort(503, "El silo de '{$slug}' no está disponible.");
        }

        // Fijar contexto de tenant en sesión
        app(InstanceTenantContextInterface::class)->bindPortalTenantFromSession($tenant->id);

        // Pasar el app_url del silo para redirect correcto (opcional, ver §Estrategia de proxy)
        $request->attributes->set('resolved_tenant', $tenant);
        $request->attributes->set('resolved_silo_url', $localInstance['app_url']);

        return $next($request);
    }
}
```

### 2. Grupo de rutas `/{tenant_slug}/*` en el control plane

```php
// routes/tenant_portal.php — solo activo cuando PLATFORM_FRIENDLY_ROUTING=true y control plane
Route::prefix('{tenant_slug}')
    ->middleware(['control.plane', 'web', 'tenant.path.resolver'])
    ->group(function (): void {
        Route::get('/login', [TenantPortalProxyController::class, 'login'])
            ->name('tenant.login');
        Route::post('/login', [TenantPortalProxyController::class, 'authenticate'])
            ->name('tenant.login.store');
        Route::get('/dashboard', [TenantPortalProxyController::class, 'dashboard'])
            ->name('tenant.dashboard');
        Route::get('/middleware', [TenantPortalProxyController::class, 'middleware'])
            ->name('tenant.middleware');
    });
```

### 3. Feature flag de activación

```ini
PLATFORM_FRIENDLY_ROUTING=false   # false: solo routing por puertos (estado actual)
                                   # true: habilita rutas /{tenant}/* en el CP
```

Config en `config/platform.php`:
```php
'friendly_routing' => filter_var(
    env('PLATFORM_FRIENDLY_ROUTING', false),
    FILTER_VALIDATE_BOOLEAN,
),
```

El flag asegura que el runtime actual (Fases 0-5) NO SE VE AFECTADO hasta que se active en Fase 7.

### 4. Estrategia de proxy: redirección vs. proxy HTTP

Dos opciones para servir el contenido del silo cuando se accede desde `/acme-retail/login`:

#### Opción A — Redirección al puerto del silo (recomendada para v1.6)
El control plane extrae el `app_url` del silo (`http://127.0.0.1:8001`) y devuelve un redirect 302:

```php
return redirect($siloUrl . '/' . $path, 302);
```

**Pros:** Sin cambios en silos, sin proxy HTTP, 100% compatible. El silo maneja la request con su propio contexto.  
**Contras:** La URL final en el browser muestra el puerto. Aceptable para el estado intermedio (desarrollo local).

#### Opción B — Proxy transparente (estado futuro / Fase 7+ avanzada)
El control plane hace una petición HTTP interna al silo y retransmite la respuesta. Requiere gestión de cookies, CSRF y sesiones cross-process.

**Pros:** La URL en el browser permanece como `http://127.0.0.1:8000/{slug}/...`  
**Contras:** Complejidad alta, sesiones y CSRF compartidos, conflictos de Inertia headers.

**Decisión para Fase 7:** implementar Opción A (redirección). La URL amigable en el browser es la prioridad de Fases 6-7 (Opción B es post-v1.6).

### 5. Aislamiento de sesiones

Con la Opción A (redirección), la sesión de cada silo sigue siendo independiente:
- Cada silo tiene su propia cookie (`platform_session_acme_retail`, `platform_session_pruebas_retail`, etc.)
- El CP tiene su propia cookie (`platform_session_platform`)
- No hay conflicto entre sesiones

Con la Opción B (proxy), se requeriría:
- Session cookie con path-scoping por tenant (`SESSION_COOKIE=platform_session_{slug}`)
- O sesiones separadas por sub-path

### 6. CSRF

Con Opción A: cada silo gestiona su propio CSRF. El redirect lleva al formulario del silo que genera su propio token. Sin cambios.

---

## Diseño de la Etapa 2 — Routing por subdominio (Post-v1.6)

URL: `https://acme-retail.middleware.example.com/login`

### Middleware `ResolveTenantFromSubdomain`

```php
$host = $request->getHost(); // acme-retail.middleware.example.com
$baseDomain = config('platform.deployment.base_domain'); // middleware.example.com
$slug = Str::before($host, '.' . $baseDomain);

$tenant = TenantModel::query()->where('slug', $slug)->first();
// ... misma lógica de resolución que ResolveTenantFromRoutePath
```

### Nuevo parámetro de config

```ini
PLATFORM_INSTANCE_BASE_DOMAIN=middleware.example.com
```

Ya existe `PLATFORM_INSTANCE_URL_TEMPLATE=https://{slug}.middleware.example.com` que sirve como referencia para generación de links en la UI del CP.

### Estrategia de proxy para subdominios

Con subdominios, la Opción B (proxy transparente) se vuelve más viable porque:
- Cada silo tiene un subdominio propio (no se comparten paths)
- Las session cookies tienen domain scope automático
- No hay conflicto entre tenants en el mismo dominio

Sin embargo, esto requiere un servidor web (nginx) que actúe de reverse proxy y routee el tráfico por subdominio. En desarrollo local, requiere modificaciones en `/etc/hosts` o `dnsmasq`. Fuera de alcance de v1.6.

---

## Alternativas rechazadas

| Alternativa | Motivo de rechazo |
|---|---|
| Proxy HTTP transparente en Fase 7 | Complejidad alta de sesiones y CSRF compartidas; no aporta valor inmediato para desarrollo local |
| Modificar silos para aceptar rutas `/{slug}/*` | Requiere cambiar cada silo independientemente; viola el principio de instancia aislada |
| Routing por query param (`?tenant=acme-retail`) | URLs no amigables, fácilmente manipulables, sin semántica REST |
| Eliminar routing por puertos | Breaking change; silos en producción dependen de su propio puerto y APP_URL |

---

## Impacto en componentes existentes

| Componente | Impacto | Acción requerida |
|---|---|---|
| `DatabaseInstanceTenantContext` | Sin cambios en lógica core | Nueva ruta llama a `bindPortalTenantFromSession` explícitamente |
| `AuthenticateOperatorUseCase` | Sin cambios | Recibe `portal_tenant_id` ya seteado en sesión por el resolver |
| `EnsureTenantOperationalStatus` | Sin cambios | Ya bloquea si tenant suspendido |
| `EnsureControlPlaneHost` | Sin cambios | Rutas `/{slug}/*` están bajo middleware `control.plane` |
| `LocalFleetRegistry` | Sin cambios | Se lee para obtener `app_url` del silo destino |
| Rutas por puerto (8001, 8002, 8003) | Sin cambios | Siguen funcionando exactamente igual |
| Tests existentes | Sin cambios | Todos usan rutas directas sin slug prefix |

---

## Consecuencias

### Positivas
- URLs amigables sin romper el modelo de instancia física por cliente
- Activación opcional mediante feature flag (`PLATFORM_FRIENDLY_ROUTING`)
- Aprovecha infraestructura existente (`portal_tenant_id`, `bindPortalTenantFromSession`)
- Ruta de evolución clara hacia subdominios

### Negativas
- Con Opción A (redirect), la URL final muestra el puerto del silo en el browser (aceptable en dev local)
- El feature flag requiere gestión activa para no dejar rutas huérfanas
- Cada nueva ruta de silo requiere una entrada espejo en `routes/tenant_portal.php`

---

## Configuración de activación (v1.6 Fase 7)

```ini
# .env.control-plane
PLATFORM_FRIENDLY_ROUTING=true
PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true   # requerido para que el resolver funcione
```

---

## Plan de migración (enlace con Fase 7)

Ver `Plan_Migracion_Routing_v1.6.md` en este mismo directorio.

---

## Referencias

- `app/Shared/Platform/DatabaseInstanceTenantContext.php`
- `app/Http/Middleware/EnsureInstancePortalAccess.php`
- `app/Http/Middleware/EnsureControlPlaneHost.php`
- `config/platform.php` § `multi_tenant_portal_login`, `deployment.app_url_template`
- `deploy/local-instances/fleet-registry.json`
- [ADR-001](ADR_001_instancia_por_cliente.md)
- [ADR-010](ADR_010_tenant_lifecycle_management.md)
- [Runbook v1.6](../Plan_Desarrollo_Serviciov1.6/Runbook_v1.6_Estabilizacion_Operativa_y_Routing_MultiTenant.md) §Problema 5
