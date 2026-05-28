<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\Entities\EventLogEntry;
use App\Middleware\Domain\Entities\StoredEvent;
use App\Middleware\Domain\Repositories\EventLogRepositoryInterface;
use App\Shared\Logging\StructuredLogContext;

/**
 * Writer for operational event_logs rows — summary + payload hash only (Plan_Logs Fase 2).
 */
final class EventLogService
{
    public function __construct(
        private readonly EventLogRepositoryInterface $eventLogs,
    ) {}

    public function recordReceived(StoredEvent $stored): int
    {
        StructuredLogContext::setEventUuid($stored->eventId());
        StructuredLogContext::setEventType($stored->eventType());
        StructuredLogContext::setOrigin($stored->origin());
        if ($stored->correlationId() !== null) {
            StructuredLogContext::setCorrelationId($stored->correlationId());
        }

        return $this->eventLogs->append(EventLogEntry::received($stored));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordProcessed(
        string $eventId,
        string $eventType,
        string $origin,
        array $payload,
        ?string $correlationId = null,
        ?string $channelId = null,
        ?string $integrationId = null,
    ): int {
        return $this->eventLogs->append(EventLogEntry::lifecycle(
            eventId: $eventId,
            eventType: $eventType,
            origin: $origin,
            status: 'processed',
            summary: 'Event processed by bus listeners',
            payload: $payload,
            correlationId: $correlationId,
            channelId: $channelId,
            integrationId: $integrationId,
        ));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function recordFailed(
        string $eventId,
        string $eventType,
        string $origin,
        array $payload,
        string $reason,
        ?string $correlationId = null,
    ): int {
        return $this->eventLogs->append(EventLogEntry::lifecycle(
            eventId: $eventId,
            eventType: $eventType,
            origin: $origin,
            status: 'failed',
            summary: mb_substr($reason, 0, 255),
            payload: $payload,
            correlationId: $correlationId,
        ));
    }
}
