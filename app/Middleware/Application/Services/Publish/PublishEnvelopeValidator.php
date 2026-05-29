<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Publish;

use InvalidArgumentException;

final class PublishEnvelopeValidator
{
    /**
     * @param array<string, mixed> $envelope
     */
    public function validateStructure(array $envelope): void
    {
        foreach (['event_id', 'event_type', 'payload', 'occurred_at'] as $key) {
            if (empty($envelope[$key])) {
                throw new InvalidArgumentException("EventBus validation failed: missing or empty '{$key}'.");
            }
        }

        if (! is_array($envelope['payload'])) {
            throw new InvalidArgumentException("EventBus validation failed: 'payload' must be an array.");
        }
    }
}
