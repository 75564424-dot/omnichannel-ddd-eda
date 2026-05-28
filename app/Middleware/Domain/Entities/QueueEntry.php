<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Entities;

use App\Middleware\Domain\ValueObjects\ConsumerList;
use App\Middleware\Domain\ValueObjects\EventStatus;
use DateTimeImmutable;

/**
 * Tracking record of an event in the FIFO bus queue.
 * Written when an event enters the bus; updated as it's processed.
 */
final class QueueEntry
{
    private ?DateTimeImmutable $dispatchedAt    = null;
    private ?int               $processingTimeMs = null;
    private int                $attemptCount     = 0;

    private function __construct(
        private readonly int           $id,
        private readonly string        $eventId,
        private readonly string        $eventType,
        private readonly string        $origin,
        private readonly ConsumerList  $consumers,
        private readonly array         $payload,
        private EventStatus            $status,
        private readonly DateTimeImmutable $publishedAt,
        private readonly ?string       $correlationId = null,
        private readonly ?string       $channelId = null,
        private readonly ?string       $integrationId = null,
    ) {}

    public static function record(
        string       $eventId,
        string       $eventType,
        string       $origin,
        ConsumerList $consumers,
        array        $payload,
        string       $status = EventStatus::PROCESADO,
        ?string      $correlationId = null,
        ?string      $channelId = null,
        ?string      $integrationId = null,
    ): self {
        return new self(
            id:          0,
            eventId:     $eventId,
            eventType:   $eventType,
            origin:      $origin,
            consumers:   $consumers,
            payload:     $payload,
            status:      new EventStatus($status),
            publishedAt: new DateTimeImmutable($payload['occurred_at'] ?? 'now'),
            correlationId: $correlationId,
            channelId: $channelId,
            integrationId: $integrationId,
        );
    }

    public static function reconstitute(
        int                $id,
        string             $eventId,
        string             $eventType,
        string             $origin,
        ConsumerList       $consumers,
        array              $payload,
        EventStatus        $status,
        DateTimeImmutable  $publishedAt,
        ?DateTimeImmutable $dispatchedAt,
        ?int               $processingTimeMs,
        int                $attemptCount,
        ?string            $correlationId = null,
        ?string            $channelId = null,
        ?string            $integrationId = null,
    ): self {
        $entry                   = new self($id, $eventId, $eventType, $origin, $consumers, $payload, $status, $publishedAt, $correlationId, $channelId, $integrationId);
        $entry->dispatchedAt     = $dispatchedAt;
        $entry->processingTimeMs = $processingTimeMs;
        $entry->attemptCount     = $attemptCount;
        return $entry;
    }

    public function markProcessed(): void
    {
        $this->status        = EventStatus::procesado();
        $this->dispatchedAt  = new DateTimeImmutable();
        $this->processingTimeMs = max(0, (int) (
            ($this->dispatchedAt->getTimestamp() - $this->publishedAt->getTimestamp()) * 1000
        ));
    }

    public function markFailed(): void
    {
        $this->status       = EventStatus::fallido();
        $this->attemptCount = $this->attemptCount + 1;
    }

    public function id(): int                          { return $this->id; }
    public function eventId(): string                  { return $this->eventId; }
    public function eventType(): string                { return $this->eventType; }
    public function origin(): string                   { return $this->origin; }
    public function consumers(): ConsumerList          { return $this->consumers; }
    public function payload(): array                   { return $this->payload; }
    public function status(): EventStatus              { return $this->status; }
    public function publishedAt(): DateTimeImmutable   { return $this->publishedAt; }
    public function dispatchedAt(): ?DateTimeImmutable { return $this->dispatchedAt; }
    public function processingTimeMs(): ?int           { return $this->processingTimeMs; }
    public function attemptCount(): int                { return $this->attemptCount; }
    public function correlationId(): ?string           { return $this->correlationId; }
    public function channelId(): ?string               { return $this->channelId; }
    public function integrationId(): ?string           { return $this->integrationId; }
}
