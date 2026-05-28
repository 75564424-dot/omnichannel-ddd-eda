<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use App\Middleware\Application\Services\EventPublisherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class WorkflowEngineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function active_workflow_creates_processing_job_on_publish(): void
    {
        config()->set('eventbus.workflows.enabled', true);

        $workflowId = (string) Str::uuid();
        DB::table('workflows')->insert([
            'id'                  => $workflowId,
            'code'                => 'order_fulfillment',
            'name'                => 'Order Fulfillment',
            'trigger_event_type'  => 'Platform.Workflow.Test',
            'status'              => 'active',
            'version'             => 1,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);
        $publisher->publish([
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Workflow.Test',
            'occurred_at' => $occurred,
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => 'Platform.Workflow.Test',
                'occurred_at' => $occurred,
            ],
        ]);

        $this->assertDatabaseHas('processing_jobs', [
            'job_type'       => 'workflow_trigger',
            'reference_type' => 'workflow',
            'reference_id'   => $workflowId,
            'status'         => 'pending',
        ]);
    }
}
