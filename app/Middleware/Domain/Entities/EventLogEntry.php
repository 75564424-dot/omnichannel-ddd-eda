<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Entities;

use DateTimeImmutable;

/**
 * Operational log projection row for event_logs (Plan_Middleware).
 */
final class EventLogEntry
{
    public function __construct(
        private readonly string $eventId,
        private readonly string $eventType,
        private readonly string $origin,
        private readonly string $status,
        private readonly DateTimeImmutable $occurredAt,
        private readonly DateTimeImmutable $loggedAt,
        private readonly ?string $correlationId = null,
        private readonly ?string $summary = null,
        private readonly ?string $payloadHash = null,
        private readonly ?string $channelId = null,
        private readonly ?string $integrationId = null,
    ) {}

    public static function received(StoredEvent $stored): self
    {
        return self::lifecycle(
            eventId: $stored->eventId(),
            eventType: $stored->eventType(),
            origin: $stored->origin(),
            status: 'received',
            summary: 'Event ingested into middleware pipeline',
            payload: $stored->payload(),
            correlationId: $stored->correlationId(),
            channelId: $stored->channelId(),
            integrationId: $stored->integrationId(),
            occurredAt: $stored->occurredAt(),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function lifecycle(
        string $eventId,
        string $eventType,
        string $origin,
        string $status,
        string $summary,
        array $payload,
        ?string $correlationId = null,
        ?string $channelId = null,
        ?string $integrationId = null,
        ?DateTimeImmutable $occurredAt = null,
    ): self {
        return new self(
            eventId: $eventId,
            eventType: $eventType,
            origin: $origin,
            status: $status,
            occurredAt: $occurredAt ?? new DateTimeImmutable,
            loggedAt: new DateTimeImmutable,
            correlationId: $correlationId,
            summary: $summary,
            payloadHash: hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR)),
            channelId: $channelId,
            integrationId: $integrationId,
        );
    }

    public function eventId(): string { return $this->eventId; }
    public function eventType(): string { return $this->eventType; }
    public function origin(): string { return $this->origin; }
    public function status(): string { return $this->status; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function loggedAt(): DateTimeImmutable { return $this->loggedAt; }
    public function correlationId(): ?string { return $this->correlationId; }
    public function summary(): ?string { return $this->summary; }
    public function payloadHash(): ?string { return $this->payloadHash; }
    public function channelId(): ?string { return $this->channelId; }
    public function integrationId(): ?string { return $this->integrationId; }
}
