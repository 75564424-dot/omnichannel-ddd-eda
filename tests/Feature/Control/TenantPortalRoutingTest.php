<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Validates ADR-011 Etapa 1 — path-based friendly routing.
 *
 * Route: GET /{tenant_slug}/{path?} (routes/tenant_portal.php)
 * Expected: 302 redirect to silo's port-based URL.
 *
 * @see docs/production/ADR_011_friendly_routing_multitenant.md
 * @see docs/Plan_Desarrollo_Serviciov1.6/Plan_Migracion_Routing_v1.6.md
 */
final class TenantPortalRoutingTest extends TestCase
{
    use RefreshDatabase;

    private function createActiveTenant(
        string $slug   = 'acme-retail',
        string $appUrl = 'http://127.0.0.1:8001',
        string $status = 'active',
    ): TenantModel {
        return TenantModel::query()->create([
            'id'       => 'tenant-' . $slug,
            'slug'     => $slug,
            'name'     => ucfirst(str_replace('-', ' ', $slug)),
            'status'   => $status,
            'settings' => [
                'deployment' => [
                    'lifecycle'      => 'provisioned',
                    'local_instance' => [
                        'port'    => 8001,
                        'env_id'  => 'client-' . $slug,
                        'app_url' => $appUrl,
                    ],
                ],
            ],
        ]);
    }

    private function withFriendlyRouting(): void
    {
        config([
            'platform.friendly_routing' => true,
            'platform.control_plane'    => true,
        ]);
    }

    // -------------------------------------------------------------------------
    // Happy path — redirect to silo URL
    // -------------------------------------------------------------------------

    #[Test]
    public function it_redirects_login_path_to_silo_login(): void
    {
        $this->withFriendlyRouting();
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001');

        $response = $this->get('/acme-retail/login');

        $response->assertRedirect('http://127.0.0.1:8001/login');
        $response->assertStatus(302);
    }

    #[Test]
    public function it_redirects_dashboard_path_to_silo_dashboard(): void
    {
        $this->withFriendlyRouting();
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001');

        $response = $this->get('/acme-retail/dashboard');

        $response->assertRedirect('http://127.0.0.1:8001/dashboard');
    }

    #[Test]
    public function it_defaults_to_login_for_root_tenant_path(): void
    {
        $this->withFriendlyRouting();
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001');

        $response = $this->get('/acme-retail');

        $response->assertRedirect('http://127.0.0.1:8001/login');
    }

    #[Test]
    public function it_redirects_nested_paths(): void
    {
        $this->withFriendlyRouting();
        $this->createActiveTenant('pruebas-retail', 'http://127.0.0.1:8002');

        $response = $this->get('/pruebas-retail/portal/operators');

        $response->assertRedirect('http://127.0.0.1:8002/portal/operators');
    }

    // -------------------------------------------------------------------------
    // Tenant state guards
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_503_for_suspended_tenant(): void
    {
        $this->withFriendlyRouting();
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001', 'suspended');

        $response = $this->get('/acme-retail/login');

        $response->assertStatus(503);
    }

    #[Test]
    public function it_returns_404_for_unknown_tenant_slug(): void
    {
        $this->withFriendlyRouting();

        $response = $this->get('/tenant-does-not-exist/login');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_503_when_tenant_has_no_provisioned_silo(): void
    {
        $this->withFriendlyRouting();

        TenantModel::query()->create([
            'id'       => 'tenant-unprovisioned',
            'slug'     => 'unprovisioned-co',
            'name'     => 'Unprovisioned Co',
            'status'   => 'active',
            'settings' => ['deployment' => ['lifecycle' => 'pending']],
        ]);

        $response = $this->get('/unprovisioned-co/login');

        $response->assertStatus(503);
    }

    // -------------------------------------------------------------------------
    // Feature flag — flag OFF or non-CP environment
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_404_when_friendly_routing_flag_is_disabled(): void
    {
        config([
            'platform.friendly_routing' => false,
            'platform.control_plane'    => true,
        ]);
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001');

        $response = $this->get('/acme-retail/login');

        $response->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_on_non_control_plane_host(): void
    {
        config([
            'platform.friendly_routing' => true,
            'platform.control_plane'    => false,
        ]);
        $this->createActiveTenant('acme-retail', 'http://127.0.0.1:8001');

        $response = $this->get('/acme-retail/login');

        $response->assertStatus(404);
    }
}
