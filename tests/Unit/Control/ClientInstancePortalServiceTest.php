<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Application\Services\ClientInstancePortalService;
use App\Control\Application\Services\Tenants\TenantModuleCatalogService;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Contracts\InstanceTenantContextInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientInstancePortalServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function branding_and_live_modules_follow_instance_tenant_catalog(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.client_name' => 'Fallback Name',
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme Retail Middleware',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus Acme', 'description' => 'Desc'],
                    'producers' => [
                        ['id' => 'acme_pos', 'name' => 'POS Acme', 'event_types_emitted' => [], 'channels' => []],
                    ],
                    'subscribers' => [
                        ['id' => 'acme_reporting', 'name' => 'Reporting Acme', 'event_types_consumed' => []],
                    ],
                ],
            ],
        ]);

        $service = new ClientInstancePortalService(
            app(InstanceTenantContextInterface::class),
            app(TenantModuleCatalogService::class),
            app(DatabaseManager::class),
        );

        $this->assertSame($tenant->id, $service->resolveTenant()?->id);
        $this->assertSame('Acme Retail Middleware', $service->branding()['company_name']);

        $rows = $service->liveModuleRows();
        $this->assertCount(3, $rows);
        $this->assertSame('middleware', $rows[0]['key']);
        $this->assertSame('Bus Acme', $rows[0]['label']);
        $this->assertSame('producer:acme_pos', $rows[1]['key']);
        $this->assertSame('subscriber:acme_reporting', $rows[2]['key']);

        $keys = $service->monitoredNodeKeys();
        $this->assertContains('middleware', $keys);
        $this->assertContains('producer:acme_pos', $keys);
        $this->assertContains('subscriber:acme_reporting', $keys);
    }

    #[Test]
    public function producer_and_subscriber_node_keys_are_stable(): void
    {
        $this->assertSame('producer:pos_1', ClientInstancePortalService::producerNodeKey('pos_1'));
        $this->assertSame('subscriber:crm', ClientInstancePortalService::subscriberNodeKey('crm'));
    }
}

