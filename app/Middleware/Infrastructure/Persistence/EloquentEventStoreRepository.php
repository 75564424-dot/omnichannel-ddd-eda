<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Entities\StoredEvent;
use App\Middleware\Domain\Repositories\EventStoreRepositoryInterface;
use App\Middleware\Infrastructure\Models\EventStoreModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

final class EloquentEventStoreRepository implements EventStoreRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function append(StoredEvent $event): int
    {
        $model = EventStoreModel::create([
            'tenant_id'      => $this->instanceTenant->tenantId(),
            'event_uuid'     => $event->eventId(),
            'correlation_id' => $event->correlationId(),
            'causation_id'   => $event->causationId(),
            'aggregate_type' => $event->aggregateType(),
            'aggregate_id'   => $event->aggregateId(),
            'event_type'     => $event->eventType(),
            'event_version'  => $event->eventVersion(),
            'origin'         => $event->origin(),
            'payload'        => $event->payload(),
            'metadata'       => $event->metadata(),
            'occurred_at'    => $event->occurredAt()->format('Y-m-d H:i:s'),
            'recorded_at'    => $event->recordedAt()->format('Y-m-d H:i:s'),
            'schema_version' => $event->schemaVersion(),
            'channel_id'     => $event->channelId(),
            'integration_id' => $event->integrationId(),
        ]);

        return (int) $model->id;
    }

    public function existsByEventId(string $eventId): bool
    {
        return EventStoreModel::where('event_uuid', $eventId)->exists();
    }
}
