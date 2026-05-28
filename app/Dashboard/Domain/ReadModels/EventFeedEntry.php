<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ReadModels;

use App\Dashboard\Domain\ValueObjects\EventImpact;
use App\Dashboard\Domain\ValueObjects\EventOrigin;
use DateTimeImmutable;

/**
 * Read Model: one entry in the Dashboard event feed.
 * Built exclusively from event payloads — never modified after creation.
 */
final class EventFeedEntry
{
    public function __construct(
        public readonly int             $id,
        public readonly string          $eventId,
        public readonly string          $eventType,
        public readonly EventOrigin     $origin,
        public readonly EventImpact     $impact,
        public readonly string          $status,
        public readonly DateTimeImmutable $occurredAt,
        public readonly DateTimeImmutable $receivedAt,
        public readonly array           $rawPayload,
        public readonly ?string         $correlationId = null,
    ) {}

    public static function project(
        string      $eventId,
        string      $eventType,
        EventOrigin $origin,
        EventImpact $impact,
        string      $occurredAt,
        array       $rawPayload,
        string      $status = 'SUCCESS',
        ?string     $correlationId = null,
    ): self {
        return new self(
            id:         0,
            eventId:    $eventId,
            eventType:  $eventType,
            origin:     $origin,
            impact:     $impact,
            status:     $status,
            occurredAt: new DateTimeImmutable($occurredAt),
            receivedAt: new DateTimeImmutable(),
            rawPayload: $rawPayload,
            correlationId: $correlationId,
        );
    }

    public function latencyMs(): int
    {
        return max(0, (int) (($this->receivedAt->getTimestamp() - $this->occurredAt->getTimestamp()) * 1000));
    }
}
