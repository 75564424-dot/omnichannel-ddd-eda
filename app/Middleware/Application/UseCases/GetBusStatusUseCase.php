<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\Services\BusHealthService;

final class GetBusStatusUseCase
{
    public function __construct(
        private readonly BusHealthService $busHealthService,
    ) {}

    public function execute(): string
    {
        return $this->busHealthService->getStatus()->value();
    }
}
