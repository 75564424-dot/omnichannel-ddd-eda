**Documento 2**  
**EJEMPLAR DEL SOFTWARE — VERSIÓN COMPLETADA**

> **Fuente de generación:** análisis del repositorio `omnichannel-ddd-eda` (2026-06-10).  
> **Plantilla base:** `Documento 2 - Ejemplar del software.docx.md` (intacta).

---

## 1. Código Fuente o Ejecutable

### 1.1 Identificación del ejemplar

| Campo | Valor | Estado |
|-------|-------|--------|
| Nombre sugerido del archivo comprimido | `platform-event-bus-core_v1.7-beta_ejemplar.zip` [PENDIENTE_VALIDACIÓN nombre final] | **PENDIENTE DE VALIDACIÓN** |
| Repositorio origen | `omnichannel-ddd-eda` | **VALIDADO** (Fuente: nombre del directorio) |
| Paquete Composer | `platform/event-bus-core` | **VALIDADO** (Fuente: `composer.json`) |
| Licencia declarada | MIT (`composer.json`) | **VALIDADO** (Fuente: `composer.json`) |

### 1.2 Estructura real del repositorio (carpetas principales)

```
omnichannel-ddd-eda/
├── app/                    # Bounded contexts DDD (311 archivos PHP)
│   ├── Middleware/         # Event bus, cola, topología, DLQ
│   ├── Dashboard/          # Observabilidad, feed, métricas
│   ├── Control/            # Portal SaaS, tenants, simulación
│   ├── Integration/        # Webhooks, canales, adaptadores
│   ├── Shared/             # Platform, Identity, EventBus, API
│   ├── Observability/      # Prometheus, trace logs
│   ├── Monitoring/         # Alertas, canary
│   ├── Console/Commands/   # 14 comandos Artisan plataforma
│   └── Http/               # Controllers, middleware HTTP
├── bootstrap/              # Arranque Laravel, providers
├── config/                 # platform.php, eventbus.php, saas_catalog.php, etc.
├── database/migrations/    # 31 migraciones esquema middleware
├── database/seeders/       # InstanceTenantSeeder, etc.
├── docs/                   # ADRs, planes, runbooks, arquitectura (158 archivos)
├── resources/js/           # Frontend Vue/Inertia (14 páginas)
├── routes/                 # web.php, control.php, api.php, console.php
├── scripts/                # CI, ops, local-dev (14 archivos)
├── tests/                  # Unit, Integration, Feature, E2E (81 PHP)
├── composer.json           # Dependencias PHP
├── package.json            # Dependencias frontend
└── artisan                 # CLI Laravel
```

**Fuente:** exploración filesystem y `docs/matriz_generada/reporte_generacion.md`. **VALIDADO** (Fuente: estructura de carpetas)

### 1.3 Contenido representativo del ejemplar (sin credenciales)

El ejemplar debe incluir módulos que demuestren autoría y funcionalidad:

| Carpeta / archivo | Justificación | Estado |
|-------------------|---------------|--------|
| `app/Middleware/` | Núcleo event bus — publicación, cola, topología | **VALIDADO** |
| `app/Dashboard/` | Observabilidad — listeners, projectors, use cases | **VALIDADO** |
| `app/Control/` | Gestión empresas y simulación | **VALIDADO** |
| `app/Integration/` | Integraciones y webhooks | **VALIDADO** |
| `app/Shared/Platform/` | Instancia por cliente ADR-001 | **VALIDADO** |
| `app/Shared/Api/Routes/` | Definición rutas API | **VALIDADO** |
| `app/Console/Commands/` | Comandos operativos plataforma | **VALIDADO** |
| `config/eventbus.php`, `config/platform.php` | Configuración core | **VALIDADO** |
| `config/modules/modules_config.json` | Catálogo declarativo | **VALIDADO** |
| `database/migrations/` | Esquema BD | **VALIDADO** |
| `routes/web.php`, `routes/control.php` | Rutas UI | **VALIDADO** |
| `resources/js/Pages/` | Interfaces Vue | **VALIDADO** |
| `tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php` | Prueba integrada | **VALIDADO** |
| `docs/production/ADR_*.md` | Decisiones arquitectónicas | **VALIDADO** |
| `docs/api/openapi.yaml` | Contrato API | **VALIDADO** |
| `composer.json`, `package.json` | Manifiestos dependencias | **VALIDADO** |

