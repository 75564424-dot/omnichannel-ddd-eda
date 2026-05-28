<?php

declare(strict_types=1);

namespace App\Middleware\Infrastructure\Persistence;

use App\Middleware\Domain\Repositories\ProcessingJobRepositoryInterface;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentProcessingJobRepository implements ProcessingJobRepositoryInterface
{
    public function __construct(
        private readonly InstanceTenantContextInterface $instanceTenant,
    ) {}

    public function createWorkflowJob(string $workflowId, string $eventId, array $payload): string
    {
        $id = (string) Str::uuid();

        DB::table('processing_jobs')->insert([
            'id'             => $id,
            'tenant_id'      => $this->instanceTenant->tenantId(),
            'job_type'       => 'workflow_trigger',
            'reference_type' => 'workflow',
            'reference_id'   => $workflowId,
            'payload'        => json_encode([
                'event_id' => $eventId,
                'payload'  => $payload,
            ], JSON_THROW_ON_ERROR),
            'status'         => 'pending',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return $id;
    }
}
