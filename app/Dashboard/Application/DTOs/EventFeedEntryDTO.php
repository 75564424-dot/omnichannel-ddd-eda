<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTOs;

use App\Dashboard\Domain\ReadModels\EventFeedEntry;
use DateTimeInterface;

final class EventFeedEntryDTO
{
    public function __construct(
        public readonly int    $id,
        public readonly string $eventId,
        public readonly string $eventType,
        public readonly string $origin,
        public readonly string $impact,
        public readonly string $status,
        public readonly string $occurredAt,
        public readonly string $receivedAt,
        public readonly int    $latencyMs,
    ) {}

    public static function fromReadModel(EventFeedEntry $entry): self
    {
        return new self(
            id:         $entry->id,
            eventId:    $entry->eventId,
            eventType:  $entry->eventType,
            origin:     $entry->origin->value(),
            impact:     $entry->impact->value(),
            status:     $entry->status,
            occurredAt: $entry->occurredAt->format(DateTimeInterface::ATOM),
            receivedAt: $entry->receivedAt->format(DateTimeInterface::ATOM),
            latencyMs:  $entry->latencyMs(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'event_id'    => $this->eventId,
            'event_type'  => $this->eventType,
            'origin'      => $this->origin,
            'impact'      => $this->impact,
            'status'      => $this->status,
            'occurred_at' => $this->occurredAt,
            'received_at' => $this->receivedAt,
            'latency_ms'  => $this->latencyMs,
        ];
    }
}
