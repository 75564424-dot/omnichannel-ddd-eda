<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Entities;

use DateTimeImmutable;

/**
 * Append-only canonical event record for event_store (Plan_Middleware).
 */
final class StoredEvent
{
    private function __construct(
        private readonly string $eventId,
        private readonly string $eventType,
        private readonly string $origin,
        private readonly array $payload,
        private readonly DateTimeImmutable $occurredAt,
        private readonly DateTimeImmutable $recordedAt,
        private readonly int $eventVersion,
        private readonly ?string $schemaVersion,
        private readonly ?string $correlationId,
        private readonly ?string $causationId,
        private readonly ?string $aggregateType,
        private readonly ?string $aggregateId,
        private readonly ?array $metadata,
        private readonly ?string $channelId,
        private readonly ?string $integrationId,
    ) {}

    /**
     * @param array<string, mixed> $envelope
     */
    public static function fromPublishEnvelope(
        array $envelope,
        ?string $correlationId = null,
        ?string $causationId = null,
    ): self {
        $occurred = new DateTimeImmutable((string) $envelope['occurred_at']);

        return new self(
            eventId: $envelope['event_id'],
            eventType: $envelope['event_type'],
            origin: $envelope['origin'] ?? 'External',
            payload: $envelope['payload'],
            occurredAt: $occurred,
            recordedAt: new DateTimeImmutable,
            eventVersion: (int) ($envelope['event_version'] ?? 1),
            schemaVersion: isset($envelope['schema_version']) ? (string) $envelope['schema_version'] : null,
            correlationId: $correlationId,
            causationId: $causationId,
            aggregateType: isset($envelope['aggregate_type']) ? (string) $envelope['aggregate_type'] : null,
            aggregateId: isset($envelope['aggregate_id']) ? (string) $envelope['aggregate_id'] : null,
            metadata: is_array($envelope['metadata'] ?? null) ? $envelope['metadata'] : null,
            channelId: isset($envelope['channel_id']) ? (string) $envelope['channel_id'] : null,
            integrationId: isset($envelope['integration_id']) ? (string) $envelope['integration_id'] : null,
        );
    }

    public function eventId(): string { return $this->eventId; }
    public function eventType(): string { return $this->eventType; }
    public function origin(): string { return $this->origin; }
    /** @return array<string, mixed> */
    public function payload(): array { return $this->payload; }
    public function occurredAt(): DateTimeImmutable { return $this->occurredAt; }
    public function recordedAt(): DateTimeImmutable { return $this->recordedAt; }
    public function eventVersion(): int { return $this->eventVersion; }
    public function schemaVersion(): ?string { return $this->schemaVersion; }
    public function correlationId(): ?string { return $this->correlationId; }
    public function causationId(): ?string { return $this->causationId; }
    public function aggregateType(): ?string { return $this->aggregateType; }
    public function aggregateId(): ?string { return $this->aggregateId; }
    /** @return array<string, mixed>|null */
    public function metadata(): ?array { return $this->metadata; }
    public function channelId(): ?string { return $this->channelId; }
    public function integrationId(): ?string { return $this->integrationId; }
}
