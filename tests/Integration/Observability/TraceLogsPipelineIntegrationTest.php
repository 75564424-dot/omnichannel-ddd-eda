<?php

declare(strict_types=1);

namespace Tests\Integration\Observability;

use App\Middleware\Application\Services\EventPublisherService;
use App\Shared\Logging\StructuredLogContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class TraceLogsPipelineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function publish_track_and_project_write_trace_spans_and_feed_correlation(): void
    {
        $correlation = Uuid::uuid4()->toString();
        $eventId     = Uuid::uuid4()->toString();
        $occurred    = now()->toIso8601String();

        StructuredLogContext::setCorrelationId($correlation);

        /** @var EventPublisherService $publisher */
        $publisher = app(EventPublisherService::class);

        $publisher->publish([
            'event_id'       => $eventId,
            'event_type'     => 'Platform.Bus.IntegrationTest',
            'payload'        => [
                'event_id'       => $eventId,
                'event'          => 'Platform.Bus.IntegrationTest',
                'occurred_at'    => $occurred,
                'correlation_id' => $correlation,
            ],
            'occurred_at'    => $occurred,
            'origin'         => 'Test',
            'correlation_id' => $correlation,
        ]);

        $this->assertDatabaseHas('message_queue', [
            'event_uuid'     => $eventId,
            'correlation_id' => $correlation,
        ]);

        $spans = DB::table('trace_logs')
            ->where('correlation_id', $correlation)
            ->pluck('operation_name')
            ->all();

        $this->assertContains('bus.publish', $spans);
        $this->assertContains('bus.track', $spans);
        $this->assertContains('feed.project', $spans);

        $this->assertDatabaseHas('event_feed_projections', [
            'event_uuid'     => $eventId,
            'correlation_id' => $correlation,
        ]);
    }
}
