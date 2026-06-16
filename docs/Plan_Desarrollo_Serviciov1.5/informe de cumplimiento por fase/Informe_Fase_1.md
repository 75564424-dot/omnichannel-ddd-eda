# Informe Fase 1

## Estado

Cumple con observaciones.

## Evidencia encontrada

- ADR lifecycle: `docs/production/ADR_010_tenant_lifecycle_management.md`.
- Feature flag: `config/platform.php` (`lifecycle_v15`, `local_fleet.stop_on_suspend`).
- Compatibilidad retroactiva: `App\Control\Domain\Policies\TenantLifecyclePolicy::inferLifecycle`.
- Inventario base documentado en `docs/Plan_Desarrollo_Serviciov1.5/Runbook_v1.5_Gestion_Ciclo_Vida_Tenants.md`.

## Correcciones realizadas

- Se fijo `phpunit.xml` para ejecutar tests como control plane por defecto, coherente con rutas `/control/*`.
- Se agrego catalogo aislado `pruebas-retail` para evitar fallback a catalogo global Acme.

## Archivos modificados

- `phpunit.xml`

## Archivos nuevos

- `tests/Fixtures/clients/pruebas-retail/modules_config.json`

## Riesgos detectados

- La suite completa aun revela tests de soporte/control que no preparan tenant para operadores de instancia.

## Riesgos mitigados

- La inferencia de lifecycle conserva datos previos sin migracion destructiva.

## Deuda tecnica pendiente

- Normalizar fixtures de tests que mezclan control plane y portal cliente.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| ADR generado | Cumple | `ADR_010_tenant_lifecycle_management.md` |
| Inventario realizado | Cumple con observaciones | Runbook v1.5 |
| Estados documentados | Cumple | `TenantLifecyclePolicy`, ADR-010 |
| Compatibilidad retroactiva | Cumple | `inferLifecycle()` |
| Feature flags | Cumple | `config/platform.php` |
