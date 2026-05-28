<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Projectors;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use App\Observability\Application\Services\SliMetricsRecorder;
use App\Observability\Application\Services\TraceSpanService;
use Illuminate\Support\Facades\Log;

/**
 * Responsible for writing EventFeedEntry projections to the Read Store.
 * Enforces idempotency — skips if event_id already exists.
 */
final class EventFeedProjector
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
        private readonly TraceSpanService $traceSpans,
        private readonly SliMetricsRecorder $sliMetrics,
    ) {}

    /**
     * Projects a domain event into the event feed Read Store.
     *
     * @return int|null The new entry ID, or null if idempotency guard triggered.
     */
    public function project(
        string      $eventId,
        string      $eventType,
        EventOrigin $origin,
        EventImpact $impact,
        string      $occurredAt,
        array       $rawPayload,
        string      $status = 'SUCCESS',
        ?string     $correlationId = null,
    ): ?int {
        $started = microtime(true);

        if ($this->feedRepository->existsByEventId($eventId)) {
            Log::info("Dashboard: duplicate event_id [{$eventId}] for [{$eventType}] — skipped projection");
            return null;
        }

        $entry = EventFeedEntry::project(
            eventId:     $eventId,
            eventType:   $eventType,
            origin:      $origin,
            impact:      $impact,
            occurredAt:  $occurredAt,
            rawPayload:  $rawPayload,
            status:      $status,
            correlationId: $correlationId,
        );

        $newId = $this->feedRepository->save($entry);

        $durationMs = (int) round((microtime(true) - $started) * 1000);
        $lagMs      = $entry->latencyMs();

        $this->traceSpans->record(
            operationName: 'feed.project',
            status: 'OK',
            durationMs: $durationMs,
            eventUuid: $eventId,
            attributes: ['event_type' => $eventType, 'feed_lag_ms' => $lagMs],
            correlationId: $correlationId,
        );

        $this->sliMetrics->record('feed_projection_lag_ms', (float) $lagMs, [
            'event_type' => $eventType,
        ]);

        Log::info("Dashboard: EventFeedEntry projected", [
            'id'              => $newId,
            'event_type'      => $eventType,
            'origin'          => $origin->value(),
            'correlation_id'  => $correlationId,
        ]);

        return $newId;
    }
}
