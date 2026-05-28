<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

interface RetryAttemptRepositoryInterface
{
    public function recordAttempt(
        int $messageQueueId,
        string $eventUuid,
        int $attemptNumber,
        string $status,
        ?string $errorMessage = null,
    ): void;
}
