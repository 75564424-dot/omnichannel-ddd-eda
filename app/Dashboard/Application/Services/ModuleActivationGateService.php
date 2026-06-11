<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Control\Application\Services\ClientInstancePortalService;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;

/**
 * Enforces LIVE-panel activation before modules may publish, consume, or simulate.
 */
final class ModuleActivationGateService
{
    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
    ) {}

    public function isNodeActive(string $nodeKey): bool
    {
        return $this->nodeStatusRepository->middlewareEventsEnabled($nodeKey);
    }

    /**
     * @param array<string, mixed> $catalog
     *
     * @return list<string> Human-readable labels for inactive producers.
     */
    public function inactiveProducerLabels(array $catalog): array
    {
        $inactive = [];
        $producers = is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [];

        foreach ($producers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $nodeKey = ClientInstancePortalService::producerNodeKey($id);
            if (! $this->isNodeActive($nodeKey)) {
                $inactive[] = trim((string) ($row['name'] ?? $id));
            }
        }

        return $inactive;
    }

    /**
     * @param array<string, mixed> $catalog
     */
    public function simulationBlockReason(array $catalog): ?string
    {
        if (! $this->isNodeActive('middleware')) {
            return 'Active el bus de eventos (Middleware) en el panel Live antes de simular.';
        }

        $producers = is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [];
        if ($producers === []) {
            return null;
        }

        $inactive = $this->inactiveProducerLabels($catalog);
        if (count($inactive) === count($producers)) {
            $names = implode(', ', $inactive);

            return "Active los productores configurados en el panel Live antes de simular: {$names}.";
        }

        return null;
    }
}
