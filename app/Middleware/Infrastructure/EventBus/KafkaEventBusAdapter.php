<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\EventBus;

use App\Shared\Contracts\EventBus\EventBusPort;
use Illuminate\Support\Facades\Log;

/**
 * Kafka driver stub — logs intent until broker wiring (Plan_Middleware Fase 3).
 */
final class KafkaEventBusAdapter implements EventBusPort
{
    public function publish(string $eventType, array $payload): void
    {
        Log::info('[EventBus][Kafka] publish stub', [
            'event_type' => $eventType,
            'event_id'   => $payload['event_id'] ?? null,
            'topic'      => config('eventbus.kafka.topic', 'platform.events'),
        ]);
    }
}
