# Calidad y Testing — Middleware Omnicanal

**Plan:** `Plan_Calidad.md` | **Estado:** Implementado (Fases 1–3)

---

## Pirámide de tests

```
        / E2E UI (Playwright) \
       / Feature + E2E PHP    \
      / Integration           \
     / Unit                    \
```

---

## Fase 1 — CI + PHPStan + validate-catalog

| Componente | Ubicación |
|------------|-----------|
| Workflow CI | `.github/workflows/ci.yml` |
| PHPStan level 5 | `phpstan.neon` |
| Pint | `composer lint` |
| validate-catalog | `platform:validate-catalog` + tests |
| Sync conteos README | `docs/testing/tools/sync_test_stats.php` |
| Config calidad | `config/platform_quality.php` |

```bash
composer ci
composer test:stats   # actualiza docs/testing/README.md
composer test:stats -- --check   # falla si README desactualizado
```

---

## Fase 2 — Coverage + load test

| Componente | Ubicación |
|------------|-----------|
| Coverage gate ≥70% Application | `scripts/ci/check-application-coverage.php` |
| k6 100 eps sustained | `docs/testing/load/k6_publish_sustained.js` |
| Runner local | `scripts/ci/run-k6-load-test.sh` |
| CI load (manual/semanal) | `.github/workflows/quality-load.yml` |

---

## Fase 3 — UI E2E + security scan

| Componente | Ubicación |
|------------|-----------|
| Playwright smoke | `tests/e2e-ui/smoke-pages.spec.js` |
| Config | `playwright.config.js` |
| CI UI E2E | `.github/workflows/quality-ui-e2e.yml` |
| OWASP ZAP baseline | `.github/workflows/staging.yml` (post-smoke) |

```bash
npm install
npx playwright install chromium
npm run build
php artisan serve &
APP_URL=http://127.0.0.1:8000 npx playwright test
```

---

## Runner oficial

**PHPUnit** es el runner enforced en CI. Pest está en `require-dev` para migración futura — no usar en pipelines hasta decisión explícita.

---

## Variables de entorno

Ver `.env.example` sección Calidad y `config/platform_quality.php`.

---

## Referencias

- [docs/testing/README.md](../testing/README.md)
- [priority_tests_matrix.md](../testing/priority_tests_matrix.md)
- [Plan_CI_CD.md](Plan_CI_CD.md)
