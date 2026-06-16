<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Presenters;

/**
 * Chart payload presenter for dashboard dynamic metrics (config-driven, product-agnostic).
 */
final class DynamicMetricSeriesPresenter
{
    /**
     * @param list<array{label: string, value: float|int}> $points
     * @param array<string, mixed>                         $spec
     *
     * @return array<string, mixed>
     */
    public function barSeries(array $spec, int $days, array $points): array
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

    /**
     * @param array<string, mixed> $spec
     * @param array<string, mixed> $meta
     *
     * @return array<string, mixed>
     */
    public function emptyBarSeries(array $spec, int $days, array $meta): array
    {
        return [
            ...$this->barSeries($spec, $days, []),
            'meta' => $meta,
        ];
    }

    /**
     * @param array<string, int>   $byOrigin
     * @param array<string, int>   $byConsumer
     * @param array<string, mixed> $spec
     *
     * @return array<string, mixed>
     */
    public function dualOriginConsumerSeries(
        array $spec,
        int $days,
        array $byOrigin,
        array $byConsumer,
    ): array {
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
                    'points'      => $this->countMapToPoints($byOrigin),
                    'description' => 'Basado en bus_queue_entries.origin',
                ],
                [
                    'panel_id'    => 'by_consumer',
                    'title'       => 'Por consumidor (routing)',
                    'points'      => $this->countMapToPoints($byConsumer),
                    'description' => 'Agrupa módulos declarados en consumers del bus (vacío si el catálogo no asigna suscriptores).',
                ],
            ],
        ];
    }

    /**
     * @param array<string, int> $assoc
     *
     * @return list<array{label: string, value: int}>
     */
    public function countMapToPoints(array $assoc): array
    {
        $points = [];
        foreach ($assoc as $label => $count) {
            $points[] = ['label' => (string) $label, 'value' => (int) $count];
        }

        return $points;
    }
}
