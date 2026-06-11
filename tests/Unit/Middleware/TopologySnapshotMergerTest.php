<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Middleware\Application\Services\Topology\TopologySnapshotMerger;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TopologySnapshotMergerTest extends TestCase
{
    #[Test]
    public function merge_producers_unions_events_and_prefers_observed_label(): void
    {
        $merged = (new TopologySnapshotMerger())->mergeProducers(
            [
                ['id' => 'pos', 'label' => 'Config POS', 'events' => ['A']],
            ],
            [
                ['id' => 'pos', 'label' => 'Observed POS', 'events' => ['B', 'A']],
                ['id' => 'api', 'label' => 'Partner API', 'events' => ['C']],
            ],
        );

        $this->assertEquals([
            [
                'id'     => 'pos',
                'label'  => 'Observed POS',
                'events' => ['A', 'B'],
            ],
            [
                'id'     => 'api',
                'label'  => 'Partner API',
                'events' => ['C'],
            ],
        ], $merged);
    }

    #[Test]
    public function merge_consumers_unions_subscriptions_and_skips_empty_ids(): void
    {
        $merged = (new TopologySnapshotMerger())->mergeConsumers(
            [
                ['id' => 'sink', 'label' => 'Sink', 'subscribed_to' => ['E1']],
                ['id' => '', 'label' => 'Ignored', 'subscribed_to' => ['E9']],
            ],
            [
                ['id' => 'sink', 'label' => 'Observed Sink', 'subscribed_to' => ['E2', 'E1']],
            ],
        );

        $this->assertSame([
            [
                'id'            => 'sink',
                'label'         => 'Observed Sink',
                'subscribed_to' => ['E1', 'E2'],
            ],
        ], $merged);
    }
}
