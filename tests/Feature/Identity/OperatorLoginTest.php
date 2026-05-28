<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Database\Seeders\PlatformOperatorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OperatorLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('platform_auth.web_auth_enabled', true);
    }

    #[Test]
    public function dashboard_redirects_guest_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    #[Test]
    public function operator_can_login_and_access_dashboard(): void
    {
        config(['platform.client_slug' => 'acme-retail']);

        $tenant = TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'tenant_id'     => $tenant->id,
            'name'          => 'Admin',
            'email'         => 'admin@local',
            'password'      => Hash::make('password'),
            'platform_role' => 'platform_admin',
        ]);

        $this->post('/login', [
            'email'    => 'admin@local',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    #[Test]
    public function operator_of_another_tenant_can_login_when_multi_tenant_portal_enabled(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.multi_tenant_portal_login' => true,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $other = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas Retail',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'tenant_id'     => $other->id,
            'name'          => 'Prueba',
            'email'         => 'prueba@prueba',
            'password'      => Hash::make('password'),
            'platform_role' => 'platform_admin',
        ]);

        $this->post('/login', [
            'email'    => 'prueba@prueba',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertSame($other->id, session('portal_tenant_id'));
        $this->get('/dashboard')->assertOk();
    }

    #[Test]
    public function operator_of_another_tenant_is_rejected_when_multi_tenant_portal_disabled(): void
    {
        config([
            'platform.client_slug' => 'acme-retail',
            'platform.multi_tenant_portal_login' => false,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        $other = TenantModel::query()->create([
            'id'     => '22222222-2222-2222-2222-222222222222',
            'name'   => 'Pruebas Retail',
            'slug'   => 'pruebas-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'tenant_id'     => $other->id,
            'name'          => 'Prueba',
            'email'         => 'prueba@prueba',
            'password'      => Hash::make('password'),
            'platform_role' => 'platform_admin',
        ]);

        $this->from('/login')->post('/login', [
            'email'    => 'prueba@prueba',
            'password' => 'password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function saas_admin_cannot_login_on_client_silo(): void
    {
        config([
            'platform.client_slug'     => 'acme-retail',
            'platform.control_plane'   => false,
            'platform.multi_tenant_portal_login' => false,
        ]);

        TenantModel::query()->create([
            'id'     => '11111111-1111-1111-1111-111111111111',
            'name'   => 'Acme',
            'slug'   => 'acme-retail',
            'status' => 'active',
            'settings' => [],
        ]);

        User::query()->create([
            'tenant_id'     => null,
            'name'          => 'SaaS Admin',
            'email'         => 'saas@local',
            'password'      => Hash::make('password'),
            'platform_role' => 'saas_admin',
        ]);

        $this->from('/login')->post('/login', [
            'email'    => 'saas@local',
            'password' => 'password',
        ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function control_routes_return_not_found_on_client_silo(): void
    {
        config(['platform.control_plane' => false]);

        $this->get('/control/companies')->assertNotFound();
    }

    #[Test]
    public function platform_operator_seeder_creates_admin_user(): void
    {
        config()->set('platform_auth.seed_admin_operator', true);
        config()->set('platform_auth.admin_operator', [
            'name'     => 'Seed Admin',
            'email'    => 'seed-admin@local',
            'password' => 'seed-pass',
        ]);
        config(['platform.client_slug' => 'default']);

        TenantModel::query()->create([
            'id'     => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'name'   => 'Default',
            'slug'   => 'default',
            'status' => 'active',
            'settings' => [],
        ]);

        $this->seed(PlatformOperatorSeeder::class);

        $this->assertDatabaseHas('users', [
            'email'     => 'seed-admin@local',
            'tenant_id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        ]);
    }
}
