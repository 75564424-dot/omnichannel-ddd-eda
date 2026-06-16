<?php

declare(strict_types=1);

namespace App\Integration\Application\Services;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final class WebhookEventEnvelopeBuilder
{
    /**
     * @param array<string, mixed> $integration
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    public function build(array $integration, array $payload, array $config): array
    {
        /** @var array<string, mixed> $webhookConfig */
        $webhookConfig = $config['webhook'] ?? [];

        $eventTypeField = (string) ($webhookConfig['event_type_field'] ?? 'event_type');
        $eventIdField   = (string) ($webhookConfig['event_id_field'] ?? 'event_id');
        $occurredField  = (string) ($webhookConfig['occurred_at_field'] ?? 'occurred_at');

        $eventType = (string) ($payload[$eventTypeField] ?? $payload['event'] ?? '');
        if ($eventType === '') {
            throw new InvalidArgumentException('Webhook payload missing event_type.');
        }

        $eventId = (string) ($payload[$eventIdField] ?? '');
        if ($eventId === '' || ! Uuid::isValid($eventId)) {
            $eventId = Uuid::uuid4()->toString();
            $payload[$eventIdField] = $eventId;
        }

        $occurredAt = (string) ($payload[$occurredField] ?? now()->toIso8601String());
        $payload['event_id']    = $eventId;
        $payload['event']       = $eventType;
        $payload['event_type']  = $eventType;
        $payload['occurred_at'] = $occurredAt;

        return [
            'event_id'       => $eventId,
            'event_type'     => $eventType,
            'occurred_at'    => $occurredAt,
            'origin'         => (string) ($webhookConfig['origin'] ?? 'Webhook:'.$integration['code']),
            'payload'        => $payload,
            'channel_id'     => $integration['channel_id'],
            'integration_id' => $integration['id'],
        ];
    }
}
