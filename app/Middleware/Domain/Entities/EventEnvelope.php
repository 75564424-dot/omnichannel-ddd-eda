<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Entities;

use App\Middleware\Domain\ValueObjects\ConsumerList;
use App\Middleware\Domain\ValueObjects\EventId;
use App\Middleware\Domain\ValueObjects\EventOrigin;
use App\Middleware\Domain\ValueObjects\EventType;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

/**
 * An event in transit through the bus.
 * Contains the event and its routing metadata.
 * The bus NEVER modifies the payload — it is immutable in transit.
 */
final class EventEnvelope
{
    private function __construct(
        private readonly EventId      $eventId,
        private readonly EventType    $eventType,
        private readonly EventOrigin  $origin,
        private readonly ConsumerList $consumers,
        private readonly array        $payload,
        private readonly DateTimeImmutable $publishedAt,
    ) {}

    public static function wrap(
        string       $eventId,
        string       $eventType,
        string       $origin,
        array        $consumers,
        array        $payload,
    ): self {
        return new self(
            eventId:     new EventId($eventId),
            eventType:   new EventType($eventType),
            origin:      new EventOrigin($origin),
            consumers:   new ConsumerList($consumers),
            payload:     $payload,
            publishedAt: new DateTimeImmutable(),
        );
    }

    /**
     * Creates an EventEnvelope from a raw event payload arriving from a producer Job.
     */
    public static function fromPayload(array $payload, array $consumers = []): self
    {
        $eventId   = $payload['event_id']   ?? Uuid::uuid4()->toString();
        $eventType = $payload['event']      ?? $payload['event_type'] ?? 'Unknown';
        $origin    = EventOrigin::inferFromPayload($payload)->value();

        return self::wrap($eventId, $eventType, $origin, $consumers, $payload);
    }

    public function eventId(): EventId       { return $this->eventId; }
    public function eventType(): EventType   { return $this->eventType; }
    public function origin(): EventOrigin    { return $this->origin; }
    public function consumers(): ConsumerList { return $this->consumers; }
    public function payload(): array         { return $this->payload; }
    public function publishedAt(): DateTimeImmutable { return $this->publishedAt; }
}
