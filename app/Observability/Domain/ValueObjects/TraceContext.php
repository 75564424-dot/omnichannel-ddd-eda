<?php

declare(strict_types=1);

namespace App\Observability\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;

/**
 * Lightweight trace context (OpenTelemetry-compatible IDs, Plan_Observabilidad).
 */
final class TraceContext
{
    public function __construct(
        public readonly string $traceId,
        public readonly string $spanId,
        public readonly ?string $parentSpanId,
        public readonly ?string $correlationId,
    ) {}

    public static function start(?string $correlationId = null, ?string $parentSpanId = null): self
    {
        $traceId = ($correlationId !== null && Uuid::isValid($correlationId))
            ? $correlationId
            : Uuid::uuid4()->toString();

        return new self(
            traceId: $traceId,
            spanId: Uuid::uuid4()->toString(),
            parentSpanId: $parentSpanId,
            correlationId: $correlationId,
        );
    }
}
