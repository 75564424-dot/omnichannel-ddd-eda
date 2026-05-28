<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;

final class UpdateIntegrationUseCase
{
    public function __construct(private readonly IntegrationRepositoryInterface $integrations) {}

    public function execute(string $id, array $data): void
    {
        $this->integrations->update($id, $data);
    }
}
