<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Application\DTOs\PublishResult;
use App\Middleware\Application\Services\Publish\EventPublishIdempotencyGuard;
use App\Middleware\Application\Services\Publish\PublishEnvelopeSchemaResolver;
use App\Middleware\Application\Services\Publish\PublishEnvelopeValidator;
use App\Middleware\Domain\Entities\QueueEntry;
use App\Middleware\Domain\Entities\StoredEvent;
use App\Middleware\Domain\Repositories\EventStoreRepositoryInterface;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use App\Middleware\Domain\ValueObjects\CorrelationContext;
use App\Observability\Application\Services\SliMetricsRecorder;
use App\Observability\Application\Services\TraceSpanService;
use App\Shared\Contracts\EventBus\EventPublisherInterface;
use App\Shared\Logging\PlatformStructuredLogger;
use InvalidArgumentException;

/**
 * Entry point for external or programmatic event publishing into the bus.
 *
 * Pipeline (Plan_Middleware): validate → event_store → event_logs → message_queue → dispatch.
 */
final class EventPublisherService implements EventPublisherInterface
{
    public function __construct(
        private readonly QueueEntryRepositoryInterface $queueEntryRepository,
        private readonly EventStoreRepositoryInterface $eventStoreRepository,
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly PublishPayloadSchemaValidator $schemaValidator,
        private readonly PublishEnvelopeValidator $envelopeValidator,
        private readonly PublishEnvelopeSchemaResolver $schemaResolver,
        private readonly EventPublishIdempotencyGuard $idempotencyGuard,
        private readonly EventLogProjector $eventLogProjector,
        private readonly EventProcessingService $eventProcessing,
        private readonly PlatformStructuredLogger $logger,
        private readonly TraceSpanService $traceSpans,
        private readonly SliMetricsRecorder $sliMetrics,
    ) {}

    /**
     * @param array<string, mixed> $envelope Expected keys: event_id, event_type, payload, occurred_at, origin (optional)
     *
     * @throws InvalidArgumentException on structural validation failure
     */
    public function publish(array $envelope): PublishResult
    {
        $started = microtime(true);
        $this->envelopeValidator->validateStructure($envelope);
        $envelope = $this->schemaResolver->applyDefaults($envelope);

        $eventType = (string) $envelope['event_type'];
        $payload   = $envelope['payload'];
        $this->schemaValidator->validate($eventType, $payload);
        $eventId = (string) $envelope['event_id'];
        $origin  = (string) ($envelope['origin'] ?? 'External');

        if ($this->idempotencyGuard->isAlreadyPublished($eventId)) {
            $existing = $this->queueEntryRepository->findByEventId($eventId);

            return new PublishResult(
                entryId: $existing?->id() ?? 0,
                idempotent: true,
            );
        }

        $correlation = CorrelationContext::fromEnvelope($envelope);
        $stored = StoredEvent::fromPublishEnvelope(
            $envelope,
            $correlation->correlationId,
            $correlation->causationId,
        );

        $this->eventStoreRepository->append($stored);
        $this->eventLogProjector->projectReceived($stored);

        $consumers = $this->subscriptionRegistry->getConsumersFor($eventType);

        $queueEntry = QueueEntry::record(
            eventId:       $eventId,
            eventType:     $eventType,
            origin:        $origin,
            consumers:     $consumers,
            payload:       $payload,
            status:        'PENDING',
            correlationId: $correlation->correlationId,
            channelId:     isset($envelope['channel_id']) ? (string) $envelope['channel_id'] : null,
            integrationId: isset($envelope['integration_id']) ? (string) $envelope['integration_id'] : null,
        );

        $entryId = $this->queueEntryRepository->save($queueEntry);

        $this->eventProcessing->dispatchAfterPublish($eventId, $eventType, $payload, $origin);

        $this->logger->info('Event published', [
            'event_uuid'     => $eventId,
            'event_type'     => $eventType,
            'origin'         => $origin,
            'correlation_id' => $correlation->correlationId,
            'consumers'      => $consumers->toArray(),
        ]);

        $durationMs = (int) round((microtime(true) - $started) * 1000);
        $this->traceSpans->record(
            operationName: 'bus.publish',
            status: 'OK',
            durationMs: $durationMs,
            eventUuid: $eventId,
            attributes: ['event_type' => $eventType, 'origin' => $origin],
            correlationId: $correlation->correlationId,
        );
        $this->sliMetrics->record('bus_events_published_total', 1.0, [
            'event_type' => $eventType,
        ]);

        return new PublishResult(entryId: $entryId, idempotent: false);
    }
}
