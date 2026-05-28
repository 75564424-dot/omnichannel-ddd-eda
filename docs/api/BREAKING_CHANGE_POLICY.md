# API Breaking Change Policy

**Plan:** Plan_APIs.md Fase 3

## Versioning

| Prefix | Status | Policy |
|--------|--------|--------|
| `/api/v1/` | **Current** | Frozen behavior; additive changes only |
| `/api/middleware`, `/api/dashboard`, `/api/integrations` | **Legacy** | Mirrors v1; deprecated in future v2 |
| `/api/v2/` | **Future** | Breaking changes (auth mandatory, envelope changes) |

## Allowed in v1 (non-breaking)

- New optional query parameters
- New response fields
- New endpoints
- New optional headers

## Breaking changes (require v2 + changelog + migration window)

- Removing or renaming fields
- Changing response status codes for same input
- Making auth required where optional
- Changing pagination defaults in incompatible ways

## Process

1. Document in `docs/api/CHANGELOG.md`
2. Update `docs/api/openapi.yaml`
3. Add contract tests if behavior changes
4. Notify integrators ≥ 90 days before legacy path removal

## Contract validation

- OpenAPI Spectral lint in CI: `bash scripts/ci/lint-openapi.sh`
- PHPUnit contract tests: `tests/Feature/Api/`
