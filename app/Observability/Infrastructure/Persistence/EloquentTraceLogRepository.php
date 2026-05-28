<?php

declare(strict_types=1);

namespace App\Observability\Infrastructure\Persistence;

use App\Observability\Domain\Repositories\TraceLogRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentTraceLogRepository implements TraceLogRepositoryInterface
{
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
    ): string {
        $id = (string) Str::uuid();
        $now = now();

        DB::table('trace_logs')->insert([
            'id'              => $id,
            'trace_id'        => $traceId,
            'span_id'         => $spanId,
            'parent_span_id'  => $parentSpanId,
            'correlation_id'  => $correlationId,
            'event_uuid'      => $eventUuid,
            'operation_name'  => $operationName,
            'service_name'    => $serviceName,
            'status'          => $status,
            'duration_ms'     => $durationMs,
            'attributes'      => $attributes !== null ? json_encode($attributes, JSON_THROW_ON_ERROR) : null,
            'started_at'      => $now->copy()->subMilliseconds($durationMs),
            'ended_at'        => $now,
            'created_at'      => $now,
        ]);

        return $id;
    }
}
