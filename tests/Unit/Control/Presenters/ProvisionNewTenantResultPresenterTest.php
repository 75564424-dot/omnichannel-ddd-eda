<?php

declare(strict_types=1);

namespace Tests\Unit\Control\Presenters;

use App\Control\Application\Presenters\ProvisionNewTenantResultPresenter;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\LocalFleetProvisionResult;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ProvisionNewTenantResultPresenterTest extends TestCase
{
    #[Test]
    public function it_presents_success_message_when_fleet_provisioned(): void
    {
        $tenant = new TenantModel([
            'id' => 'tenant-1',
            'slug' => 'acme',
            'name' => 'Acme',
        ]);

        $result = new LocalFleetProvisionResult(
            provisioned: true,
            instance: [],
            localInstance: ['app_url' => 'http://127.0.0.1:18001'],
        );

        $presented = (new ProvisionNewTenantResultPresenter())->present($tenant, $result);

        $this->assertSame($tenant, $presented['tenant']);
        $this->assertStringContainsString('http://127.0.0.1:18001', $presented['message']);
        $this->assertFalse($presented['show_deployment_guide']);
    }

    #[Test]
    public function it_presents_deployment_guide_when_fleet_not_provisioned(): void
    {
        $tenant = new TenantModel([
            'id' => 'tenant-2',
            'slug' => 'beta',
            'name' => 'Beta',
        ]);

        $result = new LocalFleetProvisionResult(
            provisioned: false,
            instance: [],
            localInstance: [],
        );

        $presented = (new ProvisionNewTenantResultPresenter())->present($tenant, $result);

        $this->assertStringContainsString('PLATFORM_CLIENT_SLUG=beta', $presented['message']);
        $this->assertTrue($presented['show_deployment_guide']);
    }
}
