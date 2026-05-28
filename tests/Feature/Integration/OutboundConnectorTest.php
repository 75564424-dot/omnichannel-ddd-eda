<?php

declare(strict_types=1);

namespace Tests\Feature\Integration;

use App\Integration\Infrastructure\Connectors\HttpOutboundConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OutboundConnectorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function outbound_dispatch_posts_to_connector_endpoint(): void
    {
        Http::fake([
            'https://erp.example/hooks*' => Http::response(['ok' => true], 200),
        ]);

        $integrationId = (string) Str::uuid();
        $connectorId = (string) Str::uuid();

        DB::table('integrations')->insert([
            'id'        => $integrationId,
            'code'      => 'erp-out',
            'name'      => 'ERP Outbound',
            'direction' => 'outbound',
            'status'    => 'active',
            'version'   => 1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('connectors')->insert([
            'id'             => $connectorId,
            'integration_id' => $integrationId,
            'connector_type' => 'http',
            'endpoint'       => 'https://erp.example/hooks/orders',
            'config'         => json_encode(['method' => 'POST'], JSON_THROW_ON_ERROR),
            'health_status'  => 'unknown',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $result = app(\App\Integration\Application\UseCases\DispatchOutboundConnectorUseCase::class)
            ->execute($integrationId, $connectorId, ['order_id' => 'ORD-1']);

        $this->assertSame(200, $result['status']);
        Http::assertSent(fn ($request) => $request->url() === 'https://erp.example/hooks/orders');
    }
}
