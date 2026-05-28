<?php

declare(strict_types=1);

namespace App\Shared\Contracts\EventBus;

use App\Middleware\Application\DTOs\PublishResult;

/**
 * Minimal contract for publishing a validated envelope to the in-process event bus.
 * Implementations must not apply domain rules; structural validation only.
 */
interface EventPublisherInterface
{
    /**
     * @param array{
     *     event_id:string,
     *     event_type:string,
     *     payload:array,
     *     occurred_at:string,
     *     origin?:string
     * } $envelope
     */
    public function publish(array $envelope): PublishResult;
}
