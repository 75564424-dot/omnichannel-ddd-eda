<?php

declare(strict_types=1);

namespace App\Integration\Domain\Repositories;

interface IntegrationCredentialRepositoryInterface
{
    public function store(string $integrationId, string $credentialType, string $plainValue, ?\DateTimeInterface $expiresAt = null): string;

    public function getPlaintext(string $integrationId, string $credentialType): ?string;

    public function delete(string $integrationId, string $credentialType): void;
}
