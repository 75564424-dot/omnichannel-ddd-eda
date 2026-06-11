<?php

declare(strict_types=1);

namespace App\Shared\Platform\Services;

final class TenantCatalogEventBusMapper
{
    /**
     * @param array{
     *     producers: list<array<string, mixed>>,
     *     subscribers: list<array<string, mixed>>
     * } $normalized
     *
     * @return array{
     *     producers: array<string, array{label: string, produces: list<string>}>,
     *     subscriptions: array<string, list<array{module: string}>>
     * }
     */
    public function map(array $normalized): array
    {
        $producers = [];
        $subscriptions = [];

        foreach ($normalized['producers'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            $name = trim((string) ($row['name'] ?? $id));
            $types = $row['event_types_emitted'] ?? [];
            if ($id === '' || ! is_array($types)) {
                continue;
            }
            $eventTypes = [];
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType !== '') {
                    $eventTypes[] = $eventType;
                }
            }
            if ($eventTypes === []) {
                continue;
            }
            $producers[$id] = [
                'label'    => $name,
                'produces' => $eventTypes,
            ];
        }

        foreach ($normalized['subscribers'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $moduleId = trim((string) ($row['id'] ?? ''));
            $types = $row['event_types_consumed'] ?? [];
            if ($moduleId === '' || ! is_array($types)) {
                continue;
            }
            foreach ($types as $eventType) {
                $eventType = trim((string) $eventType);
                if ($eventType === '') {
                    continue;
                }
                $subscriptions[$eventType] ??= [];
                $subscriptions[$eventType][] = ['module' => $moduleId];
            }
        }

        return [
            'producers'     => $producers,
            'subscriptions' => $subscriptions,
        ];
    }
}