**Excluir del ejemplar:** `.env`, `vendor/`, `node_modules/`, credenciales, `database/database.sqlite` con datos reales, tokens.

### 1.4 Archivos de código fuente a adjuntar posteriormente

En lugar de copiar código completo en este documento, se indican fragmentos representativos para el anexo impreso (10–15 páginas):

| # | Archivo representativo | Sección a incluir | Estado |
|---|------------------------|-------------------|--------|
| 1 | `app/Middleware/Application/Services/EventPublisherService.php` | Método `publish()` | [FRAGMENTO_CODIGO_PENDIENTE] |
| 2 | `app/Shared/Api/Routes/MiddlewareApiRoutes.php` | Registro rutas API | [FRAGMENTO_CODIGO_PENDIENTE] |
| 3 | `app/Dashboard/Listeners/UniversalDashboardFeedListener.php` | Observación wildcard | [FRAGMENTO_CODIGO_PENDIENTE] |
| 4 | `app/Control/Application/Services/TenantAdminService.php` | Gestión tenant | [FRAGMENTO_CODIGO_PENDIENTE] |
| 5 | `app/Shared/Platform/DatabaseInstanceTenantContext.php` | Contexto instancia | [FRAGMENTO_CODIGO_PENDIENTE] |
| 6 | `app/Console/Commands/SimulateClientCommand.php` | Simulación E2E | [FRAGMENTO_CODIGO_PENDIENTE] |
| 7 | `app/Integration/Application/UseCases/ReceiveWebhookUseCase.php` | Ingress webhook | [FRAGMENTO_CODIGO_PENDIENTE] |
| 8 | `config/eventbus.php` | Suscripciones y driver | [FRAGMENTO_CODIGO_PENDIENTE] |
| 9 | `routes/control.php` | Portal control plane | [FRAGMENTO_CODIGO_PENDIENTE] |
| 10 | `resources/js/Pages/Dashboard/Index.vue` | UI dashboard | [FRAGMENTO_CODIGO_PENDIENTE] |
| 11 | `database/migrations/2026_05_21_100001_create_middleware_event_messaging_schema.php` | Esquema mensajería | [FRAGMENTO_CODIGO_PENDIENTE] |
| 12 | `tests/Feature/Middleware/MiddlewarePipelineEndToEndTest.php` | Prueba pipeline | [FRAGMENTO_CODIGO_PENDIENTE] |

[ANEXO_CODIGO_REPRESENTATIVO_PENDIENTE] — generar PDF de 10–15 páginas al momento del trámite INDECOPI.

---

## 2. Manual Técnico

### 2.1 Requisitos del sistema

| Requisito | Valor evidenciado | Fuente | Estado |
|-----------|-------------------|--------|--------|
| PHP | ^8.2 | `composer.json` | **VALIDADO** |
| Composer | 2.x [PENDIENTE_VALIDACIÓN versión mínima] | estándar Laravel 11 | **PENDIENTE DE VALIDACIÓN** |
| Node.js | [PENDIENTE_VALIDACIÓN] — requerido para Vite | `package.json` scripts | **PENDIENTE DE VALIDACIÓN** |
| npm | [PENDIENTE_VALIDACIÓN] | `package.json` | **PENDIENTE DE VALIDACIÓN** |
| Base de datos | SQLite (local) o MySQL (producción documentada) | migraciones, `docs/production/` | **VALIDADO** |
| Sistema operativo | Windows / Linux (evidencia scripts `.sh` y `.ps1`) | `scripts/` | **VALIDADO** |
| Navegador | Chrome/Firefox [PENDIENTE_VALIDACIÓN versión mínima] | estándar SPA | **PENDIENTE DE VALIDACIÓN** |
| Extensiones PHP | PDO, JSON, mbstring, etc. [PENDIENTE_VALIDACIÓN lista completa] | Laravel 11 requirements | **PENDIENTE DE VALIDACIÓN** |

