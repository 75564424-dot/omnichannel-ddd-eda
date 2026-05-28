<?php

declare(strict_types=1);

namespace App\Observability\Application\Services;

use App\Http\Middleware\CorrelationIdMiddleware;
use App\Observability\Domain\Repositories\TraceLogRepositoryInterface;
use App\Observability\Domain\ValueObjects\TraceContext;
use App\Shared\Logging\StructuredLogContext;

/**
 * Records pipeline spans into trace_logs (Plan_Observabilidad Fase 2).
 */
final class TraceSpanService
{
    private ?TraceContext $rootContext = null;

    public function __construct(
        private readonly TraceLogRepositoryInterface $traces,
    ) {}

    /**
     * @param array<string, mixed>|null $attributes
     */
    public function record(
        string $operationName,
        string $status,
        int $durationMs,
        ?string $eventUuid = null,
        ?array $attributes = null,
        ?string $serviceName = null,
        ?string $correlationId = null,
    ): void {
        if (! config('platform_observability.trace_spans_enabled', true)) {
            return;
        }

        $correlationId ??= StructuredLogContext::toArray()['correlation_id']
            ?? CorrelationIdMiddleware::currentCorrelationId();

        $root = $this->rootContext ?? TraceContext::start($correlationId);
        if ($this->rootContext === null) {
            $this->rootContext = $root;
        }

        $span = TraceContext::start($correlationId, $root->spanId);

        $this->traces->recordSpan(
            traceId: $root->traceId,
            spanId: $span->spanId,
            parentSpanId: $root->spanId,
            correlationId: $correlationId,
            eventUuid: $eventUuid,
            operationName: $operationName,
            serviceName: $serviceName ?? (string) config('platform_observability.service_name', 'middleware'),
            status: $status,
            durationMs: max(0, $durationMs),
            attributes: $attributes,
        );
    }
}
