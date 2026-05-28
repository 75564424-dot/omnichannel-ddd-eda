<?php

declare(strict_types=1);

namespace App\Providers;

use App\Shared\EventBus\PackSubscriptionCatalogMerger;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Merges pack-defined subscriptions ({@see \App\Shared\Contracts\EventBus\EventConsumerRegistrationInterface})
 * into runtime config and registers Laravel listeners. Core file config/eventbus.php stays the base;
 * this provider is additive. Compatible with B.2 sync (reads merged config('eventbus.subscriptions')).
 */
final class EventBusIntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $registrars = config('eventbus.consumer_registrars', []);
        if (! is_array($registrars) || $registrars === []) {
            return;
        }

        /** @var list<class-string> $classes */
        $classes  = array_values(array_filter($registrars, static fn ($c) => is_string($c) && $c !== ''));
        $merger   = new PackSubscriptionCatalogMerger();
        $base     = config('eventbus.subscriptions', []);
        if (! is_array($base)) {
            $base = [];
        }

        [$merged, $listeners] = $merger->merge($classes, $base);
        config()->set('eventbus.subscriptions', $merged);

        foreach ($listeners as $item) {
            Event::listen($item['event_type'], $item['listener']);
        }
    }
}
