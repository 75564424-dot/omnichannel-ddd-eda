# ADR-002 — Autenticación enterprise (Fase 3 diferida)

**Estado:** Propuesto | **Fecha:** 2026-05-21

## Contexto

`Plan_Autenticacion.md` Fase 3 contempla OAuth2 client credentials, IdP enterprise (Azure AD, Okta) y MFA para operadores admin. La Fase 1–2 cubre Sanctum session + PAT + API keys estáticas, suficiente para piloto instancia-por-cliente.

## Decisión

**Diferir** Passport/OAuth2 e IdP hasta:

1. Existir ≥2 integradores enterprise exigiendo client credentials estándar
2. Clientes requieran SSO operador (Azure AD / Okta)
3. Compliance exija MFA en panel admin

## Alternativas evaluadas

| Opción | Pros | Contras |
|--------|------|---------|
| **Sanctum PAT + API keys (actual)** | Simple, operable hoy | No OAuth2 estándar |
| Laravel Passport | OAuth2 completo | Complejidad, mantenimiento keys JWT |
| IdP externo + Socialite | SSO familiar | Mapping roles, coste licencias |

## Roadmap Fase 3 (cuando aplique)

1. Evaluar `laravel/passport` vs proxy OAuth2 en API Gateway
2. Azure AD: OIDC para operadores UI; client credentials para M2M vía App Registration
3. MFA: delegar a IdP (Conditional Access) antes que MFA custom en app
4. Migración PAT → OAuth2: periodo dual con deprecation header

## Consecuencias

- Documentación M2M actual (`Flujo_M2M_Integradores.md`) permanece válida
- No instalar Passport en esta fase — evita deuda prematura
- RBAC humano granular sigue en `Plan_Usuarios.md`

## Referencias

- [Plan_Autenticacion.md](Plan_Autenticacion.md)
- [Plan_Seguridad.md](Plan_Seguridad.md)
