<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Registry;

use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Domain\ModuleRegistry;

/**
 * Upserts registry rows from eventbus config and modules catalog.
 */
final class ConfiguredModuleRegistrySyncService
{
    public function __construct(
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly ModuleRegistry $moduleRegistry,
    ) {}

    /**
     * @return array{producer_bindings: int, consumer_bindings: int}
     */
    public function sync(): array
    {
        $producerSeen = [];
        $consumerSeen = [];
        $producerBindings = 0;
        $consumerBindings = 0;

        foreach ($this->subscriptionRegistry->getProducers() as $id => $info) {
            $idStr = (string) $id;
            $label = (string) ($info['label'] ?? $idStr);
            foreach ($info['produces'] ?? [] as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '' || ! $this->markProducerBinding($producerSeen, $idStr, $eventType)) {
                    continue;
                }
                $this->moduleRegistry->recordProducerObservation($idStr, $label, $eventType);
                ++$producerBindings;
            }
        }

        foreach ($this->subscriptionRegistry->getAll() as $eventType => $modules) {
            $eventType = trim((string) $eventType);
            if ($eventType === '') {
                continue;
            }
            foreach ($modules as $moduleName) {
                $moduleName = trim((string) $moduleName);
                if ($moduleName === '' || ! $this->markConsumerBinding($consumerSeen, $moduleName, $eventType)) {
                    continue;
                }
                $this->moduleRegistry->recordConsumerObservation(strtolower($moduleName), $moduleName, $eventType);
                ++$consumerBindings;
            }
        }

        if ($producerBindings === 0 && $consumerBindings === 0) {
            [$p, $c] = $this->syncDeclarativeCatalogFromConfig($producerSeen, $consumerSeen);
            $producerBindings += $p;
            $consumerBindings += $c;
        }

        return [
            'producer_bindings' => $producerBindings,
            'consumer_bindings' => $consumerBindings,
        ];
    }

    /**
     * @param array<string, true> $producerSeen
     * @param array<string, true> $consumerSeen
     *
     * @return array{0: int, 1: int}
     */
    private function syncDeclarativeCatalogFromConfig(array &$producerSeen, array &$consumerSeen): array
    {
        $producerBindings = 0;
        $consumerBindings = 0;

        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            return [0, 0];
        }

        $producers = isset($catalog['producers']) && is_array($catalog['producers'])
            ? array_values($catalog['producers'])
            : [];

        foreach ($producers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $types = $row['event_types_emitted'] ?? [];
            if (! is_array($types)) {
                continue;
            }
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '' || ! $this->markProducerBinding($producerSeen, $id, $eventType)) {
                    continue;
                }
                $this->moduleRegistry->recordProducerObservation($id, $name, $eventType);
                ++$producerBindings;
            }
        }

        $subscribers = isset($catalog['subscribers']) && is_array($catalog['subscribers'])
            ? array_values($catalog['subscribers'])
            : [];

        foreach ($subscribers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($id === '' || $name === '') {
                continue;
            }
            $logicalId = strtolower($id);
            $types = $row['event_types_consumed'] ?? [];
            if (! is_array($types)) {
                continue;
            }
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '' || ! $this->markConsumerBinding($consumerSeen, $id, $eventType)) {
                    continue;
                }
                $this->moduleRegistry->recordConsumerObservation($logicalId, $name, $eventType);
                ++$consumerBindings;
            }
        }

        return [$producerBindings, $consumerBindings];
    }

    /** @param array<string, true> $seen */
    private function markProducerBinding(array &$seen, string $logicalId, string $eventType): bool
    {
        $key = "p\0{$logicalId}\0{$eventType}";
        if (isset($seen[$key])) {
            return false;
        }
        $seen[$key] = true;

        return true;
    }

    /** @param array<string, true> $seen */
    private function markConsumerBinding(array &$seen, string $moduleOrId, string $eventType): bool
    {
        $logicalId = strtolower($moduleOrId);
        $key = "c\0{$logicalId}\0{$eventType}";
        if (isset($seen[$key])) {
            return false;
        }
        $seen[$key] = true;

        return true;
    }
}
