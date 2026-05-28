# Matriz — tests prioritarios Plan_Calidad

| # | Test prioritario | Estado | Archivo |
|---|------------------|--------|---------|
| 1 | Auth middleware | ✅ | `tests/Feature/Security/PlatformApiAuthenticationTest.php` |
| 2 | Webhook signature validation | ✅ | `tests/Feature/Integration/WebhookIngressTest.php` |
| 3 | event_store append idempotency | ✅ | `tests/Integration/Middleware/EventStoreIdempotencyIntegrationTest.php` |
| 4 | Retention purge command | ✅ | `tests/Feature/Platform/PurgePlatformRetentionTest.php` |
| 5 | Idempotent publish HTTP 200 | ✅ | `tests/Feature/Middleware/ResilienceApiTest.php` |
| 6 | validate-catalog command | ✅ | `tests/Unit/Platform/ValidatePlatformCatalogTest.php` |

Todos cubiertos en CI vía `.github/workflows/ci.yml`.
