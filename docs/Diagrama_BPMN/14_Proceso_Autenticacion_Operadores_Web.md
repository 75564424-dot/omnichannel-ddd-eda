# PROC-005 — Autenticación operadores web

**ID:** PROC-005  
**Versión documento:** 1.0  
**Fecha:** 2026-06-27  
**Estado:** Implementado  
**Tipo:** Técnico — Seguridad / Administrativo  
**Macroproceso:** MP-04 Seguridad y Acceso

---

## Descripción

Proceso de autenticación por sesión web (Inertia) para operadores humanos de la plataforma: administradores SaaS del control plane y operadores de instancia cliente (portal tenant). El flujo cubre acceso a `/login`, validación de credenciales, verificación de rol y contexto de instancia, y redirección post-login hacia el home operativo (`/dashboard`, `/middleware` o `/control/overview`) según `platform_role`.

OAuth2 / IdP enterprise está **diferido** (ADR-002); la compuerta documental ACT-029/GW-OAUTH2 no está implementada en runtime.

---

## Objetivo

Autenticar operadores web con sesión Laravel/Sanctum session guard, aplicar RBAC por rol y contexto multi-instancia (ADR-001), y dirigir cada operador al panel correcto sin mezclar acceso SaaS control plane con portal de instancia cliente.

---

## Alcance

**Incluye:**

- `GET/POST /login` (ACT-014).
- `AuthenticateOperatorUseCase` — intento de credenciales, validación SaaS vs instancia, binding tenant portal.
- `ResolveOperatorHomePathUseCase` — redirección post-login (GW-AUTH-OK).
- Middleware `auth.platform.web`, `instance.web`, `control.web` en rutas posteriores.
- Logout `POST /logout`.

**Excluye:**

- Autenticación API M2M (PROC-006).
- OAuth2 / Passport / IdP enterprise (ACT-029, ADR-002 diferido).
- MFA custom en aplicación (delegado a IdP en Fase 3 documentada).
- Gestión CRUD de usuarios en CP (PROC-007).

---

## Actores

| Actor | Rol en el proceso |
|-------|---------------------|
| Operador SaaS (`saas_admin`) | Administra tenants desde control plane |
| Operador instancia (`platform_admin`, `bus_operator`, `dashboard_viewer`) | Opera dashboard/middleware del silo cliente |
| `LoginController` | Punto HTTP de login/logout |
| `AuthenticateOperatorUseCase` | Valida credenciales y contexto tenant |
| `ResolveOperatorHomePathUseCase` | Resuelve URL home por rol |
| `EnsureInstanceWebAuth` / `EnsureControlWebAuth` | Protegen rutas web post-login |

---

## Entradas

| Entrada | Formato | Origen |
|---------|---------|--------|
| Credenciales | `email`, `password`, `remember` (opcional) | Formulario Inertia `/login` |
| Sesión previa | Cookie session Laravel | Navegador |
| URL intended | `url.intended` en sesión | Middleware `auth` guest redirect |
| Config instancia | `PLATFORM_CONTROL_PLANE`, `platform.client_slug`, `platform_auth.web_auth_enabled` | `.env` / `config/platform.php`, `config/platform_auth.php` |
| Rol operador | `users.platform_role`, `users.tenant_id` | BD |

---

## Salidas

| Salida | Descripción |
|--------|-------------|
| Sesión autenticada | `Auth::user()` con session regenerada |
| Redirección home | `/control/overview`, `/dashboard`, `/middleware` o URL intended |
| Error validación | `back()->withErrors(['email' => ...])` |
| Logout | Sesión terminada; redirect `/login` |

---

## Reglas de negocio

