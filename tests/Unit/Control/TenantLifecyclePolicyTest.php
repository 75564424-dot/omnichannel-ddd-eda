<?php

declare(strict_types=1);

namespace Tests\Unit\Control;

use App\Control\Domain\Policies\TenantLifecyclePolicy;
use App\Shared\Infrastructure\Models\TenantModel;
use Tests\TestCase;

final class TenantLifecyclePolicyTest extends TestCase
{
    public function test_can_start_blocks_suspended_and_allows_expected_lifecycles(): void
    {
        $this->assertFalse(TenantLifecyclePolicy::canStart('suspended', 'provisioned'));
        $this->assertFalse(TenantLifecyclePolicy::canStart('suspended', 'running'));

        $this->assertTrue(TenantLifecyclePolicy::canStart('active', 'provisioned'));
        $this->assertTrue(TenantLifecyclePolicy::canStart('active', 'stopped'));
        $this->assertTrue(TenantLifecyclePolicy::canStart('active', 'running')); // idempotent start is allowed

        $this->assertFalse(TenantLifecyclePolicy::canStart('active', 'unknown'));
    }

    public function test_can_suspend_only_allows_active_status(): void
    {
        $this->assertTrue(TenantLifecyclePolicy::canSuspend('active', 'running'));
        $this->assertTrue(TenantLifecyclePolicy::canSuspend('active', 'provisioned'));

        $this->assertFalse(TenantLifecyclePolicy::canSuspend('suspended', 'running'));
        $this->assertFalse(TenantLifecyclePolicy::canSuspend('provisioned', 'running'));
    }

    public function test_can_restore_only_allows_suspended_status(): void
    {
        $this->assertTrue(TenantLifecyclePolicy::canRestore('suspended', 'stopped'));
        $this->assertTrue(TenantLifecyclePolicy::canRestore('suspended', 'running'));

        $this->assertFalse(TenantLifecyclePolicy::canRestore('active', 'running'));
        $this->assertFalse(TenantLifecyclePolicy::canRestore('provisioned', 'provisioned'));
    }

    public function test_infer_lifecycle_handles_missing_or_legacy_deployment_settings(): void
    {
        $tenant = new TenantModel();
        $tenant->settings = null;
        $this->assertSame('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = [];
        $this->assertSame('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = ['deployment' => 'invalid'];
        $this->assertSame('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = ['deployment' => ['status' => 'active_on_instance']];
        $this->assertSame('running', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = ['deployment' => ['status' => 'pending_dedicated_instance']];
        $this->assertSame('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));

        $tenant->settings = ['deployment' => ['status' => 'unknown']];
        $this->assertSame('provisioned', TenantLifecyclePolicy::inferLifecycle($tenant));
    }

    public function test_infer_lifecycle_prefers_explicit_lifecycle_field(): void
    {
        $tenant = new TenantModel();
        $tenant->settings = [
            'deployment' => [
                'status' => 'active_on_instance',
                'lifecycle' => 'stopped',
            ],
        ];

        $this->assertSame('stopped', TenantLifecyclePolicy::inferLifecycle($tenant));
    }
}

