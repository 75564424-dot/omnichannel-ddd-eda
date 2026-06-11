<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Topology;

final class TopologyRegistryConfigMapper
{
    /**
     * @param array<string, array<string, mixed>> $producersConfig
     *
     * @return list<array<string, mixed>>
     */
    public function mapProducers(array $producersConfig): array
    {
        $rows = [];
        foreach ($producersConfig as $id => $info) {
            $rows[] = [
                'id'     => $id,
                'label'  => $info['label'],
                'events' => $info['produces'] ?? [],
            ];
        }

        return $rows;
    }

    /**
     * @param array<string, list<string>> $subscriptions
     *
     * @return list<array<string, mixed>>
     */
    public function mapConsumers(array $subscriptions): array
    {
        $consumerMap = [];
        foreach ($subscriptions as $eventType => $modules) {
            foreach ($modules as $module) {
                $consumerMap[$module][] = $eventType;
            }
        }

        $rows = [];
        foreach ($consumerMap as $module => $eventTypes) {
            $rows[] = [
                'id'            => strtolower($module),
                'label'         => $module,
                'subscribed_to' => array_values(array_unique($eventTypes)),
            ];
        }

        return $rows;
    }
}
