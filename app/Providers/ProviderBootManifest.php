<?php

declare(strict_types=1);

namespace App\Providers;

use App\Monitoring\Interfaces\Providers\MonitoringServiceProvider;
use App\Observability\Interfaces\Providers\ObservabilityServiceProvider;
use App\Quality\Interfaces\Providers\QualityServiceProvider;
use App\Shared\Api\Interfaces\Providers\ApiServiceProvider;
use App\Simulation\Interfaces\Providers\SimulationServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Single source of truth for Laravel provider boot order (composition root).
 */
final class ProviderBootManifest
{
    /** @return list<class-string<ServiceProvider>> */
    public static function providers(): array
    {
        return [
            PlatformServiceProvider::class,
            SimulationServiceProvider::class,
            LoggingServiceProvider::class,
            ObservabilityServiceProvider::class,
            MonitoringServiceProvider::class,
            QualityServiceProvider::class,
            ApiServiceProvider::class,
            IdentityServiceProvider::class,
            SecurityServiceProvider::class,
            AppServiceProvider::class,
            EventBusIntegrationServiceProvider::class,
        ];
    }
}
