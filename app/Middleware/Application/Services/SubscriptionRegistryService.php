<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\ValueObjects\ConsumerList;

/**
 * Reads from config/eventbus.php to answer:
 * "Which modules are subscribed to this event type?"
 *
 * The bus uses this to build QueueEntry.consumers and the TopologyView.
 * No business logic — pure routing registry.
 */
final class SubscriptionRegistryService
{
    public function getConsumersFor(string $eventType): ConsumerList
    {
        $subscribed = config('eventbus.subscriptions', [])[$eventType] ?? [];
        $modules    = array_values(array_unique(array_column($subscribed, 'module')));
        return new ConsumerList($modules);
    }

    public function isKnownEventType(string $eventType): bool
    {
        return array_key_exists($eventType, config('eventbus.subscriptions', []));
    }

    /**
     * Returns the full registry as an array of event_type => [consumer module names].
     */
    public function getAll(): array
    {
        $result = [];
        foreach (config('eventbus.subscriptions', []) as $eventType => $subscribers) {
            $result[$eventType] = array_values(array_unique(array_column($subscribers, 'module')));
        }
        return $result;
    }

    /**
     * Returns which event types each module produces (from config).
     */
    public function getProducers(): array
    {
        return config('eventbus.producers', []);
    }
}
