<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\ClientDashboardModulesService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Dashboard\Infrastructure\Modules\ConfigModulesCatalogDataProvider;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientDashboardModulesConfigurationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function presentation_is_empty_until_dashboard_visibility_is_configured(): void
    {
        config(['platform.client_slug' => 'acme-retail']);

        TenantModel::query()->create([
            'id'       => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'slug'     => 'acme-retail',
            'name'     => 'Acme',
            'status'   => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS', 'event_types_emitted' => ['Order.Created'], 'channels' => []],
                    ],
                    'subscribers' => [],
                ],
            ],
        ]);

        $service = new ClientDashboardModulesService(
            app(InstanceTenantContextInterface::class),
            app(TenantModuleCatalogService::class),
            app(ConfigModulesCatalogDataProvider::class),
            app(DatabaseManager::class),
        );

        $catalog = $service->presentationCatalog();
        $this->assertFalse($catalog['dashboard_configured']);
        $this->assertSame([], $catalog['producers']);
        $this->assertCount(1, $catalog['available_producers']);
    }
}
