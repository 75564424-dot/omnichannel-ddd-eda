<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Dashboard\Application\Presenters\DynamicMetricSeriesPresenter;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

/**
 * Resolves chart data from dashboard_config.json metric definitions via repository ports.
 */
final class DynamicMetricSeriesBuilder
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly BusQueueAnalyticsRepositoryInterface $busQueueAnalyticsRepository,
        private readonly DynamicMetricSeriesPresenter $presenter,
    ) {}

    /**
     * @param array<string, mixed> $spec
     *
     * @return array<string, mixed>
     */
    public function buildFromSpec(array $spec, int $days): array
    {
        return match ((string) ($spec['aggregation'] ?? '')) {
            'sum_by_day' => $this->buildSumByDayBar($spec, $days),
            'count_origin_and_consumer' => $this->buildDualOriginConsumer($spec, $days),
            default => throw new \InvalidArgumentException(
                'Unsupported aggregation for metric '.((string) ($spec['id'] ?? '')),
            ),
        };
    }

    /** @param array<string, mixed> $spec */
    private function buildSumByDayBar(array $spec, int $days): array
    {
        $eventTypes = $spec['event_types'] ?? [];
        if (! is_array($eventTypes) || $eventTypes === []) {
            return $this->presenter->emptyBarSeries($spec, $days, ['reason' => 'no_event_types']);
        }

        $eventType = (string) ($eventTypes[0] ?? '');
        $path      = $spec['payload_path'] ?? [];
        $pathKeys  = is_array($path) ? array_values(array_map(static fn ($p) => (string) $p, $path)) : [];

        $rows = $this->eventFeedRepository->sumPayloadPathByCalendarDay($eventType, $pathKeys, $days);
        $points = [];
        foreach ($rows as $row) {
            $points[] = [
                'label' => (string) $row['date'],
                'value' => (float) $row['total'],
            ];
        }

        return $this->presenter->barSeries($spec, $days, $points);
    }

    /** @param array<string, mixed> $spec */
    private function buildDualOriginConsumer(array $spec, int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();

        return $this->presenter->dualOriginConsumerSeries(
            $spec,
            $days,
            $this->busQueueAnalyticsRepository->countByOriginSince($since),
            $this->busQueueAnalyticsRepository->countByConsumerSince($since),
        );
    }
}
