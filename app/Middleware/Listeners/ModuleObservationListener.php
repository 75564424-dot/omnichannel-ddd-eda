<?php

declare(strict_types=1);

namespace App\Middleware\Listeners;

use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Domain\ModuleRegistry;
use App\Middleware\Domain\ValueObjects\EventOrigin;
use App\Shared\EventBus\PlatformWildcardPayload;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Side-effect only: upserts producer/consumer rows when events traverse the bus (synchronous for wildcard safety).
 *
 * @see BusTrackingListener
 */
final class ModuleObservationListener
{
    public function __construct(
        private readonly ModuleRegistry $moduleRegistry,
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @param  string|array<string, mixed>  $first
     * @param  array<int, mixed>|null  $second
     */
    public function handle(mixed $first, mixed $second = null): void
    {
        if (is_string($first) && ! PlatformWildcardPayload::shouldObserveWildcardEvent($first)) {
            return;
        }

        [, $payload] = PlatformWildcardPayload::parse($first, $second);
        $eventId   = $payload['event_id'] ?? null;
        $eventType = (string) ($payload['event'] ?? $payload['event_type'] ?? '');

        if (empty($eventId) || $eventType === '') {
            return;
        }
        try {
            $channel = strtoupper(trim((string) ($payload['channel'] ?? '')));
            $catalog = config('modules.catalog', []);
            $producers = $catalog['producers'] ?? [];
            $foundProducer = null;
            foreach ($producers as $prod) {
                $channels = $prod['channels'] ?? [];
                if (is_array($channels) && in_array($channel, $channels)) {
                    $foundProducer = $prod;
                    break;
                }
            }

            if ($foundProducer !== null) {
                $producerId = $foundProducer['id'];
                $producerName = $foundProducer['name'] ?? $foundProducer['id'];
            } else {
                $origin       = EventOrigin::inferFromPayload($payload);
                $producerId   = Str::slug($origin->value());
                if ($producerId === '') {
                    $producerId = 'unknown';
                }
                $producerName = $origin->value();
            }

            $this->moduleRegistry->recordProducerObservation($producerId, $producerName, $eventType);

            $consumers = $this->subscriptionRegistry->getConsumersFor($eventType)->toArray();
            foreach ($consumers as $consumerName) {
                $consumerName = trim($consumerName);
                if ($consumerName === '') {
                    continue;
                }
                $cid = strtolower($consumerName);
                $this->moduleRegistry->recordConsumerObservation($cid, $consumerName, $eventType);
            }
        } catch (Throwable $e) {
            $this->logger->warning('[EventBus][ModuleRegistry] Observation failed', [
                'event_id'   => $eventId,
                'event_type' => $eventType,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
