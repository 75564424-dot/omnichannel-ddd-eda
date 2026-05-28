<?php

declare(strict_types=1);

namespace App\Middleware\Listeners;

use App\Middleware\Application\Services\EventLogService;
use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Domain\Entities\QueueEntry;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\ValueObjects\EventOrigin;
use App\Observability\Application\Services\SliMetricsRecorder;
use App\Observability\Application\Services\TraceSpanService;
use App\Shared\EventBus\PlatformWildcardPayload;
use App\Shared\Logging\PlatformStructuredLogger;
use Throwable;

/**
 * Observes platform string events and records tracking entries (runs synchronously — safe with wildcard registration).
 */
final class BusTrackingListener
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntryRepository,
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly EventLogService $eventLogs,
        private readonly PlatformStructuredLogger $logger,
        private readonly TraceSpanService $traceSpans,
        private readonly SliMetricsRecorder $sliMetrics,
    ) {}

    /**
     * @param  string|array<string, mixed>  $first
     * @param  array<int, mixed>|null  $second
     */
    public function handle(mixed $first, mixed $second = null): void
    {
        if (is_string($first) && ! PlatformWildcardPayload::shouldObserveWildcardEvent($first)) {
            return;
        }

        [, $payload] = PlatformWildcardPayload::parse($first, $second);
        $eventId   = $payload['event_id'] ?? null;
        $eventType = $payload['event'] ?? $payload['event_type'] ?? 'Unknown';

        if (empty($eventId)) {
            $this->logger->warning('Received event with no event_id — skipping tracking', [
                'event_type' => $eventType,
            ]);

            return;
        }

        $started = microtime(true);
        $correlationId = null;

        try {
            $existing = $this->queueEntryRepository->findByEventId($eventId);

            if ($existing !== null) {
                $existing->markProcessed();
                $this->queueEntryRepository->save($existing);
                $correlationId = $existing->correlationId();

                $this->eventLogs->recordProcessed(
                    eventId: (string) $eventId,
                    eventType: $eventType,
                    origin: $existing->origin(),
                    payload: $payload,
                    correlationId: $existing->correlationId(),
                    channelId: $existing->channelId(),
                    integrationId: $existing->integrationId(),
                );

                $this->recordTrackingSpan($started, (string) $eventId, $eventType, $correlationId);

                return;
            }

            $origin    = EventOrigin::inferFromPayload($payload)->value();
            $consumers = $this->subscriptionRegistry->getConsumersFor($eventType);

            $entry = QueueEntry::record(
                eventId:   $eventId,
                eventType: $eventType,
                origin:    $origin,
                consumers: $consumers,
                payload:   $payload,
                status:    'PROCESADO',
            );

            $this->queueEntryRepository->save($entry);

            $this->eventLogs->recordProcessed(
                eventId: (string) $eventId,
                eventType: $eventType,
                origin: $origin,
                payload: $payload,
            );

            $this->recordTrackingSpan($started, (string) $eventId, $eventType, $correlationId);
        } catch (Throwable $e) {
            $this->logger->error('Failed to track event', [
                'event_uuid' => $eventId,
                'event_type' => $eventType,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function recordTrackingSpan(float $started, string $eventId, string $eventType, ?string $correlationId): void
    {
        $durationMs = (int) round((microtime(true) - $started) * 1000);

        $this->traceSpans->record(
            operationName: 'bus.track',
            status: 'OK',
            durationMs: $durationMs,
            eventUuid: $eventId,
            attributes: ['event_type' => $eventType],
            correlationId: $correlationId,
        );

        $this->sliMetrics->record('bus_processing_latency_ms', (float) $durationMs, [
            'event_type' => $eventType,
        ]);
    }
}
