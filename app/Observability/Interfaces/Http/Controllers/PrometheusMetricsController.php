<?php

declare(strict_types=1);

namespace App\Observability\Interfaces\Http\Controllers;

use App\Observability\Application\Services\PrometheusMetricsExporter;
use Illuminate\Http\Response;

final class PrometheusMetricsController
{
    public function __construct(
        private readonly PrometheusMetricsExporter $exporter,
    ) {}

    public function __invoke(): Response
    {
        if (! config('platform_observability.prometheus_enabled', true)) {
            return response('Prometheus metrics disabled', 404);
        }

        return response($this->exporter->export(), 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
