<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Canary;

use Illuminate\Support\Facades\Cache;

final class CanarySuccessTracker
{
    public const CACHE_KEY_LAST_SUCCESS = 'platform.monitoring.canary_last_success';

    public function markSuccess(): void
    {
        Cache::put(self::CACHE_KEY_LAST_SUCCESS, now()->timestamp, now()->addDay());
    }

    public function lastSuccessAgeSeconds(): int
    {
        $ts = Cache::get(self::CACHE_KEY_LAST_SUCCESS);
        if ($ts === null) {
            return -1;
        }

        return max(0, now()->timestamp - (int) $ts);
    }
}
