# API Changelog

All notable API changes are documented here (Plan_APIs Fase 3).

## [1.0.0] - 2026-05-22

### Added

- OpenAPI 3.0 spec at `docs/api/openapi.yaml`
- Versioned routes under `/api/v1/middleware`, `/api/v1/dashboard`, `/api/v1/integrations`
- Pagination `?page=&limit=` on queue and event feed
- `Idempotency-Key` header on publish
- RFC 7807 Problem Details on API auth/validation errors
- `X-RateLimit-*` header normalization
- Postman collection at `docs/api/postman_collection.json`

### Unchanged (legacy)

- `/api/middleware/*`, `/api/dashboard/*`, `/api/integrations/*` remain active for backward compatibility

## Upcoming (v2 — breaking, not scheduled)

- Mandatory authentication on all endpoints (coordinate with integrators)
- Deprecation of unversioned `/api/*` paths
