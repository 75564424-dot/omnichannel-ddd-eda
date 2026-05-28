<?php

declare(strict_types=1);

namespace App\Shared\Contracts\EventBus;

/**
 * Optional hook for integration packs: declare listeners and queues without editing core providers.
 * Host applications merge results into {@see config('eventbus.subscriptions')}.
 */
interface EventConsumerRegistrationInterface
{
    /**
     * @return array<string, list<array{module:string, listener:class-string, queue:string}>>
     */
    public static function subscriptionCatalog(): array;
}
