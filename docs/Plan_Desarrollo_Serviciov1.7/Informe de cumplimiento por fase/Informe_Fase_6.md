# Informe Fase 6 — Validación Friendly Routing

## Estado
**Cumple**

## Objetivo
Certificar que el routing amigable (ADR-011) funciona en el control plane con tenants `tenant-test-*` creados post-limpieza, validando redirecciones 302, guards de estado, rutas exactas no capturadas, y distinción entre redirect vs operatividad del silo.

## Evidencia encontrada

### Componentes analizados
| Componente | Rol |
|---|---|
| `routes/tenant_portal.php` | Rutas `GET /{tenant_slug}/*` bajo `control.plane` + `tenant.path.resolver` |
| `ResolveTenantFromRoutePath` | Valida flag, slug, status, `local_instance.app_url` |
| `TenantPortalProxyController` | Emite 302 al `app_url` del silo |
| `config/platform.php` | Key `friendly_routing` ← `PLATFORM_FRIENDLY_ROUTING` |
| `bootstrap/app.php` | `tenant_portal.php` registrado al final (wildcard no sombrea rutas exactas) |
| `tests/Feature/Control/TenantPortalRoutingTest.php` | Cobertura automatizada ADR-011 |

### Hallazgo heredado (Fases 2–3)
- `.env.control-plane` **no contenía** `PLATFORM_FRIENDLY_ROUTING=true` → `config('platform.friendly_routing')` resolvía `false` y las rutas amigables retornaban 404.
- Clasificación: **Configuración incorrecta** (no bug de código ni legacy).

### Tenants de certificación HTTP (solo `tenant-test-*`)
| Slug | Puerto silo | `app_url` | Lifecycle |
|---|---|---|---|
| `tenant-test-routing` | 8002 | `http://127.0.0.1:8002` | running |
| `tenant-test-branding` | 8001 | `http://127.0.0.1:8001` | running |
| `tenant-test-simulation` | 8003 | `http://127.0.0.1:8003` | running |

Tenant auxiliar para guard `503 sin app_url`: `platform` (self-tenant CP, `app_url=MISSING`).

### Matriz de validación HTTP (CP `:8000`, flag activo)

| Requisito Runbook | Resultado | Evidencia |
|---|---|---|
| `PLATFORM_FRIENDLY_ROUTING=true` solo en CP | Cumple | `.env.control-plane`; client `tenant-test-*` sin flag |
| `GET /tenant-test-routing/login` → 302 | Cumple | `code=302 location=http://127.0.0.1:8002/login` |
| Destino `/login` del silo → 200 | Cumple | `http://127.0.0.1:8002/login` → 200 |
| `GET /tenant-test-routing` → `/login` silo | Cumple | 302 → `http://127.0.0.1:8002/login` |
| Path anidado `/dashboard` | Cumple | 302 → `http://127.0.0.1:8002/dashboard` |
| Tenant inexistente | Cumple | `/tenant-does-not-exist-xyz/login` → 404 |
| Tenant suspendido | Cumple | `tenant-test-branding` suspendido temporalmente → 503; restaurado a `active` |
| Tenant sin `app_url` | Cumple | `/platform/login` → 503 |
| Silo detenido: 302 sin operatividad | Cumple | CP 302 → `:8003/login`; destino `code=0` (conexión rechazada) |
| `/control/*` no capturado | Cumple | `/control/companies` → 302 a `/login` CP (no wildcard tenant) |
| `/health/ready` no capturado | Cumple | → 200 |
| `/up` no capturado | Cumple | → 200 |
| `/login` CP no capturado | Cumple | → 200 |

### Pruebas automatizadas
```
Tests\Feature\Control\TenantPortalRoutingTest — 9 passed (14 assertions)
php artisan config:show platform.friendly_routing --env=control-plane → true
```

### Health post-validación
```
/up → 200 en :8000, :8001, :8002, :8003
```

## Cambios realizados
1. Activación de `PLATFORM_FRIENDLY_ROUTING=true` en `.env.control-plane` (requisito explícito del Runbook para habilitar certificación).
2. Reinicio de flota local (`npm run instances:serve`) para cargar el flag en el proceso CP.
3. Validación HTTP exhaustiva sobre `tenant-test-routing` y escenarios negativos/operativos.
4. Suspensión temporal de `tenant-test-branding` para probar guard 503 (restaurado inmediatamente a `active`).
5. Detención controlada del silo `:8003` para demostrar que 302 ≠ operatividad; reinicio de flota posterior.

**No se modificó código de aplicación.** Los componentes ADR-011 implementados en v1.6 funcionan correctamente una vez activada la configuración.

## Archivos modificados
- `.env.control-plane` — añadido `PLATFORM_FRIENDLY_ROUTING=true`

## Archivos nuevos
- `docs/Plan_Desarrollo_Serviciov1.7/informe de cumplimiento por fase/Informe_Fase_6.md`

