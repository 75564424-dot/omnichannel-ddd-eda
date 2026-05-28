# Reporte de Implementación — Planes de Producción

Registro incremental de implementaciones por plan. **No refactor masivo** — solo lo definido en cada `Plan_*.md`.

---

## Plan_Tenants.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2). Fase 3 diferida según ADR-001.

### Resumen ejecutivo

Se formalizó el modelo **instancia por cliente** (ADR-001): cada despliegue es un silo con identidad propia. Se añadió configuración de plataforma, seed del tenant de instancia, contexto de tenant en runtime, persistencia de `tenant_id` en cola/métricas y contexto estructurado en logs.

### Qué se implementó

#### Fase 1 — Documentación y templates

| Entregable | Archivo |
|------------|---------|
| ADR instancia por cliente | `docs/production/ADR_001_instancia_por_cliente.md` |
| Plantilla `.env` raíz | `.env.example` |
| Template env por cliente | `docs/production/templates/env.client.example` |
| Runbook onboarding | `docs/production/Runbook_Onboarding_Cliente.md` |
| Inventario instancias (plantilla) | `docs/production/Inventario_Instancias.md` |
| Propuesta comercial | `docs/production/Propuesta_Comercial_Modelo_Instancia.md` |

#### Fase 2 — Runtime

| Componente | Archivo |
|------------|---------|
| Config plataforma | `config/platform.php` |
| Contrato contexto instancia | `app/Shared/Platform/Contracts/InstanceTenantContextInterface.php` |
| Implementación | `app/Shared/Platform/DatabaseInstanceTenantContext.php` |
| Modelo Eloquent tenant | `app/Shared/Infrastructure/Models/TenantModel.php` |
| Service provider | `app/Providers/PlatformServiceProvider.php` |
| Seeder tenant instancia | `database/seeders/InstanceTenantSeeder.php` |
| Comando ops | `app/Console/Commands/EnsureInstanceTenantCommand.php` |
| Log context global | `Log::shareContext()` en `PlatformServiceProvider` |

**Repositorios actualizados** (persisten `tenant_id` cuando existe fila tenant):

- `EloquentQueueEntryRepository`
- `EloquentBusMetricsRepository`
- `EloquentMiddlewareBusMetricsRepository`
- `EloquentMetricsRepository`

**Tests añadidos:**

- `tests/Unit/Platform/InstanceTenantContextTest.php`
- `tests/Integration/Platform/InstanceTenantSeedingIntegrationTest.php`

#### Fase 3 — No implementada (diferida)

