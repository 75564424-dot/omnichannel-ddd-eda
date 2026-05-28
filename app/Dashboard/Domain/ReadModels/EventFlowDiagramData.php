<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ReadModels;

/**
 * Read Model: data required to render the EventFlowDiagram component.
 * Computed from recent event_feed_entries — represents activity in the last N minutes.
 */
final class EventFlowDiagramData
{
    public function __construct(
        public readonly array  $nodes,
        public readonly array  $edges,
        public readonly int    $totalEvents,
        public readonly string $updatedAt,
    ) {}

    public function toArray(): array
    {
        return [
            'nodes'        => $this->nodes,
            'edges'        => $this->edges,
            'total_events' => $this->totalEvents,
            'updated_at'   => $this->updatedAt,
        ];
    }
}
