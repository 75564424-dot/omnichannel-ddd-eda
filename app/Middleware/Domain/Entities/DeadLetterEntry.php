<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Entities;

use DateTimeImmutable;

/**
 * An event that exhausted all retries and was moved to the dead-letter queue.
 * Requires manual intervention: re-enqueue or discard.
 */
final class DeadLetterEntry
{
    private ?DateTimeImmutable $resolvedAt = null;

    private function __construct(
        private readonly int    $id,
        private readonly string $eventId,
        private readonly string $eventType,
        private readonly string $origin,
        private readonly array  $payload,
        private readonly string $failureReason,
        private readonly DateTimeImmutable $failedAt,
    ) {}

    public static function fromFailedJob(
        string $eventId,
        string $eventType,
        string $origin,
        array  $payload,
        string $failureReason,
    ): self {
        return new self(
            id:            0,
            eventId:       $eventId,
            eventType:     $eventType,
            origin:        $origin,
            payload:       $payload,
            failureReason: $failureReason,
            failedAt:      new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        int                $id,
        string             $eventId,
        string             $eventType,
        string             $origin,
        array              $payload,
        string             $failureReason,
        DateTimeImmutable  $failedAt,
        ?DateTimeImmutable $resolvedAt,
    ): self {
        $entry             = new self($id, $eventId, $eventType, $origin, $payload, $failureReason, $failedAt);
        $entry->resolvedAt = $resolvedAt;
        return $entry;
    }

    public function resolve(): void { $this->resolvedAt = new DateTimeImmutable(); }
    public function isResolved(): bool { return $this->resolvedAt !== null; }

    public function id(): int                          { return $this->id; }
    public function eventId(): string                  { return $this->eventId; }
    public function eventType(): string                { return $this->eventType; }
    public function origin(): string                   { return $this->origin; }
    public function payload(): array                   { return $this->payload; }
    public function failureReason(): string            { return $this->failureReason; }
    public function failedAt(): DateTimeImmutable      { return $this->failedAt; }
    public function resolvedAt(): ?DateTimeImmutable   { return $this->resolvedAt; }
}
