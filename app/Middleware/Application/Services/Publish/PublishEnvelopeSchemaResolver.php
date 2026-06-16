<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Publish;

use App\Middleware\Application\Services\EventSchemaRegistry;

final class PublishEnvelopeSchemaResolver
{
    public function __construct(
        private readonly EventSchemaRegistry $schemaRegistry,
    ) {}

    /**
     * @param array<string, mixed> $envelope
     *
     * @return array<string, mixed>
     */
    public function applyDefaults(array $envelope): array
    {
        $eventType = (string) $envelope['event_type'];
        $resolved  = $this->schemaRegistry->resolve($eventType);

        if ($resolved !== null) {
            $envelope['event_version'] ??= $resolved['event_version'];
            if ($resolved['schema_version'] !== null) {
                $envelope['schema_version'] ??= $resolved['schema_version'];
            }
        }

        $envelope['event_version'] ??= 1;

        return $envelope;
    }
}
