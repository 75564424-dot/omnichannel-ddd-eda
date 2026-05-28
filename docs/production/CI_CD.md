# CI/CD — Pipeline y despliegue

Documentación operativa del pipeline implementado según `Plan_CI_CD.md`.

## Workflows GitHub Actions

| Workflow | Archivo | Trigger | Propósito |
|----------|---------|---------|-----------|
| CI | `.github/workflows/ci.yml` | push/PR `main`, `develop` | Lint, validate, test, audit, coverage |
| Staging | `.github/workflows/staging.yml` | push `main`, manual | Build Docker + smoke local |
| Release | `.github/workflows/release.yml` | tag `v*.*.*`, manual | Notas de release + GitHub Release |

## Comandos locales

```bash
composer lint              # Pint (--test)
composer analyse           # PHPStan (Application + Domain)
composer validate-config   # JSON lint + platform:validate-catalog
composer test              # PHPUnit (SQLite :memory:)
composer ci                # lint + analyse + validate-config + test
```

## Validación de catálogo (B.3)

```bash
php artisan platform:validate-catalog
```

Comprueba que productores/suscriptores declarados en `config/modules/modules_config.json` estén alineados con `config/eventbus.php` (`producers` / `subscriptions`).

## JSON lint

```bash
php docs/testing/tools/validate_json_configs.php
```

Valida sintaxis y estructura mínima de `modules_config.json` y `dashboard_config.json`.

## Docker (staging CI)

```bash
docker build -t platform-event-bus-core:local .
docker run -p 8080:8080 \
  -e APP_KEY=base64:... \
  platform-event-bus-core:local
bash scripts/ci/smoke-test.sh   # APP_URL=http://127.0.0.1:8080
```

## Coverage gate

El job `coverage` en CI exige **≥70%** de statements en:

- `app/Middleware/Application`
- `app/Dashboard/Application`
- `app/Shared/Platform`

Script: `scripts/ci/check-application-coverage.php`

## Variables / secretos (staging/prod)

| Secreto | Uso |
|---------|-----|
| `STAGING_REGISTRY` | Push imagen Docker (futuro) |
| `STAGING_HOST` | Target deploy staging |
| `DEPLOY_KEY` | SSH o cloud deploy key |

Los jobs `deploy-staging` y `deploy-production` son **placeholders** con approval manual hasta conectar infra cloud (`Plan_Cloud.md`).

## Pint (alcance inicial)

El gate de Pint aplica primero a rutas del pipeline y plataforma (`app/Shared/Platform`, scripts CI, validate-catalog). La adopción Pint en todo el repositorio queda pendiente (`Plan_Calidad.md`) para evitar un diff masivo de estilo.

## Dependabot

`.github/dependabot.yml` — actualizaciones semanales de Composer, npm y GitHub Actions.

## Smoke test

`scripts/ci/smoke-test.sh` verifica:

1. `GET /api/middleware/status`
2. `POST /api/middleware/registry/sync-config`
3. `POST /api/middleware/events/publish`
4. `GET /api/middleware/events/{id}`
