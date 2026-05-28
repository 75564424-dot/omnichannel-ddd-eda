<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use App\Shared\Infrastructure\Models\AuditLogModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PlatformApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('security.api_auth_enabled', true);
        config()->set('security.api_keys', 'test-key|events:publish,bus:read,bus:admin,dashboard:read');
    }

    #[Test]
    public function middleware_status_returns_401_without_credentials(): void
    {
        $this->getJson('/api/middleware/status')->assertUnauthorized();
    }

    #[Test]
    public function middleware_status_accepts_static_api_key(): void
    {
        $this->withHeader('X-API-Key', 'test-key')
            ->getJson('/api/middleware/status')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    #[Test]
    public function publish_requires_events_publish_ability(): void
    {
        config()->set('security.api_keys', 'read-only|bus:read');

        $this->withHeader('X-API-Key', 'read-only')
            ->postJson('/api/middleware/events/publish', [
                'event_id'   => '00000000-0000-4000-8000-000000000099',
                'event_type' => 'Platform.Test',
                'occurred_at'=> now()->toIso8601String(),
                'payload'    => ['event_id' => '00000000-0000-4000-8000-000000000099'],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function sync_config_writes_audit_log_when_enabled(): void
    {
        $this->withHeader('X-API-Key', 'test-key')
            ->postJson('/api/middleware/registry/sync-config')
            ->assertOk();

        $this->assertSame(1, AuditLogModel::query()->where('action', 'registry.sync')->count());
    }

    #[Test]
    public function sanctum_token_grants_access_with_abilities(): void
    {
        $user = User::factory()->create([
            'email'    => 'token-test@local',
            'password' => bcrypt('secret'),
        ]);

        $token = $user->createToken('test', ['bus:read'])->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/middleware/queue')
            ->assertOk();
    }
}
