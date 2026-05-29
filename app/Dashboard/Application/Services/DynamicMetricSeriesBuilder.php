<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

/**
 * Builds chart payloads from dashboard_config.json metric definitions.
 */
final class DynamicMetricSeriesBuilder
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly BusQueueAnalyticsRepositoryInterface $busQueueAnalyticsRepository,
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
            return $this->emptyBarPayload($spec, $days, ['reason' => 'no_event_types']);
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

        return $this->barPayload($spec, $days, $points);
    }

    /** @param array<string, mixed> $spec */
    private function buildDualOriginConsumer(array $spec, int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();
        $byOrigin   = $this->busQueueAnalyticsRepository->countByOriginSince($since);
        $byConsumer = $this->busQueueAnalyticsRepository->countByConsumerSince($since);

        $toPoints = static function (array $assoc): array {
            $pts = [];
            foreach ($assoc as $label => $count) {
                $pts[] = ['label' => (string) $label, 'value' => (int) $count];
            }

            return $pts;
        };

        return [
            'metric_id'    => (string) $spec['id'],
            'title'        => (string) ($spec['name'] ?? $spec['id']),
            'chart'        => 'dual_bar',
            'value_label'  => (string) ($spec['value_label'] ?? 'Eventos'),
            'y_label'      => (string) ($spec['y_label'] ?? ''),
            'value_format' => (string) ($spec['value_format'] ?? 'number'),
            'days'         => $days,
            'panels'       => [
                [
                    'panel_id'    => 'by_origin',
                    'title'       => 'Por origen (productor)',
                    'points'      => $toPoints($byOrigin),
                    'description' => 'Basado en bus_queue_entries.origin',
                ],
                [
                    'panel_id'    => 'by_consumer',
                    'title'       => 'Por consumidor (routing)',
                    'points'      => $toPoints($byConsumer),
                    'description' => 'Agrupa módulos declarados en consumers del bus (vacío si el catálogo no asigna suscriptores).',
                ],
            ],
        ];
    }

    /**
     * @param list<array{label: string, value: float}> $points
     *
     * @param array<string, mixed> $spec
     *
     * @return array<string, mixed>
     */
    private function barPayload(array $spec, int $days, array $points): array
    {
        return [
            'metric_id'    => (string) $spec['id'],
            'title'        => (string) ($spec['name'] ?? $spec['id']),
            'chart'        => (string) ($spec['chart'] ?? 'bar'),
            'value_label'  => (string) ($spec['value_label'] ?? 'value'),
            'y_label'      => (string) ($spec['y_label'] ?? ''),
            'value_format' => (string) ($spec['value_format'] ?? 'number'),
            'days'         => $days,
            'points'       => $points,
        ];
    }

    /** @param array<string, mixed> $spec */
    private function emptyBarPayload(array $spec, int $days, array $meta): array
    {
        return [
            ...$this->barPayload($spec, $days, []),
            'meta' => $meta,
        ];
    }
}
