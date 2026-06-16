<?php

declare(strict_types=1);

namespace Tests\Unit\Platform;

use App\Shared\Platform\LocalInstanceEnvironmentLoader;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LocalInstanceEnvironmentLoaderTest extends TestCase
{
    #[Test]
    public function it_loads_database_path_from_instance_env_file(): void
    {
        $envId = 'client-fixture-branding';
        $envPath = base_path('.env.'.$envId);
        $dbPath = database_path('instances/fixture-branding.sqlite');
        file_put_contents($envPath, implode("\n", [
            'APP_KEY=base64:fixture-key-for-unit-test-only==',
            'APP_NAME="Fixture Branding Co"',
            'APP_URL=http://127.0.0.1:8099',
            'DB_CONNECTION=sqlite',
            "DB_DATABASE={$dbPath}",
            'CACHE_STORE=database',
            'SESSION_DRIVER=database',
            'SESSION_COOKIE=platform_session_fixture_branding',
            'SESSION_XSRF_COOKIE=platform_xsrf_fixture_branding',
            'QUEUE_CONNECTION=sync',
            'PLATFORM_DEPLOYMENT_MODE=instance_per_client',
            'PLATFORM_CLIENT_SLUG=fixture-branding',
            'PLATFORM_CLIENT_NAME="Fixture Branding Co"',
            'PLATFORM_CONTROL_PLANE=false',
            'PLATFORM_CONTROL_PLANE_URL=http://127.0.0.1:8000',
            'PLATFORM_SIMULATION_INTERNAL_TOKEN=fixture-token',
            'PLATFORM_SEED_INSTANCE_TENANT=true',
        ])."\n");

        try {
            $loader = new LocalInstanceEnvironmentLoader;
            $vars = $loader->criticalForWorker($envId);

            $this->assertArrayHasKey('DB_DATABASE', $vars);
            $this->assertStringContainsString('fixture-branding.sqlite', $vars['DB_DATABASE']);
            $this->assertSame('false', $vars['PLATFORM_CONTROL_PLANE'] ?? null);
            $this->assertSame('Fixture Branding Co', $vars['APP_NAME'] ?? null);
            $this->assertSame('platform_session_fixture_branding', $vars['SESSION_COOKIE'] ?? null);
            $this->assertSame('platform_xsrf_fixture_branding', $vars['SESSION_XSRF_COOKIE'] ?? null);
        } finally {
            @unlink($envPath);
        }
    }
}
