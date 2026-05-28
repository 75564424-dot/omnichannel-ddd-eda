<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class IntegrationAdminApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_crud_channel_and_integration(): void
    {
        $channel = $this->postJson('/api/integrations/channels', [
            'code'         => 'ecom-main',
            'name'         => 'E-commerce',
            'channel_type' => 'ecommerce',
        ])->assertCreated()->json('id');

        $this->getJson('/api/integrations/channels')
            ->assertOk()
            ->assertJsonPath('count', 1);

        $integrationId = $this->postJson('/api/integrations', [
            'code'       => 'shopify-in',
            'name'       => 'Shopify Inbound',
            'direction'  => 'inbound',
            'channel_id' => $channel,
        ])->assertCreated()->json('id');

        $this->postJson("/api/integrations/{$integrationId}/credentials", [
            'credential_type' => 'webhook_hmac_secret',
            'value'           => 'encrypted-at-rest',
        ])->assertCreated();

        $this->patchJson("/api/integrations/{$integrationId}", [
            'status' => 'inactive',
        ])->assertOk();

        $row = DB::table('integration_credentials')
            ->where('integration_id', $integrationId)
            ->first();

        $this->assertNotNull($row);
        $this->assertNotSame('encrypted-at-rest', $row->encrypted_value);

        $plain = app(\App\Integration\Domain\Repositories\IntegrationCredentialRepositoryInterface::class)
            ->getPlaintext($integrationId, 'webhook_hmac_secret');

        $this->assertSame('encrypted-at-rest', $plain);
    }
}
