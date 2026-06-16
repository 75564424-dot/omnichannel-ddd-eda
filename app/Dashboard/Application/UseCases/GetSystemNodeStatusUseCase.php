<?php

declare(strict_types=1);

namespace App\Dashboard\Application\UseCases;

use App\Dashboard\Application\DTOs\NodeStatusDTO;
use App\Dashboard\Domain\DashboardKnownNodes;
use App\Dashboard\Domain\ReadModels\NodeStatusSnapshot;
use App\Dashboard\Domain\Repositories\EventFeedRepositoryInterface;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\NodeStatus;

/**
 * Node snapshots are updated opportunistically when events arrive; transient states like
 * SYNCING are reconciled against recent dashboard feed activity.
 */
final class GetSystemNodeStatusUseCase
{
    /** Must match middleware topology “recent activity” window (see Middleware/Index.vue `FLOW_ACTIVITY_MS`). */
    private const IDLE_RECONCILE_FEED_WINDOW_SEC = 45;

    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
        private readonly EventFeedRepositoryInterface $eventFeedRepository,
        private readonly DashboardKnownNodes $knownNodes,
    ) {}

    public function execute(): NodeStatusDTO
    {
        $this->reconcileStaleSyncingIfFeedIdle();

        $keys     = $this->knownNodes->keys();
        $statuses = $this->nodeStatusRepository->getAllStatuses();

        $ingest = [];
        foreach ($keys as $key) {
            $ingest[$key] = (bool) ($statuses[$key]['middleware_events_enabled'] ?? false);
        }

        $byNode = [];
        foreach ($keys as $key) {
            $raw = strtoupper((string) ($statuses[$key]['status'] ?? NodeStatus::OFFLINE));
            $byNode[$key] = new NodeStatus($raw);
        }

        $lastUpdated = $statuses['middleware']['updated_at']
            ?? ($statuses[array_key_first($statuses) ?? '']['updated_at'] ?? now()->toDateTimeString());

        $snapshot = new NodeStatusSnapshot(
            statusByNode: $byNode,
            middlewareEventsEnabledByNode: $ingest,
            lastUpdated: is_string($lastUpdated) ? $lastUpdated : now()->toDateTimeString(),
        );

        return NodeStatusDTO::fromReadModel($snapshot);
    }

    private function reconcileStaleSyncingIfFeedIdle(): void
    {
        $transientKeys = config('dashboard.transient_sync_nodes', ['middleware']);
        $statuses      = $this->nodeStatusRepository->getAllStatuses();

        $stillSyncing = false;
        foreach ($transientKeys as $key) {
            if (strtoupper((string) ($statuses[$key]['status'] ?? '')) === NodeStatus::SYNCING) {
                $stillSyncing = true;
                break;
            }
        }

        if (! $stillSyncing) {
            return;
        }

        if ($this->eventFeedRepository->countEventsInLastSeconds(self::IDLE_RECONCILE_FEED_WINDOW_SEC) > 0) {
            return;
        }

        foreach ($transientKeys as $key) {
            if (strtoupper((string) ($statuses[$key]['status'] ?? '')) === NodeStatus::SYNCING) {
                $this->nodeStatusRepository->setStatus($key, NodeStatus::ONLINE);
            }
        }
    }
}
