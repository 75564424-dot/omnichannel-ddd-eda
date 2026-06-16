<?php

declare(strict_types=1);

namespace App\Dashboard\Application\Services;

use App\Dashboard\Domain\Repositories\NodeStatusRepositoryInterface;

/**
 * ACL: decides whether a bus envelope should be projected into the dashboard feed.
 */
final class DashboardIngestionGateService
{
    public function __construct(
        private readonly NodeStatusRepositoryInterface $nodeStatusRepository,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function allows(string $eventType, array $payload): bool
    {
        $gates = config('dashboard.ingestion_gates', []);
        $spec  = $gates[$eventType] ?? null;
        if ($spec === null) {
            return true;
        }

        if (($spec['type'] ?? '') !== 'channel_nodes') {
            return true;
        }

        $channelField = (string) ($spec['channel_field'] ?? 'channel');
        $ch           = strtoupper(trim((string) ($payload[$channelField] ?? '')));
        $webVal       = strtoupper((string) ($spec['web_value'] ?? 'WEB'));
        $nodeKey      = $ch === $webVal
            ? (string) ($spec['web_node'] ?? 'web')
            : (string) ($spec['default_node'] ?? 'default');

        return $this->nodeStatusRepository->middlewareEventsEnabled($nodeKey);
    }
}
