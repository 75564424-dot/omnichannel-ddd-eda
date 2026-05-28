<?php

declare(strict_types=1);

namespace Tests\Integration\Logging;

use App\Middleware\Application\Services\EventLogService;
use App\Middleware\Domain\Entities\StoredEvent;
use App\Shared\Logging\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EventAndAuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function event_log_service_writes_received_and_processed_rows(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();

        /** @var EventLogService $service */
        $service = app(EventLogService::class);

        $stored = StoredEvent::fromPublishEnvelope([
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Logs.Test',
            'occurred_at' => $occurred,
            'origin'      => 'Test',
            'payload'     => ['event_id' => $eventId, 'event' => 'Platform.Logs.Test', 'occurred_at' => $occurred],
        ]);

        $service->recordReceived($stored);
        $service->recordProcessed($eventId, 'Platform.Logs.Test', 'Test', $stored->payload());

        $this->assertDatabaseHas('event_logs', ['event_uuid' => $eventId, 'status' => 'received']);
        $this->assertDatabaseHas('event_logs', ['event_uuid' => $eventId, 'status' => 'processed']);
    }

    #[Test]
    public function audit_log_service_writes_audit_row(): void
    {
        app(AuditLogService::class)->record(
            action: 'test.action',
            entityType: 'test_entity',
            entityId: '1',
            changes: ['field' => 'value'],
            actorType: 'test',
            actorId: 'actor-1',
        );

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'test.action',
            'entity_type' => 'test_entity',
            'entity_id'   => '1',
        ]);
    }
}
