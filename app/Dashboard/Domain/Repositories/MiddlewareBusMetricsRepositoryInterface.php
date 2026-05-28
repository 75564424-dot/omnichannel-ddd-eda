<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\Repositories;

use App\Dashboard\Domain\ReadModels\MiddlewareBusMetrics;

interface MiddlewareBusMetricsRepositoryInterface
{
    public function saveSnapshot(int $latencyMs, int $eps, int $queueSize, string $streamStatus): void;

    public function getLatest(): ?MiddlewareBusMetrics;
}
