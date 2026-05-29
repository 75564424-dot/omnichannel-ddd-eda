<?php

declare(strict_types=1);

namespace App\Integration\Domain\Contracts;

use App\Middleware\Application\DTOs\PublishResult;

/**
 * Port: publish normalized external events into the middleware event bus.
 */
interface ExternalEventPublisherInterface
{
    /**
     * @param array<string, mixed> $envelope
     */
    public function publish(array $envelope): PublishResult;
}
