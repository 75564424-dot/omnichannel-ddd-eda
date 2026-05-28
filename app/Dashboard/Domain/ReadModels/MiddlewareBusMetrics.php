<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ReadModels;

use App\Dashboard\Domain\ValueObjects\StreamStatus;

/**
 * Read Model: operational state of the Event Bus (Middleware).
 * Computed from event feed data and heartbeat signals.
 */
final class MiddlewareBusMetrics
{
    public function __construct(
        public readonly int          $latencyMs,
        public readonly int          $processingRateEps,
        public readonly int          $queueSize,
        public readonly StreamStatus $streamStatus,
        public readonly string       $recordedAt,
    ) {}
}
