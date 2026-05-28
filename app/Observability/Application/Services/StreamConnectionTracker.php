<?php

declare(strict_types=1);

namespace App\Observability\Application\Services;

/**
 * Tracks active SSE dashboard stream connections (Plan_Observabilidad).
 */
final class StreamConnectionTracker
{
    private static int $active = 0;

    public function increment(): void
    {
        ++self::$active;
    }

    public function decrement(): void
    {
        if (self::$active > 0) {
            --self::$active;
        }
    }

    public function activeCount(): int
    {
        return self::$active;
    }
}
