<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\ClientDashboardModulesService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Dashboard\Infrastructure\Modules\ConfigModulesCatalogDataProvider;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientDashboardModulesServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function presentation_filters_by_visible_ids_and_exposes_available(): void
    {
        config(['platform.client_slug' => 'acme-retail']);

        TenantModel::query()->create([
            'id'       => '11111111-1111-1111-1111-111111111111',
            'slug'     => 'acme-retail',
            'name'     => 'Acme',
            'status'   => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS', 'event_types_emitted' => [], 'channels' => []],
                        ['id' => 'acme_web', 'name' => 'Web', 'event_types_emitted' => [], 'channels' => []],
                    ],
                    'subscribers' => [],
                ],
                'dashboard_visible_modules' => [
                    'producers' => ['acme_pos'],
                    'subscribers' => [],
                ],
            ],
        ]);

        $service = new ClientDashboardModulesService(
            app(InstanceTenantContextInterface::class),
            app(TenantModuleCatalogService::class),
            app(ConfigModulesCatalogDataProvider::class),
        );

        $catalog = $service->presentationCatalog();
        $this->assertCount(1, $catalog['producers']);
        $this->assertSame('acme_pos', $catalog['producers'][0]['id']);
        $this->assertCount(2, $catalog['available_producers']);
        $this->assertSame(['acme_pos'], $catalog['visible_producer_ids']);
    }

    #[Test]
    public function update_visible_modules_persists_subset(): void
    {
        config(['platform.client_slug' => 'acme-retail']);

        $tenant = TenantModel::query()->create([
            'id'       => '22222222-2222-2222-2222-222222222222',
            'slug'     => 'acme-retail',
            'name'     => 'Acme',
            'status'   => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS', 'event_types_emitted' => [], 'channels' => []],
                    ],
                    'subscribers' => [],
                ],
            ],
        ]);

        $service = new ClientDashboardModulesService(
            app(InstanceTenantContextInterface::class),
            app(TenantModuleCatalogService::class),
            app(ConfigModulesCatalogDataProvider::class),
        );

        $service->updateVisibleModules(['acme_pos'], []);

        $tenant->refresh();
        $visible = $tenant->settings['dashboard_visible_modules'] ?? [];
        $this->assertSame(['acme_pos'], $visible['producers'] ?? []);
    }
}

