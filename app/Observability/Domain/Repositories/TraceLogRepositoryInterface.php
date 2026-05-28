<?php

declare(strict_types=1);

namespace App\Observability\Domain\Repositories;

interface TraceLogRepositoryInterface
{
    /**
     * @param array<string, mixed>|null $attributes
     */
    public function recordSpan(
        string $traceId,
        string $spanId,
        ?string $parentSpanId,
        ?string $correlationId,
        ?string $eventUuid,
        string $operationName,
        string $serviceName,
        string $status,
        int $durationMs,
        ?array $attributes = null,
    ): string;
}
