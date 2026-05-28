<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\IntegrationRepositoryInterface;
use RuntimeException;

final class GetIntegrationUseCase
{
    public function __construct(private readonly IntegrationRepositoryInterface $integrations) {}

    public function execute(string $id): array
    {
        $integration = $this->integrations->findById($id);
        if ($integration === null) {
            throw new RuntimeException('Integration not found.', 404);
        }

        return $integration;
    }
}
