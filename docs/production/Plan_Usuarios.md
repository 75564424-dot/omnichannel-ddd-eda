# Plan de Usuarios y Acceso

**Versión:** 1.0 | **Fecha:** 2026-05-21 | **Prioridad global:** Crítico

---

## 1. Estado Actual

### Qué existe

- Concepto de **operador** implícito en runbooks (persona que ejecuta sync, publish, revisa DLQ)
- Tabla `audit_logs` con campos `actor_type`, `actor_id` — sin población
- UI Inertia en `/dashboard` y `/middleware` sin gate de acceso
- Documentación Fase D: operador por instancia, no portal multi-cliente

### Qué está incompleto

- Sin entidad User, Role, Permission
- Sin RBAC en controllers o policies
- Sin separación operador / integrador / administrador
- Sin gestión de cuentas ni invitaciones

### Riesgos detectados

| Riesgo | Severidad |
|--------|-----------|
| Cualquier persona con URL accede al panel de control | **Crítico** |
| Sin trazabilidad de quién ejecutó sync o resolve DLQ | **Alto** |
| Sin principio de mínimo privilegio | **Alto** |

---

## 2. Objetivo

Gestionar **identidades humanas** que operan el middleware: administradores de plataforma, operadores NOC, integradores técnicos. Implementar **RBAC** alineado con acciones del plano de control omnicanal.

---

## 3. Problemas Detectados

1. No hay modelo de roles (admin, operator, viewer)
2. PATCH `/nodes/{node}/middleware-events` sin autorización
3. `audit_logs` diseñada pero desconectada de acciones reales
4. Confusión entre "cliente" comercial (tenant) y "usuario" operador

---

## 4. Requerimientos

### Funcionalidades

- [ ] Entidades: User, Role, Permission (o Spatie Permission)
- [ ] Roles mínimos: `platform_admin`, `bus_operator`, `dashboard_viewer`
- [ ] Laravel Policies en use cases críticos
- [ ] Población de `audit_logs` con actor en cada acción admin
- [ ] Seed de usuario admin por instancia
- [ ] (Opcional) Invitación por email

### Librerías sugeridas

- `spatie/laravel-permission` — RBAC maduro
- O policies nativas Laravel si roles simples (<5)

---

## 5. Propuesta Técnica

### Matriz RBAC propuesta

| Rol | publish | sync | DLQ | dashboard | config |
|-----|---------|------|-----|-----------|--------|
| platform_admin | ✓ | ✓ | ✓ | ✓ | ✓ |
| bus_operator | ✓ | ✓ | ✓ | ✓ | — |
| dashboard_viewer | — | — | — | ✓ | — |
| api_integrator | ✓* | — | — | — | — |

*Solo vía API token con scope, no UI.

### DDD

- Contexto **Identity** (Supporting Domain)
- Middleware/Dashboard consumen `AuthorizationService` vía interfaces en Shared
- No acoplar User model a entidades de dominio del bus

---

## 6. Roadmap de Implementación

### Fase 1

- User + Role básico (3 roles)
- Policies en sync, publish, DLQ
- Login web mínimo

### Fase 2

- Audit log wiring
- UI gestión usuarios (admin only)
- Tests de autorización

### Fase 3

- SSO / LDAP
- Roles custom por cliente enterprise

---

## 7. Prioridad

**Crítico** (junto con Plan_Autenticacion.md)

---

## 8. Riesgo si no se implementa

Operaciones no auditables; imposible cumplir SOC2/ISO27001; riesgo interno (empleado modifica topología o resuelve DLQ incorrectamente sin registro).

---

## Referencias

- [Plan_Autenticacion.md](Plan_Autenticacion.md)
- [Plan_Tenants.md](Plan_Tenants.md)
- [Plan_Logs.md](Plan_Logs.md)
