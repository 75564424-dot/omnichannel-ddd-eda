<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Topology;

use App\Middleware\Application\DTOs\TopologyDTO;
use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Domain\ModuleRegistry;
use App\Middleware\Domain\ReadModels\TopologyView;
use App\Middleware\Domain\TopologyService;

final class TopologySnapshotAssembler
{
    public function __construct(
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly BusHealthService $busHealthService,
        private readonly ModuleRegistry $moduleRegistry,
        private readonly TopologyService $topologyService,
    ) {}

    public function assemble(): TopologyDTO
    {
        $producersConfig = $this->subscriptionRegistry->getProducers();
        $subscriptions   = $this->subscriptionRegistry->getAll();
        $busStatus       = $this->busHealthService->getStatus();

        $configProducers = $this->configProducersFromRegistry($producersConfig);
        $configConsumers = $this->configConsumersFromSubscriptions($subscriptions);

        $observed = $this->topologyService->buildObservedSnapshot($this->moduleRegistry);
        $diagram  = $this->topologyService->toDiagramNodes($observed);

        $producers = $this->mergeProducers($configProducers, $diagram['producers']);
        $consumers = $this->mergeConsumers($configConsumers, $diagram['consumers']);

        $bus = [
            'id'     => 'event_bus',
            'label'  => 'Event Bus',
            'status' => $busStatus->value(),
        ];

        $view = new TopologyView(
            producers:   $producers,
            bus:         $bus,
            consumers:   $consumers,
            generatedAt: now()->toDateTimeString(),
        );

        return TopologyDTO::fromView($view, $observed);
    }

    /**
     * @param array<string, array<string, mixed>> $producersConfig
     *
     * @return list<array<string, mixed>>
     */
    private function configProducersFromRegistry(array $producersConfig): array
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
    private function configConsumersFromSubscriptions(array $subscriptions): array
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

    /**
     * @param list<array<string, mixed>> $configRows
     * @param list<array<string, mixed>> $observedRows
     *
     * @return list<array<string, mixed>>
     */
    private function mergeProducers(array $configRows, array $observedRows): array
    {
        $byId = [];
        foreach ($configRows as $p) {
            $id = (string) ($p['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $byId[$id] = [
                'id'     => $id,
                'label'  => $p['label'] ?? $id,
                'events' => array_values(array_unique($p['events'] ?? [])),
            ];
        }

        foreach ($observedRows as $p) {
            $id = (string) ($p['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $events = array_values(array_unique($p['events'] ?? []));
            if (! isset($byId[$id])) {
                $byId[$id] = [
                    'id'     => $id,
                    'label'  => (string) ($p['label'] ?? $id),
                    'events' => $events,
                ];
                continue;
            }

            $byId[$id]['events'] = array_values(array_unique([...$byId[$id]['events'], ...$events]));
            if (($p['label'] ?? '') !== '') {
                $byId[$id]['label'] = (string) $p['label'];
            }
        }

        return array_values($byId);
    }

    /**
     * @param list<array<string, mixed>> $configRows
     * @param list<array<string, mixed>> $observedRows
     *
     * @return list<array<string, mixed>>
     */
    private function mergeConsumers(array $configRows, array $observedRows): array
    {
        $byId = [];
        foreach ($configRows as $c) {
            $id = (string) ($c['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $byId[$id] = [
                'id'            => $id,
                'label'         => $c['label'] ?? $id,
                'subscribed_to' => array_values(array_unique($c['subscribed_to'] ?? [])),
            ];
        }

        foreach ($observedRows as $c) {
            $id = (string) ($c['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $subs = array_values(array_unique($c['subscribed_to'] ?? []));
            if (! isset($byId[$id])) {
                $byId[$id] = [
                    'id'            => $id,
                    'label'         => (string) ($c['label'] ?? $id),
                    'subscribed_to' => $subs,
                ];
                continue;
            }

            $byId[$id]['subscribed_to'] = array_values(array_unique([...$byId[$id]['subscribed_to'], ...$subs]));
            if (($c['label'] ?? '') !== '') {
                $byId[$id]['label'] = (string) $c['label'];
            }
        }

        return array_values($byId);
    }
}
