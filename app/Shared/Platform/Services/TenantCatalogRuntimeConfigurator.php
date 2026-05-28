<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

/**
 * Applies a tenant modules_catalog to runtime modules + eventbus routing for simulation.
 */
final class TenantCatalogRuntimeConfigurator
{
    /**
     * @param array<string, mixed> $catalog
     */
    public function apply(array $catalog): void
    {
        $normalized = [
            'middleware'  => is_array($catalog['middleware'] ?? null) ? $catalog['middleware'] : [],
            'producers'   => is_array($catalog['producers'] ?? null) ? array_values($catalog['producers']) : [],
            'subscribers' => is_array($catalog['subscribers'] ?? null) ? array_values($catalog['subscribers']) : [],
        ];

        config()->set('modules.catalog', $normalized);

        if (isset($catalog['service_contact_message']) && is_string($catalog['service_contact_message'])) {
            config()->set('modules.service_contact_message', $catalog['service_contact_message']);
        }

        $producers = [];
        $subscriptions = [];

        foreach ($normalized['producers'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? $id));
            $types = $row['event_types_emitted'] ?? [];
            if ($id === '' || ! is_array($types)) {
                continue;
            }
            $eventTypes = [];
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType !== '') {
                    $eventTypes[] = $eventType;
                }
            }
            if ($eventTypes === []) {
                continue;
            }
            $producers[$id] = [
                'label'    => $name,
                'produces' => $eventTypes,
            ];
        }

        foreach ($normalized['subscribers'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $moduleId = trim((string) ($row['id'] ?? ''));
            $types = $row['event_types_consumed'] ?? [];
            if ($moduleId === '' || ! is_array($types)) {
                continue;
            }
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }
                $subscriptions[$eventType] ??= [];
                $subscriptions[$eventType][] = ['module' => $moduleId];
            }
        }

        /** @var array<string, mixed> $existingProducers */
        $existingProducers = config('eventbus.producers', []);
        /** @var array<string, mixed> $existingSubscriptions */
        $existingSubscriptions = config('eventbus.subscriptions', []);

        config()->set('eventbus.producers', array_replace($existingProducers, $producers));
        config()->set('eventbus.subscriptions', array_replace($existingSubscriptions, $subscriptions));
    }
}
