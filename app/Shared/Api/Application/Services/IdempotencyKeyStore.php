<?php

declare(strict_types=1);

namespace App\Shared\Api\Application\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Stores publish responses keyed by Idempotency-Key header (Plan_APIs).
 */
final class IdempotencyKeyStore
{
    private const PREFIX = 'platform.api.idempotency.';

    /**
     * @return array{status: int, body: array<string, mixed>}|null
     */
    public function get(string $key): ?array
    {
        if (! config('platform_api.idempotency.enabled', true)) {
            return null;
        }

        $cached = Cache::get(self::PREFIX.$key);

        return is_array($cached) ? $cached : null;
    }

    /**
     * @param array<string, mixed> $body
     */
    public function remember(string $key, int $status, array $body): void
    {
        if (! config('platform_api.idempotency.enabled', true)) {
            return;
        }

        $ttl = (int) config('platform_api.idempotency.ttl_seconds', 86400);

        Cache::put(self::PREFIX.$key, [
            'status' => $status,
            'body'   => $body,
        ], $ttl);
    }
}
