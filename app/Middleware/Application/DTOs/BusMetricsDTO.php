<?php

declare(strict_types=1);

namespace App\Middleware\Application\DTOs;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;

final class BusMetricsDTO
{
    public function __construct(
        public readonly int    $latencyMs,
        public readonly int    $eventsPerSecond,
        public readonly float  $errorRate,
        public readonly int    $deadLettersCount,
        public readonly string $busStatus,
        public readonly string $recordedAt,
    ) {}

    public static function fromSnapshot(BusMetricsSnapshot $snapshot): self
    {
        return new self(
            latencyMs:        $snapshot->latencyMs->value(),
            eventsPerSecond:  $snapshot->eventsPerSecond->value(),
            errorRate:        $snapshot->errorRate->value(),
            deadLettersCount: $snapshot->deadLettersCount,
            busStatus:        $snapshot->busStatus->value(),
            recordedAt:       $snapshot->recordedAt,
        );
    }

    public static function unavailable(): self
    {
        return new self(0, 0, 0.0, 0, 'STOPPED', now()->toDateTimeString());
    }

    public function toArray(): array
    {
        return [
            'latency_ms'        => $this->latencyMs,
            'events_per_second' => $this->eventsPerSecond,
            'error_rate'        => $this->errorRate,
            'dead_letters'      => $this->deadLettersCount,
            'bus_status'        => $this->busStatus,
            'recorded_at'       => $this->recordedAt,
        ];
    }
}
