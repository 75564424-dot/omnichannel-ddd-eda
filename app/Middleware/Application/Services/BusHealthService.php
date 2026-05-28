<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\ReadModels\BusMetricsSnapshot;
use App\Middleware\Domain\Repositories\BusMetricsRepositoryInterface;
use App\Middleware\Domain\ValueObjects\BusStatus;

/**
 * Evaluates the operational health of the Event Bus.
 * Reads from the latest stored snapshot or computes on demand.
 */
final class BusHealthService
{
    public function __construct(
        private readonly BusMetricsRepositoryInterface $metricsRepository,
        private readonly BusMetricsService             $metricsService,
    ) {}

    public function getStatus(): BusStatus
    {
        return $this->getLatestSnapshot()->busStatus;
    }

    public function getLatestSnapshot(): BusMetricsSnapshot
    {
        return $this->metricsService->computeAndSnapshot();
    }
}
