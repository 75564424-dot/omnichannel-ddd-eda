<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services;

use App\Middleware\Domain\Repositories\ProcessingJobRepositoryInterface;
use App\Middleware\Domain\Repositories\WorkflowRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * Minimal workflow trigger engine — creates processing_jobs for active workflows (Plan_Middleware Fase 3).
 */
final class WorkflowEngine
{
    public function __construct(
        private readonly WorkflowRepositoryInterface $workflows,
        private readonly ProcessingJobRepositoryInterface $processingJobs,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function triggerForEvent(string $eventId, string $eventType, array $payload): void
    {
        if (! filter_var(config('eventbus.workflows.enabled', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        foreach ($this->workflows->findActiveByTriggerEventType($eventType) as $workflow) {
            $jobId = $this->processingJobs->createWorkflowJob($workflow['id'], $eventId, $payload);

            Log::info('[WorkflowEngine] workflow triggered', [
                'workflow_code' => $workflow['code'],
                'workflow_id'   => $workflow['id'],
                'event_id'      => $eventId,
                'job_id'        => $jobId,
            ]);
        }
    }
}
