<?php

declare(strict_types=1);

namespace App\Dashboard\Domain\ReadModels;

use App\Dashboard\Domain\ValueObjects\NodeStatus;

/**
 * Read Model: current status of each configured system node (dynamic set of keys).
 */
final class NodeStatusSnapshot
{
    /**
     * @param array<string, NodeStatus> $statusByNode
     * @param array<string, bool>      $middlewareEventsEnabledByNode
     */
    public function __construct(
        public readonly array $statusByNode,
        public readonly array $middlewareEventsEnabledByNode,
        public readonly string $lastUpdated,
    ) {}

    public function middlewareEventsEnabledFor(string $nodeKey): bool
    {
        return $this->middlewareEventsEnabledByNode[$nodeKey] ?? false;
    }
}
