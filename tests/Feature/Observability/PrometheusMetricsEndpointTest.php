<?php

declare(strict_types=1);

namespace Tests\Feature\Observability;

use App\Observability\Application\Services\PrometheusMetricsExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PrometheusMetricsEndpointTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function metrics_endpoint_returns_prometheus_text_format(): void
    {
        $response = $this->get('/metrics');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; version=0.0.4; charset=utf-8');
        $this->assertStringContainsString('bus_events_published_total', $response->getContent());
        $this->assertStringContainsString('bus_processing_latency_ms', $response->getContent());
        $this->assertStringContainsString('bus_error_rate_percent', $response->getContent());
        $this->assertStringContainsString('bus_stream_status', $response->getContent());
        $this->assertStringContainsString('bus_dlq_unresolved', $response->getContent());
        $this->assertStringContainsString('feed_projection_lag_ms', $response->getContent());
        $this->assertStringContainsString('database_usage_percent', $response->getContent());
        $this->assertStringContainsString('queue_jobs_pending', $response->getContent());
        $this->assertStringContainsString('canary_last_success_age_seconds', $response->getContent());
        $this->assertStringContainsString('sse_stream_connections_active', $response->getContent());
    }

    #[Test]
    public function exporter_builds_valid_prometheus_lines(): void
    {
        /** @var PrometheusMetricsExporter $exporter */
        $exporter = app(PrometheusMetricsExporter::class);
        $body     = $exporter->export();

        $this->assertMatchesRegularExpression('/bus_events_published_total\{client="[^"]+"\} \d+/', $body);
    }
}
