<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RoleBasedAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security.api_auth_enabled', true);
    }

    #[Test]
    public function dashboard_viewer_cannot_sync_registry(): void
    {
        $user = User::query()->create([
            'name'          => 'Viewer',
            'email'         => 'viewer@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'dashboard_viewer',
        ]);

        $this->actingAs($user)
            ->postJson('/api/middleware/registry/sync-config')
            ->assertForbidden();
    }

    #[Test]
    public function dashboard_viewer_can_read_middleware_status(): void
    {
        $user = User::query()->create([
            'name'          => 'Viewer',
            'email'         => 'viewer@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'dashboard_viewer',
        ]);

        $this->actingAs($user)
            ->getJson('/api/middleware/status')
            ->assertOk();
    }

    #[Test]
    public function bus_operator_can_sync_registry(): void
    {
        $user = User::query()->create([
            'name'          => 'Operator',
            'email'         => 'operator@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'bus_operator',
        ]);

        $this->actingAs($user)
            ->postJson('/api/middleware/registry/sync-config')
            ->assertOk();
    }

    #[Test]
    public function saas_admin_can_access_control_user_management(): void
    {
        config(['platform.control_plane' => true, 'platform_auth.web_auth_enabled' => true]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->get('/control/companies')
            ->assertOk();
    }

    #[Test]
    public function platform_admin_cannot_access_control_user_management(): void
    {
        config(['platform.control_plane' => true, 'platform_auth.web_auth_enabled' => true]);

        $admin = User::query()->create([
            'name'          => 'Admin',
            'email'         => 'admin@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        $this->actingAs($admin)
            ->get('/control/companies')
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function bus_operator_cannot_access_control_companies(): void
    {
        config(['platform.control_plane' => true, 'platform_auth.web_auth_enabled' => true]);

        $user = User::query()->create([
            'name'          => 'Operator',
            'email'         => 'operator@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'bus_operator',
        ]);

        $this->actingAs($user)
            ->get('/control/companies')
            ->assertRedirect('/dashboard');
    }

    #[Test]
    public function sync_config_audit_includes_actor_label_with_role(): void
    {
        $user = User::query()->create([
            'name'          => 'Operator',
            'email'         => 'operator@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'bus_operator',
        ]);

        $this->actingAs($user)
            ->postJson('/api/middleware/registry/sync-config')
            ->assertOk();

        $log = \App\Shared\Infrastructure\Models\AuditLogModel::query()
            ->where('action', 'registry.sync')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('operator@local (bus_operator)', $log->getAttribute('changes')['actor_label'] ?? null);
    }
}
