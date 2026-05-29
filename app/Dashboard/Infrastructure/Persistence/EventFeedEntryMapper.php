<?php

declare(strict_types=1);

namespace App\Dashboard\Infrastructure\Persistence;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use App\Dashboard\Infrastructure\Models\EventFeedEntryModel;
use DateTimeImmutable;

final class EventFeedEntryMapper
{
    public function toDomain(EventFeedEntryModel $model): EventFeedEntry
    {
        return new EventFeedEntry(
            id:         (int) $model->id,
            eventId:    $model->event_uuid,
            eventType:  $model->event_type,
            origin:     new EventOrigin($model->origin),
            impact:     new EventImpact($model->impact),
            status:     $model->status,
            occurredAt: DateTimeImmutable::createFromInterface($model->occurred_at),
            receivedAt: DateTimeImmutable::createFromInterface($model->received_at),
            rawPayload: $model->raw_payload ?? [],
            correlationId: $model->correlation_id,
        );
    }
}
