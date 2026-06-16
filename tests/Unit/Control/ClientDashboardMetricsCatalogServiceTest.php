<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\ClientDashboardMetricsCatalogService;
use App\Control\Application\Services\ClientDashboardModulesService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Dashboard\Infrastructure\Modules\ConfigModulesCatalogDataProvider;
use App\Dashboard\Infrastructure\Persistence\DbBusQueueAnalyticsRepository;
use App\Dashboard\Infrastructure\Persistence\EloquentEventFeedRepository;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientDashboardMetricsCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function catalog_empty_without_tenant_modules(): void
    {
        config([
            'platform.client_slug' => 'no-modules-client',
            'modules.catalog'      => [
                'middleware'  => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                'producers'   => [],
                'subscribers' => [],
            ],
        ]);

        $service = $this->service();
        $this->assertFalse($service->hasConfiguredModules());
        $this->assertSame([], $service->catalogEntries());
    }

    #[Test]
    public function catalog_and_series_when_modules_configured(): void
    {
        config(['platform.client_slug' => 'acme-retail']);

        TenantModel::query()->create([
            'id'       => '33333333-3333-3333-3333-333333333333',
            'slug'     => 'acme-retail',
            'name'     => 'Acme',
            'status'   => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS', 'event_types_emitted' => ['AcmePOS.Sale.Completed'], 'channels' => []],
                    ],
                    'subscribers' => [],
                ],
            ],
        ]);

        $service = $this->service();
        $this->assertTrue($service->hasConfiguredModules());
        $this->assertCount(2, $service->catalogEntries());

        $series = $service->buildSeries(ClientDashboardMetricsCatalogService::METRIC_EVENTS_DAILY, 7);
        $this->assertIsArray($series);
        $this->assertSame('bar', $series['chart']);
        $this->assertCount(7, $series['points']);
    }

    private function service(): ClientDashboardMetricsCatalogService
    {
        return new ClientDashboardMetricsCatalogService(
            new ClientDashboardModulesService(
                app(InstanceTenantContextInterface::class),
                app(TenantModuleCatalogService::class),
                app(ConfigModulesCatalogDataProvider::class),
                app(DatabaseManager::class),
            ),
            app(EloquentEventFeedRepository::class),
            app(DbBusQueueAnalyticsRepository::class),
        );
    }
}

