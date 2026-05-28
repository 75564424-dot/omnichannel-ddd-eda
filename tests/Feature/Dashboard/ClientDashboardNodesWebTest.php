<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Shared\Infrastructure\Models\TenantModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClientDashboardNodesWebTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function web_patch_middleware_events_persists_for_producer_node(): void
    {
        config([
            'platform.client_slug'     => 'acme-retail',
            'platform_auth.web_auth_enabled' => true,
        ]);

        TenantModel::query()->create([
            'id'       => '11111111-1111-1111-1111-111111111111',
            'slug'     => 'acme-retail',
            'name'     => 'Acme',
            'status'   => 'active',
            'settings' => [
                'modules_catalog' => [
                    'middleware' => ['id' => 'middleware', 'name' => 'Bus', 'description' => '', 'role' => 'routing'],
                    'producers' => [
                        ['id' => 'acme_web', 'name' => 'Web', 'event_types_emitted' => [], 'channels' => []],
                    ],
                    'subscribers' => [],
                ],
            ],
        ]);

        $user = User::query()->create([
            'name'          => 'Admin',
            'email'         => 'admin@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'platform_admin',
        ]);

        $this->actingAs($user)
            ->patchJson('/dashboard/nodes/producer%3Aacme_web/middleware-events', [
                'middleware_events_enabled' => true,
            ])
            ->assertOk()
            ->assertJsonPath('producer:acme_web.middleware_events_enabled', true)
            ->assertJsonPath('producer:acme_web.status', 'ONLINE');

        $this->assertDatabaseHas('channel_status_snapshots', [
            'node_code'      => 'producer:acme_web',
            'events_enabled' => 1,
            'status'         => 'ONLINE',
        ]);
    }
}
