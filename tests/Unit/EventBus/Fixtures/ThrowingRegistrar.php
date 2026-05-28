<?php

declare(strict_types=1);

namespace Tests\Unit\EventBus\Fixtures;

use App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface;

final class ThrowingRegistrar implements EventConsumerRegistrationInterface
{
    public static function subscriptionCatalog(): array
    {
        throw new \RuntimeException('boom');
    }
}
