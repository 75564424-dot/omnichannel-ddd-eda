<?php

declare(strict_types=1);

namespace Tests\Feature\Health;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function up_endpoint_returns_ok(): void
    {
        $this->get('/up')->assertOk();
    }

    #[Test]
    public function readiness_returns_ready_with_sqlite_and_no_redis(): void
    {
        config()->set('cache.default', 'array');
        config()->set('queue.default', 'sync');
        config()->set('session.driver', 'array');

        $this->getJson('/health/ready')
            ->assertOk()
            ->assertJsonPath('status', 'ready')
            ->assertJsonPath('checks.database', 'ok')
            ->assertJsonPath('checks.redis', 'skipped');
    }

    #[Test]
    public function readiness_health_routes_do_not_require_authentication(): void
    {
        config()->set('security.api_auth_enabled', true);

        $this->get('/up')->assertOk();
        $this->getJson('/health/ready')->assertOk();
    }
}
