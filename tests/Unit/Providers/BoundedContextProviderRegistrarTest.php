<?php

declare(strict_types=1);

namespace Tests\Unit\Providers;

use App\Dashboard\Interfaces\Providers\DashboardServiceProvider;
use App\Integration\Interfaces\Providers\IntegrationServiceProvider;
use App\Middleware\Interfaces\Providers\MiddlewareServiceProvider;
use App\Providers\Registrars\BoundedContextProviderRegistrar;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BoundedContextProviderRegistrarTest extends TestCase
{
    #[Test]
    public function provider_classes_include_core_bcs(): void
    {
        $classes = BoundedContextProviderRegistrar::providerClasses();

        $this->assertSame([
            DashboardServiceProvider::class,
            MiddlewareServiceProvider::class,
            IntegrationServiceProvider::class,
        ], $classes);
    }
}
