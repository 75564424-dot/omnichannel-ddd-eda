# Runbook — Simulación de cliente (staging / pre-GO)

**Plan:** [Plan_SimulacionClientes.md](Plan_SimulacionClientes.md) | **Audiencia:** QA, DevOps, operador

Consolida procedimientos de `docs/personal_notes/Runbook_cliente_simulado.md` para uso en **producción/staging**.

## Objetivo

Repetir de forma automatizada la simulación de un cliente omnicanal antes del primer GO real.

## Acme Retail (instancia actual)

Guía paso a paso: **[Simulacion_Acme_Retail.md](Simulacion_Acme_Retail.md)**

```bash
php artisan platform:simulation:prepare --slug=acmepos
php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
```

## Clientes simulados versionados

| Slug | Descripción | Fixture |
|------|-------------|---------|
| `retailco` | Retail POS + WEB, AnalyticsSink | `tests/fixtures/clients/retailco/` |
| `acmepos` | Acme POS terminal | `tests/fixtures/clients/acmepos/` |

Cada fixture incluye: `modules_config.json`, `eventbus_overlay.json`, `sample_events.json`.

## Flujo automatizado (recomendado)

```bash
# 1. Staging con identidad cliente
export PLATFORM_CLIENT_SLUG=retailco-staging

# 2. (Opcional) Materializar config en disco
php artisan platform:simulate-client retailco --apply-fixture --events=0 --skip-sync
php artisan config:clear

# 3. Validar alineación JSON ↔ eventbus
php artisan platform:validate-catalog

# 4. Simulación completa
php artisan platform:simulate-client retailco --events=50

# 5. Smoke post-deploy
CLIENT_SLUG=retailco EVENTS=10 bash scripts/ops/simulate-client-smoke.sh
```

Windows:

```powershell
.\scripts\ops\simulate-client-smoke.ps1 -ClientSlug retailco -Events 10
```

## Flujo manual (UI)

1. Ejecutar simulación (comando arriba)
2. Abrir `/middleware` — verificar cola y topología observada
3. Abrir `/dashboard` — verificar catálogo y feed
4. Completar [Checklist_Staging_PreGO.md](Checklist_Staging_PreGO.md)

## Demo pack (opcional)

Para probar listeners in-process del pack demo:

```env
DEMO_PACK_ENABLED=true
```

Requiere `php artisan config:clear` tras cambiar. No usar en producción real salvo laboratorio.

## Coherencia config (regla B.3)

- `modules_config.json` declara productores/suscriptores para Dashboard
- `eventbus_overlay.json` declara routing real del bus
- `platform:validate-catalog` detecta drift entre ambos

## Sign-off

Registrar en ticket de release: slug simulado, commit, conteo eventos, resultado checklist pre-GO.

## Referencias

- [SimulacionClientes.md](SimulacionClientes.md)
- [Staging_Environment.md](Staging_Environment.md)
- [Runbook_Onboarding_Cliente.md](Runbook_Onboarding_Cliente.md)
