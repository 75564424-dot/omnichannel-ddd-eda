<?php

declare(strict_types=1);

namespace Tests\Unit\Control\Support;

use App\Control\Application\Services\Support\ClientIncidentReportSeverityNormalizer;
use App\Control\Application\Services\Support\ClientIncidentReportTenantResolver;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientIncidentReportSupportServicesTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function severity_normalizer_defaults_unknown_values_to_normal(): void
    {
        $normalizer = new ClientIncidentReportSeverityNormalizer();

        $this->assertSame('normal', $normalizer->normalize('unknown'));
        $this->assertSame('critical', $normalizer->normalize('CRITICAL'));
    }

    #[Test]
    public function tenant_resolver_prefers_configured_client_slug(): void
    {
        config(['platform.client_slug' => 'acme']);

        TenantModel::query()->create([
            'id'       => 'aaaaaaaa-1111-1111-1111-111111111111',
            'name'     => 'Other',
            'slug'     => 'other',
            'status'   => 'active',
            'settings' => [],
        ]);

        $acme = TenantModel::query()->create([
            'id'       => 'bbbbbbbb-2222-2222-2222-222222222222',
            'name'     => 'Acme',
            'slug'     => 'acme',
            'status'   => 'active',
            'settings' => [],
        ]);

        $resolved = (new ClientIncidentReportTenantResolver())->resolveInstanceTenant();

        $this->assertNotNull($resolved);
        $this->assertSame($acme->id, $resolved->id);
    }

    #[Test]
    public function tenant_resolver_falls_back_to_first_tenant_when_slug_missing(): void
    {
        config(['platform.client_slug' => '']);

        $first = TenantModel::query()->create([
            'id'       => 'cccccccc-3333-3333-3333-333333333333',
            'name'     => 'First',
            'slug'     => 'first',
            'status'   => 'active',
            'settings' => [],
        ]);

        TenantModel::query()->create([
            'id'       => 'dddddddd-4444-4444-4444-444444444444',
            'name'     => 'Second',
            'slug'     => 'second',
            'status'   => 'active',
            'settings' => [],
        ]);

        $resolved = (new ClientIncidentReportTenantResolver())->resolveInstanceTenant();

        $this->assertNotNull($resolved);
        $this->assertSame($first->id, $resolved->id);
    }
}
