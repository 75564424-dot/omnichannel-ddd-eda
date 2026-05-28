<?php

return [
    App\Providers\PlatformServiceProvider::class,
    App\Providers\LoggingServiceProvider::class,
    App\Observability\Interfaces\Providers\ObservabilityServiceProvider::class,
    App\Monitoring\Interfaces\Providers\MonitoringServiceProvider::class,
    App\Quality\Interfaces\Providers\QualityServiceProvider::class,
    App\Shared\Api\Interfaces\Providers\ApiServiceProvider::class,
    App\Providers\IdentityServiceProvider::class,
    App\Providers\SecurityServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\EventBusIntegrationServiceProvider::class,
];
