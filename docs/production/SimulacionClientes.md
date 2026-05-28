# Simulación de clientes

Implementación según `Plan_SimulacionClientes.md`.

## Componentes

| Componente | Ubicación |
|------------|-----------|
| Fixtures versionados | `tests/fixtures/clients/{slug}/` |
| Loader | `App\Shared\Platform\Services\ClientFixtureLoader` |
| Orquestador | `App\Shared\Platform\Services\ClientSimulationService` |
| Comando | `php artisan platform:simulate-client {slug}` |
| Smoke scripts | `scripts/ops/simulate-client-smoke.{sh,ps1}` |
| Overlay eventbus | `config/eventbus_client_overlay.json` (generado, no versionado) |

## Comando

```bash
php artisan platform:simulate-client retailco --events=50
php artisan platform:simulate-client acmepos --per-minute=10 --duration-minutes=1
php artisan platform:simulate-client retailco --apply-fixture  # copia a config/
```

Opciones: `--skip-sync`, `--skip-validate`, `--events=N`.

## CI

- Feature: `SimulateClientCommandTest`
- E2E multi-cliente: `MultiClientFixtureSimulationTest`
- Nightly: `.github/workflows/nightly-client-simulation.yml`

## Variables

| Variable | Propósito |
|----------|-----------|
| `DEMO_PACK_ENABLED` | Activa `DemoPackEventConsumers` en eventbus |
| `PLATFORM_CLIENT_SLUG` | Identidad instancia staging |

## Limitaciones conocidas

- Config dinámica runtime sin panel UI — sigue siendo archivos + comando (Plan_de_implementacion §1.1).
- `--apply-fixture` escribe en `config/` — requiere `config:clear` si hay cache.

## Referencias

- [Runbook_Simulacion_Cliente.md](Runbook_Simulacion_Cliente.md)
- [Checklist_Staging_PreGO.md](Checklist_Staging_PreGO.md)
