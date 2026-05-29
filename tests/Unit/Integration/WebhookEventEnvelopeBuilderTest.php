<?php

declare(strict_types=1);

namespace Tests\Unit\Integration;

use App\Integration\Application\Services\WebhookEventEnvelopeBuilder;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class WebhookEventEnvelopeBuilderTest extends TestCase
{
    #[Test]
    public function build_generates_event_id_when_missing(): void
    {
        $builder = new WebhookEventEnvelopeBuilder();

        $envelope = $builder->build(
            ['id' => 'int-1', 'code' => 'shopify_orders', 'channel_id' => 'ch-1'],
            ['event_type' => 'Order.Created', 'amount' => 10],
            ['webhook' => ['origin' => 'Webhook:shopify']],
        );

        $this->assertSame('Order.Created', $envelope['event_type']);
        $this->assertTrue(Uuid::isValid($envelope['event_id']));
        $this->assertSame('Webhook:shopify', $envelope['origin']);
        $this->assertSame('int-1', $envelope['integration_id']);
    }

    #[Test]
    public function build_preserves_valid_event_id_from_payload(): void
    {
        $eventId = Uuid::uuid4()->toString();
        $builder = new WebhookEventEnvelopeBuilder();

        $envelope = $builder->build(
            ['id' => 'int-1', 'code' => 'erp', 'channel_id' => null],
            ['event_type' => 'Stock.Low', 'event_id' => $eventId],
            [],
        );

        $this->assertSame($eventId, $envelope['event_id']);
    }

    #[Test]
    public function build_rejects_payload_without_event_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new WebhookEventEnvelopeBuilder())->build(
            ['id' => 'int-1', 'code' => 'erp', 'channel_id' => null],
            ['amount' => 10],
            [],
        );
    }
}
