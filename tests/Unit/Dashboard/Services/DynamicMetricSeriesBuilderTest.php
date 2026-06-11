<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Services;

use App\Dashboard\Application\Presenters\DynamicMetricSeriesPresenter;
use App\Dashboard\Application\Services\DynamicMetricSeriesBuilder;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DynamicMetricSeriesBuilderTest extends TestCase
{
    #[Test]
    public function sum_by_day_delegates_to_event_feed_repository(): void
    {
        $feed = $this->createMock(EventFeedRepositoryInterface::class);
        $feed->expects($this->once())
            ->method('sumPayloadPathByCalendarDay')
            ->with('Platform.Demo.Measurement', ['amount'], 7)
            ->willReturn([
                ['date' => '2026-06-01', 'total' => 5.0],
            ]);

        $builder = new DynamicMetricSeriesBuilder(
            $feed,
            $this->createMock(BusQueueAnalyticsRepositoryInterface::class),
            new DynamicMetricSeriesPresenter(),
        );

        $payload = $builder->buildFromSpec([
            'id'           => 'demo_feed_sum_by_day',
            'name'         => 'Demo',
            'aggregation'  => 'sum_by_day',
            'event_types'  => ['Platform.Demo.Measurement'],
            'payload_path' => ['amount'],
            'chart'        => 'bar',
        ], 7);

        $this->assertSame('demo_feed_sum_by_day', $payload['metric_id']);
        $this->assertSame(5.0, $payload['points'][0]['value']);
    }

    #[Test]
    public function dual_origin_consumer_uses_bus_queue_analytics(): void
    {
        $bus = $this->createMock(BusQueueAnalyticsRepositoryInterface::class);
        $bus->expects($this->once())->method('countByOriginSince')->willReturn(['POS' => 2]);
        $bus->expects($this->once())->method('countByConsumerSince')->willReturn(['CRM' => 1]);

        $builder = new DynamicMetricSeriesBuilder(
            $this->createMock(EventFeedRepositoryInterface::class),
            $bus,
            new DynamicMetricSeriesPresenter(),
        );

        $payload = $builder->buildFromSpec([
            'id'          => 'traffic_dual',
            'name'        => 'Traffic',
            'aggregation' => 'count_origin_and_consumer',
        ], 7);

        $this->assertSame('dual_bar', $payload['chart']);
        $this->assertSame(2, $payload['panels'][0]['points'][0]['value']);
        $this->assertSame(1, $payload['panels'][1]['points'][0]['value']);
    }
}
