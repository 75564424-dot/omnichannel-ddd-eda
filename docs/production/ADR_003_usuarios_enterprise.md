# ADR-003 — Usuarios enterprise (SSO / LDAP / roles custom)

**Estado:** Propuesto | **Fecha:** 2026-05-21

## Contexto

`Plan_Usuarios.md` Fase 3 contempla SSO, LDAP y roles personalizados por cliente enterprise. La Fase 1–2 implementa RBAC nativo con 3 roles fijos por instancia (ADR-001 instance-per-client).

## Decisión

**Diferir** integración SSO/LDAP y roles custom hasta:

1. Cliente enterprise exija directory sync (Azure AD, Okta, LDAP)
2. Modelo comercial requiera delegación de admin por organización
3. Compliance exija provisioning SCIM

## Alternativas

| Opción | Cuándo |
|--------|--------|
| RBAC nativo actual | Piloto y producción instancia-por-cliente |
| Laravel Socialite + OIDC | SSO operadores UI |
| LDAP bind + group mapping | Clientes on-prem |
| Roles JSON por instancia | >3 roles sin Spatie |

## Consecuencias

- `users.platform_role` string suficiente hoy; migración a `roles` table si Spatie se adopta
- No bloquea producción con 3 roles estándar

## Referencias

- [Plan_Usuarios.md](Plan_Usuarios.md)
- [Plan_Autenticacion.md](Plan_Autenticacion.md)