## Riesgos detectados
- **`instances:bootstrap` no persiste el flag**: `scripts/local-instances/lib.mjs` no escribe `PLATFORM_FRIENDLY_ROUTING` al regenerar `.env.control-plane`; un bootstrap futuro podría desactivar routing amigable silenciosamente.
- **`instances:serve` termina si un hijo muere**: al detener `:8003` para el escenario stopped-silo, el supervisor Node finalizó toda la flota (comportamiento operativo conocido).
- **Redirect 302 no implica silo operativo**: documentado y demostrado; el CP no valida reachability del destino.

## Riesgos mitigados
- Flag activado y verificado en runtime (`config:show` → `true`).
- Silo `tenant-test-routing` levantado antes de certificar URL amigable.
- Ninguna conclusión basada en tenants prohibidos (`acme-retail`, `pruebas-retail`, `lifecycle-test`, etc.).

## Hallazgos clasificados

### Legacy
- Ninguno. Routing amigable validado exclusivamente con `tenant-test-*` post-Fase 1/2.

### Bug Real
- Ninguno detectado en esta fase. El fallo previo (404 en rutas amigables) era ausencia de configuración, no defecto en `ResolveTenantFromRoutePath` ni `TenantPortalProxyController`.

### Configuración
1. **`PLATFORM_FRIENDLY_ROUTING` ausente en `.env.control-plane`** — corregido activando `true` según Runbook.

### Operativo
1. Flota con listeners duplicados previos a reinicio limpio (procesos huérfanos de sesiones anteriores).
2. `instances:serve` cascada al morir un hijo durante prueba stopped-silo.
3. Suspensión/restauración temporal de `tenant-test-branding` para evidencia de guard 503 (manipulación de estado solo para validación, no como solución).

## Control de Alcance — Trabajo perteneciente a otras fases
| Trabajo fuera de alcance | Fase | Impacto |
|---|---|---|
| Persistir flag en `lib.mjs` / bootstrap | Mejora operativa (no bloqueante) | Re-bootstrap podría resetear flag |
| Elegibilidad simulación | Fase 7 | No evaluado |
| Pruebas integrales E2E | Fase 8 | No evaluado |
| Corrección Tenant Identity | Fase 5 | Ya cumplida; sin regresión detectada |

## Checklist del Runbook
| Requisito (Fase 6) | Estado | Evidencia |
|---|---|---|
| Activar `PLATFORM_FRIENDLY_ROUTING=true` solo en CP | Cumple | `.env.control-plane` |
| Client `.env` sin flag | Cumple | Grep en `.env.client-tenant-test-*` |
| Silo levantado antes de certificar | Cumple | `lifecycle=running`, `/up` 200 en :8002 |
| `GET /tenant-test-routing/login` → 302 a `app_url` | Cumple | HTTP validation |
| Destino `/login` → 200 | Cumple | `curl :8002/login` |
| Root `GET /tenant-test-routing` → login silo | Cumple | 302 → `:8002/login` |
| Path anidado redirige correctamente | Cumple | `/dashboard` → `:8002/dashboard` |
| Tenant inexistente → 404 | Cumple | slug sintético |
| Tenant suspendido → 503 | Cumple | `tenant-test-branding` suspendido |
| Sin `app_url` → 503 | Cumple | `/platform/login` |
| Provisionado sin levantar: 302 ≠ operativo | Cumple | `:8003` detenido, dest `code=0` |
| Rutas exactas no capturadas | Cumple | `/control/*`, `/health/ready`, `/up`, `/login` |
| **CA**: Routing amigable en tenants nuevos | Cumple | `tenant-test-routing` |
| **CA**: Sin evidencia en tenants prohibidos | Cumple | Solo `tenant-test-*` |

## Compatibilidad Retroactiva
- **Lifecycle sigue funcionando**: silos en `running`; reinicio de flota exitoso tras pruebas.
- **Provisioning sigue funcionando**: metadata `app_url` intacta en los 3 `tenant-test-*`; sin data fix.
- **Routing sigue funcionando**: silos accesibles por puerto (`:8001-8003` → `/up` 200); overlay amigable operativo en CP.
- **Simulación sigue funcionando**: no se ejecutaron runs; servicios intactos.
- **Control Plane sigue funcionando**: `/up` 200, rutas `/control/*` y guards operativos.
- **Branding / Tenant Identity (Fase 5)**: sin regresión; validación de identidad no re-ejecutada en profundidad (fuera de alcance Fase 6).

## Conclusión
La Fase 6 demuestra que el friendly routing funciona correctamente para `tenant-test-routing` y escenarios de guard con tenants `tenant-test-*` y `platform`, tras activar `PLATFORM_FRIENDLY_ROUTING=true` en el control plane. El único hallazgo fue **configuración ausente**, no bug de implementación. **Estado = Cumple**.

No se avanza automáticamente a la Fase 7; se espera nueva instrucción.
