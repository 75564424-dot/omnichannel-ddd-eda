<?php

declare(strict_types=1);

namespace Tests\Unit\EventBus\Fixtures;

final class BadInterfaceRegistrar
{
    public static function subscriptionCatalog(): array
    {
        return [];
    }
}
