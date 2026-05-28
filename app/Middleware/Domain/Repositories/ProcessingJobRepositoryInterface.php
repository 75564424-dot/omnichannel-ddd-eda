<?php

declare(strict_types=1);

namespace App\Middleware\Domain\Repositories;

interface ProcessingJobRepositoryInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function createWorkflowJob(string $workflowId, string $eventId, array $payload): string;
}
