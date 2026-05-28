<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;
use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use InvalidArgumentException;

/**
 * Resolves chart payloads from dashboard_config.json metric definitions (no module-specific code paths).
 */
final class GetDynamicMetricSeriesUseCase
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly BusQueueAnalyticsRepositoryInterface $busQueueAnalyticsRepository,
        private readonly ClientDashboardMetricsCatalogService $clientMetrics,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $metricId, ?int $days = null): array
    {
        $clientSeries = $this->clientMetrics->buildSeries($metricId, $days);
        if ($clientSeries !== null) {
            return $clientSeries;
        }

        $spec = $this->findSpec($metricId);
        if ($spec === null) {
            throw new InvalidArgumentException("Unknown or disabled metric: {$metricId}");
        }

        $days = max(1, min(90, $days ?? (int) ($spec['days_default'] ?? 14)));

        return match ((string) ($spec['aggregation'] ?? '')) {
            'sum_by_day' => $this->buildSumByDayBar($spec, $days),
            'count_origin_and_consumer' => $this->buildDualOriginConsumer($spec, $days),
            default => throw new InvalidArgumentException('Unsupported aggregation for metric '.$metricId),
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

    /** @param array<string, mixed> $spec */
    private function emptyBarPayload(array $spec, int $days, array $meta): array
    {
        return [
            'metric_id'    => (string) $spec['id'],
            'title'        => (string) ($spec['name'] ?? $spec['id']),
            'chart'        => (string) ($spec['chart'] ?? 'bar'),
            'value_label'  => (string) ($spec['value_label'] ?? ''),
            'y_label'      => (string) ($spec['y_label'] ?? ''),
            'value_format' => (string) ($spec['value_format'] ?? 'number'),
            'days'         => $days,
            'points'       => [],
            'meta'         => $meta,
        ];
    }

    /** @return array<string, mixed>|null */
    private function findSpec(string $metricId): ?array
    {
        foreach (config('dashboard.dynamic_metrics', []) as $spec) {
            if (is_array($spec) && ($spec['id'] ?? '') === $metricId && ! empty($spec['enabled'])) {
                return $spec;
            }
        }

        return null;
    }
}
