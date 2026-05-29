<?php

declare(strict_types=1);

namespace App\Observability\Application\Services;

use App\Observability\Application\Services\Prometheus\PrometheusGaugeCollector;
use App\Observability\Application\Services\Prometheus\PrometheusTextRenderer;

/**
 * Exports Prometheus text format from live read models (Plan_Observabilidad + Plan_Monitoreo).
 */
final class PrometheusMetricsExporter
{
    public function __construct(
        private readonly PrometheusGaugeCollector $collector,
        private readonly PrometheusTextRenderer $renderer,
    ) {}

    public function export(): string
    {
        $clientSlug = (string) config('platform.client_slug', 'default');

        return $this->renderer->render(
            $this->collector->collect(),
            $clientSlug,
        );
    }
}