| ID | Regla | Evidencia |
|----|-------|-----------|
| RN-005-01 | `saas_admin` solo puede autenticarse en host con `platform.control_plane=true` | `AuthenticateOperatorUseCase` L35–44 |
| RN-005-02 | Operadores instancia deben tener `tenant_id` asignado | `AuthenticateOperatorUseCase` L50–57 |
| RN-005-03 | En despliegue dedicado, `tenant_id` del usuario debe coincidir con tenant de la instancia (`PLATFORM_CLIENT_SLUG`) | `AuthenticateOperatorUseCase` L63–74 |
| RN-005-04 | Post-login: `saas_admin` → `control.overview`; `bus_operator` → `middleware`; `platform_admin`/`dashboard_viewer` → `dashboard` | `ResolveOperatorHomePathUseCase` |
| RN-005-05 | Si `web_auth_enabled=false`, login redirige directo a dashboard | `LoginController::create` L25–27 |
| RN-005-06 | OAuth2 enterprise no está activo; Sanctum session + PAT es el modelo vigente | ADR-002 |

---

## Precondiciones

1. Usuario operador existente en BD (seed o alta PROC-007/008).
2. Instancia con `PLATFORM_WEB_AUTH_ENABLED` según política de entorno.
3. Para SaaS admin: proceso desplegado como control plane (`PLATFORM_CONTROL_PLANE=true`).
4. Para operadores instancia: URL del silo cliente correcta (ADR-001).

---

## Postcondiciones

1. Sesión web válida con rol y tenant coherentes.
2. Operador ubicado en panel autorizado (GW-AUTH-OK).
3. Rutas protegidas accesibles según middleware `instance.web` / `control.web`.
4. En fallo: sin sesión activa y mensaje de error en formulario login.

---

## Flujo principal (paso a paso)

1. **EVT-INICIO-LOGIN:** Operador navega a `GET /login` (`routes/web.php` L15).
2. **ACT-014 (create):** `LoginController::create` — si ya autenticado, redirige a home path; si auth deshabilitado, redirige a dashboard; si no, renderiza `Auth/Login`.
3. Operador envía `POST /login` con email/password.
4. **ACT-014 (store):** Valida request (`email`, `password`, `remember`).
5. Invoca `AuthenticateOperatorUseCase::execute`:
   - `Auth::attempt` con credenciales.
   - Si `saas_admin` y no control plane → logout + error.
   - Si operador instancia sin `tenant_id` → logout + error.
   - Si tenant no coincide con instancia dedicada → logout + error.
   - `session()->regenerate()` en éxito.
6. **GW-AUTH-OK:** Si credenciales inválidas → flujo alternativo error.
7. Si existe `url.intended` en sesión → redirige a URL intended.
8. **GW-AUTH-OK (rol):** `ResolveOperatorHomePathUseCase::execute`:
   - `saas_admin` → `/control/overview` (`control.overview`).
   - `bus_operator` → `/middleware`.
   - `platform_admin` / `dashboard_viewer` → `/dashboard`.
9. Operador accede a rutas con middleware `auth.platform.web` + `instance.web` o `control.web`.

---

## Flujos alternativos

| ID | Condición | Resultado |
|----|-----------|-----------|
| FA-01 | Usuario ya autenticado en `GET /login` | Redirect inmediato a home path |
| FA-02 | `web_auth_enabled=false` | Redirect `/dashboard` sin formulario |
| FA-03 | URL intended guardada (guest middleware) | Post-login redirect a intended en lugar de home |
| FA-04 | Rol sin mapping en `ResolveOperatorHomePathUseCase` | Fallback `route('dashboard')` |
| FA-05 | **GW-OAUTH2 / ACT-029 (diferido)** | Requisito enterprise SSO → flujo IdP documentado en ADR-002, no implementado |

---

## Excepciones

| Excepción | Manejo |
|-----------|--------|
| Credenciales inválidas | `Invalid credentials.` en campo email |
| SaaS admin fuera de CP | Error: panel SaaS solo en URL control plane |
| Cuenta sin empresa | Error tenant no asignado |
| Cuenta de otra instancia | Error URL empresa dedicada |
| `saas_admin` en rutas instancia | `EnsureInstanceWebAuth` redirige a CP o logout |

---

## Eventos

