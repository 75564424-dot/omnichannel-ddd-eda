<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTOs;

use App\Dashboard\Domain\ReadModels\MiddlewareBusMetrics;

final class MiddlewareBusMetricsDTO
{
    public function __construct(
        public readonly int    $latencyMs,
        public readonly int    $processingRateEps,
        public readonly int    $queueSize,
        public readonly string $streamStatus,
        public readonly string $recordedAt,
    ) {}

    public static function fromReadModel(MiddlewareBusMetrics $metrics): self
    {
        return new self(
            latencyMs:         $metrics->latencyMs,
            processingRateEps: $metrics->processingRateEps,
            queueSize:         $metrics->queueSize,
            streamStatus:      $metrics->streamStatus->value(),
            recordedAt:        $metrics->recordedAt,
        );
    }

    public static function unavailable(): self
    {
        return new self(0, 0, 0, 'STOPPED', now()->toDateTimeString());
    }

    public function toArray(): array
    {
        return [
            'latency_ms'          => $this->latencyMs,
            'processing_rate_eps' => $this->processingRateEps,
            'queue_size'          => $this->queueSize,
            'stream_status'       => $this->streamStatus,
            'recorded_at'         => $this->recordedAt,
        ];
    }
}
