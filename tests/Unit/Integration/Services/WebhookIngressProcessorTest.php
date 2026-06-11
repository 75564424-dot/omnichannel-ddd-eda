<?php

declare(strict_types=1);

namespace Tests\Unit\Integration\Services;

use App\Integration\Application\Services\WebhookEventEnvelopeBuilder;
use App\Integration\Application\Services\WebhookIngressProcessor;
use App\Integration\Domain\Contracts\ExternalEventPublisherInterface;
use App\Integration\Infrastructure\Adapters\AdapterRegistry;
use App\Integration\Infrastructure\Adapters\FieldMapAdapter;
use App\Integration\Infrastructure\Adapters\JsonValidateAdapter;
use App\Middleware\Application\DTOs\PublishResult;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class WebhookIngressProcessorTest extends TestCase
{
    #[Test]
    public function process_publishes_built_envelope_and_returns_ids(): void
    {
        $integration = [
            'id'         => 'int-1',
            'code'       => 'pos-webhook',
            'channel_id' => 'ch-1',
            'config'     => [
                'webhook'  => ['origin' => 'Webhook:pos-webhook'],
                'adapters' => [],
            ],
        ];
        $body = [
            'event_type'  => 'Retail.Order.Created',
            'occurred_at' => '2026-01-01T00:00:00Z',
        ];

        $registry = new AdapterRegistry;
        $registry->register(new JsonValidateAdapter);
        $registry->register(new FieldMapAdapter);

        $pipeline = new \App\Integration\Application\Services\AdapterPipeline(
            new class implements \App\Integration\Domain\Repositories\AdapterRepositoryInterface {
                public function listEnabledForIntegration(string $integrationId): array
                {
                    return [];
                }
            },
            $registry,
        );

        $publisher = Mockery::mock(ExternalEventPublisherInterface::class);
        $publisher->shouldReceive('publish')
            ->once()
            ->withArgs(function (array $envelope) {
                return $envelope['event_type'] === 'Retail.Order.Created'
                    && Uuid::isValid($envelope['event_id'])
                    && $envelope['origin'] === 'Webhook:pos-webhook';
            })
            ->andReturn(new PublishResult(entryId: 99, idempotent: false));

        $result = (new WebhookIngressProcessor(
            $pipeline,
            new WebhookEventEnvelopeBuilder(),
            $publisher,
        ))->process($integration, $body);

        $this->assertSame(99, $result['entry_id']);
        $this->assertTrue(Uuid::isValid($result['event_id']));
    }
}