| Evento | Tipo BPMN | Descripción |
|--------|-----------|-------------|
| EVT-INICIO-LOGIN | Inicio | Operador accede `/login` |
| EVT-SESSION-REGEN | Intermedio | `session()->regenerate()` tras auth OK |
| EVT-AUTH-OK | Intermedio | Sesión establecida |
| EVT-AUTH-FAIL | Intermedio | Credenciales o contexto rechazado |
| EVT-LOGOUT | Fin / inicio logout | `POST /logout` termina sesión |

---

## Dependencias

| Dependencia | Tipo | Proceso / componente |
|-------------|------|-------------------|
| Alta operadores / tenants | Previo | PROC-007, PROC-008 |
| Portal instancia (acceso posterior) | Posterior | PROC-019 |
| Control plane rutas | Posterior | PROC-007 |
| API tokens integradores | Paralelo | PROC-006 (distinto canal) |

---

## Riesgos

| ID | Riesgo | Mitigación documentada |
|----|--------|------------------------|
| R1 | Operador SaaS en URL instancia | `EnsureInstanceWebAuth` + mensaje explícito |
| R2 | Fuga cross-tenant en portal | Validación `tenant_id` en auth y `EnsureInstancePortalAccess` |
| R3 | Credenciales seed por defecto | Cambio en producción vía env (`platform_auth.php`) |
| R4 | Expectativa OAuth2 enterprise | ADR-002 documenta diferimiento y roadmap |

---

## Indicadores

| Indicador | Fuente |
|-----------|--------|
| Intentos login fallidos | Logs Laravel / auditoría HTTP |
| Sesiones activas operadores | Tabla `sessions` (si driver database) |
| Errores tenant mismatch | Mensajes error auth use case |
| Uso CP vs portal | Accesos rutas `/control/*` vs `/dashboard` |

---

## Relación con otros procesos

| Proceso | Relación |
|---------|----------|
| PROC-006 | Canal paralelo: API Bearer vs sesión web |
| PROC-007 | CRUD empresas y operadores en CP |
| PROC-008 | Provisioning crea admin instancia con credenciales iniciales |
| PROC-019 | Middleware portal tras login exitoso en silo |
| PROC-018 | ADR multi-tenant lógico diferido; modelo actual instancia por cliente |

---

## Componentes involucrados

| Capa | Componente |
|------|------------|
| HTTP | `LoginController`, `routes/web.php`, `routes/control.php` |
| Aplicación | `AuthenticateOperatorUseCase`, `ResolveOperatorHomePathUseCase`, `OperatorSessionTerminator` |
| Middleware | `auth.platform.web`, `instance.web`, `control.web`, `EnsureInstanceWebAuth`, `EnsureControlWebAuth` |
| Dominio | `PlatformRole` enum |
| Config | `config/platform_auth.php`, `config/platform_roles.php`, `config/platform.php` |
| Modelo | `App\Models\User` |

---

## Documentación relacionada

- `docs/production/ADR_002_autenticacion_enterprise.md`
- `docs/production/Plan_Autenticacion.md`
- `docs/production/ADR_001_instancia_por_cliente.md`
- `docs/Diagrama_BPMN/00_Mapa_Procesos.md`
- `docs/Diagrama_BPMN/Matriz_Trazabilidad_BPMN.md`

---

## Trazabilidad

| Elemento | Evidencia |
|----------|-----------|
| PROC-005 | `docs/Patente/matriz_generada/procesos.csv` fila PROC-005 |
| ACT-014 | `docs/Patente/matriz_generada/actividades_bpmn.csv`; `app/Http/Controllers/Auth/LoginController.php` |
| ACT-029 / GW-OAUTH2 | `docs/Patente/matriz_generada/actividades_bpmn.csv` L30; `docs/production/ADR_002_autenticacion_enterprise.md` |
| FLU-013–016, FLU-027 | `docs/Patente/matriz_generada/flujo_bpmn.csv` |
| EVT-INICIO-LOGIN | `flujo_bpmn.csv` FLU-013 |
| GW-AUTH-OK → `/dashboard` | `flujo_bpmn.csv` FLU-015; `routes/web.php` L21; `ResolveOperatorHomePathUseCase.php` |
| GW-AUTH-OK → `/control/overview` | `flujo_bpmn.csv` FLU-016; `routes/control.php` L18–19 |
| Auth operador | `app/Shared/Identity/Application/AuthenticateOperatorUseCase.php` |
| Criterio evaluación C11 | `docs/evaluation/05_Matriz_Seguridad.csv`; `Matriz_Trazabilidad_BPMN.md` |

