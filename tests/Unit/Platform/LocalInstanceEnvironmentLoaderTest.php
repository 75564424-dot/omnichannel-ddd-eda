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
        $loader = new LocalInstanceEnvironmentLoader;
        $vars = $loader->criticalForWorker('client-pruebas-retail');

        $this->assertArrayHasKey('DB_DATABASE', $vars);
        $this->assertStringContainsString('pruebas-retail.sqlite', $vars['DB_DATABASE']);
        $this->assertSame('false', $vars['PLATFORM_CONTROL_PLANE'] ?? null);
    }
}
