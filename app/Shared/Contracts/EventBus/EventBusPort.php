<?php

declare(strict_types=1);

namespace App\Shared\Contracts\EventBus;

/**
 * Port for publishing events to the runtime bus (Laravel in-process today, Kafka tomorrow).
 */
interface EventBusPort
{
    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $eventType, array $payload): void;
}
