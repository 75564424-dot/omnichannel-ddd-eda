<?php

declare(strict_types=1);

namespace App\Middleware\Application\DTOs;

use App\Middleware\Domain\Entities\DeadLetterEntry;

final class DeadLetterDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $eventId,
        public readonly string  $eventType,
        public readonly string  $origin,
        public readonly string  $failureReason,
        public readonly string  $failedAt,
        public readonly ?string $resolvedAt,
    ) {}

    public static function fromEntity(DeadLetterEntry $entry): self
    {
        return new self(
            id:            $entry->id(),
            eventId:       $entry->eventId(),
            eventType:     $entry->eventType(),
            origin:        $entry->origin(),
            failureReason: $entry->failureReason(),
            failedAt:      $entry->failedAt()->format('Y-m-d H:i:s'),
            resolvedAt:    $entry->resolvedAt()?->format('Y-m-d H:i:s'),
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'event_id'       => $this->eventId,
            'event_type'     => $this->eventType,
            'origin'         => $this->origin,
            'failure_reason' => $this->failureReason,
            'failed_at'      => $this->failedAt,
            'resolved_at'    => $this->resolvedAt,
        ];
    }
}
