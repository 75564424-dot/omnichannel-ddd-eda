<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use App\Shared\Platform\LocalFleet\Contracts\LocalFleetTenantMirrorInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TenantModuleCatalogTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function saas_admin_can_save_tenant_module_catalog(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'       => '33333333-3333-3333-3333-333333333333',
            'slug'     => 'test-co',
            'name'     => 'Test Co',
            'status'   => 'active',
            'settings' => ['plan' => 'starter'],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->mock(LocalFleetTenantMirrorInterface::class, function ($mock): void {
            $mock->shouldNotReceive('mirror');
        });

        $this->actingAs($saas)
            ->patch("/control/companies/{$tenant->id}/modules-catalog", [
                'limits' => [
                    'producers_max'   => 4,
                    'subscribers_max' => 2,
                ],
                'catalog' => [
                    'service_contact_message' => 'Test catalog',
                    'middleware' => [
                        'name'        => 'Bus',
                        'description' => 'Desc',
                    ],
                    'producers' => [
                        [
                            'id'                  => 'pos_a',
                            'name'                => 'POS A',
                            'event_types_emitted' => 'Order.Created, Order.Paid',
                            'channels'            => 'POS',
                        ],
                    ],
                    'subscribers' => [
                        [
                            'id'                   => 'sink_b',
                            'name'                 => 'Sink B',
                            'event_types_consumed' => 'Order.Created',
                        ],
                    ],
                ],
            ])
            ->assertRedirect();

        $tenant->refresh();
        $settings = $tenant->settings;
        $this->assertIsArray($settings['modules_catalog']);
        $this->assertCount(1, $settings['modules_catalog']['producers']);
        $this->assertSame('pos_a', $settings['modules_catalog']['producers'][0]['id']);
    }

    #[Test]
    public function saving_catalog_mirrors_to_local_instance_when_tenant_has_silo(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        $tenant = TenantModel::query()->create([
            'id'       => '44444444-4444-4444-4444-444444444444',
            'slug'     => 'mirror-co',
            'name'     => 'Mirror Co',
            'status'   => 'active',
            'settings' => [
                'plan'       => 'starter',
                'deployment' => [
                    'local_instance' => [
                        'db_path' => 'database/instances/mirror-co.sqlite',
                        'port'    => 8010,
                        'env_id'  => 'client-mirror-co',
                    ],
                ],
            ],
        ]);

        $saas = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->mock(LocalFleetTenantMirrorInterface::class, function ($mock) use ($tenant): void {
            $mock->shouldReceive('mirrorCatalog')
                ->once()
                ->withArgs(fn (TenantModel $mirrored): bool => $mirrored->id === $tenant->id);
        });

        $this->actingAs($saas)
            ->patch("/control/companies/{$tenant->id}/modules-catalog", [
                'limits' => [
                    'producers_max'   => 2,
                    'subscribers_max' => 2,
                ],
                'catalog' => [
                    'producers' => [
                        [
                            'id'                  => 'pos_mirror',
                            'name'                => 'POS Mirror',
                            'event_types_emitted' => 'Mirror.Event',
                            'channels'            => 'POS',
                        ],
                    ],
                    'subscribers' => [],
                ],
            ])
            ->assertRedirect();
    }
}
