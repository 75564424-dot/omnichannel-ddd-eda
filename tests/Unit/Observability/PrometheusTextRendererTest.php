<?php

declare(strict_types=1);

namespace Tests\Unit\Observability;

use App\Observability\Application\Services\Prometheus\PrometheusGaugeSnapshot;
use App\Observability\Application\Services\Prometheus\PrometheusTextRenderer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PrometheusTextRendererTest extends TestCase
{
    #[Test]
    public function render_outputs_all_prometheus_metric_lines(): void
    {
        $snapshot = new PrometheusGaugeSnapshot(
            publishedTotal: 42,
            processingLatencyMs: 120,
            errorRatePercent: 1.5,
            streamStatus: 1,
            dlqUnresolved: 0,
            feedProjectionLagMs: 50,
            sseActiveConnections: 2,
            databaseUsagePercent: 10.0,
            queueJobsPending: 3,
            canaryLastSuccessAgeSeconds: 60,
        );

        $body = (new PrometheusTextRenderer())->render($snapshot, 'acme-retail');

        $this->assertStringContainsString('bus_events_published_total{client="acme-retail"} 42', $body);
        $this->assertStringContainsString('bus_processing_latency_ms{client="acme-retail"} 120', $body);
        $this->assertStringContainsString('canary_last_success_age_seconds{client="acme-retail"} 60', $body);
        $this->assertStringContainsString('# TYPE bus_stream_status gauge', $body);
    }

    #[Test]
    public function render_escapes_special_characters_in_client_label(): void
    {
        $snapshot = new PrometheusGaugeSnapshot(0, 0, 0.0, 0, 0, 0, 0, 0.0, 0, -1);

        $body = (new PrometheusTextRenderer())->render($snapshot, 'client"with\\quotes');

        $this->assertStringContainsString('{client="client\\"with\\\\quotes"}', $body);
    }
}
