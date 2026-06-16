<?php

declare(strict_types=1);

namespace App\Monitoring\Application\Services\Canary;

use Ramsey\Uuid\Uuid;

final class CanaryProbeEnvelopeFactory
{
    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $eventId   = Uuid::uuid4()->toString();
        $occurred  = now()->toIso8601String();
        $eventType = (string) config('platform_monitoring.canary.event_type', 'Platform.Monitoring.Canary');

        return [
            'event_id'    => $eventId,
            'event_type'  => $eventType,
            'occurred_at' => $occurred,
            'origin'      => 'MonitoringCanary',
            'payload'     => [
                'event_id'    => $eventId,
                'event'       => $eventType,
                'occurred_at' => $occurred,
                'probe'       => 'canary',
            ],
        ];
    }
}
