# Plan de Simulación de Clientes

**Versión:** 2.0 | **Actualizado:** 2026-05-28 | **Prioridad:** Alto

---

## 1. Estado actual (actualizado)

### Implementado

| Ítem | Ubicación |
|------|-----------|
| Fixtures versionados | `tests/fixtures/clients/{retailco,acmepos}/` |
| `platform:simulation:prepare` | Preparar instancia sin publicar |
| `platform:simulate-client` | Validar, sync, publicar (ráfaga o **por minuto**) |
| `platform:validate-catalog` | Drift modules_config ↔ eventbus |
| Runbook producción | [Runbook_Simulacion_Cliente.md](Runbook_Simulacion_Cliente.md) |
| Guía Acme Retail | [Simulacion_Acme_Retail.md](Simulacion_Acme_Retail.md) |
| Smoke scripts | `scripts/ops/simulate-client-smoke.{sh,ps1}` |
| CI nightly | `.github/workflows/nightly-client-simulation.yml` |
| Demo pack opcional | `DEMO_PACK_ENABLED` |

### Opciones de ritmo (`platform:simulate-client`)

| Modo | Ejemplo |
|------|---------|
| Ráfaga | `--events=10` |
| **N eventos/min × M minutos** | `--per-minute=10 --duration-minutes=5` → 50 eventos |
| **N eventos/min (1 min)** | `--per-minute=10 --duration-minutes=1` → 10 eventos en ~60s |

### Pendiente / limitaciones

- Config dinámica runtime sin redeploy (sigue manual: SaaS + `prepare`)
- Simulación en background (hoy el comando bloquea hasta terminar el ritmo)
- Staging dedicado por cliente (mismo código, distinto `PLATFORM_CLIENT_SLUG`)

---

## 2. Objetivo

Simulación **realista y repetible** antes del GO: validar bus, cola, dashboard, métricas y panel Live con carga gradual (p. ej. 10 → 30 → 100 evt/min).

---

## 3. Flujo estándar (Acme Retail)

```
1. PLATFORM_CLIENT_SLUG=acme-retail
2. php artisan platform:reset-demo-identity          # opcional lab
3. php artisan db:seed --class=AcmeRetailSimulationSeeder
4. Portal Live: ONLINE + eventos middleware ON
5. php artisan platform:simulation:prepare --slug=acmepos
6. php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
7. Revisar /middleware, /dashboard, /control/incidents
8. Subir --per-minute según plan de prueba
```

---

## 4. Roadmap

| Fase | Estado |
|------|--------|
| Fase 1 — Docs + fixtures + smoke | **Hecho** |
| Fase 2 — `simulate-client` + prepare | **Hecho** |
| Fase 2b — Ritmo `--per-minute` | **Hecho** (2026-05-28) |
| Fase 3 — CI multi-cliente nightly | **Hecho** |
| Fase 4 — Worker/scheduler para simulación larga en background | Pendiente |

---

## 5. Referencias

- [Simulacion_Acme_Retail.md](Simulacion_Acme_Retail.md) — instructivo operativo Acme
- [SimulacionClientes.md](SimulacionClientes.md) — resumen técnico
- [Checklist_Staging_PreGO.md](Checklist_Staging_PreGO.md)
