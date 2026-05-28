<?php

declare(strict_types=1);

namespace Tests\Unit\EventBus\Fixtures;

use App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface;

final class InvalidReturnRegistrar implements EventConsumerRegistrationInterface
{
    public static function subscriptionCatalog(): array
    {
        return ['Bad' => 'not-array-rows'];
    }
}
