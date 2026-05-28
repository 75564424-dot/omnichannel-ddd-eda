# Plan CI/CD

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Crítico

---

## 1. Estado Actual

### Qué existe

- `composer test` → PHPUnit
- PHPStan y Pint en `require-dev` — **sin config en raíz**
- `docs/testing/tools/generate_test_catalogs.php`
- 86 tests passing localmente
- QA docs con checklist manual de release

### Qué falta entirely

- `.github/workflows/` en el proyecto
- `phpstan.neon`, `pint.json`
- Pipeline de build Docker
- Deploy automático staging/prod
- Gates: coverage, security scan, JSON lint
- Comando `platform:validate-catalog` (planificado B.3, no implementado)

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| Merge sin tests en CI | **Crítico** |
| Regresiones no detectadas | **Alto** |
| Config JSON inválida en prod | **Medio** |

---

## 2. Objetivo

Pipeline **automático** que garantice calidad antes de cada deploy: tests, análisis estático, build de imagen, deploy staging, smoke test, promote a prod.

---

## 3. Problemas Detectados

1. Conteos de tests divergen en docs (83 vs 86)
2. Pest instalado pero PHPUnit es runner activo — confusión
3. Sin validación de `modules_config.json` en CI
4. Sin semver ni changelog automatizado

---

## 4. Requerimientos

### Pipeline stages

- [ ] **Lint:** Pint, PHPStan level 5+, ESLint (si aplica)
- [ ] **Test:** PHPUnit en SQLite :memory:
- [ ] **Validate:** JSON configs, `platform:validate-catalog`
- [ ] **Build:** Docker image tag = git sha
- [ ] **Deploy staging:** auto on main
- [ ] **Smoke:** curl health + sync + publish
- [ ] **Deploy prod:** manual approval

### Herramientas

- GitHub Actions / GitLab CI / Azure DevOps
- Dependabot para dependencias
- Opcional: SonarQube, Snyk

---

## 5. Propuesta Técnica

### Workflow ejemplo (GitHub Actions)

```yaml
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: composer install
      - run: php artisan test
      - run: vendor/bin/phpstan analyse
      - run: vendor/bin/pint --test
      - run: php docs/testing/tools/validate_json_configs.php
```

### Release flow

```
PR → CI green → merge main → build image → deploy staging → smoke → tag release → deploy prod
```

---

## 6. Roadmap de Implementación

### Fase 1

- GitHub Actions: test + pint
- `phpstan.neon` básico
- JSON lint script

### Fase 2

- Docker build en CI
- Deploy staging automatizado
- Smoke test post-deploy

### Fase 3

- Coverage gate (≥70% en Application layer)
- Security scan (composer audit)
- Release notes automatizados

---

## 7. Prioridad

**Crítico**

---

## 8. Riesgo si no se implementa

Cada deploy es apuesta manual; alta probabilidad de regresión en bus o dashboard; imposible escalar equipo de desarrollo con confianza.

---

## Referencias

- [Plan_Calidad.md](Plan_Calidad.md)
- [Plan_Cloud.md](Plan_Cloud.md)
- `docs/personal_notes/Estrategia_pruebas_pre_produccion.md`
