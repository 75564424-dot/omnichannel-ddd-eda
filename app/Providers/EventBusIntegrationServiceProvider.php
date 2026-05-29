<?php

declare(strict_types=1);

namespace App\Providers;

use App\Providers\Registrars\EventBusPackSubscriptionBootstrapper;
use Illuminate\Support\ServiceProvider;

/**
 * Merges pack-defined subscriptions ({@see \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface})
 * into runtime config and registers Laravel listeners. Core file config/eventbus.php stays the base;
 * this provider is additive. Compatible with B.2 sync (reads merged config('eventbus.subscriptions')).
 */
final class EventBusIntegrationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        (new EventBusPackSubscriptionBootstrapper())->bootstrap();
    }
}
