<?php

declare(strict_types=1);

namespace Tests\Unit\Monitoring;

use App\Monitoring\Application\Services\Canary\CanaryProbeEnvelopeFactory;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class CanaryProbeEnvelopeFactoryTest extends TestCase
{
    #[Test]
    public function build_produces_valid_canary_envelope(): void
    {
        config(['platform_monitoring.canary.event_type' => 'Platform.Monitoring.Canary']);

        $envelope = (new CanaryProbeEnvelopeFactory())->build();

        $this->assertSame('Platform.Monitoring.Canary', $envelope['event_type']);
        $this->assertSame('MonitoringCanary', $envelope['origin']);
        $this->assertTrue(Uuid::isValid((string) $envelope['event_id']));
        $this->assertSame($envelope['event_id'], $envelope['payload']['event_id']);
        $this->assertSame('canary', $envelope['payload']['probe']);
    }
}
