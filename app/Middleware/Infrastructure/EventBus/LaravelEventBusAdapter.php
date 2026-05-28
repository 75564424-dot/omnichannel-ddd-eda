<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\EventBus;

use App\Shared\Contracts\EventBus\EventBusPort;
use Illuminate\Support\Facades\Event;

final class LaravelEventBusAdapter implements EventBusPort
{
    public function publish(string $eventType, array $payload): void
    {
        Event::dispatch($eventType, [$payload]);
    }
}
