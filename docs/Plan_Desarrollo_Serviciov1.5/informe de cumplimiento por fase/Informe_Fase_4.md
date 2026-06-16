# Informe Fase 4

## Estado

Cumple.

## Evidencia encontrada

- `resources/js/Pages/Control/Companies/Show.vue` muestra acciones contextuales `Levantar servicio`, `Suspender servicio`, `Restaurar servicio`.
- `resources/js/Pages/Control/Companies/Index.vue` muestra estado comercial y lifecycle.
- `resources/js/Pages/Tenant/Suspended.vue` implementa la pantalla dedicada de suspension.

## Correcciones realizadas

- Se reemplazaron botones legacy `Suspender`/`Activar` por acciones basadas en `tenant.actions_available`.
- Se cablearon acciones UI a `/lifecycle/start`, `/lifecycle/suspend`, `/lifecycle/restore`.
- Se agregaron badges visuales de acceso y servicio.

## Archivos modificados

- `resources/js/Pages/Control/Companies/Show.vue`
- `resources/js/Pages/Control/Companies/Index.vue`

## Archivos nuevos

- `resources/js/Pages/Tenant/Suspended.vue`

## Riesgos detectados

- La UI no hace polling de health posterior a levantar; depende del mensaje flash del backend.

## Riesgos mitigados

- `npm.cmd run build` compila correctamente e incluye la pagina suspendida en el bundle generado localmente.

## Deuda tecnica pendiente

- Modal de confirmacion especifico para suspension/restauracion.

## Checklist Runbook

| Requisito | Estado | Evidencia |
| --------- | ------ | --------- |
| Companies/Show.vue | Cumple | Acciones contextuales |
| Companies/Index.vue | Cumple | Badges status/lifecycle |
| TenantSuspended.vue | Cumple | Pagina creada |
| Modales | Cumple con observaciones | Acciones directas, sin modal dedicado |
| Estados visuales | Cumple | Badges |
| Acciones backend | Cumple | Rutas lifecycle |
