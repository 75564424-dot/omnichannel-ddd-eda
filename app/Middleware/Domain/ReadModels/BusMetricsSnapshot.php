<?php

declare(strict_types=1);

namespace App\Middleware\Domain\ReadModels;

use App\Middleware\Domain\ValueObjects\BusStatus;
use App\Middleware\Domain\ValueObjects\ErrorRate;
use App\Middleware\Domain\ValueObjects\LatencyMs;
use App\Middleware\Domain\ValueObjects\ThroughputEps;

final class BusMetricsSnapshot
{
    public function __construct(
        public readonly LatencyMs     $latencyMs,
        public readonly ThroughputEps $eventsPerSecond,
        public readonly ErrorRate     $errorRate,
        public readonly int           $deadLettersCount,
        public readonly BusStatus     $busStatus,
        public readonly string        $recordedAt,
    ) {}
}
