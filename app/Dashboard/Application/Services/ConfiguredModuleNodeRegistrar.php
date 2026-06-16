<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Control\Application\Services\ClientInstancePortalService;
use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;
use App\Dashboard\Domain\ValueObjects\NodeStatus;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Schema;

/**
 * Seeds node_status_snapshots for catalog modules (disabled until operator activates in LIVE panel).
 */
final class ConfiguredModuleNodeRegistrar
{
    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
    ) {}

    /**
     * @param array<string, mixed> $catalog
     */
    public function registerFromCatalog(array $catalog): void
    {
        if (! Schema::hasTable('channel_status_snapshots')) {
            return;
        }

        $this->registerNodeKeys($this->nodeKeysFromCatalog($catalog), null);
    }

    /**
     * Mirror path — writes directly into the client silo SQLite connection.
     *
     * @param array<string, mixed> $catalog
     */
    public function registerFromCatalogOnConnection(array $catalog, Connection $connection): void
    {
        $this->registerNodeKeys($this->nodeKeysFromCatalog($catalog), $connection);
    }

    /**
     * @param array<string, mixed> $catalog
     *
     * @return list<string>
     */
    private function nodeKeysFromCatalog(array $catalog): array
    {
        $keys = ['middleware'];

        foreach (is_array($catalog['producers'] ?? null) ? $catalog['producers'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            if ($id !== '') {
                $keys[] = ClientInstancePortalService::producerNodeKey($id);
            }
        }

        foreach (is_array($catalog['subscribers'] ?? null) ? $catalog['subscribers'] : [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $id = trim((string) ($row['id'] ?? ''));
            if ($id !== '') {
                $keys[] = ClientInstancePortalService::subscriberNodeKey($id);
            }
        }

        return array_values(array_unique($keys));
    }

    /** @param list<string> $nodeKeys */
    private function registerNodeKeys(array $nodeKeys, ?Connection $connection): void
    {
        if ($connection === null) {
            foreach ($nodeKeys as $nodeKey) {
                $this->ensureNodeViaRepository($nodeKey);
            }

            return;
        }

        if (! $connection->getSchemaBuilder()->hasTable('node_status_snapshots')) {
            return;
        }

        $existing = $connection->table('node_status_snapshots')
            ->whereIn('node_code', $nodeKeys)
            ->pluck('node_code')
            ->all();
        $existingSet = array_flip($existing);
        $now = now();

        foreach ($nodeKeys as $nodeKey) {
            if (isset($existingSet[$nodeKey])) {
                continue;
            }
            $connection->table('node_status_snapshots')->insert([
                'node_code'      => $nodeKey,
                'status'         => NodeStatus::OFFLINE,
                'events_enabled' => false,
                'recorded_at'    => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        }
    }

    private function ensureNodeViaRepository(string $nodeKey): void
    {
        $statuses = $this->nodeStatusRepository->getAllStatuses();
        if (isset($statuses[$nodeKey])) {
            return;
        }

        $this->nodeStatusRepository->setMiddlewareEventsEnabled($nodeKey, false);
        $this->nodeStatusRepository->setStatus($nodeKey, NodeStatus::OFFLINE);
    }
}
