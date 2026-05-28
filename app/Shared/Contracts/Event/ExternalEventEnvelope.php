<?php

declare(strict_types=1);

namespace App\Shared\Contracts\Event;

/**
 * Shape expected for externally produced payloads on the string-based Laravel event bus.
 * @see \App\Shared\Contracts\EventBus\EventPublisherInterface
 */
interface ExternalEventEnvelope
{
    /**
     * Stable PascalCase name (matches dispatch first argument when applicable).
     */
    public function eventType(): string;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array;
}
