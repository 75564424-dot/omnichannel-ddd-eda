<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Providers\ProviderBootManifest;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProviderBootManifestTest extends TestCase
{
    #[Test]
    public function providers_lists_composition_root_in_boot_order(): void
    {
        $providers = ProviderBootManifest::providers();

        $this->assertNotEmpty($providers);
        $this->assertSame(
            ProviderBootManifest::providers(),
            require base_path('bootstrap/providers.php'),
        );
        $this->assertContains(\App\Providers\PlatformServiceProvider::class, $providers);
        $this->assertContains(\App\Providers\AppServiceProvider::class, $providers);
        $this->assertTrue(
            array_search(\App\Providers\AppServiceProvider::class, $providers, true)
            < array_search(\App\Providers\EventBusIntegrationServiceProvider::class, $providers, true),
        );
    }
}
