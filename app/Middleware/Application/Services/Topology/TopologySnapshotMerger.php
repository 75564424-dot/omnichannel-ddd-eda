<?php

declare(strict_types=1);

namespace App\Middleware\Application\Services\Topology;

final class TopologySnapshotMerger
{
    /**
     * @param list<array<string, mixed>> $configRows
     * @param list<array<string, mixed>> $observedRows
     *
     * @return list<array<string, mixed>>
     */
    public function mergeProducers(array $configRows, array $observedRows): array
    {
        $byId = [];
        foreach ($configRows as $p) {
            $id = (string) ($p['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $byId[$id] = [
                'id'     => $id,
                'label'  => $p['label'] ?? $id,
                'events' => array_values(array_unique($p['events'] ?? [])),
            ];
        }

        foreach ($observedRows as $p) {
            $id = (string) ($p['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $events = array_values(array_unique($p['events'] ?? []));
            if (! isset($byId[$id])) {
                $byId[$id] = [
                    'id'     => $id,
                    'label'  => (string) ($p['label'] ?? $id),
                    'events' => $events,
                ];
                continue;
            }

            $byId[$id]['events'] = array_values(array_unique([...$byId[$id]['events'], ...$events]));
            if (($p['label'] ?? '') !== '') {
                $byId[$id]['label'] = (string) $p['label'];
            }
        }

        return array_values($byId);
    }

    /**
     * @param list<array<string, mixed>> $configRows
     * @param list<array<string, mixed>> $observedRows
     *
     * @return list<array<string, mixed>>
     */
    public function mergeConsumers(array $configRows, array $observedRows): array
    {
        $byId = [];
        foreach ($configRows as $c) {
            $id = (string) ($c['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $byId[$id] = [
                'id'            => $id,
                'label'         => $c['label'] ?? $id,
                'subscribed_to' => array_values(array_unique($c['subscribed_to'] ?? [])),
            ];
        }

        foreach ($observedRows as $c) {
            $id = (string) ($c['id'] ?? '');
            if ($id === '') {
                continue;
            }
            $subs = array_values(array_unique($c['subscribed_to'] ?? []));
            if (! isset($byId[$id])) {
                $byId[$id] = [
                    'id'            => $id,
                    'label'         => (string) ($c['label'] ?? $id),
                    'subscribed_to' => $subs,
                ];
                continue;
            }

            $byId[$id]['subscribed_to'] = array_values(array_unique([...$byId[$id]['subscribed_to'], ...$subs]));
            if (($c['label'] ?? '') !== '') {
                $byId[$id]['label'] = (string) $c['label'];
            }
        }

        return array_values($byId);
    }
}
