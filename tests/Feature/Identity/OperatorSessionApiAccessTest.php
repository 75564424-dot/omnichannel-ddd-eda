<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OperatorSessionApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('platform_auth.web_auth_enabled', true);
        config()->set('security.api_auth_enabled', true);
    }

    #[Test]
    public function authenticated_operator_session_can_call_middleware_api_without_bearer_token(): void
    {
        $user = User::query()->create([
            'name'     => 'Operator',
            'email'    => 'operator@local',
            'password' => Hash::make('secret'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/middleware/status')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    #[Test]
    public function authenticated_operator_can_access_dashboard_api_via_session(): void
    {
        $user = User::query()->create([
            'name'     => 'Operator',
            'email'    => 'operator@local',
            'password' => Hash::make('secret'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/dashboard/modules/catalog')
            ->assertOk();
    }
}
