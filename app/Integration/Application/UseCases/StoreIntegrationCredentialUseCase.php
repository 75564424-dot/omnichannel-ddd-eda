<?php

declare(strict_types=1);

namespace App\Integration\Application\UseCases;

use App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface;

final class StoreIntegrationCredentialUseCase
{
    public function __construct(
        private readonly IntegrationCredentialRepositoryInterface $credentials,
    ) {}

    public function execute(string $integrationId, string $credentialType, string $plainValue): string
    {
        return $this->credentials->store($integrationId, $credentialType, $plainValue);
    }
}
