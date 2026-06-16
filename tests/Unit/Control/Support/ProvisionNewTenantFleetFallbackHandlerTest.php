<?php

declare(strict_types=1);

namespace Tests\Unit\Control\Support;

use App\Control\Application\Services\Support\ProvisionNewTenantFleetFallbackHandler;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\Services\InstanceDeploymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProvisionNewTenantFleetFallbackHandlerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_persists_pending_deployment_settings_when_fleet_is_unavailable(): void
    {
        $tenant = TenantModel::query()->create([
            'id' => 'dddddddd-4444-4444-4444-444444444444',
            'name' => 'Pending Co',
            'slug' => 'pending-co',
            'status' => 'active',
            'settings' => ['plan' => 'starter'],
        ]);

        $handler = new ProvisionNewTenantFleetFallbackHandler(app(InstanceDeploymentService::class));
        $handler->applyPendingDeploymentSettings($tenant, [
            'admin_email' => 'admin@pending.test',
        ]);

        $tenant->refresh();
        $settings = $tenant->settings;

        $this->assertSame('admin@pending.test', $settings['primary_admin_email']);
        $this->assertSame('instance_per_client', $settings['deployment']['mode']);
        $this->assertSame('pending_dedicated_instance', $settings['deployment']['status']);
        $this->assertSame('pending-co', $settings['deployment']['required_client_slug']);
        $this->assertNotEmpty($settings['deployment']['provisioned_at']);
        $this->assertNotEmpty($settings['app_url']);
    }
}
