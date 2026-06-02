# Informe Fase 6 — Diseño routing amigable

Estado: Cumple

## Objetivo
Definir el resolver de tenant por ruta y el plan de evolución hacia subdominios. Producir el ADR técnico y el plan de migración con decisiones aprobadas, sin alterar el runtime actual.

## Evidencia encontrada

### Routing actual (antes de Fase 6)

El sistema usa una instancia Laravel por cliente (ADR-001), con routing basado en puertos:

```
http://127.0.0.1:8000   → Control plane
http://127.0.0.1:8001   → Silo acme-retail
http://127.0.0.1:8002   → Silo pruebas-retail
http://127.0.0.1:8003   → Silo lifecycle-test
```

`PLATFORM_CLIENT_SLUG` en el `.env` de cada silo define su identidad. No existe ninguna ruta por slug ni por subdominio.

### Infraestructura existente aprovechable

| Componente | Archivo | Función relevante para el diseño |
|---|---|---|
| `allowsMultiTenantPortalLogin()` | `DatabaseInstanceTenantContext` | Flag para resolver tenant por sesión |
| `bindPortalTenantFromSession()` | `DatabaseInstanceTenantContext` | Fija `portal_tenant_id` en sesión |
| `PLATFORM_INSTANCE_URL_TEMPLATE` | `config/platform.php` | Template de URL para subdominios futuro |
| `EnsureTenantOperationalStatus` | `bootstrap/app.php` | Middleware global que bloquea acceso si suspendido |
| `EnsureControlPlaneHost` | Middleware alias `control.plane` | Restringe `/control/*` al CP |
| `LocalFleetRegistry` / `fleet-registry.json` | Fleet | Contiene `app_url` del silo destino |
| `settings.deployment.local_instance.app_url` | CP SQLite | URL del silo para redirect |

### Decisiones de diseño tomadas

1. **Estrategia de proxy: Opción A — Redirección (302) al puerto del silo.** El control plane extrae el slug, resuelve el `app_url` del silo y devuelve un redirect. La URL final en el browser mostrará el puerto (aceptable para desarrollo local). El proxy transparente (Opción B) queda diferido a versiones post-v1.6.

2. **Activación mediante feature flag `PLATFORM_FRIENDLY_ROUTING`.** El runtime actual NO se ve afectado hasta que se active. Por defecto es `false`.

3. **`PLATFORM_PORTAL_MULTI_TENANT_LOGIN=true` requerido en el CP.** El resolver llama a `bindPortalTenantFromSession()` que requiere este flag activo.

4. **Silos no se modifican.** Siguen siendo instancias independientes con su propio `PLATFORM_CLIENT_SLUG`. El routing amigable es un overlay en el control plane.

5. **Routing por subdominio = Etapa 2 (post-v1.6).** Usa `PLATFORM_INSTANCE_BASE_DOMAIN` y middleware `ResolveTenantFromSubdomain`. Requiere DNS/nginx. Fuera del alcance de v1.6.

## Cambios realizados

### Documentos nuevos creados

1. **`docs/production/ADR_011_friendly_routing_multitenant.md`**
   - Contexto y problema (routing por puertos)
   - Decisión: migración en dos etapas (ruta → subdominio)
   - Diseño del middleware `ResolveTenantFromRoutePath`
   - Diseño del grupo de rutas `/{tenant_slug}/*`
   - Estrategia de proxy (Opción A — redirect)
   - Aislamiento de sesiones
   - Etapa 2 (subdominios, post-v1.6)
   - Alternativas rechazadas
   - Impacto en componentes existentes
   - Consecuencias

2. **`docs/Plan_Desarrollo_Serviciov1.6/Plan_Migracion_Routing_v1.6.md`**
   - Estado actual vs estado objetivo
   - Etapa 1 — 6 pasos detallados de implementación para Fase 7
   - Etapa 2 — Plan de subdominios (post-v1.6)
   - Tabla de compatibilidad retroactiva (todos los componentes: No afectado)
   - Feature flags requeridos en `.env.control-plane`
   - Criterios de aceptación verificables para Fase 7
   - Diagrama de flujo del proceso de redirección

