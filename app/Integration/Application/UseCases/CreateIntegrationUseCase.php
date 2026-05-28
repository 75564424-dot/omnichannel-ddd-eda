<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;

final class CreateIntegrationUseCase
{
    public function __construct(private readonly IntegrationRepositoryInterface $integrations) {}

    public function execute(array $data): string
    {
        return $this->integrations->create($data);
    }
}
