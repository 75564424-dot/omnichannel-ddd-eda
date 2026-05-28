<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

/**
 * Builds simulation publish templates from a tenant modules_catalog (SaaS UI).
 */
final class TenantCatalogSampleEventBuilder
{
    /**
     * @param array<string, mixed> $catalog
     *
     * @return list<array<string, mixed>>
     */
    public function fromCatalog(array $catalog): array
    {
        $producers = isset($catalog['producers']) && is_array($catalog['producers'])
            ? array_values($catalog['producers'])
            : [];

        $templates = [];
        foreach ($producers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $origin = trim((string) ($row['name'] ?? $row['id'] ?? 'Simulation'));
            $types = $row['event_types_emitted'] ?? [];
            if (! is_array($types)) {
                continue;
            }
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }
                $templates[] = [
                    'event_type'        => $eventType,
                    'origin'            => $origin,
                    'payload_template'  => [
                        'sequence'  => '{n}',
                        'simulated' => true,
                        'channel'   => is_array($row['channels'] ?? null) && ($row['channels'][0] ?? '') !== ''
                            ? (string) $row['channels'][0]
                            : 'SIM',
                    ],
                ];
            }
        }

        return $templates;
    }
}
