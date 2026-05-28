<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantOperatorsScopedTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function company_show_lists_only_operators_for_that_tenant(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $acme = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => ['primary_admin_email' => 'admin@acme'],
        ]);

        $prueba = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Prueba',
            'slug'   => 'prueba-01',
            'status' => 'active',
            'settings' => ['primary_admin_email' => '1@prueba'],
        ]);

        User::query()->create([
            'tenant_id'     => $acme->id,
            'name'          => 'Acme Admin',
            'email'         => 'admin@acme',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        User::query()->create([
            'tenant_id'     => $prueba->id,
            'name'          => 'Prueba Admin',
            'email'         => '1@prueba',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        User::query()->create([
            'tenant_id'     => null,
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $saas = User::query()->where('email', 'saas@local')->first();

        $this->actingAs($saas)
            ->get("/control/companies/{$prueba->id}")
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('tenant.operators', 1)
                ->where('tenant.operators.0.email', '1@prueba'));
    }

    #[Test]
    public function created_operator_is_bound_to_tenant(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $tenant = TenantModel::query()->create([
            'id'     => '33333333-3333-3333-3333-333333333333',
            'name'   => 'Demo Co',
            'slug'   => 'demo-co',
            'status' => 'active',
            'settings' => [],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->post("/control/companies/{$tenant->id}/operators", [
                'name'          => 'Viewer',
                'email'         => 'viewer@demo-co',
                'password'      => 'password123',
                'platform_role' => 'dashboard_viewer',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email'         => 'viewer@demo-co',
            'tenant_id'     => $tenant->id,
            'platform_role' => 'dashboard_viewer',
        ]);
    }

    #[Test]
    public function bus_operator_cannot_open_dashboard_web_route(): void
    {
        config([
            'platform.client_slug' => 'demo-co',
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '44444444-4444-4444-4444-444444444444',
            'name'   => 'Demo Co',
            'slug'   => 'demo-co',
            'status' => 'active',
            'settings' => [],
        ]);

        $operator = User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => 'Bus Op',
            'email'         => 'bus@demo-co',
            'password'      => Hash::make('secret'),
            'platform_role' => 'bus_operator',
        ]);

        $this->actingAs($operator)
            ->get('/dashboard')
            ->assertRedirect('/middleware');
    }

    #[Test]
    public function saas_admin_can_reset_operator_password_for_tenant(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $tenant = TenantModel::query()->create([
            'id'     => '66666666-6666-6666-6666-666666666666',
            'name'   => 'Demo Co',
            'slug'   => 'demo-co',
            'status' => 'active',
            'settings' => [],
        ]);

        $operator = User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => 'Op',
            'email'         => 'op@demo-co',
            'password'      => Hash::make('old-secret'),
            'platform_role' => 'platform_admin',
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->patch("/control/companies/{$tenant->id}/operators/{$operator->id}/password", [
                'password'              => 'new-secret-99',
                'password_confirmation' => 'new-secret-99',
            ])
            ->assertRedirect();

        $operator->refresh();
        $this->assertTrue(Hash::check('new-secret-99', (string) $operator->password));
        $this->assertFalse(Hash::check('old-secret', (string) $operator->password));
    }

    #[Test]
    public function cannot_reset_password_for_operator_of_another_tenant(): void
    {
        config(['platform_auth.web_auth_enabled' => true]);

        $tenantA = TenantModel::query()->create([
            'id'     => '77777777-7777-7777-7777-777777777777',
            'name'   => 'A',
            'slug'   => 'tenant-a',
            'status' => 'active',
            'settings' => [],
        ]);

        $tenantB = TenantModel::query()->create([
            'id'     => '88888888-8888-8888-8888-888888888888',
            'name'   => 'B',
            'slug'   => 'tenant-b',
            'status' => 'active',
            'settings' => [],
        ]);

        $operatorB = User::query()->create([
            'tenant_id'     => $tenantB->id,
            'name'          => 'B Op',
            'email'         => 'b@tenant-b',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($saas)
            ->patch("/control/companies/{$tenantA->id}/operators/{$operatorB->id}/password", [
                'password'              => 'hacked-password',
                'password_confirmation' => 'hacked-password',
            ])
            ->assertNotFound();
    }

    #[Test]
    public function dashboard_viewer_cannot_open_middleware_web_route(): void
    {
        config([
            'platform.client_slug' => 'demo-co',
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'     => '55555555-5555-5555-5555-555555555555',
            'name'   => 'Demo Co',
            'slug'   => 'demo-co',
            'status' => 'active',
            'settings' => [],
        ]);

        $viewer = User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => 'Viewer',
            'email'         => 'view@demo-co',
            'password'      => Hash::make('secret'),
            'platform_role' => 'dashboard_viewer',
        ]);

        $this->actingAs($viewer)
            ->get('/middleware')
            ->assertRedirect('/dashboard');
    }
}