---

## Diagrama Mermaid

```mermaid
flowchart TD
    START([EVT-INICIO-LOGIN: GET /login]) --> G0{¿web_auth_enabled?}
    G0 -->|No| DASH0[Redirect /dashboard]
    G0 -->|Sí| G1{¿Ya autenticado?}
    G1 -->|Sí| HOME0[ResolveOperatorHomePath]
    G1 -->|No| FORM[Render Auth/Login]
    FORM --> POST[POST /login ACT-014]
    POST --> AUTH[AuthenticateOperatorUseCase]
    AUTH --> G2{¿Credenciales OK?}
    G2 -->|No| ERR[Error email + back]
    G2 -->|Sí| G3{¿saas_admin sin CP?}
    G3 -->|Sí| ERR2[Logout + error CP]
    G3 -->|No| G4{¿tenant_id válido instancia?}
    G4 -->|No| ERR3[Logout + error tenant]
    G4 -->|Sí| REGEN[session regenerate]
    REGEN --> G5{¿url.intended?}
    G5 -->|Sí| INT[Redirect intended]
    G5 -->|No| GW[GW-AUTH-OK: ResolveOperatorHomePath]
    GW --> R1[/control/overview saas_admin]
    GW --> R2[/middleware bus_operator]
    GW --> R3[/dashboard platform_admin / viewer]
    R1 --> END([Sesión activa panel autorizado])
    R2 --> END
    R3 --> END
    INT --> END
    GW_OAUTH[GW-OAUTH2 ACT-029 diferido ADR-002] -.->|Fase 3| POST
```

---

## BPMN Mapping

| Elemento BPMN | Identificador / descripción |
|---------------|----------------------------|
| **Evento Inicio** | EVT-INICIO-LOGIN — operador accede `GET /login` |
| **Eventos Intermedios** | EVT-SESSION-REGEN; EVT-AUTH-OK; EVT-AUTH-FAIL; guest redirect a login en rutas protegidas |
| **Evento Final** | Sesión activa en panel autorizado; o error en formulario login; o logout en `/login` |
| **Actividades** | ACT-014 Login operador web (`LoginController` + `AuthenticateOperatorUseCase`); resolución home (`ResolveOperatorHomePathUseCase`) |
| **Subprocesos** | Validación credenciales Laravel Auth; validación contexto CP/instancia; regeneración sesión |
| **Gateways** | GW-AUTH-OK — rol y contexto determinan `/dashboard`, `/middleware` o `/control/overview`; GW-OAUTH2 (ACT-029) — OAuth2/IdP enterprise **diferido** ADR-002 |
| **Pools** | Pool Operador Humano; Pool Aplicación Web (silo o CP) |
| **Lanes** | Lane UI (`LoginController`, Inertia); Lane Identidad (`AuthenticateOperatorUseCase`); Lane Autorización web (`EnsureInstanceWebAuth`, `EnsureControlWebAuth`) |
| **Mensajes** | Msg-Login-Form (email/password); Msg-Auth-Error; Msg-Redirect-Response |
| **Objetos de datos** | Credenciales; objeto `User`; `platform_role`; `tenant_id`; URL intended |
| **Almacenes** | Tabla `users`; store sesión Laravel |
| **Artefactos** | `config/platform_auth.php`; ADR-002; `routes/web.php`; `routes/control.php` |
| **Asociaciones** | Credenciales → ACT-014; rol → GW-AUTH-OK; ADR-002 → GW-OAUTH2 (documental) |

---

*Fin del documento PROC-005*
