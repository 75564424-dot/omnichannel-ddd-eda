<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Application\DTOs\GlobalMetricsDTO;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

/**
 * Builds configurable KPI cards from counter_cards — no fixed retail semantics.
 */
final class GetGlobalMetricsUseCase
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly BusQueueAnalyticsRepositoryInterface $busQueueAnalyticsRepository,
    ) {}

    public function execute(): GlobalMetricsDTO
    {
        $cards   = config('dashboard.counter_cards', []);
        $counters = [];
        $now     = now();

        foreach ($cards as $spec) {
            if (! is_array($spec) || empty($spec['enabled'])) {
                continue;
            }
            $id   = (string) ($spec['id'] ?? '');
            $name = (string) ($spec['name'] ?? $id);
            if ($id === '') {
                continue;
            }

            $suffix = (string) ($spec['suffix'] ?? '');
            $agg    = (string) ($spec['aggregation'] ?? '');
            $source = (string) ($spec['source'] ?? 'event_feed');

            $value = match (true) {
                $source === 'event_feed' && $agg === 'count_window_seconds' => (float) $this->eventFeedRepository->countReceivedSince(
                    $now->clone()->subSeconds(max(1, (int) ($spec['seconds'] ?? 60)))
                ),
                $source === 'event_feed' && $agg === 'avg_latency_ms' => (float) $this->eventFeedRepository->computeAverageLatencyMs(
                    max(1, (int) ($spec['sample_size'] ?? 100))
                ),
                $source === 'bus_queue' && $agg === 'count_window_seconds' => (float) $this->busQueueAnalyticsRepository->countPublishedSince(
                    $now->clone()->subSeconds(max(1, (int) ($spec['seconds'] ?? 60)))
                ),
                default => null,
            };

            if ($value === null) {
                continue;
            }

            $counters[] = [
                'id'    => $id,
                'name'  => $name,
                'value' => $value,
                'suffix'=> $suffix,
            ];
        }

        return new GlobalMetricsDTO(
            counters: $counters,
            lastUpdated: $now->timezone(config('app.timezone'))->toIso8601String(),
        );
    }
}
