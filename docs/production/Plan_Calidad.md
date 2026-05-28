# Plan de Calidad y Testing

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Alto

---

## 1. Estado Actual

### Qué existe

- **86 tests**, 317 assertions (PHPUnit)
- Suites: Unit (14), Integration (10), Feature (3), E2E (1)
- `phpunit.xml` — SQLite :memory:, sync queue
- Catálogos autogenerados en `docs/testing/`
- Matrix validación: `matrix_validacion_middleware.md`
- QA GO-with-risks: `Release_decision_QA.md`
- PHPStan + Pint + Pest en dev — **PHPUnit activo**

### Qué falta

- CI enforcement
- Coverage report / threshold
- Performance / load tests
- Security tests (OWASP API)
- UI E2E (Playwright/Cypress) en CI
- Contract tests OpenAPI
- Chaos testing
- `platform:validate-catalog` (B.3)

### Riesgos

| Riesgo | Severidad |
|--------|-----------|
| Regresión sin CI | **Crítico** |
| Conteos tests desactualizados en docs | **Bajo** |
| Sin load test — unknown capacity | **Alto** |

---

## 2. Objetivo

Suite de calidad **enterprise**: automatizada, medible, que cubra funcional, integración, E2E, performance y seguridad antes de cada release.

---

## 3. Problemas Detectados

1. Tests no cubren auth (no existe aún)
2. No hay tests para tablas nuevas sin código (correcto — pero gap cuando se implementen)
3. Pest instalado pero no usado — confusión onboarding
4. Frontend Vue sin tests unitarios visibles

---

## 4. Requerimientos

- [ ] CI: PHPUnit + PHPStan level 5 + Pint
- [ ] Coverage ≥70% Application layer (objetivo)
- [ ] Load test k6/Locust: 100 eps publish sustained
- [ ] Security: OWASP ZAP baseline en staging
- [ ] E2E UI: smoke dashboard + middleware pages
- [ ] `platform:validate-catalog` + test
- [ ] Actualizar README testing counts automáticamente

---

## 5. Propuesta Técnica

### Pirámide de tests

```
        / E2E UI \
       / Feature  \
      / Integration \
     /    Unit       \
```

### Tests prioritarios a añadir

1. Auth middleware (cuando exista)
2. Webhook signature validation
3. event_store append idempotency
4. Retention purge command
5. Idempotent publish 200

---

## 6. Roadmap

### Fase 1: CI + PHPStan + validate-catalog
### Fase 2: Coverage gate + load test baseline
### Fase 3: UI E2E + security scan en pipeline

---

## 7. Prioridad

**Alto**

---

## 8. Riesgo si no se implementa

Calidad no reproducible; releases con regresiones; imposible certificar SLA de throughput.

---

## Referencias

- [Plan_CI_CD.md](Plan_CI_CD.md)
- `docs/testing/README.md`
- `docs/personal_notes/Estrategia_pruebas_pre_produccion.md`
