<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

/**
 * Applies a tenant modules_catalog to runtime modules + eventbus routing for simulation.
 */
final class TenantCatalogRuntimeConfigurator
{
    public function __construct(
        private readonly TenantCatalogNormalizer $normalizer,
        private readonly TenantCatalogEventBusMapper $eventBusMapper,
    ) {}

    /**
     * @param array<string, mixed> $catalog
     */
    public function apply(array $catalog): void
    {
        $normalized = $this->normalizer->normalize($catalog);

        config()->set('modules.catalog', [
            'middleware'  => $normalized['middleware'],
            'producers'   => $normalized['producers'],
            'subscribers' => $normalized['subscribers'],
        ]);

        if (isset($normalized['service_contact_message'])) {
            config()->set('modules.service_contact_message', $normalized['service_contact_message']);
        }

        $mapped = $this->eventBusMapper->map($normalized);

        /** @var array<string, mixed> $existingProducers */
        $existingProducers = config('eventbus.producers', []);
        /** @var array<string, mixed> $existingSubscriptions */
        $existingSubscriptions = config('eventbus.subscriptions', []);

        config()->set('eventbus.producers', array_replace($existingProducers, $mapped['producers']));
        config()->set('eventbus.subscriptions', array_replace($existingSubscriptions, $mapped['subscriptions']));
    }
}
