<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Resilience;

use Illuminate\Support\Facades\Cache;

/**
 * Simple circuit breaker for connector/event-type failures (Plan_Resiliencia Fase 3).
 */
final class ConnectorCircuitBreaker
{
    private const CACHE_PREFIX = 'platform:circuit:';

    public function isOpen(string $connectorKey): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $state = Cache::get($this->cacheKey($connectorKey));

        return is_array($state) && ($state['open'] ?? false) === true;
    }

    public function recordSuccess(string $connectorKey): void
    {
        if (! $this->enabled()) {
            return;
        }

        Cache::forget($this->cacheKey($connectorKey));
    }

    public function recordFailure(string $connectorKey): void
    {
        if (! $this->enabled()) {
            return;
        }

        $key       = $this->cacheKey($connectorKey);
        $threshold = max(1, (int) config('eventbus.resilience.circuit_breaker.failure_threshold', 5));
        $openSecs  = max(1, (int) config('eventbus.resilience.circuit_breaker.open_seconds', 60));

        /** @var array{failures: int, open: bool} $state */
        $state = Cache::get($key, ['failures' => 0, 'open' => false]);
        $state['failures'] = (int) ($state['failures'] ?? 0) + 1;

        if ($state['failures'] >= $threshold) {
            $state['open'] = true;
            Cache::put($key, $state, $openSecs);

            return;
        }

        Cache::put($key, $state, $openSecs);
    }

    private function enabled(): bool
    {
        return filter_var(
            config('eventbus.resilience.circuit_breaker.enabled', false),
            FILTER_VALIDATE_BOOLEAN,
        );
    }

    private function cacheKey(string $connectorKey): string
    {
        return self::CACHE_PREFIX.hash('sha256', $connectorKey);
    }
}
