<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\DTOs\BusMetricsDTO;
use App\Middleware\Application\Services\BusMetricsService;

final class GetBusMetricsUseCase
{
    public function __construct(
        private readonly BusMetricsService $metricsService,
    ) {}

    public function execute(): BusMetricsDTO
    {
        return BusMetricsDTO::fromSnapshot($this->metricsService->computeAndSnapshot());
    }
}
