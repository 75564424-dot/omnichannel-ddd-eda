<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
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
        config()->set('platform_auth.web_auth_enabled', true);

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
}
