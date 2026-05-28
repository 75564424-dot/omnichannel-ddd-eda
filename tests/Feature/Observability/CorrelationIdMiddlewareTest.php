<?php

declare(strict_types=1);

namespace Tests\Feature\Observability;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class CorrelationIdMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function api_generates_correlation_id_when_missing_and_echoes_header(): void
    {
        $response = $this->getJson('/api/middleware/metrics');

        $response->assertOk();
        $header = $response->headers->get('X-Correlation-ID');
        $this->assertNotNull($header);
        $this->assertTrue(Uuid::isValid((string) $header));
    }

    #[Test]
    public function api_preserves_incoming_correlation_id_header(): void
    {
        $correlation = Uuid::uuid4()->toString();

        $response = $this->withHeader('X-Correlation-ID', $correlation)
            ->getJson('/api/middleware/metrics');

        $response->assertOk();
        $this->assertSame($correlation, $response->headers->get('X-Correlation-ID'));
    }
}
