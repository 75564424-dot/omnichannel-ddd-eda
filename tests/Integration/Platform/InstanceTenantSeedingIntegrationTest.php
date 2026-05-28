<?php

declare(strict_types=1);

namespace Tests\Integration\Platform;

use Database\Seeders\InstanceTenantSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class InstanceTenantSeedingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'platform.deployment_mode'       => 'instance_per_client',
            'platform.client_slug'           => 'integration-test-client',
            'platform.client_name'           => 'Integration Test',
            'platform.seed_instance_tenant'  => true,
        ]);
    }

    #[Test]
    public function seeder_creates_tenant_row_for_instance_slug(): void
    {
        $this->seed(InstanceTenantSeeder::class);

        $this->assertDatabaseHas('tenants', [
            'slug'   => 'integration-test-client',
            'status' => 'active',
        ]);
    }

    #[Test]
    public function message_queue_persists_tenant_id_after_seed(): void
    {
        $this->seed(InstanceTenantSeeder::class);

        $tenantId = DB::table('tenants')->where('slug', 'integration-test-client')->value('id');
        $this->assertNotEmpty($tenantId);

        $eventId = Uuid::uuid4()->toString();

        Event::dispatch('Platform.Tenant.Test', [[
            'event_id'    => $eventId,
            'event'       => 'Platform.Tenant.Test',
            'occurred_at' => now()->toIso8601String(),
        ]]);

        $this->assertDatabaseHas('message_queue', [
            'event_uuid' => $eventId,
            'tenant_id'  => $tenantId,
        ]);
    }
}
