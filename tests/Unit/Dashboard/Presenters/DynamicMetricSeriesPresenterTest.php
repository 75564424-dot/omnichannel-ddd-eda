<?php

declare(strict_types=1);

namespace Tests\Unit\Dashboard\Presenters;

use App\Dashboard\Application\Presenters\DynamicMetricSeriesPresenter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DynamicMetricSeriesPresenterTest extends TestCase
{
    #[Test]
    public function bar_series_preserves_metric_contract_fields(): void
    {
        $spec = [
            'id'           => 'demo_feed_sum_by_day',
            'name'         => 'Demo feed sum',
            'chart'        => 'bar',
            'value_label'  => 'Total',
            'y_label'      => 'Units',
            'value_format' => 'number',
        ];

        $payload = (new DynamicMetricSeriesPresenter())->barSeries($spec, 7, [
            ['label' => '2026-06-01', 'value' => 12.5],
        ]);

        $this->assertSame('demo_feed_sum_by_day', $payload['metric_id']);
        $this->assertSame('bar', $payload['chart']);
        $this->assertSame(7, $payload['days']);
        $this->assertSame('Total', $payload['value_label']);
        $this->assertCount(1, $payload['points']);
    }

    #[Test]
    public function dual_origin_consumer_series_builds_two_panels(): void
    {
        $spec = ['id' => 'traffic_dual', 'name' => 'Traffic'];

        $payload = (new DynamicMetricSeriesPresenter())->dualOriginConsumerSeries(
            $spec,
            14,
            ['POS' => 3],
            ['CRM' => 2],
        );

        $this->assertSame('dual_bar', $payload['chart']);
        $this->assertCount(2, $payload['panels']);
        $this->assertSame('by_origin', $payload['panels'][0]['panel_id']);
        $this->assertSame('POS', $payload['panels'][0]['points'][0]['label']);
        $this->assertSame(3, $payload['panels'][0]['points'][0]['value']);
    }

    #[Test]
    public function empty_bar_series_includes_meta_reason(): void
    {
        $payload = (new DynamicMetricSeriesPresenter())->emptyBarSeries(
            ['id' => 'empty_metric', 'name' => 'Empty'],
            7,
            ['reason' => 'no_event_types'],
        );

        $this->assertSame([], $payload['points']);
        $this->assertSame('no_event_types', $payload['meta']['reason']);
    }
}
