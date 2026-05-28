<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class WebhookIngressTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function valid_signature_publishes_event_to_event_store(): void
    {
        $channelId = (string) Str::uuid();
        $integrationId = (string) Str::uuid();
        $secret = 'test-webhook-secret';

        DB::table('channels')->insert([
            'id'           => $channelId,
            'code'         => 'pos-test',
            'name'         => 'POS Test',
            'channel_type' => 'pos',
            'status'       => 'active',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DB::table('integrations')->insert([
            'id'         => $integrationId,
            'channel_id' => $channelId,
            'code'       => 'pos-webhook',
            'name'       => 'POS Webhook',
            'direction'  => 'inbound',
            'status'     => 'active',
            'config'     => json_encode([
                'webhook' => ['origin' => 'Webhook:pos-webhook'],
                'adapters' => [
                    ['type' => 'json_validate', 'config' => ['required' => ['event_type', 'occurred_at']]],
                ],
            ], JSON_THROW_ON_ERROR),
            'version'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(\App\Integration\Application\UseCases\StoreIntegrationCredentialUseCase::class)
            ->execute($integrationId, 'webhook_hmac_secret', $secret);

        $eventId = Uuid::uuid4()->toString();
        $occurred = now()->toIso8601String();
        $body = [
            'event_id'    => $eventId,
            'event_type'  => 'Retail.Order.Created',
            'occurred_at' => $occurred,
        ];
        $raw = json_encode($body, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $raw, $secret);

        $this->withHeader('X-Webhook-Signature', $signature)
            ->postJson('/api/integrations/webhooks/pos-webhook', $body)
            ->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('event_id', $eventId);

        $this->assertDatabaseHas('event_store', [
            'event_uuid'     => $eventId,
            'integration_id' => $integrationId,
            'channel_id'     => $channelId,
        ]);

        $this->assertDatabaseHas('webhook_requests', [
            'integration_id' => $integrationId,
            'status'         => 'processed',
        ]);
    }

    #[Test]
    public function invalid_signature_returns_401(): void
    {
        $integrationId = (string) Str::uuid();

        DB::table('integrations')->insert([
            'id'         => $integrationId,
            'code'       => 'bad-sig',
            'name'       => 'Bad Sig',
            'direction'  => 'inbound',
            'status'     => 'active',
            'config'     => null,
            'version'    => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(\App\Integration\Application\UseCases\StoreIntegrationCredentialUseCase::class)
            ->execute($integrationId, 'webhook_hmac_secret', 'real-secret');

        $this->withHeader('X-Webhook-Signature', 'sha256=invalid')
            ->postJson('/api/integrations/webhooks/bad-sig', [
                'event_type'  => 'Test.Event',
                'occurred_at' => now()->toIso8601String(),
            ])
            ->assertStatus(401);
    }
}
