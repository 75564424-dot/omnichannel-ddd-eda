<?php

declare(strict_types=1);

namespace App\Integration\Domain\Repositories;

interface WebhookRequestRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function recordReceived(array $data): string;

    public function markStatus(string $id, string $status): void;

    /**
     * @param array<string, mixed> $data
     */
    public function recordResponse(array $data): string;
}