## Archivos modificados

Ninguno (Fase 6 es diseño puro — sin cambios en código ni runtime).

## Archivos nuevos

- `docs/production/ADR_011_friendly_routing_multitenant.md`
- `docs/Plan_Desarrollo_Serviciov1.6/Plan_Migracion_Routing_v1.6.md`

## Riesgos detectados

1. **Aislamiento de sesiones con proxy transparente (Opción B):** si en el futuro se implementa proxy en lugar de redirect, habrá que gestionar sesiones cross-process. Mitigado para v1.6: se usa Opción A (redirect) donde cada silo mantiene sesión independiente.

2. **Conflicto de rutas `/{slug}` vs rutas existentes:** el patrón `/{tenant_slug}` podría capturar rutas como `/login`, `/dashboard` si no se delimita correctamente. Mitigado en el diseño: el grupo de rutas está bajo el middleware `control.plane` (solo CP) y requiere que `{tenant_slug}` coincida con un tenant existente.

3. **Assets Vite/Inertia:** con routing path-based desde el CP, el build compilado debe estar disponible. En desarrollo (HMR) no es problema. En producción o compilación estática se necesita un build unificado. Registrado para Fase 8.

4. **`PLATFORM_PORTAL_MULTI_TENANT_LOGIN` en silos:** si algún silo tiene este flag en `true`, puede interferir con la resolución de tenant. El diseño requiere que el flag esté activo SOLO en el CP. Los silos tienen `PLATFORM_PORTAL_MULTI_TENANT_LOGIN=false` por defecto (verificado en Fase 4).

## Riesgos mitigados

- Runtime actual protegido por `PLATFORM_FRIENDLY_ROUTING=false` por defecto
- Modelo físico ADR-001 preservado: silos siguen siendo instancias independientes
- Backward compatibility garantizada: todos los componentes existentes sin cambios

## Hallazgos fuera de alcance

1. **Implementación del middleware `ResolveTenantFromRoutePath`** — Fase 7.
2. **Implementación de `TenantPortalProxyController`** — Fase 7.
3. **Registro de rutas `/{tenant_slug}/*`** — Fase 7.
4. **Subdominios DNS/nginx** — Post-v1.6.
5. **Build Vite unificado para routing path-based** — Fase 8.

## Checklist Runbook

| Requisito | Estado | Evidencia |
|---|---|---|
| ADR técnico de routing redactado | Cumple | `ADR_011_friendly_routing_multitenant.md` — contexto, decisión, diseño completo |
| Plan de migración definido | Cumple | `Plan_Migracion_Routing_v1.6.md` — 2 etapas, 6 pasos en Etapa 1, criterios de aceptación para Fase 7 |
| Decisiones aprobadas | Cumple | Estrategia de proxy (Opción A), feature flag, activación opt-in definidos |
| Runtime actual sin alterar | Cumple | Cero cambios en código, middleware, rutas o configuración |
| Resolver por ruta diseñado | Cumple | Contrato de `ResolveTenantFromRoutePath` documentado en ADR-011 |
| Resolver por subdominio diseñado | Cumple | `ResolveTenantFromSubdomain` diseñado como Etapa 2 en ADR-011 |
| Plan de coexistencia con routing por puertos | Cumple | Rutas `/{slug}/*` son overlay aditivo; puertos siguen funcionando |

## Compatibilidad Retroactiva

Fase 6 no modifica ningún archivo de código. El runtime permanece idéntico al estado al final de Fase 5. Verificación:

- **Lifecycle:** sin cambios. Confirmado por no-modificación de archivos.
- **Provisioning:** sin cambios. Confirmado por no-modificación de archivos.
- **Login:** sin cambios. Confirmado: el resolver será opt-in en Fase 7.
- **Fleet/registry:** sin cambios. Solo se lee como referencia para el diseño.
- **Control plane:** sin cambios. Las rutas nuevas solo se añaden en Fase 7 con flag activo.
- **Simulación:** sin cambios. No relacionada con routing.
