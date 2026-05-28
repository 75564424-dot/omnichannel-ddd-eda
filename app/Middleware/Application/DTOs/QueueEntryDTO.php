<?php

declare(strict_types=1);

namespace App\Middleware\Application\DTOs;

use App\Middleware\Domain\Entities\QueueEntry;

final class QueueEntryDTO
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $eventId,
        public readonly string  $eventType,
        public readonly string  $origin,
        public readonly array   $consumers,
        public readonly string  $status,
        public readonly string  $publishedAt,
        public readonly ?string $dispatchedAt,
        public readonly ?int    $processingTimeMs,
        public readonly int     $attemptCount,
    ) {}

    public static function fromEntity(QueueEntry $entry): self
    {
        return new self(
            id:               $entry->id(),
            eventId:          $entry->eventId(),
            eventType:        $entry->eventType(),
            origin:           $entry->origin(),
            consumers:        $entry->consumers()->toArray(),
            status:           $entry->status()->value(),
            publishedAt:      $entry->publishedAt()->format('Y-m-d H:i:s'),
            dispatchedAt:     $entry->dispatchedAt()?->format('Y-m-d H:i:s'),
            processingTimeMs: $entry->processingTimeMs(),
            attemptCount:     $entry->attemptCount(),
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'event_id'          => $this->eventId,
            'event_type'        => $this->eventType,
            'origin'            => $this->origin,
            'consumers'         => $this->consumers,
            'status'            => $this->status,
            'published_at'      => $this->publishedAt,
            'dispatched_at'     => $this->dispatchedAt,
            'processing_time_ms' => $this->processingTimeMs,
            'attempt_count'     => $this->attemptCount,
        ];
    }
}
