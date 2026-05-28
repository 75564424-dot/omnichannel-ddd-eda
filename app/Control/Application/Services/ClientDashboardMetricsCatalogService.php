<?php

declare(strict_types=1);

namespace App\Control\Application\Services;

use App\Dashboard\Domain\Repositories\BusQueueAnalyticsRepositoryInterface;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;

/**
 * Métricas del dashboard derivadas del catálogo SaaS del tenant (no del demo genérico).
 */
final class ClientDashboardMetricsCatalogService
{
    public const METRIC_EVENTS_DAILY = 'tenant_events_daily';

    public const METRIC_BUS_FLOW = 'tenant_bus_flow';

    public function __construct(
        private readonly ClientDashboardModulesService $modules,
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly BusQueueAnalyticsRepositoryInterface $busQueueAnalytics,
    ) {}

    public function hasConfiguredModules(): bool
    {
        $catalog = $this->modules->presentationCatalog();

        return count($catalog['available_producers'] ?? []) > 0
            || count($catalog['available_subscribers'] ?? []) > 0;
    }

    /** @return list<array{id: string, name: string, type: string, chart: string}> */
    public function catalogEntries(): array
    {
        if (! $this->hasConfiguredModules()) {
            return [];
        }

        return [
            [
                'id'    => self::METRIC_EVENTS_DAILY,
                'name'  => 'Eventos por día (sus módulos)',
                'type'  => 'chart',
                'chart' => 'bar',
            ],
            [
                'id'    => self::METRIC_BUS_FLOW,
                'name'  => 'Flujo en bus (origen / consumidor)',
                'type'  => 'chart',
                'chart' => 'dual_bar',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildSeries(string $metricId, ?int $days = null): ?array
    {
        if (! $this->hasConfiguredModules()) {
            return null;
        }

        $days = max(1, min(90, $days ?? 14));

        return match ($metricId) {
            self::METRIC_EVENTS_DAILY => $this->buildEventsDaily($days),
            self::METRIC_BUS_FLOW => $this->buildBusFlow($days),
            default => null,
        };
    }

    /** @return list<string> */
    private function trackedEventTypes(): array
    {
        $saas = $this->modules->presentationCatalog();
        $types = [];

        foreach ($saas['available_producers'] ?? [] as $producer) {
            if (! is_array($producer)) {
                continue;
            }
            foreach ($producer['event_types_emitted'] ?? [] as $type) {
                $t = trim((string) $type);
                if ($t !== '') {
                    $types[] = $t;
                }
            }
        }

        foreach ($saas['available_subscribers'] ?? [] as $subscriber) {
            if (! is_array($subscriber)) {
                continue;
            }
            foreach ($subscriber['event_types_consumed'] ?? [] as $type) {
                $t = trim((string) $type);
                if ($t !== '') {
                    $types[] = $t;
                }
            }
        }

        return array_values(array_unique($types));
    }

    /** @return array<string, mixed> */
    private function buildEventsDaily(int $days): array
    {
        $eventTypes = $this->trackedEventTypes();
        $rows       = $this->eventFeedRepository->countEventsByCalendarDay($eventTypes, $days);
        $points     = [];

        foreach ($rows as $row) {
            $points[] = [
                'label' => (string) $row['date'],
                'value' => (int) $row['total'],
            ];
        }

        return [
            'metric_id'    => self::METRIC_EVENTS_DAILY,
            'title'        => 'Eventos por día (sus módulos)',
            'chart'        => 'bar',
            'value_label'  => 'Eventos',
            'y_label'      => 'Cantidad',
            'value_format' => 'number',
            'days'         => $days,
            'points'       => $points,
            'meta'         => [
                'event_types' => $eventTypes,
                'empty_hint'  => $eventTypes === []
                    ? 'Configure tipos de evento en los módulos SaaS.'
                    : 'Sin eventos aún — al simular o integrar verá barras aquí.',
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function buildBusFlow(int $days): array
    {
        $since = now()->subDays($days - 1)->startOfDay();
        $byOrigin   = $this->busQueueAnalytics->countByOriginSince($since);
        $byConsumer = $this->busQueueAnalytics->countByConsumerSince($since);

        $toPoints = static function (array $assoc): array {
            $pts = [];
            foreach ($assoc as $label => $count) {
                $pts[] = ['label' => (string) $label, 'value' => (int) $count];
            }

            return $pts;
        };

        return [
            'metric_id'    => self::METRIC_BUS_FLOW,
            'title'        => 'Flujo en bus (origen / consumidor)',
            'chart'        => 'dual_bar',
            'value_label'  => 'Eventos',
            'y_label'      => 'Cantidad',
            'value_format' => 'number',
            'days'         => $days,
            'panels'       => [
                [
                    'panel_id'    => 'by_origin',
                    'title'       => 'Por productor',
                    'points'      => $toPoints($byOrigin),
                    'description' => 'Entradas en cola por origen declarado.',
                ],
                [
                    'panel_id'    => 'by_consumer',
                    'title'       => 'Por consumidor',
                    'points'      => $toPoints($byConsumer),
                    'description' => 'Distribución hacia suscriptores del catálogo.',
                ],
            ],
            'meta' => [
                'empty_hint' => 'Sin tráfico en el bus todavía.',
            ],
        ];
    }
}
