<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Entities\EventLogEntry;
use App\Middleware\Domain\Repositories\EventLogRepositoryInterface;
use App\Middleware\Infrastructure\Models\EventLogModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;

final class EloquentEventLogRepository implements EventLogRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function append(EventLogEntry $entry): int
    {
        $model = EventLogModel::create([
            'tenant_id'      => $this->instanceTenant->tenantId(),
            'event_uuid'     => $entry->eventId(),
            'event_type'     => $entry->eventType(),
            'origin'         => $entry->origin(),
            'correlation_id' => $entry->correlationId(),
            'status'         => $entry->status(),
            'summary'        => $entry->summary(),
            'payload_hash'   => $entry->payloadHash(),
            'channel_id'     => $entry->channelId(),
            'integration_id' => $entry->integrationId(),
            'occurred_at'    => $entry->occurredAt()->format('Y-m-d H:i:s'),
            'logged_at'      => $entry->loggedAt()->format('Y-m-d H:i:s'),
        ]);

        return (int) $model->id;
    }
}
