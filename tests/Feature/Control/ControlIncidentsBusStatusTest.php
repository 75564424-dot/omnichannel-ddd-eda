<?php

declare(strict_types=1);

namespace Tests\Feature\Control;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ControlIncidentsBusStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function incidents_page_shows_active_bus_without_bus_stopped_alert_when_idle(): void
    {
        config([
            'platform.control_plane' => true,
            'platform_monitoring.enabled' => true,
            'platform_auth.web_auth_enabled' => true,
        ]);

        Cache::forget('platform.monitoring.bus_stopped_since');

        $user = User::query()->create([
            'name'          => 'SaaS',
            'email'         => 'saas@local',
            'password'      => Hash::make('secret'),
            'platform_role' => 'saas_admin',
        ]);

        $this->actingAs($user)
            ->get('/control/incidents')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Control/Incidents/Index')
                ->where('metrics.bus_status', 'ACTIVE')
                ->has('alerts', 0));
    }
}
