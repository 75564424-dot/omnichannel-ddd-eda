<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Domain\ReadModels\EventFlowDiagramData;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;

final class GetEventFlowDiagramDataUseCase
{
    public function __construct(
        private readonly EventFeedRepositoryInterface $feedRepository,
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
    ) {}

    /**
     * Builds flow diagram nodes/edges from recent feed rows (origins → Event Bus). No static module graph.
     */
    public function execute(): array
    {
        $recentEntries = $this->feedRepository->getRecent(200);
        $statuses      = $this->nodeStatusRepository->getAllStatuses();

        return $this->diagramFromFeed($recentEntries, $statuses)->toArray();
    }

    /**
     * @param \App\Dashboard\Domain\ReadModels\EventFeedEntry[] $recentEntries
     */
    private function diagramFromFeed(array $recentEntries, array $statuses): EventFlowDiagramData
    {
        $originAggregates = [];
        $edgeCounts       = [];

        foreach ($recentEntries as $entry) {
            $label = $entry->origin->value();
            $slug  = (string) preg_replace('/[^a-z0-9]+/i', '-', strtolower($label));
            $slug  = trim($slug, '-');
            $oid   = $slug !== '' ? 'origin_'.$slug : 'origin-unknown';

            $originAggregates[$oid] = [
                'label' => $label,
                'count' => ($originAggregates[$oid]['count'] ?? 0) + 1,
            ];

            $edgeKey              = $oid."\t".$entry->eventType;
            $edgeCounts[$edgeKey] = ($edgeCounts[$edgeKey] ?? 0) + 1;
        }

        $nodes = [[
            'id'           => 'middleware',
            'label'        => 'Event Bus',
            'status'       => $statuses['middleware']['status'] ?? 'OFFLINE',
            'events_count' => count($recentEntries),
        ]];

        foreach ($originAggregates as $id => $meta) {
            $nodes[] = [
                'id'           => $id,
                'label'        => (string) $meta['label'],
                'status'       => 'ONLINE',
                'events_count' => (int) $meta['count'],
            ];
        }

        $edges = [];
        foreach ($edgeCounts as $edgeKey => $count) {
            [$fromId, $eventType] = explode("\t", $edgeKey, 2);
            $edges[] = [
                'from'       => $fromId,
                'to'         => 'middleware',
                'event_type' => $eventType,
                'count'      => $count,
            ];
        }

        return new EventFlowDiagramData(
            nodes:       $nodes,
            edges:       $edges,
            totalEvents: count($recentEntries),
            updatedAt:   now()->toDateTimeString(),
        );
    }
}