Multi-tenant lógico, tenant resolver HTTP, RLS y portal admin — documentado como futuro en ADR-001 § Fase 3.

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_CLIENT_SLUG` | Slug único de la instancia/cliente | `default` |
| `PLATFORM_CLIENT_NAME` | Nombre legible | `APP_NAME` |
| `PLATFORM_DEPLOYMENT_MODE` | `instance_per_client` \| `multi_tenant` (reservado) | `instance_per_client` |
| `PLATFORM_SEED_INSTANCE_TENANT` | Ejecutar seed de tenant en `db:seed` | `true` |

### Decisiones técnicas

1. **Un tenant row = esta instancia** — no ACL multi-tenant en runtime; aislamiento físico por despliegue.
2. **`tenant_id` nullable** — tests sin seed siguen pasando; producción debe ejecutar `db:seed` o `platform:ensure-instance-tenant`.
3. **Lookup lazy del tenant** — reintenta hasta encontrar fila tras seed (evita cache null en boot).
4. **`PlatformServiceProvider` antes de `AppServiceProvider`** — config y log context disponibles temprano.
5. **Guard `Schema::hasTable('tenants')`** — tests unitarios sin BD no fallan en boot.

### Archivos modificados

- `bootstrap/providers.php`
- `database/seeders/DatabaseSeeder.php`
- `app/Middleware/Infrastructure/Models/QueueEntryModel.php`
- `app/Middleware/Infrastructure/Persistence/EloquentQueueEntryRepository.php`
- `app/Middleware/Infrastructure/Persistence/EloquentBusMetricsRepository.php`
- `app/Dashboard/Infrastructure/Persistence/EloquentMiddlewareBusMetricsRepository.php`
- `app/Dashboard/Infrastructure/Persistence/EloquentMetricsRepository.php`
- `phpunit.xml`
- `docs/production/Plan_de_implementacion.md` (referencias v1.1 previas)

### Arquitectura afectada

- **Shared/Platform** — nuevo supporting module para identidad de instancia
- **Middleware/Dashboard Infrastructure** — inyección de `InstanceTenantContextInterface` en repositorios de escritura
- **Sin cambio** en Domain entities, event bus routing, listeners signatures

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `php artisan test` | **90 passed** (325 assertions) |
| `php artisan migrate:fresh --seed` | OK — tenant row creada |
| Coherencia DDD | Contexto en Shared; dominio sin acoplamiento a TenantModel |
| Compatibilidad tests existentes | OK — `PLATFORM_SEED_INSTANCE_TENANT=false` en phpunit |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Deploy sin seed → `tenant_id` null | Medio | Runbook exige `db:seed` + `platform:ensure-instance-tenant` |
| Log context con `tenant_id` null si boot antes de seed | Bajo | Comando ensure refresca `Log::shareContext` |
| Confusión multi-tenant vs instancia | Medio | ADR-001 + propuesta comercial |

### Pendientes (fuera de Plan_Tenants)

- `audit_logs` escritura (Plan_Logs)
- Scope global Eloquent multi-tenant (Fase 3)
- Auth en APIs (Plan_Autenticacion)
- CI/CD (Plan_CI_CD)

### Impacto producción / cloud

- **Cloud-ready:** variables documentadas en `.env.example` y template cliente
- **Compatibilidad:** modelo silo por instancia alineado con K8s namespace / VM dedicada
- **Estabilidad:** sin breaking changes en APIs; tests verdes

### Próximo plan recomendado

**Plan_CI_CD.md** (#2 en orden acordado) o **Plan_Seguridad.md** (#3) según prioridad ops vs gates.

---

*Última actualización: 2026-05-21 — Plan_Tenants.md*

---

## Plan_CI_CD.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3).

### Resumen ejecutivo

Se implementó pipeline CI/CD con GitHub Actions: lint (Pint + PHPStan), validación de configs JSON, comando `platform:validate-catalog` (B.3), tests PHPUnit, auditoría Composer, gate de cobertura Application ≥70%, build Docker con smoke test en staging, release notes automatizados y Dependabot.

### Qué se implementó

#### Fase 1 — CI base

| Entregable | Archivo |
|------------|---------|
| Workflow CI principal | `.github/workflows/ci.yml` |
| PHPStan level 5 (Application + Domain) | `phpstan.neon` |
| Pint config | `pint.json` |
| JSON lint script | `docs/testing/tools/validate_json_configs.php` |
| Validador catálogo B.3 | `app/Shared/Platform/Services/PlatformCatalogValidator.php` |
| Comando artisan | `app/Console/Commands/ValidatePlatformCatalogCommand.php` |
| Tests unitarios validate-catalog | `tests/Unit/Platform/ValidatePlatformCatalogTest.php` |
| Scripts composer | `composer.json` (`lint`, `analyse`, `validate-config`, `ci`) |
| Documentación operativa | `docs/production/CI_CD.md` |

#### Fase 2 — Docker + staging

| Entregable | Archivo |
|------------|---------|
| Dockerfile multi-stage | `Dockerfile` |
| Entrypoint (migrate + serve) | `docker/entrypoint.sh` |
| Docker ignore | `.dockerignore` |
| Workflow staging | `.github/workflows/staging.yml` |
| Smoke test post-deploy | `scripts/ci/smoke-test.sh` |

#### Fase 3 — Quality gates avanzados

| Entregable | Archivo |
|------------|---------|
| Coverage gate Application ≥70% | `scripts/ci/check-application-coverage.php` + job `coverage` en CI |
| Composer audit | job `security` en CI |
| Release notes | `scripts/ci/generate-release-notes.sh` + `.github/workflows/release.yml` |
| Dependabot | `.github/dependabot.yml` |

### Decisiones técnicas

1. **PHPStan acotado a Application + Domain** — evita ruido Eloquent sin Larastan; nivel 5 cumple plan.
2. **Pint en alcance incremental** — solo archivos CI/plataforma nuevos; evita reformateo masivo del repo existente.
3. **validate-catalog unidireccional** — JSON declarativo debe estar reflejado en `eventbus.php`; routing solo en eventbus no falla (compatibilidad B.2).
4. **Staging CI = build + smoke local** — deploy cloud real diferido a `Plan_Cloud.md` (jobs placeholder con environment approval).
5. **Composer audit `continue-on-error`** — CVE Symfony transitivo vía Laravel; requiere upgrade framework (fuera de alcance).

### Archivos nuevos / modificados

**Nuevos:** `.github/workflows/*.yml`, `.github/dependabot.yml`, `Dockerfile`, `docker/entrypoint.sh`, `.dockerignore`, `phpstan.neon`, `pint.json`, `PlatformCatalogValidator.php`, `ValidatePlatformCatalogCommand.php`, `validate_json_configs.php`, `scripts/ci/*`, `CI_CD.md`, `ValidatePlatformCatalogTest.php`

**Modificados:** `composer.json`, `.gitignore`

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **94 passed** (331 assertions) |
| `php artisan platform:validate-catalog` | OK |
| `validate_json_configs.php` | OK (2 archivos) |
| `vendor/bin/phpstan analyse` | OK (Application + Domain) |
| `composer lint` | OK (alcance CI) |
| Docker build local | No ejecutado (daemon Docker detenido en entorno dev) |
| Composer audit | **Advisories** Symfony CVE (transitivo) — documentado |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Pint no cubre todo el repo | Medio | Expandir en Plan_Calidad |
| CVE Composer audit | Medio | Upgrade Laravel/Symfony; audit informativo en CI |
| Deploy staging/prod placeholder | Medio | Plan_Cloud + secretos GitHub |
| Coverage gate sin validar local (sin pcov) | Bajo | Job CI en Ubuntu con pcov |

### Pendientes (fuera de Plan_CI_CD)

- Push imagen a registry cloud
- Deploy real staging/prod (K8s/compose remoto)
- ESLint frontend en CI (no requerido explícitamente; sin ESLint config)
- Pint full-repo
- Larastan para Infrastructure

### Impacto producción / cloud

- **CI gates** activos en PR/push — regresiones detectadas antes de merge
- **Dockerfile** listo para registry; smoke valida APIs middleware
- **Estabilidad:** sin cambios en runtime de negocio; solo tooling + validate-catalog

### Próximo plan recomendado

**Plan_Seguridad.md** (#3 en orden acordado) o **Plan_Cloud.md** para completar deploy staging real.

---

*Última actualización: 2026-05-21 — Plan_CI_CD.md*

---

## Plan_Seguridad.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 documental/operativa). RBAC granular UI diferido a `Plan_Usuarios.md`.

### Resumen ejecutivo

Se estableció postura defensiva para el middleware: CORS explícito, rate limiting, security headers, autenticación API (Sanctum + claves estáticas), abilities por ruta, auditoría en `audit_logs`, validación JSON Schema opt-in en publish, documentación de hardening/WAF/pentest y rotación de tokens.

### Qué se implementó

#### Fase 1 — Defensa inmediata

| Entregable | Archivo |
|------------|---------|
| Config seguridad | `config/security.php` |
| CORS allowlist | `config/cors.php` |
| Security headers | `app/Http/Middleware/SecurityHeadersMiddleware.php` |
| Rate limiters | `app/Providers/SecurityServiceProvider.php` |
| `.env.example` ampliado | variables `PLATFORM_*`, `CORS_*`, `EVENTBUS_SCHEMA_*` |
| Matriz endpoints | `docs/production/Matriz_Endpoints_Seguridad.md` |

#### Fase 2 — Auth + audit + schema

| Entregable | Archivo |
|------------|---------|
| Sanctum + User | `composer.json`, `app/Models/User.php`, migración users/tokens |
| Authenticator | `app/Shared/Security/Services/PlatformApiAuthenticator.php` |
| Middleware auth/abilities/audit | `AuthenticatePlatformApi`, `EnforcePlatformAbility`, `AuditControlPlaneMiddleware` |
| Audit writer | `AuditLogWriter` → tabla `audit_logs` |
| JSON Schema publish | `PublishPayloadSchemaValidator`, `config/schemas/` |
| Rutas protegidas | `app/Middleware/Interfaces/Routes/api.php`, `app/Dashboard/Interfaces/Routes/api.php` |
| Comando issue token | `platform:issue-api-token` |
| Docs operativos | `docs/production/Seguridad.md`, `Hardening_Por_Entorno.md` |

#### Fase 3 — RBAC light + ops security

| Entregable | Archivo |
|------------|---------|
| Rotación tokens | `platform:rotate-api-token` |
| WAF reglas (doc) | `docs/production/WAF_Reglas_Recomendadas.md` |
| Pentest checklist | `docs/production/Pentest_Checklist_Basico.md` |
| Smoke CI con API key | `scripts/ci/smoke-test.sh`, `.github/workflows/staging.yml` |

**Diferido (Plan_Usuarios / Plan_Autenticacion):** login web Inertia, CRUD API keys UI, OAuth2, RBAC roles humanos.

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_API_AUTH_ENABLED` | Gate auth en APIs | `true` |
| `PLATFORM_API_KEYS` | Claves M2M `key\|abilities` | vacío |
| `PLATFORM_RATE_LIMIT_*` | Throttle publish/sync/stream | ver `config/security.php` |
| `PLATFORM_SECURITY_HEADERS` | Headers defensivos | `true` |
| `PLATFORM_AUDIT_ENABLED` | Escritura `audit_logs` | `true` |
| `CORS_ALLOWED_ORIGINS` | Allowlist CORS | `APP_URL` |
| `EVENTBUS_SCHEMA_VALIDATION` | JSON Schema en publish | `false` |
| `SANCTUM_STATEFUL_DOMAINS` | Dominios cookie Sanctum | localhost |

### Decisiones técnicas

1. **Auth desactivable en tests** — `PLATFORM_API_AUTH_ENABLED=false` en `phpunit.xml`; compatibilidad con 94+ tests existentes.
2. **Dual auth** — Sanctum PAT + `X-API-Key` estático para integradores M2M (instancia por cliente).
3. **Abilities en rutas** — middleware `platform.ability` sin mezclar auth en listeners EDA.
4. **Audit post-respuesta** — middleware `platform.audit` solo en acciones de control exitosas; payload hasheado en changes.
5. **SSE token query** — `?token=` soportado en authenticator para EventSource.
6. **Fase 3 RBAC** — scopes por token; roles humanos quedan en Plan_Usuarios.

### Archivos principales

**Nuevos:** `app/Shared/Security/**`, `app/Http/Middleware/{AuthenticatePlatformApi,EnforcePlatformAbility,AuditControlPlaneMiddleware,SecurityHeadersMiddleware}.php`, `app/Providers/SecurityServiceProvider.php`, `config/{security,cors,auth,sanctum}.php`, migración users, comandos issue/rotate token, tests Security, docs production seguridad.

**Modificados:** `bootstrap/app.php`, `bootstrap/providers.php`, rutas API Middleware/Dashboard, `EventPublisherService`, `.env.example`, `phpunit.xml`, `config/eventbus.php`, smoke/staging CI.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **102 passed** (341 assertions) |
| Feature auth 401/403/audit | OK |
| JSON Schema validator unit | OK |
| `platform:validate-catalog` | OK |
| PHPStan (Application + Domain + Security) | OK |
| Migraciones users/tokens | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| UI web `/dashboard` sin login | Alto | Plan_Autenticacion Fase 2 |
| Claves estáticas en env | Medio | Secrets manager + rotate command |
| CSP permisiva (`unsafe-inline` Vite) | Medio | Endurecer en prod con nonce (Plan_Cloud) |
| RBAC granular humano pendiente | Medio | Plan_Usuarios |

### Pendientes (fuera de Plan_Seguridad)

- Login/logout operadores (Plan_Autenticacion)
- UI gestión API keys
- WAF desplegado en cloud (Plan_Cloud)
- OAuth2 / IdP enterprise

### Impacto producción / cloud

- **APIs ya no son públicas** cuando `PLATFORM_API_AUTH_ENABLED=true` (default prod).
- **Audit trail** en acciones de control.
- **Smoke CI** valida auth + flujo publish con API key.
- **Breaking change:** integradores deben enviar `Authorization` o `X-API-Key`.

### Próximo plan recomendado

**Plan_Autenticacion.md** (#4) para login UI y gestión operador, o **Plan_Cloud.md** para TLS/ingress/WAF real.

---

*Última actualización: 2026-05-21 — Plan_Seguridad.md*

---

## Plan_Autenticacion.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2). Fase 3 enterprise diferida (ADR-002).

### Resumen ejecutivo

Se completó el modelo de autenticación dual: operadores humanos vía sesión Sanctum (login Inertia) e integradores M2M vía tokens/API keys (heredado y extendido desde Plan_Seguridad). Las APIs aceptan sesión de operador autenticado sin Bearer token en el browser.

### Qué se implementó

#### Fase 1 — Base Sanctum + operador

| Entregable | Archivo |
|------------|---------|
| Config auth plataforma | `config/platform_auth.php` |
| Seed operador admin | `database/seeders/PlatformOperatorSeeder.php` |
| Sesión en authenticator | `PlatformApiAuthenticator::fromOperatorSession()` |
| Sanctum stateful API | `bootstrap/app.php` (`EnsureFrontendRequestsAreStateful`) |
| Use cases Identity | `Shared/Identity/Application/{AuthenticateOperator,IssueApiToken}UseCase.php` |

*(Sanctum, users/tokens, auth.platform ya existían desde Plan_Seguridad — completados y extendidos.)*

#### Fase 2 — UI + gestión tokens

| Entregable | Archivo |
|------------|---------|
| Login/logout web | `app/Http/Controllers/Auth/LoginController.php` |
| Página login Inertia | `resources/js/Pages/Auth/Login.vue` |
| Protección UI | middleware `auth.platform.web`, `routes/web.php` |
| Auth compartida Inertia | `HandleInertiaRequests` → `auth.user` |
| Sign out funcional | `AppLayout.vue` |
| Listar/revocar tokens | `platform:list-api-tokens`, `platform:revoke-api-token` |
| Docs M2M | `docs/production/Flujo_M2M_Integradores.md`, `Autenticacion.md` |

#### Fase 3 — Enterprise (diferida)

| Entregable | Archivo |
|------------|---------|
| ADR OAuth2/IdP/MFA | `docs/production/ADR_002_autenticacion_enterprise.md` |

**No implementado:** Passport, Azure AD/Okta, MFA (fuera de alcance inmediato).

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_WEB_AUTH_ENABLED` | Login requerido para UI | `true` |
| `PLATFORM_SEED_ADMIN_OPERATOR` | Seed admin en db:seed | `true` |
| `PLATFORM_ADMIN_EMAIL` | Email operador inicial | `admin@local` |
| `PLATFORM_ADMIN_PASSWORD` | Password inicial | `password` |
| `PLATFORM_ADMIN_NAME` | Nombre operador | `Platform Admin` |

### Decisiones técnicas

1. **Reutilizar `auth.platform`** — sesión operador integrada en authenticator existente (no duplicar middleware).
2. **Auth UI desactivable en tests** — `PLATFORM_WEB_AUTH_ENABLED=false` en phpunit; suite existente intacta.
3. **Operador = abilities completas** — instancia por cliente; RBAC fino en Plan_Usuarios.
4. **Use cases en Shared/Identity** — sin lógica auth en listeners EDA.
5. **Fase 3 = ADR only** — Passport/IdP cuando haya demanda enterprise documentada.

### Archivos principales

**Nuevos:** `config/platform_auth.php`, `PlatformOperatorSeeder.php`, `LoginController.php`, `EnsurePlatformWebAuth.php`, `Auth/Login.vue`, Identity use cases, comandos list/revoke token, tests Identity, docs Autenticacion/Flujo_M2M/ADR-002.

**Modificados:** `PlatformApiAuthenticator.php`, `routes/web.php`, `bootstrap/app.php`, `HandleInertiaRequests.php`, `DatabaseSeeder.php`, `IssuePlatformApiTokenCommand.php`, `AppLayout.vue`, `.env.example`, `phpunit.xml`, `SecurityServiceProvider.php`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **107 passed** (350 assertions) |
| Login + redirect dashboard | OK |
| Session API sin Bearer | OK |
| Seeder operador | OK |
| Tests existentes (auth off) | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Password default `admin@local` | **Alto** | Cambiar en prod; documentado en Hardening |
| Sin MFA / SSO | Medio | ADR-002 Fase 3 |
| UI axios depende de cookies SameSite | Medio | SANCTUM_STATEFUL_DOMAINS |

### Pendientes (fuera de Plan_Autenticacion)

- OAuth2 Passport (ADR-002)
- IdP Azure AD / Okta
- MFA operadores
- Panel UI gestión tokens (solo CLI hoy)
- RBAC roles (Plan_Usuarios)

### Impacto producción / cloud

- **UI protegida** con login cuando `PLATFORM_WEB_AUTH_ENABLED=true`.
- **Browser APIs** funcionan con sesión (axios + credentials).
- **M2M** sin cambios de contrato (Bearer / X-API-Key).
- **Breaking:** deploys prod deben seed admin y configurar password.

### Próximo plan recomendado

**Plan_Usuarios.md** (RBAC) o **Plan_Cloud.md** (TLS, ingress, health).

---

*Última actualización: 2026-05-21 — Plan_Autenticacion.md*

---

## Plan_Usuarios.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2). Fase 3 enterprise diferida (ADR-003).

### Resumen ejecutivo

Se implementó RBAC nativo Laravel con 3 roles de operador (`platform_admin`, `bus_operator`, `dashboard_viewer`), matriz de abilities en configuración, policies en acciones críticas del plano de control (sync, publish, DLQ), UI de gestión de usuarios para admin, wiring de auditoría con `actor_label` (email + rol), y tests de autorización por rol.

### Qué se implementó

#### Fase 1 — Roles, policies y login

| Entregable | Archivo |
|------------|---------|
| Enum roles plataforma | `app/Shared/Identity/Domain/PlatformRole.php` |
| Matriz RBAC | `config/platform_roles.php` |
| Servicio autorización | `app/Shared/Identity/Services/PlatformAuthorizationService.php` |
| Contrato Identity | `app/Shared/Identity/Contracts/PlatformAuthorizationServiceInterface.php` |
| Policies | `Shared/Identity/Policies/{PublishEvent,SyncRegistry,ResolveDeadLetter,ManageUsers}Policy.php` |
| Gates Laravel | `app/Providers/IdentityServiceProvider.php` |
| Columna `platform_role` | `database/migrations/2026_05_21_130000_add_platform_role_to_users_table.php` |
| Abilities desde rol en sesión | `PlatformApiAuthenticator::fromOperatorSession()` |
| Autorización en controllers | `ModuleRegistrySyncController`, `EventQueueController`, `DeadLetterController` |

*(Login web ya existía desde Plan_Autenticacion.)*

#### Fase 2 — Audit, UI admin, tests

| Entregable | Archivo |
|------------|---------|
| UI gestión usuarios | `resources/js/Pages/Admin/Users/Index.vue` |
| Controller admin | `app/Http/Controllers/Admin/UserManagementController.php` |
| Rutas `/admin/users` | `routes/web.php` (middleware `can:platform.manage-users`) |
| Nav admin en layout | `resources/js/Layouts/AppLayout.vue` |
| `actor_label` en audit | `AuditControlPlaneMiddleware.php` |
| Rol en props Inertia | `HandleInertiaRequests.php` |
| Tests RBAC | `tests/Feature/Identity/RoleBasedAuthorizationTest.php` |
| Docs operativas | `docs/production/Usuarios.md` |

#### Fase 3 — Enterprise (diferida)

| Entregable | Archivo |
|------------|---------|
| ADR SSO/LDAP/roles custom | `docs/production/ADR_003_usuarios_enterprise.md` |

**No implementado:** Spatie Permission, LDAP, SSO/OIDC, roles custom por cliente.

### Matriz RBAC implementada

| Rol | publish | sync/DLQ | dashboard | users:manage |
|-----|---------|----------|-----------|--------------|
| `platform_admin` | ✓ | ✓ | ✓ | ✓ |
| `bus_operator` | ✓ | ✓ | ✓ | — |
| `dashboard_viewer` | — | — | ✓ | — |
| `api_integrator` | ✓* | — | — | — |

*Solo M2M vía token/API key con scopes; no es rol UI.

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_ADMIN_ROLE` | Rol del operador seed | `platform_admin` |

### Decisiones técnicas

1. **RBAC nativo (sin Spatie)** — 3 roles fijos alineados con instance-per-client (ADR-001); matriz en `config/platform_roles.php`.
2. **Doble capa de autorización** — middleware `platform.ability` (HTTP) + Gates/policies (controllers/UI); Gates respetan bypass cuando `PLATFORM_API_AUTH_ENABLED=false` (tests).
3. **Gates compatibles con M2M** — verifican `PlatformApiPrincipal` además de `User` de sesión; no rompe API keys ni Sanctum tokens.
4. **Contexto Identity en Shared** — `PlatformAuthorizationService` desacoplado del bus EDA; consumido por Security y Middleware.
5. **Fase 3 = ADR only** — SSO/LDAP cuando cliente enterprise lo exija.

### Archivos principales

**Nuevos:** `PlatformRole.php`, `platform_roles.php`, `IdentityServiceProvider.php`, policies Identity, migración `platform_role`, `UserManagementController.php`, `Admin/Users/Index.vue`, `RoleBasedAuthorizationTest.php`, `Usuarios.md`, `ADR_003_usuarios_enterprise.md`.

**Modificados:** `User.php`, `UserFactory.php`, `PlatformApiAuthenticator.php`, `PlatformOperatorSeeder.php`, `platform_auth.php`, `bootstrap/providers.php`, `SecurityServiceProvider.php`, `routes/web.php`, `HandleInertiaRequests.php`, `AppLayout.vue`, controllers sync/publish/DLQ, `AuditControlPlaneMiddleware.php`, `.env.example`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **113 passed** (358 assertions) |
| `composer analyse` (PHPStan) | **OK** — sin errores |
| `php artisan migrate` | OK — columna `platform_role` |
| Viewer no puede sync/publish | OK — 403 |
| Operator puede sync, no admin UI | OK |
| Admin accede `/admin/users` | OK |
| Audit `actor_label` con rol | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Password default admin | **Alto** | Cambiar en prod; documentado |
| Sin SSO/LDAP/MFA | Medio | ADR-003 Fase 3 |
| RBAC desactivado si auth off | Medio | Prod debe tener `PLATFORM_*_AUTH_ENABLED=true` |
| Sin invitación por email | Bajo | Crear usuarios vía UI admin o seeder |

### Pendientes (fuera de Plan_Usuarios)

- SSO / LDAP / OIDC (ADR-003)
- Roles custom por instancia
- Invitación por email
- Panel UI revocación tokens (Plan_Autenticacion pendiente)
- OAuth2 Passport (ADR-002)

### Impacto producción / cloud

- **Principio de mínimo privilegio** activo cuando auth habilitada.
- **Trazabilidad** — audit logs incluyen operador y rol en acciones de control.
- **UI admin** — `/admin/users` solo `platform_admin`.
- **Compatibilidad** — M2M sin cambios; tests legacy intactos con auth desactivada en phpunit.
- **Breaking:** migración `platform_role` requerida en deploy; re-seed admin recomendado.

### Próximo plan recomendado

**Plan_Cloud.md** (TLS, ingress, health) o **Plan_Logs.md** (observabilidad centralizada).

---

*Última actualización: 2026-05-21 — Plan_Usuarios.md*

---

## Plan_Cloud.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 documentada/IaC skeleton).

### Resumen ejecutivo

Se habilitó despliegue cloud reproducible: Dockerfile multi-stage (`fpm` producción, `serve` CI), `docker-compose.yml` con MySQL/Redis/nginx/worker/scheduler, health `/up` + readiness `/health/ready`, manifests Kubernetes, backup automatizado, HPA, runbooks operativos y esqueleto Terraform.

### Qué se implementó

#### Fase 1 — Contenedores y health

| Entregable | Archivo |
|------------|---------|
| Dockerfile multi-stage | `Dockerfile` (`fpm`, `serve`) |
| Docker Compose stack | `docker-compose.yml` |
| Nginx config | `docker/nginx/default.conf` |
| Entrypoint (migrate, cache) | `docker/entrypoint.sh` |
| Liveness `/up` | `bootstrap/app.php` |
| Readiness DB+Redis | `app/Http/Controllers/Health/ReadinessController.php` |
| Variables cloud | `.env.example` |
| Runbook VM | `docs/production/Runbook_Deploy_VM.md` |
| Tests health | `tests/Feature/Health/HealthEndpointTest.php` |

#### Fase 2 — K8s, Redis, backup, staging

| Entregable | Archivo |
|------------|---------|
| K8s namespace, deployments, service, ingress | `deploy/k8s/` |
| ConfigMap env + nginx | `deploy/k8s/configmap-*.yaml` |
| Worker deployment | `deploy/k8s/deployment-worker.yaml` |
| Backup script | `scripts/ops/backup-database.sh` |
| Backup K8s CronJob | `deploy/k8s/cronjob-backup.yaml` |
| Runbook backup | `docs/production/Runbook_Backup_Restore.md` |
| Staging docs + CI health | `Staging_Environment.md`, `.github/workflows/staging.yml` |
| Scheduler | `routes/console.php`, servicio `scheduler` en compose |

#### Fase 3 — HPA, CDN, DR, Terraform

| Entregable | Archivo |
|------------|---------|
| HPA | `deploy/k8s/hpa.yaml` |
| CDN strategy doc | `docs/production/Cloud_CDN_Assets.md` |
| DR drill runbook | `docs/production/Runbook_DR_Drill.md` |
| Terraform skeleton | `deploy/terraform/` |
| Doc central cloud | `docs/production/Cloud.md` |

**No implementado (fuera alcance inmediato):** Terraform modules completos AWS/Azure/GCP, CDN upload pipeline, blue/green automático.

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `APP_PUBLISH_PORT` | Puerto nginx en compose | `8080` |
| `MYSQL_ROOT_PASSWORD` | Root MySQL compose | — |
| `DOCKER_APP_ROLE` | web/worker/scheduler | `web` |
| `REDIS_CLIENT` | Driver Redis | `phpredis` |
| `REDIS_HOST` / `REDIS_PORT` | Conexión Redis | `127.0.0.1:6379` |

### Decisiones técnicas

1. **Dos targets Docker** — `serve` mantiene CI/staging smoke existente; `fpm`+nginx para compose/K8s prod-like.
2. **Readiness inteligente** — Redis solo se exige si cache/queue/session usan `redis`; evita falsos negativos en dev SQLite.
3. **Health sin auth** — `/up` y `/health/ready` excluidos de WAF agresivo (documentado en Plan_Seguridad).
4. **Separación procesos** — web (php-fpm), worker (`queue:work redis`), scheduler (`schedule:run` loop).
5. **Terraform skeleton** — placeholder por instancia-cliente; módulos cloud cuando se defina proveedor.

### Archivos principales

**Nuevos:** `docker-compose.yml`, `docker/nginx/default.conf`, `ReadinessController.php`, `deploy/k8s/*`, `deploy/terraform/*`, `scripts/ops/backup-database.sh`, runbooks Cloud/VM/Backup/DR/CDN, `HealthEndpointTest.php`, `routes/console.php`, `Cloud.md`, `Staging_Environment.md`.

**Modificados:** `Dockerfile`, `docker/entrypoint.sh`, `bootstrap/app.php`, `.env.example`, `scripts/ci/smoke-test.sh`, `.github/workflows/staging.yml`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **116 passed** (365 assertions) |
| `composer analyse` | OK |
| Health tests `/up`, `/health/ready` | OK |
| Docker build local | No ejecutado (daemon no disponible en entorno) |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| K8s sidecar nginx sin init copy public | Medio | Usar imagen unificada o initContainer en prod real |
| Secrets en ConfigMap ejemplo | **Alto** | Reemplazar con Sealed Secrets / cloud SM antes prod |
| Docker no validado localmente | Bajo | CI staging workflow valida build |
| Scheduler loop vs CronJob K8s | Bajo | Preferir K8s CronJob en prod |

### Pendientes (fuera de Plan_Cloud)

- Terraform modules completos por cloud
- CDN upload automatizado en pipeline
- Blue/green / canary deployment
- Managed MySQL/Redis provisioning automatizado
- Plan_Logs / Plan_Monitoreo (observabilidad centralizada)

### Impacto producción / cloud

- **Despliegue reproducible** vía Docker Compose o K8s.
- **Horizontal scaling** — HPA 2–6 réplicas web.
- **HA mínima** — 2 réplicas app + BD/Redis managed recomendados.
- **Probes LB** — `/up` liveness, `/health/ready` readiness.
- **Compatibilidad** — tests PHPUnit sin Docker; CI smoke ampliado.

### Próximo plan recomendado

**Plan_Logs.md** o **Plan_Monitoreo.md** (observabilidad, métricas centralizadas).

---

*Última actualización: 2026-05-21 — Plan_Cloud.md*

---

## Plan_SimulacionClientes.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3).

### Resumen ejecutivo

Se automatizó la simulación repetible de clientes omnicanal antes de GO real: fixtures versionados (`retailco`, `acmepos`), comando `platform:simulate-client`, overlay eventbus por archivo, flag `DEMO_PACK_ENABLED`, runbook consolidado en producción, checklist pre-GO, smoke scripts bash/PowerShell y workflow CI nightly multi-cliente.

### Qué se implementó

#### Fase 1 — Docs, fixtures, smoke

| Entregable | Archivo |
|------------|---------|
| Runbook consolidado | `docs/production/Runbook_Simulacion_Cliente.md` |
| Fixtures RetailCo | `tests/fixtures/clients/retailco/` |
| Fixtures Acme POS | `tests/fixtures/clients/acmepos/` |
| Smoke bash | `scripts/ops/simulate-client-smoke.sh` |
| Smoke PowerShell | `scripts/ops/simulate-client-smoke.ps1` |
| Actualización §1.1 | `docs/production/Plan_de_implementacion.md` |

#### Fase 2 — Comando + staging template

| Entregable | Archivo |
|------------|---------|
| Loader fixtures | `app/Shared/Platform/Services/ClientFixtureLoader.php` |
| Orquestador simulación | `app/Shared/Platform/Services/ClientSimulationService.php` |
| Comando artisan | `app/Console/Commands/SimulateClientCommand.php` |
| Overlay eventbus | `config/eventbus.php` + `eventbus_client_overlay.json` |
| Demo pack por env | `DEMO_PACK_ENABLED` en `config/eventbus.php` |
| Template staging | `docs/production/templates/env.staging.retailco.example` |
| Checklist pre-GO | `docs/production/Checklist_Staging_PreGO.md` |
| Doc operativa | `docs/production/SimulacionClientes.md` |

#### Fase 3 — CI nightly multi-cliente

| Entregable | Archivo |
|------------|---------|
| Test E2E multi-cliente | `tests/E2E/Middleware/MultiClientFixtureSimulationTest.php` |
| Tests comando | `tests/Feature/Platform/SimulateClientCommandTest.php` |
| Workflow nightly | `.github/workflows/nightly-client-simulation.yml` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `DEMO_PACK_ENABLED` | Activa demo pack en eventbus | `false` |

### Decisiones técnicas

1. **Fixtures en `tests/fixtures/clients/`** — fuente versionada; `--apply-fixture` copia a `config/` para staging persistente.
2. **Overlay JSON separado** — `eventbus_client_overlay.json` gitignored; evita editar `eventbus.php` por cliente.
3. **Validación B.3 integrada** — comando invoca `PlatformCatalogValidator` antes de sync/publish.
4. **Sin panel self-service** — config dinámica runtime sigue fuera de alcance (documentado en Plan_de_implementacion).
5. **Orquestación en Shared/Platform** — desacoplado de bounded contexts Middleware/Dashboard; usa use cases existentes.

### Archivos principales

**Nuevos:** fixtures retailco/acmepos, `ClientFixtureLoader`, `ClientSimulationService`, `SimulateClientCommand`, tests, scripts smoke, runbooks, checklist, nightly workflow, template staging.

**Modificados:** `config/eventbus.php`, `.env.example`, `.gitignore`, `Plan_de_implementacion.md`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **120 passed** (376 assertions) |
| `composer analyse` | OK |
| `platform:simulate-client retailco` | OK (feature test) |
| Multi-client E2E | OK |
| Coherencia DDD/EDA | Sin acoplar User/auth a simulación; publish vía `EventPublisherService` |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Overlay olvidado en deploy | Medio | `--apply-fixture` + checklist pre-GO |
| Expectativa self-service config | Alto (negocio) | Documentado como no cumplido §1.1 |
| DEMO_PACK en prod | Medio | Default false; checklist staging |
| Drift JSON vs eventbus | Medio | `platform:validate-catalog` en smoke |

### Pendientes (fuera de Plan_SimulacionClientes)

- Panel/API gobierno config runtime
- Fixture Partner API (tercer cliente)
- Partner API integrator E2E con auth M2M en smoke staging
- Upload automático fixtures en pipeline deploy

### Impacto producción / cloud

- **Gate pre-GO** — rehearsal staging repetible antes de primer cliente real.
- **CI nightly** — detecta regresiones en fixtures multi-cliente.
- **Compatibilidad** — tests existentes intactos; overlay vacío por defecto.
- **Estabilidad** — sin refactors en listeners EDA.

### Próximo plan recomendado

**Plan_Logs.md** o **Plan_Monitoreo.md**.

---

*Última actualización: 2026-05-21 — Plan_SimulacionClientes.md*

---

## Plan_BaseDeDatos.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 documentada/POC).

### Resumen ejecutivo

Se implementó política de retención operativa (`platform:purge-retention`), seeders de canal default y claves en `system_configurations`, eliminación de modelo legacy muerto, índices para queries de purge, ADRs tenant_id y particionamiento, estrategia greenfield squash documentada, y scheduler diario de purge.

### Qué se implementó

#### Fase 1 — Retention + seeders + cleanup

| Entregable | Archivo |
|------------|---------|
| Config retención | `config/platform_retention.php` |
| Servicio purge | `app/Shared/Platform/Services/PlatformRetentionService.php` |
| Comando artisan | `app/Console/Commands/PurgePlatformRetentionCommand.php` |
| Seeder canal + retention keys | `database/seeders/MiddlewareDatabaseSeeder.php` |
| Eliminado código muerto | `SystemMetricsSnapshotModel` (tabla ya drop) |
| Scheduler | `routes/console.php` — daily 02:30 |

#### Fase 2 — Índices + backup doc

| Entregable | Archivo |
|------------|---------|
| Índices temporales | `database/migrations/2026_05_21_140000_add_retention_query_indexes.php` |
| Doc operativa BD | `docs/production/BaseDeDatos.md` |
| ADR tenant_id | `docs/production/ADR_004_tenant_id_activation.md` |
| Backup | Referencia a `Runbook_Backup_Restore.md` (Plan_Cloud) |

#### Fase 3 — Squash + partitioning POC

| Entregable | Archivo |
|------------|---------|
| Estrategia greenfield | `docs/production/Migration_Greenfield.md` |
| ADR particionamiento | `docs/production/ADR_005_event_store_partitioning.md` |
| Script schema dump | `scripts/ops/dump-greenfield-schema.sh` |

**No implementado:** squash real de migraciones en repo (evita romper upgrades); particiones MySQL en runtime.

### Retención implementada

| Tabla | Días default |
|-------|--------------|
| message_queue | 30 |
| event_logs | 30 |
| observability_metrics | 14 |
| event_store | 90 |
| audit_logs | 2555 |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `RETENTION_MESSAGE_QUEUE_DAYS` | Override purge cola | 30 |
| `RETENTION_EVENT_LOGS_DAYS` | Override event_logs | 30 |
| `RETENTION_OBSERVABILITY_METRICS_DAYS` | Override métricas | 14 |
| `RETENTION_EVENT_STORE_DAYS` | Override event store | 90 |
| `RETENTION_AUDIT_LOGS_DAYS` | Override audit | 2555 |

### Decisiones técnicas

1. **Retención en Shared/Platform** — sin acoplar a bounded contexts; purge directo por tabla con cutoffs configurables.
2. **Prioridad config:** `system_configurations` > `config/platform_retention.php` > env.
3. **tenant_id** — ADR-004: nullable en dev/tests; poblado en prod vía Plan_Tenants.
4. **Squash diferido** — POC documentado; migraciones históricas preservadas para upgrades.
5. **Particionamiento** — ADR-005 POC SQL; purge DELETE suficiente para piloto.

### Archivos principales

**Nuevos:** `platform_retention.php`, `PlatformRetentionService`, `PurgePlatformRetentionCommand`, `MiddlewareDatabaseSeeder`, migración índices, tests `PurgePlatformRetentionTest`, docs BaseDeDatos/ADRs/Migration_Greenfield, script dump-greenfield.

**Modificados:** `DatabaseSeeder.php`, `routes/console.php`, `.env.example`.

**Eliminados:** `SystemMetricsSnapshotModel.php`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **123 passed** (383 assertions) |
| `composer analyse` | OK |
| `php artisan migrate` | OK — índices retención |
| Purge + dry-run tests | OK |
| Seeder canal/retention | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| DELETE masivo en prod sin --dry-run | Medio | Documentar; probar dry-run primero |
| Purge sin scheduler activo | Medio | Cron K8s / compose scheduler |
| ~60% esquema sin app layer | Medio | Planes Middleware/Integraciones |
| Squash prematuro rompe upgrades | Alto | Solo POC doc, no prune migraciones |

### Pendientes (fuera de Plan_BaseDeDatos)

- Wire `event_logs` writer (Plan_Logs)
- Particionamiento MySQL en prod (cuando volumen lo exija)
- Squash commit `schema:dump` tras periodo estabilidad
- EXPLAIN ANALYZE en staging MySQL real
- Seeders demo integrations (Plan_Integraciones)

### Impacto producción / cloud

- **Control crecimiento BD** — purge automatizable.
- **Compliance audit** — retención 7 años audit_logs configurable.
- **Cloud cost** — reduce storage message_queue/metrics.
- **Compatibilidad** — tests sqlite intactos; índices idempotentes.

### Próximo plan recomendado

**Plan_Logs.md** (event_logs writer) o **Plan_Middleware.md** (wire event_store).

---

*Última actualización: 2026-05-21 — Plan_Resiliencia.md*

---

## Plan_Resiliencia.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 parcial). Sagas diferidas según ADR-006.

### Resumen ejecutivo

Se implementó resiliencia del bus omnicanal: publish idempotente (HTTP 200 en duplicados), procesamiento con registro en `retries`, job async `ProcessEventJob`, API de requeue DLQ, circuit breaker configurable para conectores, y documentación operativa. Retención de cola ya existía (Plan_BaseDeDatos).

### Qué se implementó

#### Fase 1 — Idempotent publish + retention

| Componente | Archivo |
|------------|---------|
| DTO resultado publish | `app/Middleware/Application/DTOs/PublishResult.php` |
| Publish idempotente | `EventPublisherService.php` |
| HTTP 200 / 201 | `EventQueueController.php` |
| Contrato publisher | `EventPublisherInterface.php` |

Retention: `platform:purge-retention` (Plan_BaseDeDatos) — referenciado, sin cambios.

#### Fase 2 — Async workers + retries + DLQ requeue

| Componente | Archivo |
|------------|---------|
| VO política retry | `app/Middleware/Domain/ValueObjects/RetryPolicy.php` |
| Repo retries | `RetryAttemptRepositoryInterface`, `EloquentRetryAttemptRepository` |
| Orquestación | `EventProcessingService.php` |
| Job async | `app/Middleware/Infrastructure/Jobs/ProcessEventJob.php` |
| Requeue use case | `RequeueDeadLetterUseCase.php` |
| API requeue | `DeadLetterController.php`, `api.php` |
| Queue/DLQ repos | `EloquentQueueEntryRepository`, `EloquentDeadLetterRepository` |

#### Fase 3 — Circuit breaker + sagas

| Componente | Archivo |
|------------|---------|
| Circuit breaker | `ConnectorCircuitBreaker.php` |
| Config resilience | `config/eventbus.php` |
| ADR sagas diferidas | `docs/production/ADR_006_saga_transactions.md` |

Sagas / tabla `transactions`: **no implementadas** (ADR-006).

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `EVENTBUS_ASYNC_PROCESSING` | Encolar `ProcessEventJob` | `false` |
| `EVENTBUS_ASYNC_LISTENERS` | Reservado listeners ShouldQueue | `false` |
| `EVENTBUS_PROCESSING_TIMEOUT` | Timeout job (seg) | `30` |
| `EVENTBUS_CIRCUIT_BREAKER_ENABLED` | Activar circuit breaker | `false` |
| `EVENTBUS_CIRCUIT_BREAKER_FAILURES` | Umbral fallos | `5` |
| `EVENTBUS_CIRCUIT_BREAKER_OPEN_SECONDS` | Ventana abierta | `60` |

### Decisiones técnicas

1. **Idempotencia en Application** — `existsByEventId` antes de insert; no QueryException al cliente.
2. **RetryPolicy en Domain** — max_attempts/backoff desde `config/eventbus.php`.
3. **Sync por defecto** — `EVENTBUS_ASYNC_PROCESSING=false` mantiene compatibilidad dev/test; prod activa worker Redis.
4. **Dos filas retry por intento exitoso** — `executing` + `completed` para trazabilidad operativa.
5. **Circuit breaker deshabilitado por default** — opt-in por env; cache store Laravel.
6. **Sagas diferidas** — retry + DLQ + requeue cubren piloto; compensación multi-paso en ADR-006.

### Archivos principales

**Nuevos:** `PublishResult.php`, `RetryPolicy.php`, `RetryAttemptRepositoryInterface`, `EloquentRetryAttemptRepository`, `EventProcessingService.php`, `ProcessEventJob.php`, `RequeueDeadLetterUseCase.php`, `ConnectorCircuitBreaker.php`, `Resiliencia.md`, `ADR_006_saga_transactions.md`, tests `ResilienceApiTest`, `RetryPolicyTest`.

**Modificados:** `EventPublisherService.php`, `EventPublisherInterface.php`, `EventQueueController.php`, `DeadLetterController.php`, `api.php`, `QueueEntryRepositoryInterface.php`, `DeadLetterRepositoryInterface.php`, repositorios Eloquent, `config/eventbus.php`, `MiddlewareServiceProvider.php`, `.env.example`, `EventPublisherServiceIntegrationTest.php`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **127 passed** (399 assertions) |
| `composer analyse` | OK |
| Idempotencia publish HTTP | OK |
| Requeue DLQ API | OK |
| Registro `retries` | OK |
| Coherencia DDD/EDA | Resiliencia en Infrastructure/Application; Domain VO + repos |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Async sin worker en prod | Alto | Documentar `queue:work`; `Resiliencia.md` |
| Circuit breaker solo infra conectores | Medio | Habilitar cuando haya integraciones externas |
| Listeners aún síncronos | Medio | `EVENTBUS_ASYNC_LISTENERS` reservado; job boundary actual |
| DLQ requeue sin fila message_queue previa | Bajo | BusTrackingListener crea tracking al redispatch |
| Sagas no implementadas | Medio | ADR-006; no bloquea piloto idempotente |

### Pendientes (fuera de Plan_Resiliencia)

- Listeners `ShouldQueue` por módulo (flag `async_listeners`)
- Wire circuit breaker en adaptadores IntegrationPack
- Saga orchestrator / tabla `transactions`
- Métricas retry/DLQ en dashboard observabilidad

### Impacto producción / cloud

- **Entrega at-least-once** con retry configurable y DLQ operable.
- **Idempotencia publish** evita 500 en reenvíos de clientes.
- **Cloud:** requiere Redis queue + worker cuando `EVENTBUS_ASYNC_PROCESSING=true`.
- **Estabilidad:** sync default preserva comportamiento actual; opt-in async.

*Última actualización: 2026-05-21 — Plan_Middleware.md*

---

## Plan_Middleware.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 parcial). Kafka broker real y orchestrator enterprise diferidos (ADR-007).

### Resumen ejecutivo

Se completó el pipeline middleware según documentación EDA: ingesta → `event_store` (canónico) → `event_logs` → `message_queue` → dispatch vía `EventBusPort`. Se añadió propagación de `correlation_id`/`causation_id`, schema registry por `event_type`, outbox pattern con relay job, adapter Kafka stub, y motor de workflows mínimo (trigger → `processing_jobs`).

### Qué se implementó

#### Fase 1 — event_store + correlation + event_logs

| Componente | Archivo |
|------------|---------|
| Entidad canónica | `StoredEvent.php`, `EventLogEntry.php` |
| Correlación | `CorrelationContext.php` |
| Repos | `EventStoreRepositoryInterface`, `EventLogRepositoryInterface`, Eloquent impl |
| Proyector | `EventLogProjector.php` |
| Pipeline publish | `EventPublisherService.php` (reordenado) |
| HTTP headers | `EventQueueController.php` |
| correlation en cola | `QueueEntry.php`, `EloquentQueueEntryRepository.php` |

#### Fase 2 — Schema validation per event_type

| Componente | Archivo |
|------------|---------|
| Schema registry | `EventSchemaRegistry.php` |
| Validación | `PublishPayloadSchemaValidator.php` (usa registry) |
| Config | `config/eventbus.php` → `schema_registry` |
| Versionado | `event_version`, `schema_version` en envelope / event_store |

#### Fase 3 — Outbox + EventBusPort + workflow engine

| Componente | Archivo |
|------------|---------|
| Puerto bus | `EventBusPort.php` |
| Adapters | `LaravelEventBusAdapter`, `KafkaEventBusAdapter` (stub) |
| Outbox | migración `outbox_messages`, `OutboxRepositoryInterface`, `RelayOutboxJob` |
| Orquestación | `EventProcessingService.php` (outbox + EventBusPort) |
| Workflows | `WorkflowEngine`, repos workflow/processing_jobs |
| ADR workflows | `ADR_007_workflow_orchestration.md` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `EVENTBUS_DRIVER` | `laravel` \| `kafka` | `laravel` |
| `EVENTBUS_OUTBOX_ENABLED` | Outbox pattern | `false` |
| `EVENTBUS_WORKFLOWS_ENABLED` | Trigger workflows | `false` |
| `EVENTBUS_KAFKA_BROKERS` | Brokers Kafka (futuro) | `localhost:9092` |
| `EVENTBUS_KAFKA_TOPIC` | Topic Kafka (stub) | `platform.events` |

### Decisiones técnicas

1. **event_store primero** — fuente canónica antes de message_queue; idempotencia consulta event_store OR message_queue.
2. **Dashboard BC read-only** — event_logs escrito por Middleware; feed sigue en Dashboard listener.
3. **EventBusPort** — desacopla Laravel Event de futuro Kafka sin cambiar Application services.
4. **Outbox opt-in** — `RelayOutboxJob` en cola middleware; compatible con `QUEUE_CONNECTION=sync` en tests.
5. **Kafka stub** — log only hasta Plan_Integraciones / infra broker.
6. **Workflows mínimos** — solo crea `processing_jobs`; pasos multi-paso en ADR-007.

### Archivos principales

**Nuevos:** `StoredEvent`, `EventLogEntry`, `CorrelationContext`, repos event_store/event_logs/outbox/workflow, models, `EventLogProjector`, `EventSchemaRegistry`, `EventBusPort`, adapters, `RelayOutboxJob`, `WorkflowEngine`, migración outbox, `Middleware.md`, `ADR_007`, tests pipeline/outbox/workflow/domain.

**Modificados:** `EventPublisherService`, `EventProcessingService`, `EventQueueController`, `PublishPayloadSchemaValidator`, `QueueEntry`, `EloquentQueueEntryRepository`, `MiddlewareServiceProvider`, `config/eventbus.php`, `.env.example`, `ResetOperationalDemoDataCommand`, `PublishPayloadSchemaValidatorTest`, `EventPublisherServiceIntegrationTest`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **133 passed** (413 assertions) |
| `composer analyse` | OK |
| event_store + event_logs on publish | OK |
| correlation_id HTTP/body | OK |
| Schema validation | OK |
| Outbox relay | OK |
| Workflow trigger | OK |
| Coherencia DDD/EDA | Write path Middleware BC; read projections Dashboard |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Sin transacción única store+queue | Medio | Idempotencia dual; retry cliente seguro |
| Kafka driver es stub | Medio | ADR/documentación; laravel default |
| workflow_steps sin ejecutor | Medio | ADR-007; flag disabled default |
| Outbox + async simultáneos | Bajo | Outbox tiene prioridad en EventProcessingService |
| Replay event_store no implementado | Medio | Fuera alcance plan; tabla lista |

### Pendientes (fuera de Plan_Middleware)

- Broker Kafka/RabbitMQ real con productor/consumidor
- Event sourcing replay desde event_store
- Ejecutor workflow_steps / Temporal
- Outbox relay scheduler en K8s cron
- Plan_Logs — enriquecer event_logs post-consumo

### Impacto producción / cloud

- **Auditoría canónica** — event_store poblado en cada publish.
- **Trazabilidad** — correlation_id end-to-end en store/logs/queue.
- **Escalabilidad** — outbox prepara relay a broker externo.
- **Cloud:** sin cambios breaking; flags opt-in para outbox/workflows/kafka.

*Última actualización: 2026-05-21 — Plan_Integraciones.md*

---

## Plan_Integraciones.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3). Notificaciones outbound y UI admin diferidas.

### Resumen ejecutivo

Se implementó la capa de integración omnicanal como bounded context `Integration`: webhook ingress con HMAC-SHA256, CRUD admin de channels/integrations, credenciales cifradas, pipeline de adapters, connector HTTP outbound, y enlace `channel_id`/`integration_id` en event_store/message_queue/event_logs.

### Qué se implementó

#### Fase 1 — Webhook ingress + signature + event_store

| Componente | Archivo |
|------------|---------|
| Webhook use case | `ReceiveWebhookUseCase.php` |
| HMAC verifier | `WebhookSignatureVerifier.php` |
| Audit trail | `EloquentWebhookRequestRepository` |
| Ingress API | `WebhookIngressController.php` |
| Ruta pública firmada | `POST /api/integrations/webhooks/{code}` |

#### Fase 2 — Admin API + credentials

| Componente | Archivo |
|------------|---------|
| Channel CRUD | `ChannelController` + use cases |
| Integration CRUD | `IntegrationController` + use cases |
| Crypt credentials | `IntegrationCredentialCipher`, `EloquentIntegrationCredentialRepository` |
| Ability RBAC | `integrations:admin` en roles y gates |
| Config | `config/integrations.php` |

#### Fase 3 — Outbound connector + adapters

| Componente | Archivo |
|------------|---------|
| Adapter interface | `IntegrationAdapterInterface` |
| Implementaciones | `JsonValidateAdapter`, `FieldMapAdapter` |
| Pipeline | `AdapterPipeline`, `AdapterRegistry` |
| HTTP connector | `HttpOutboundConnector`, `DispatchOutboundConnectorUseCase` |
| Adapter DB | `EloquentAdapterRepository` |

**Middleware link:** `StoredEvent`, `QueueEntry`, `EventLogEntry`, repos y `EventPublisherService` propagan `channel_id` / `integration_id`.

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `INTEGRATIONS_WEBHOOK_SIGNATURE_HEADER` | Header firma HMAC | `X-Webhook-Signature` |
| `INTEGRATIONS_WEBHOOK_REQUIRE_SECRET` | Rechazar sin secret | `true` |

### Decisiones técnicas

1. **BC Integration separado** — Supporting context; no contamina Middleware Domain.
2. **Webhook sin API key** — seguridad vía HMAC + secret en `integration_credentials`.
3. **Credenciales** — Laravel `Crypt::encryptString` at rest.
4. **Adapters strategy** — registro en container; chain desde BD o `integration.config`.
5. **Outbound** — Laravel HTTP client (no guzzle directo; cumple plan vía built-in).
6. **Ability dedicada** — `integrations:admin` separada de `bus:admin`.

### Archivos principales

**Nuevo módulo:** `app/Integration/` (Domain, Application, Infrastructure, Interfaces), `IntegrationServiceProvider`, routes, `config/integrations.php`, `Integraciones.md`, tests Feature/Unit Integration.

**Modificados:** `AppServiceProvider`, `IdentityServiceProvider`, `platform_roles.php`, `platform_auth.php`, `PlatformApiAuthenticator`, `StoredEvent`, `QueueEntry`, `EventLogEntry`, repos Middleware, `EventPublisherService`, `Matriz_Endpoints_Seguridad.md`, `.env.example`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **138 passed** (431 assertions) |
| `composer analyse` | OK |
| Webhook HMAC válido/inválido | OK |
| CRUD channels/integrations | OK |
| Credenciales encrypt/decrypt | OK |
| Outbound HTTP fake | OK |
| event_store integration_id | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Webhook endpoint público (sin WAF) | Alto | HMAC obligatorio; WAF en plan Seguridad fase posterior |
| Sin UI admin integraciones | Medio | API REST operable; portal futuro |
| Notificaciones outbound no implementadas | Medio | Fuera alcance plan mínimo |
| providers table sin CRUD API | Bajo | integrations acepta provider_id nullable |
| Replay webhook duplicado | Medio | Idempotencia publish por event_id |

### Pendientes (fuera de Plan_Integraciones)

- UI admin canales/proveedores
- Notificaciones outbound (`notifications` table)
- WAF / rate limit por integration_code
- OAuth refresh en `integration_credentials`
- Marketplace adapters dinámicos (handler_class)

### Impacto producción / cloud

- **Hub integración operativo** — webhooks + admin API + outbound template.
- **Trazabilidad** — channel/integration en store y cola.
- **Cloud:** sin dependencias nuevas; HTTP client Laravel.
- **Seguridad:** secrets cifrados; webhook HMAC default on.

*Última actualización: 2026-05-21 — Plan_Logs.md*

---

## Plan_Logs.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3). Agente ELK/CloudWatch embebido diferido (ADR-008).

### Resumen ejecutivo

Se implementó trazabilidad completa: logging JSON estructurado con contexto de correlación, servicios `AuditLogService` y `EventLogService`, redacción/hash de payloads en logs técnicos, retención automatizada documentada, y patrón cloud stderr JSON para agregadores.

### Qué se implementó

#### Fase 1 — JSON logs + correlation context

| Componente | Archivo |
|------------|---------|
| Config logging JSON | `config/logging.php` (channels `json`, `stderr_json`) |
| Política logging | `config/platform_logging.php` |
| Contexto request | `StructuredLogContext`, `ShareCorrelationLogContext` |
| Logger seguro | `PlatformStructuredLogger` |
| Provider | `LoggingServiceProvider` |
| Middleware API | prepended en `bootstrap/app.php` |

#### Fase 2 — audit_logs + event_logs services

| Componente | Archivo |
|------------|---------|
| AuditLogService | `Shared/Logging/Services/AuditLogService.php` |
| EventLogService | `Middleware/Application/Services/EventLogService.php` |
| Lifecycle rows | `EventLogEntry::lifecycle()` — received/processed/failed |
| Wiring | `EventLogProjector`, `BusTrackingListener`, `EventProcessingService` (failed/DLQ) |
| Publish logs | `EventPublisherService` → `PlatformStructuredLogger` |

#### Fase 3 — Cloud + retention

| Componente | Detalle |
|------------|---------|
| ADR cloud shipping | `ADR_008_cloud_log_shipping.md` |
| Retención | `platform:purge-retention` daily 02:30 (existente Plan_BaseDeDatos) |
| Docs operativas | `Logs.md` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `LOG_STACK` | Canales stack (`single`, `json`, `stderr_json`) | `single` |
| `LOG_STDERR_JSON` | Formatter JSON en stderr | `false` |

Existentes: `LOG_CHANNEL`, `LOG_LEVEL`, `PLATFORM_AUDIT_ENABLED`, `RETENTION_*`.

### Decisiones técnicas

1. **Tres capas** — laravel.log (técnico), event_logs (operacional), audit_logs (compliance).
2. **Sin payload completo en logs técnicos** — `payload_hash` + redacción de claves sensibles.
3. **processed en BusTrackingListener** — evita duplicados con EventProcessingService.
4. **failed en EventProcessingService + DLQ** — trazabilidad de errores de dispatch.
5. **Cloud via stderr JSON** — sin SDK embebido; sidecar/agent (ADR-008).
6. **AuditLogWriter preservado** — AuditLogService es fachada; middleware audit sin cambios breaking.

### Archivos principales

**Nuevos:** `config/logging.php`, `platform_logging.php`, `StructuredLogContext`, `PlatformStructuredLogger`, `ShareCorrelationLogContext`, `AuditLogService`, `EventLogService`, `LoggingServiceProvider`, `Logs.md`, `ADR_008`, tests Logging/*.

**Modificados:** `bootstrap/app.php`, `bootstrap/providers.php`, `EventLogEntry`, `EventLogProjector`, `EventPublisherService`, `EventProcessingService`, `BusTrackingListener`, `MiddlewareServiceProvider`, `.env.example`.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| `composer test` | **142 passed** (441 assertions) |
| `composer analyse` | OK |
| event_logs received + processed | OK |
| audit_logs write | OK |
| Correlation in event_logs | OK |
| Payload hash/redaction | OK |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Múltiples filas processed por re-dispatch | Bajo | Append-only event_logs; filtrar por status en queries |
| trace_logs sin writer | Medio | Plan_Observabilidad futuro |
| Sin agregador cloud en dev | Bajo | LOG_STACK=single local |
| PII en payload si schema laxo | Medio | Docs qué NO loguear; hash default |

### Pendientes (fuera de Plan_Logs)

- Writer `trace_logs` (OpenTelemetry)
- Handler Monolog CloudWatch directo
- Dashboard búsqueda event_logs/audit_logs
- Alertas sobre failed status en event_logs

### Impacto producción / cloud

- **Forensics** — event_logs + audit_logs searchable en BD.
- **Cloud** — `LOG_STACK=stderr_json` listo para Fluent Bit/CloudWatch.
- **Compliance** — audit_logs retención 7 años configurable.
- **Estabilidad** — sin breaking changes en API.

### Próximo plan recomendado

**Plan_Observabilidad.md**.

---

## Plan_Observabilidad.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3 documentada).

### Resumen ejecutivo

Se implementó observabilidad three-pillars para el middleware: correlation ID end-to-end, spans en `trace_logs`, export Prometheus en `/metrics`, SLIs documentados, dashboard Grafana en repo y ADR OpenTelemetry (SDK diferido, trazabilidad ligera activa).

### Qué se implementó

#### Fase 1 — Correlation ID + structured context

| Componente | Archivo |
|------------|---------|
| Middleware correlation | `app/Http/Middleware/CorrelationIdMiddleware.php` |
| Alias retrocompatible | `app/Http/Middleware/ShareCorrelationLogContext.php` |
| Fallback HTTP → envelope | `CorrelationContext.php` + `StructuredLogContext` |
| Migración feed | `database/migrations/2026_05_21_160000_add_correlation_id_to_event_feed_projections.php` |
| Propagación feed | `EventFeedEntry`, `EventFeedProjector`, `UniversalDashboardFeedListener`, `EloquentEventFeedRepository` |

#### Fase 2 — trace_logs + Prometheus + Grafana

| Componente | Archivo |
|------------|---------|
| BC Observability | `app/Observability/` |
| Trace spans | `TraceSpanService`, `EloquentTraceLogRepository`, `TraceContext` |
| SLI recorder | `SliMetricsRecorder` |
| Prometheus export | `PrometheusMetricsExporter`, `PrometheusMetricsController` |
| SSE métricas | `StreamConnectionTracker` + `StreamLiveEventsUseCase` |
| Wiring publish/track/project | `EventPublisherService`, `BusTrackingListener`, `EventFeedProjector` |
| Config | `config/platform_observability.php`, `config/platform_slos.php` |
| Dashboard Grafana | `docs/observability/grafana/middleware_dashboard.json` |
| Ruta | `GET /metrics` |

#### Fase 3 — OpenTelemetry

| Entregable | Archivo |
|------------|---------|
| ADR SDK diferido + patrón sidecar | `docs/production/ADR_009_opentelemetry_distributed_tracing.md` |
| Guía operativa | `docs/production/Observabilidad.md` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_PROMETHEUS_ENABLED` | Habilita `/metrics` | `true` |
| `PLATFORM_TRACE_SPANS_ENABLED` | Escribe `trace_logs` | `true` |
| `PLATFORM_OBSERVABILITY_SERVICE_NAME` | Nombre servicio en spans | `PLATFORM_CLIENT_SLUG` |
| `PLATFORM_SLO_BUS_LATENCY_P99_MS` | SLO latencia bus | `2000` |
| `PLATFORM_SLO_DLQ_MAX` | SLO DLQ máximo | `10` |
| `PLATFORM_SLO_FEED_LAG_P99_MS` | SLO lag feed | `3000` |
| `PLATFORM_SLO_SSE_MAX_CONNECTIONS` | SLO conexiones SSE | `500` |

### Decisiones técnicas

1. **`CorrelationIdMiddleware` reemplaza lógica de `ShareCorrelationLogContext`** — genera UUID si falta y devuelve header en response.
2. **`trace_id` = `correlation_id`** cuando existe — simplifica correlación SQL sin OTel SDK.
3. **Prometheus sin package externo** — exporter texto plano desde read models existentes.
4. **OpenTelemetry SDK diferido (ADR-009)** — compatible con collector sidecar en cloud.
5. **Observability como Supporting Domain** — listeners invocan servicios; sin acoplar Application de negocio.

### Archivos modificados / creados

- `bootstrap/app.php`, `bootstrap/providers.php`
- `app/Providers/LoggingServiceProvider.php`
- `app/Middleware/Application/Services/EventPublisherService.php`
- `app/Middleware/Listeners/BusTrackingListener.php`
- `app/Middleware/Domain/ValueObjects/CorrelationContext.php`
- `app/Dashboard/Infrastructure/Projectors/EventFeedProjector.php`
- `app/Dashboard/Listeners/UniversalDashboardFeedListener.php`
- `app/Dashboard/Application/UseCases/StreamLiveEventsUseCase.php`
- `.env.example`
- Tests: `tests/Feature/Observability/*`, `tests/Integration/Observability/TraceLogsPipelineIntegrationTest.php`

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| Tests PHPUnit | **147 tests, 464 assertions — OK** |
| PHPStan | **OK — sin errores** |
| Correlation HTTP → queue → feed | Test integración |
| Spans publish/track/project | Test integración |
| Endpoint `/metrics` | Test feature |
| Coherencia DDD/EDA | BC Observability aislado; eventos sin cambio de contrato |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| `/metrics` público sin auth | Medio | Restringir en reverse proxy / security group |
| `trace_logs` crecimiento | Medio | Retención futura en Plan_Monitoreo |
| OTel no nativo | Bajo | ADR-009 + sidecar path |
| TraceSpanService stateful por request | Bajo | Singleton; root span por operación publish |

### Pendientes (fuera de Plan_Observabilidad)

- Plan_Monitoreo.md (alerting, retención trace_logs)
- Export OTLP real
- Auto-instrumentación HTTP outbound

### Impacto producción / cloud

- **MTTR** — correlación HTTP → cola → feed → spans.
- **Alerting** — Prometheus scrape `/metrics`; Grafana dashboard importable.
- **Cloud** — compatible ECS/K8s + sidecar OTel futuro.
- **Estabilidad** — sin breaking changes; middleware alias retrocompatible.

### Próximo plan recomendado

**Plan_Cloud.md** (despliegue productivo completo con stack de monitoreo).

---

## Plan_Monitoreo.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3).

### Resumen ejecutivo

Se implementó monitoreo proactivo 24/7: uptime en `/up`, evaluación de alertas internas, export Prometheus ampliado, templates Alertmanager/Grafana, canary sintético cada 5 min, runbooks operativos y retención de `trace_logs`.

### Qué se implementó

#### Fase 1 — Uptime + alertas manuales

| Entregable | Archivo |
|------------|---------|
| Config monitoreo | `config/platform_monitoring.php` |
| Checklist uptime externo | `docs/monitoring/Uptime_Checklist.md` |
| Checklist alertas manuales | `docs/monitoring/Alertas_Manual_Checklist.md` |
| Endpoints existentes documentados | `/up`, `/health/ready` |

#### Fase 2 — Prometheus + Alertmanager + Grafana

| Entregable | Archivo |
|------------|---------|
| Reglas de alerta | `docs/monitoring/prometheus/alert_rules.yml` |
| Scrape config | `docs/monitoring/prometheus/prometheus.yml` |
| Alertmanager template | `docs/monitoring/alertmanager/alertmanager.yml` |
| Métricas ampliadas | `bus_error_rate_percent`, `bus_stream_status`, `database_usage_percent`, `queue_jobs_pending`, `canary_last_success_age_seconds` |
| BC Monitoring | `app/Monitoring/` — `AlertEvaluationService`, checkers, commands |
| Comando evaluación | `platform:monitoring-evaluate` (scheduler cada minuto) |

#### Fase 3 — Canary + SLO dashboards

| Entregable | Archivo |
|------------|---------|
| Canary sintético | `CanaryPublishService`, `platform:canary-publish` |
| Scheduler | `routes/console.php` — cada 5 min |
| SLO dashboard Grafana | `docs/observability/grafana/slo_dashboard.json` |
| Runbook por alerta | `docs/monitoring/Runbook_Alertas.md` |
| Guía operativa | `docs/production/Monitoreo.md` |
| Retención trace_logs | `config/platform_retention.php`, `PlatformRetentionService` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_MONITORING_ENABLED` | Activa evaluación de alertas | `true` |
| `PLATFORM_CANARY_ENABLED` | Activa canary sintético | `true` |
| `PLATFORM_CANARY_EVENT_TYPE` | Tipo de evento canary | `Platform.Monitoring.Canary` |
| `PLATFORM_ALERT_*` | Umbrales de alertas | ver `.env.example` |
| `PLATFORM_DB_MAX_SIZE_MB` | Límite BD para alerta DiskSpace | `10240` |
| `PLATFORM_MONITOR_QUEUES` | Colas monitoreadas | `middleware,dashboard-feed,default` |
| `RETENTION_TRACE_LOGS_DAYS` | Retención trace_logs | `14` |

### Decisiones técnicas

1. **BC Monitoring separado de Observability** — evaluación de alertas vs export de métricas.
2. **Alertmanager/Slack/PagerDuty como templates** — credenciales por cliente fuera del repo.
3. **BusStopped requiere 5 min sostenidos** — evita falsos positivos en idle transitorio.
4. **Canary vía EventPublisherService** — valida pipeline real publish → track → project.
5. **trace_logs en purge-retention** — cierra pendiente de Plan_Observabilidad sin scope creep.

### Archivos modificados / creados

- `bootstrap/providers.php`
- `routes/console.php`
- `app/Observability/Application/Services/PrometheusMetricsExporter.php`
- `app/Shared/Platform/Services/PlatformRetentionService.php`
- `app/Console/Commands/PurgePlatformRetentionCommand.php`
- `config/platform_retention.php`, `.env.example`
- Tests: `tests/Feature/Monitoring/*`

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| Tests PHPUnit | **150 tests, 475 assertions — OK** |
| PHPStan | **OK** |
| Canary publish E2E | Test feature |
| `/metrics` con nuevas métricas | Test feature |
| Coherencia DDD/EDA | BC Monitoring Supporting Domain; canary usa bus existente |
| Docker scheduler/worker | Compatible — comandos en `routes/console.php` |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Alertmanager sin configurar en dev | Bajo | Checklist manual + `platform:monitoring-evaluate` |
| BusStopped en entornos sin tráfico | Medio | Canary cada 5 min genera throughput |
| DiskSpace aproximado en SQLite | Bajo | Documentado; MySQL usa information_schema |
| `/metrics` sin auth | Medio | Restringir en proxy (igual que Observabilidad) |

### Pendientes (fuera de Plan_Monitoreo)

- Integración Datadog agent (mencionada como opción cliente)
- PagerDuty/Slack wiring real (credenciales por instancia)
- Plan_Cloud.md — stack Prometheus en docker-compose producción

### Impacto producción / cloud

- **Detección proactiva** — alertas P1/P2 evaluadas cada minuto.
- **SLA demostrable** — SLO dashboard + canary freshness metric.
- **Cloud** — templates Prometheus/Alertmanager listos para K8s/ECS.
- **Estabilidad** — sin breaking changes en APIs existentes.

### Próximo plan recomendado

**Plan_Cloud.md**.

---

## Plan_Calidad.md — 2026-05-22

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3).

### Resumen ejecutivo

Se completó la suite de calidad enterprise: CI reforzado con verificación de conteos en README, test prioritario event_store idempotency, load test k6 (100 eps), Playwright UI smoke, OWASP ZAP baseline en staging, config `platform_quality.php` y documentación operativa.

### Qué se implementó

#### Fase 1 — CI + PHPStan + validate-catalog

| Entregable | Archivo |
|------------|---------|
| CI existente reforzado | `.github/workflows/ci.yml` (+ stats check) |
| Sync conteos README | `docs/testing/tools/sync_test_stats.php` |
| Comandos composer | `test:stats`, `test:stats:check` |
| Config calidad | `config/platform_quality.php`, `QualityServiceProvider` |
| Matriz tests prioritarios | `docs/testing/priority_tests_matrix.md` |
| Test event_store idempotency | `tests/Integration/Middleware/EventStoreIdempotencyIntegrationTest.php` |

*Nota:* CI base (PHPUnit, PHPStan L5, Pint, validate-catalog, coverage ≥70%) implementado previamente en Plan_CI_CD — Plan_Calidad lo formaliza y extiende.

#### Fase 2 — Coverage + load test

| Entregable | Archivo |
|------------|---------|
| Coverage Application ampliado | `scripts/ci/check-application-coverage.php` (+ Integration/Monitoring/Observability Application) |
| k6 100 eps sustained | `docs/testing/load/k6_publish_sustained.js` |
| Runner | `scripts/ci/run-k6-load-test.sh` |
| CI load semanal | `.github/workflows/quality-load.yml` |

#### Fase 3 — UI E2E + security scan

| Entregable | Archivo |
|------------|---------|
| Playwright smoke | `tests/e2e-ui/smoke-pages.spec.js`, `playwright.config.js` |
| CI UI E2E | `.github/workflows/quality-ui-e2e.yml` |
| OWASP ZAP baseline | `.github/workflows/staging.yml` |
| Guía | `docs/production/Calidad.md` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_QUALITY_COVERAGE_MIN` | Umbral cobertura Application | `70` |
| `PLATFORM_LOAD_TEST_EPS` | Eventos/segundo load test | `100` |
| `PLATFORM_LOAD_TEST_DURATION` | Duración load test (s) | `60` |
| `PLATFORM_ZAP_BASELINE_ENABLED` | ZAP en staging | `true` |

### Decisiones técnicas

1. **PHPUnit como runner oficial** — Pest instalado pero no usado en CI (documentado en Calidad.md).
2. **Load/UI E2E en workflows separados** — no bloquean cada PR; load semanal + UI en main/dispatch.
3. **Stats check value-based** — compara conteos reales vs README, no diff textual.
4. **ZAP continue-on-error** — baseline informativo en staging sin bloquear deploy inicial.
5. **Tests prioritarios ya existían** — solo se añadió event_store idempotency dedicado.

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| PHPUnit | **151 tests, 476 assertions — OK** |
| PHPStan | **OK** |
| sync_test_stats --check | **OK** |
| Coherencia DDD/EDA | Tests observacionales; sin lógica de negocio en middleware |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| Load test no en cada PR | Bajo | Workflow semanal + manual dispatch |
| Playwright requiere npm ci + build | Medio | Documentado en Calidad.md |
| ZAP baseline falsos positivos | Bajo | continue-on-error; revisión manual |
| Coverage gate puede fallar tras BC nuevos | Medio | Ampliar tests Application |

### Pendientes (fuera de Plan_Calidad)

- Contract tests OpenAPI
- Chaos testing
- Migración Pest (opcional futuro)
- ESLint frontend en CI

### Impacto producción / cloud

- **Regresiones** — CI enforcement + coverage gate + stats sync.
- **Estabilidad** — sin cambios runtime de negocio.

### Próximo plan recomendado

**Plan_Cloud.md**.

---

## Plan_APIs.md — 2026-05-21

**Estado:** Completado (Fase 1 + Fase 2 + Fase 3).

### Resumen ejecutivo

Se formalizaron las APIs del middleware omnicanal como contratos versionados: OpenAPI 3.0, rutas `/api/v1/`, paginación estándar en queue/feed, RFC 7807 Problem Details, `Idempotency-Key` en publish, headers `X-RateLimit-*`, colección Postman, changelog, política de breaking changes y lint Spectral en CI. Las rutas legacy `/api/*` se mantienen sin breaking changes.

### Qué se implementó

#### Fase 1 — OpenAPI + documentación

| Entregable | Archivo |
|------------|---------|
| Especificación OpenAPI 3.0.3 | `docs/api/openapi.yaml` |
| Colección Postman | `docs/api/postman_collection.json` |
| Reglas Spectral | `docs/api/spectral.yaml` |
| Changelog API | `docs/api/CHANGELOG.md` |
| Política breaking changes | `docs/api/BREAKING_CHANGE_POLICY.md` |
| Guía operativa APIs | `docs/production/APIs.md` |
| Lint OpenAPI CI | `scripts/ci/lint-openapi.sh` |

*Nota:* Se eligió OpenAPI hand-crafted en lugar de Scribe/l5-swagger para evitar dependencia pesada y mantener control del contrato v1 congelado.

#### Fase 2 — Versionado + paginación

| Componente | Archivo |
|------------|---------|
| Config API plataforma | `config/platform_api.php` |
| Service provider v1 | `app/Shared/Api/Interfaces/Providers/ApiServiceProvider.php` |
| Registradores de rutas compartidos | `app/Shared/Api/Routes/{Middleware,Dashboard,Integration}ApiRoutes.php` |
| Paginación envelope | `app/Shared/Api/Http/Responses/PaginationEnvelope.php` |
| Repositorios paginados | `EloquentQueueEntryRepository`, `EloquentEventFeedRepository` (+ interfaces) |
| Use cases paginados | `GetEventQueueUseCase`, `GetRecentEventFeedUseCase` |
| Controllers | `EventQueueController`, `EventFeedController` |

**Rutas v1 registradas:**

- `api/v1/middleware/*`
- `api/v1/dashboard/*`
- `api/v1/integrations/*`

**Rutas legacy** (delegación a mismos registradores):

- `app/Middleware/Interfaces/Routes/api.php` → `api/middleware`
- `app/Dashboard/Interfaces/Routes/api.php` → `api/dashboard`
- `app/Integration/Interfaces/Routes/api.php` → `api/integrations`

#### Fase 3 — Contratos + políticas

| Entregable | Archivo |
|------------|---------|
| RFC 7807 Problem Details | `app/Shared/Api/Http/Responses/ProblemDetailsFactory.php` |
| Idempotency store (cache) | `app/Shared/Api/Application/Services/IdempotencyKeyStore.php` |
| Rate limit headers | `app/Shared/Api/Http/Middleware/AppendRateLimitHeadersMiddleware.php` |
| Exception render auth | `bootstrap/app.php` |
| Auth 401 Problem Details | `app/Http/Middleware/AuthenticatePlatformApi.php` |
| Tests contrato API | `tests/Feature/Api/*` |
| CI Spectral | `.github/workflows/ci.yml`, `composer validate-openapi` |

### Variables de entorno nuevas

| Variable | Propósito | Default |
|----------|-----------|---------|
| `PLATFORM_API_PROBLEM_DETAILS` | Habilitar RFC 7807 en errores API | `true` |
| `PLATFORM_API_IDEMPOTENCY_ENABLED` | Cache Idempotency-Key | `true` |
| `PLATFORM_API_IDEMPOTENCY_TTL` | TTL cache idempotencia (s) | `86400` |
| `PLATFORM_API_DEFAULT_LIMIT` | Límite paginación default | `50` |
| `PLATFORM_API_MAX_LIMIT` | Límite paginación máximo | `200` |

### Decisiones técnicas

1. **v1 = comportamiento actual congelado** — legacy `/api/*` sigue activo; v2 reservado para breaking (auth obligatorio global).
2. **Paginación opt-in** — `?page=&limit=` activa envelope paginado; `?limit=` legacy sin cambio.
3. **Problem Details opt-out** — `PLATFORM_API_PROBLEM_DETAILS=false` restaura `{ success, error }` en validación y auth.
4. **Idempotency-Key complementa idempotencia por event_id** — cache HTTP devuelve primera respuesta (201/200).
5. **Shared/Api como bounded context transversal** — registradores de rutas evitan duplicación entre legacy y v1.
6. **Spectral vía npx** — sin lock-in de versión en composer; script bash portable.

### Archivos modificados / creados

**Nuevos:** `app/Shared/Api/**`, `config/platform_api.php`, `docs/api/*`, `docs/production/APIs.md`, `scripts/ci/lint-openapi.sh`, `tests/Feature/Api/*`.

**Modificados:** `EventQueueController`, `EventFeedController`, repositorios queue/feed, route files legacy, `bootstrap/app.php`, `bootstrap/providers.php`, `AuthenticatePlatformApi.php`, `composer.json`, `.github/workflows/ci.yml`, `.env.example`, `tests/Feature/Middleware/MiddlewareControlApiTest.php`.

### Arquitectura afectada

| Capa | Impacto |
|------|---------|
| **Interfaces (Middleware/Dashboard)** | Controllers exponen paginación e idempotencia; rutas delegadas a Shared/Api |
| **Application** | Use cases con métodos paginados |
| **Infrastructure** | Repositorios con `getPaginated` / `countAll` |
| **Shared/Api** | Nuevo módulo transversal (contratos HTTP, no dominio) |
| **EDA** | Sin cambio en publicación de eventos; idempotencia HTTP es capa de interfaz |

### Validaciones realizadas

| Validación | Resultado |
|------------|-----------|
| PHPUnit | **160 tests, 517 assertions — OK** |
| PHPStan | **OK** |
| Coherencia DDD/EDA | Rutas en Interfaces; lógica en Application/Domain intacta |
| Compatibilidad legacy | Rutas `/api/middleware/*` sin cambio de path |
| Config merge | `ApiServiceProvider` registra `platform_api.php` |

### Riesgos detectados

| Riesgo | Severidad | Mitigación |
|--------|-----------|------------|
| OpenAPI no generado desde código | Medio | Contract tests + Spectral lint en CI |
| Problem Details cambia shape de errores legacy | Bajo | Opt-out via env; tests adaptados |
| Idempotency cache en memoria/file | Medio | Documentado; Redis recomendado en prod |
| SSE stream sin paths OpenAPI completos | Bajo | Documentado en APIs.md; fuera de alcance v1 |
| Dual routes legacy + v1 | Bajo | Mismo controller; comportamiento idéntico |

### Pendientes (fuera de Plan_APIs)

- Webhook API endpoints (Plan_Integraciones)
- OpenAPI auto-generado (Scribe) — opcional futuro
- v2 con auth breaking change coordinado con clientes
- Documentación SSE completa en OpenAPI

### Impacto producción / cloud

- **Integradores** — contrato OpenAPI + Postman + v1 estable.
- **Operaciones** — changelog y política de breaking changes formalizada.
- **Cloud** — sin dependencia infra nueva; idempotency usa cache Laravel (Redis en prod).
- **Estabilidad** — legacy intacto; 160 tests verdes.

### Próximo plan recomendado

**Plan_Cloud.md** o **Plan_Integraciones.md** (webhooks).
