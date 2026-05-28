<?php

declare(strict_types=1);

namespace Tests\Integration\Middleware;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class ModuleRegistryObservationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function dispatch_records_producer_and_topology_lists_connections_for_subscribed_consumer(): void
    {
        $eventType = 'Platform.Integration.TopologyDemo';
        config()->set('eventbus.subscriptions', [
            $eventType => [
                ['module' => 'ExternalConsumerPack'],
            ],
        ]);

        $eventId = Uuid::uuid4()->toString();
        $payload = [
            'event_id'    => $eventId,
            'event'       => $eventType,
            'occurred_at' => now()->toIso8601String(),
            'channel'     => 'POS',
        ];

        Event::dispatch($eventType, [$payload]);

        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'retail-pos',
            'type'       => 'producer',
            'name'       => 'Retail POS',
        ]);

        $this->assertDatabaseHas('registered_modules', [
            'logical_id' => 'externalconsumerpack',
            'type'       => 'consumer',
            'name'       => 'ExternalConsumerPack',
        ]);

        $this->getJson('/api/middleware/topology')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.observed.connections.0.from', 'retail-pos')
            ->assertJsonPath('data.observed.connections.0.to', 'externalconsumerpack')
            ->assertJsonPath('data.observed.connections.0.event_type', $eventType);
    }

    #[Test]
    public function topology_observed_sections_are_empty_without_traffic(): void
    {
        $this->getJson('/api/middleware/topology')
            ->assertOk()
            ->assertJsonPath('data.observed.producers', [])
            ->assertJsonPath('data.observed.consumers', [])
            ->assertJsonPath('data.observed.connections', []);
    }
}
