# Instrumento — Checklist pre-despliegue (Staging pre-GO)

**Versión:** 1.0  
**Fecha:** 2026-06-27  
**Fuente operativa:** [docs/production/Checklist_Staging_PreGO.md](../../production/Checklist_Staging_PreGO.md)  
**Matriz de evaluación relacionada:** [docs/evaluation/06_Matriz_Operacion.csv](../../evaluation/06_Matriz_Operacion.csv), [docs/evaluation/10_Matriz_Aceptacion_Final.csv](../../evaluation/10_Matriz_Aceptacion_Final.csv)

## 1. Propósito

Traducir cada ítem del checklist **Staging pre-GO** en evidencia automatizada (PHPUnit), comandos operativos o verificación manual documentada, para decidir GO/NO-GO por instancia cliente.

## 2. Alcance

Primera certificación de instancia staging antes de producción (`Plan_SimulacionClientes.md`). No sustituye runbooks de deploy VM ni pentest.

## 3. Criterio de cumplimiento

| Símbolo | Significado |
|---------|-------------|
| **AUTO** | Cubierto por suite PHPUnit o script CI/ops |
| **CMD** | Comando artisan/bash verificable en staging |
| **MAN** | Verificación manual obligatoria (UI/ops) |
| **GAP** | Sin automatización suficiente — riesgo documentado |

## 4. Resumen de cobertura (2026-06-27)

| Sección | Ítems | AUTO | CMD | MAN | GAP |
|---------|-------|------|-----|-----|-----|
| Infraestructura | 5 | 2 | 0 | 2 | 1 |
| Configuración cliente | 4 | 2 | 1 | 1 | 0 |
| Simulación automatizada | 6 | 4 | 2 | 0 | 0 |
| UI manual | 3 | 1 | 0 | 2 | 0 |
| Seguridad y ops | 3 | 1 | 1 | 2 | 0 |

## 5. Trazabilidad por ítem

Ver CSV completo: [Checklist_PreDespliegue.csv](./Checklist_PreDespliegue.csv).

### Infraestructura

- **Instancia staging dedicada** — MAN: revisar `PLATFORM_CLIENT_SLUG` en `.env` vs inventario ([Inventario_Instancias.md](../../production/Inventario_Instancias.md)).
- **`/up` y `/health/ready`** — AUTO: `HealthEndpointTest` (TC-0064–TC-0066).
- **Auth habilitada** — AUTO: `PlatformApiAuthenticationTest`, `OperatorLoginTest` (parcial: 1 fallo abierto INC-613e3b).

### Configuración cliente

- **`platform:validate-catalog`** — AUTO+CMD: `ValidatePlatformCatalogTest` + comando en staging (PROC-016 / REQ-VAL-01).
- **`consumer_registrars` / DEMO_PACK** — CMD+MAN: revisión PR de `config/modules/modules_config.json`.

### Simulación automatizada

- **`platform:simulate-client`** — AUTO+CMD: `ClientProductionLikeSimulationTest`, `MultiClientFixtureSimulationTest`, `scripts/ops/simulate-client-smoke.sh`.
- **API cola / topología / dashboard** — AUTO: `MiddlewareControlApiTest`, `DashboardEndpointsTest`, E2E TC-0001.

### Seguridad y ops

- **Backup BD** — MAN+CMD: [Runbook_Backup_Restore.md](../../production/Runbook_Backup_Restore.md) (sin test automatizado de restore completo).
- **Runbook deploy VM** — MAN: [Runbook_Deploy_VM.md](../../production/Runbook_Deploy_VM.md).

## 6. Ejecución recomendada pre-GO

```bash
# Suite completa (363 tests, 2 fallos conocidos — ver Matriz_Riesgos_Testing)
composer test

# Smoke operativo en staging
CLIENT_SLUG=<slug> EVENTS=50 bash scripts/ops/simulate-client-smoke.sh
```

## 7. Decisión GO/NO-GO

Registrar en el checklist fuente: cliente/slug, fixture, fecha, responsable QA, decisión. Bloqueadores actuales: **TC-0070** (portal multi-tenant), **TC-0161** (tenant_id en seed).

## 8. Referencias

- [docs/testing/README.md](../README.md)
- [docs/testing/matriz_maestra_casos.csv](../matriz_maestra_casos.csv)
- [docs/evaluation/04_Guia_Instrumentos_Medicion.md](../../evaluation/04_Guia_Instrumentos_Medicion.md)
