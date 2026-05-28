<?php

declare(strict_types=1);

namespace App\Middleware\Domain;

/**
 * Builds an observed-topology snapshot from registered modules (no config, no business rules).
 */
final class TopologyService
{
    /**
     * @return array{
     *     producers: list<array{id: string, name: string, type: string, subscribed_events: list<string>, published_events: list<string>}>,
     *     consumers: list<array{id: string, name: string, type: string, subscribed_events: list<string>, published_events: list<string>}>,
     *     connections: list<array{from: string, to: string, event_type: string}>
     * }
     */
    public function buildObservedSnapshot(ModuleRegistry $registry): array
    {
        $producers = [];
        $consumers = [];
        foreach ($registry->listModules() as $module) {
            if ($module->type === Module::TYPE_PRODUCER) {
                $producers[] = $module;
            } elseif ($module->type === Module::TYPE_CONSUMER) {
                $consumers[] = $module;
            }
        }

        $producerDetails = array_map(static fn (Module $m) => $m->toDetailArray(), $producers);
        $consumerDetails = array_map(static fn (Module $m) => $m->toDetailArray(), $consumers);

        $connections = [];
        foreach ($producers as $p) {
            foreach ($p->publishedEvents as $eventType) {
                foreach ($consumers as $c) {
                    if (in_array($eventType, $c->subscribedEvents, true)) {
                        $connections[] = [
                            'from'        => $p->id,
                            'to'          => $c->id,
                            'event_type'  => $eventType,
                        ];
                    }
                }
            }
        }

        return [
            'producers'    => $producerDetails,
            'consumers'    => $consumerDetails,
            'connections'  => $connections,
        ];
    }

    /**
     * Topology map rows for the control UI (id, label, events / subscribed_to).
     *
     * @param array{producers: list<array<string, mixed>>, consumers: list<array<string, mixed>>, connections: list<array<string, mixed>>} $observed
     * @return array{producers: list<array<string, mixed>>, consumers: list<array<string, mixed>>}
     */
    public function toDiagramNodes(array $observed): array
    {
        $producerRows = [];
        foreach ($observed['producers'] as $p) {
            $producerRows[] = [
                'id'     => $p['id'],
                'label'  => $p['name'],
                'events' => $p['published_events'] ?? [],
            ];
        }

        $consumerRows = [];
        foreach ($observed['consumers'] as $c) {
            $consumerRows[] = [
                'id'             => $c['id'],
                'label'          => $c['name'],
                'subscribed_to'  => $c['subscribed_events'] ?? [],
            ];
        }

        return [
            'producers' => $producerRows,
            'consumers' => $consumerRows,
        ];
    }
}
