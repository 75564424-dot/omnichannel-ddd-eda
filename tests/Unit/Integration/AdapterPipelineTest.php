<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use App\Integration\Infrastructure\Adapters\AdapterRegistry;
use App\Integration\Infrastructure\Adapters\FieldMapAdapter;
use App\Integration\Infrastructure\Adapters\JsonValidateAdapter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class AdapterPipelineTest extends TestCase
{
    #[Test]
    public function json_validate_and_field_map_adapters_transform_payload(): void
    {
        $registry = new AdapterRegistry;
        $registry->register(new JsonValidateAdapter);
        $registry->register(new FieldMapAdapter);

        $payload = ['external_type' => 'Order.Created', 'occurred_at' => '2026-01-01T00:00:00Z'];
        $payload = $registry->get('json_validate')->transform($payload, ['required' => ['external_type', 'occurred_at']]);
        $payload = $registry->get('field_map')->transform($payload, ['map' => ['external_type' => 'event_type']]);

        $this->assertSame('Order.Created', $payload['event_type']);
    }
}
