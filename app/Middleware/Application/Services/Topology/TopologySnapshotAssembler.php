<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Topology;

use App\Middleware\Application\DTOs\TopologyDTO;
use App\Middleware\Application\Services\BusHealthService;
use App\Middleware\Application\Services\SubscriptionRegistryService;
use App\Middleware\Domain\ModuleRegistry;
use App\Middleware\Domain\ReadModels\TopologyView;
use App\Middleware\Domain\TopologyService;
use App\Shared\Platform\Services\TenantCatalogEventBusMapper;

final class TopologySnapshotAssembler
{
    public function __construct(
        private readonly SubscriptionRegistryService $subscriptionRegistry,
        private readonly BusHealthService $busHealthService,
        private readonly ModuleRegistry $moduleRegistry,
        private readonly TopologyService $topologyService,
        private readonly TopologyRegistryConfigMapper $configMapper,
        private readonly TopologySnapshotMerger $merger,
        private readonly TenantCatalogEventBusMapper $catalogMapper,
    ) {}

    public function assemble(): TopologyDTO
    {
        $producersConfig = $this->subscriptionRegistry->getProducers();
        $subscriptions   = $this->subscriptionRegistry->getAll();
        $busStatus       = $this->busHealthService->getStatus();

        [$declarativeProducers, $declarativeSubscriptions] = $this->declarativeCatalogEventBus();
        $producersConfig = array_replace($producersConfig, $declarativeProducers);
        foreach ($declarativeSubscriptions as $eventType => $modules) {
            $existing = $subscriptions[$eventType] ?? [];
            $subscriptions[$eventType] = array_values(array_unique([...$existing, ...$modules]));
        }

        $configProducers = $this->configMapper->mapProducers($producersConfig);
        $configConsumers = $this->configMapper->mapConsumers($subscriptions);

        $observed = $this->topologyService->buildObservedSnapshot($this->moduleRegistry);
        $diagram  = $this->topologyService->toDiagramNodes($observed);

        $producers = $this->merger->mergeProducers($configProducers, $diagram['producers']);
        $consumers = $this->merger->mergeConsumers($configConsumers, $diagram['consumers']);

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
     * @return array{
     *     0: array<string, array{label: string, produces: list<string>}>,
     *     1: array<string, list<string>>
     * }
     */
    private function declarativeCatalogEventBus(): array
    {
        $catalog = config('modules.catalog', []);
        if (! is_array($catalog)) {
            return [[], []];
        }

        $producers = isset($catalog['producers']) && is_array($catalog['producers'])
            ? array_values($catalog['producers'])
            : [];
        $subscribers = isset($catalog['subscribers']) && is_array($catalog['subscribers'])
            ? array_values($catalog['subscribers'])
            : [];

        if ($producers === [] && $subscribers === []) {
            return [[], []];
        }

        $mapped = $this->catalogMapper->map([
            'producers'   => $producers,
            'subscribers' => $subscribers,
        ]);

        $subscriptions = [];
        foreach ($mapped['subscriptions'] as $eventType => $rows) {
            $modules = [];
            foreach ($rows as $row) {
                if (is_array($row) && isset($row['module'])) {
                    $modules[] = (string) $row['module'];
                }
            }
            if ($modules !== []) {
                $subscriptions[$eventType] = array_values(array_unique($modules));
            }
        }

        return [$mapped['producers'], $subscriptions];
    }
}
