<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Short-lived cache flag so client UIs can show generic middleware flow animation during simulations.
 */
final class SimulationPulseService
{
    private const CACHE_KEY = 'middleware.simulation_pulse';

    /** After this many seconds without a tick, the pulse is treated as inactive (avoids stuck UI loops). */
    private const STALE_SECONDS = 90;

    public function tick(string $phase, ?string $eventType = null): void
    {
        $previous = $this->snapshot();
        $sequence = (int) ($previous['sequence'] ?? 0) + 1;

        Cache::put(self::CACHE_KEY, [
            'active'          => true,
            'phase'           => $phase,
            'last_event_type' => $eventType,
            'sequence'        => $sequence,
            'tick_at'         => now()->toIso8601String(),
        ], now()->addMinutes(6));
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, mixed> */
    public function snapshot(): array
    {
        $stored = Cache::get(self::CACHE_KEY);

        if (! is_array($stored) || ($stored['active'] ?? false) !== true) {
            return ['active' => false];
        }

        $tickAt = $stored['tick_at'] ?? null;
        if (is_string($tickAt) && $tickAt !== '') {
            try {
                $ageSeconds = now()->diffInSeconds(\Carbon\Carbon::parse($tickAt), absolute: true);
                if ($ageSeconds > self::STALE_SECONDS) {
                    $this->clear();

                    return ['active' => false, 'stale' => true];
                }
            } catch (\Throwable) {
                $this->clear();

                return ['active' => false];
            }
        }

        return $stored;
    }
}
