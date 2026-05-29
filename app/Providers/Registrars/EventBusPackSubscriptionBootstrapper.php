<?php

declare(strict_types=1);

namespace App\Providers\Registrars;

use App\Shared\EventBus\PackSubscriptionCatalogMerger;
use Illuminate\Support\Facades\Event;

final class EventBusPackSubscriptionBootstrapper
{
    public function __construct(
        private readonly PackSubscriptionCatalogMerger $merger = new PackSubscriptionCatalogMerger(),
    ) {}

    public function bootstrap(): void
    {
        $registrars = config('eventbus.consumer_registrars', []);
        if (! is_array($registrars) || $registrars === []) {
            return;
        }

        /** @var list<class-string> $classes */
        $classes = array_values(array_filter($registrars, static fn ($c) => is_string($c) && $c !== ''));
        $base    = config('eventbus.subscriptions', []);
        if (! is_array($base)) {
            $base = [];
        }

        [$merged, $listeners] = $this->merger->merge($classes, $base);
        config()->set('eventbus.subscriptions', $merged);

        foreach ($listeners as $item) {
            Event::listen($item['event_type'], $item['listener']);
        }
    }
}
