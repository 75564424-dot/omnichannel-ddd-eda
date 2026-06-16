<?php

declare(strict_types=1);

namespace App\Integration\Infrastructure\Middleware;

use App\Integration\Domain\Contracts\ExternalEventPublisherInterface;
use App\Middleware\Application\DTOs\PublishResult;
use App\Middleware\Application\Services\EventPublisherService;

final class BusExternalEventPublisher implements ExternalEventPublisherInterface
{
    public function __construct(
        private readonly EventPublisherService $eventPublisher,
    ) {}

    public function publish(array $envelope): PublishResult
    {
        return $this->eventPublisher->publish($envelope);
    }
}