### 2.2 Instalación

**Fuente:** `composer.json` scripts, `package.json` scripts, [PENDIENTE_VALIDACIÓN] README completo — `README.md` actual contiene 1 línea. **PARCIALMENTE VALIDADO**

```bash
# 1. Clonar repositorio
git clone [URL_REPOSITORIO]
cd omnichannel-ddd-eda

# 2. Dependencias PHP
composer install

# 3. Dependencias frontend
npm install

# 4. Configuración entorno
cp .env.example .env
php artisan key:generate

# 5. Base de datos
php artisan migrate
php artisan db:seed

# 6. Build frontend
npm run build

# 7. Arranque desarrollo
npm run local
# o
php artisan serve --host=127.0.0.1 --port=8000
```

**Comandos adicionales evidenciados:**

| Comando | Propósito | Fuente | Estado |
|---------|-----------|--------|--------|
| `php artisan platform:ensure-instance-tenant` | Asegurar fila tenant instancia | ADR-001 | **VALIDADO** |
| `php artisan platform:validate-catalog` | Validar alineación catálogos | `composer.json` ci | **VALIDADO** |
| `php artisan platform:simulate-client {slug}` | Simulación cliente E2E | Runbook simulación | **VALIDADO** |
| `composer ci` | Pipeline calidad completo | `composer.json` | **VALIDADO** |

### 2.3 Configuración inicial

| Paso | Acción | Evidencia | Estado |
|------|--------|-----------|--------|
| 1 | Configurar `PLATFORM_CLIENT_SLUG`, `PLATFORM_CLIENT_NAME` en `.env` | `config/platform.php` | **VALIDADO** |
| 2 | Ejecutar `platform:ensure-instance-tenant` o `db:seed` | `EnsureInstanceTenantCommand` | **VALIDADO** |
| 3 | Emitir token API: `php artisan platform:issue-api-token` | `IssuePlatformApiTokenCommand` | **VALIDADO** |
| 4 | Sincronizar catálogo: `POST /api/middleware/registry/sync-config` | Plan implementación B.2 | **VALIDADO** |
| 5 | Acceder `/login` → `/dashboard` o `/control/overview` | `routes/web.php`, `routes/control.php` | **VALIDADO** |

### 2.4 Uso básico

| Flujo | Descripción | URL / comando | Estado |
|-------|-------------|---------------|--------|
| Publicar evento | Integrador publica al bus | `POST /api/middleware/events/publish` | **VALIDADO** |
| Ver cola y métricas | Operador middleware | `/middleware`, `GET /api/middleware/queue` | **VALIDADO** |
| Ver dashboard | Operador instancia | `/dashboard` | **VALIDADO** |
| Gestionar empresas | Admin SaaS | `/control/companies` | **VALIDADO** |
| Simular cliente | QA / ops | `php artisan platform:simulate-client acmepos` | **VALIDADO** |
| Ver métricas Prometheus | Observabilidad | `GET /metrics` | **VALIDADO** |

**Fuente:** `docs/production/Runbook_Simulacion_Cliente.md`, `docs/production/Flujo_M2M_Integradores.md`, rutas API.

### 2.5 Flujo de ejecución (runtime)

```
[Cliente HTTP / Integrador API]
        │ POST /api/middleware/events/publish
        ▼
[EventPublisherService — validación sobre]
        │
        ├──► Persistencia QueueEntry / métricas
        └──► EventBusPort (Laravel Event / Kafka opcional)
                    │
                    ▼
            [Listeners suscriptores + wildcard dashboard]
                    │
                    ▼
            [Projectors → event_feed_entries, snapshots]
                    │
                    ▼
            [UI /dashboard y /middleware vía polling/API]
```

