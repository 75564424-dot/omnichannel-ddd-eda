<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\EventProcessingService;
use App\Middleware\Application\Services\EventPublisherService;
use App\Middleware\Application\Services\SimulationPublishScope;
use App\Middleware\Domain\Repositories\QueueEntryRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SimulationPublishDeferTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function publish_leaves_pending_until_simulation_scope_ends(): void
    {
        $scope = app(SimulationPublishScope::class);
        $publisher = app(EventPublisherService::class);
        $repo = app(QueueEntryRepositoryInterface::class);

        $eventId = '11111111-1111-1111-1111-111111111111';

        $scope->beginDeferring();
        $publisher->publish([
            'event_id'    => $eventId,
            'event_type'  => 'Platform.Smoke.Probe',
            'occurred_at' => now()->toIso8601String(),
            'origin'      => 'SimulationTest',
            'payload'     => [
                'event_id'   => $eventId,
                'event'      => 'Platform.Smoke.Probe',
                'event_type' => 'Platform.Smoke.Probe',
            ],
        ]);
        $scope->endDeferring();

        $entry = $repo->findByEventId($eventId);
        $this->assertNotNull($entry);
        $this->assertTrue($entry->status()->isPending());

        app(EventProcessingService::class)->processQueuedEvent($eventId);

        $processed = $repo->findByEventId($eventId);
        $this->assertNotNull($processed);
        $this->assertTrue($processed->status()->isProcessed());
    }
}