**Fuente:** `docs/Plan_Desarrollo_Modulos_v0.1/Plan_Modulo_Control_Middleware.md` §6, código implementado. **VALIDADO**

### 2.6 Mantenimiento y actualización

| Tarea | Frecuencia | Evidencia | Estado |
|-------|------------|-----------|--------|
| Purga retención tablas operativas | Diario 02:30 | `routes/console.php` → `platform:purge-retention` | **VALIDADO** |
| Evaluación alertas | Cada minuto (si habilitado) | `platform:monitoring-evaluate` | **VALIDADO** |
| Canary publish | Cada 5 min (si habilitado) | `platform:canary-publish` | **VALIDADO** |
| Pipeline CI | En cada integración | `composer ci` | **VALIDADO** |
| Backup BD | Según runbook | `docs/production/Runbook_Backup_Restore.md` | **VALIDADO** |

---

## 3. Capturas de Pantalla

Se adjuntarán capturas de las interfaces implementadas. **Estado actual:** [CAPTURA_PENDIENTE] para todas.

| # | Pantalla | Ruta | Estado |
|---|----------|------|--------|
| 1 | Login operadores | `/login` | [INSERTAR_CAPTURA_LOGIN] |
| 2 | Dashboard observabilidad | `/dashboard` | [INSERTAR_CAPTURA_DASHBOARD] |
| 3 | Consola middleware | `/middleware` | [INSERTAR_CAPTURA_MIDDLEWARE] |
| 4 | Control plane overview | `/control/overview` | [INSERTAR_CAPTURA_CONTROL] |
| 5 | Gestión empresas | `/control/companies` | [INSERTAR_CAPTURA_EMPRESAS] |
| 6 | Configuración módulos tenant | `/control/companies/{tenant}/modules` | [INSERTAR_CAPTURA_MODULOS] |
| 7 | Simulación / reporte | `/control/simulations/{run}/report` | [INSERTAR_CAPTURA_SIMULACION] |
| 8 | Provisioning | `/control/provisioning` | [INSERTAR_CAPTURA_PROVISIONING] |

**Páginas Vue evidenciadas (14):** `resources/js/Pages/` — Auth/Login, Dashboard/Index, Middleware/Index, Control/*. **VALIDADO** (Fuente: `resources/js/Pages/`)

---

## 4. Dependencias del ejemplar

### PHP (`composer.json`)

| Paquete | Versión |
|---------|---------|
| laravel/framework | ^11.0 |
| inertiajs/inertia-laravel | ^3.0 |
| laravel/sanctum | 4.0 |
| opis/json-schema | 2.3 |
| ramsey/uuid | ^4.7 |

### Frontend (`package.json`)

| Paquete | Versión |
|---------|---------|
| vue | ^3.4.21 |
| @inertiajs/vue3 | ^3.0 |
| vite | ^5.2.0 |
| tailwindcss | ^3.4.1 |
| axios | ^1.6.4 |
| @playwright/test | ^1.49.0 |

---

## 5. Observaciones Finales

El ejemplar del software **Plataforma Event Bus Core — Middleware + Dashboard (v1.7-beta)** constituye evidencia de una plataforma web original de integración por eventos con portal SaaS de gestión de instancias, desarrollada en Laravel 11 + Vue 3 + Inertia.

Este documento, junto con la Ficha Técnica, Declaración Jurada, Lista de Autores y Representación Legal, conforma el paquete de registro.

**Acciones pendientes antes del trámite:**

1. [COMPLETAR_MANUALMENTE] Empaquetar ZIP representativo sin secretos.
2. [ANEXO_CODIGO_REPRESENTATIVO_PENDIENTE] Imprimir 10–15 páginas de código.
3. [INSERTAR_CAPTURA_*] Tomar capturas de las 8 pantallas listadas.
4. [COMPLETAR_MANUALMENTE] Completar README de instalación si se requiere manual autónomo.

---

*Documento generado automáticamente. Revisar y validar antes de presentación ante INDECOPI.*
