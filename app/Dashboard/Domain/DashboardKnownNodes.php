<?php

declare(strict_types=1);

namespace App\Dashboard\Domain;

use App\Control\Application\Services\ClientInstancePortalService;

/**
 * Monitored nodes are configuration-driven so the Dashboard stays product-agnostic.
 * External integration packs (e.g. retail demo) merge additional keys into config.
 */
final class DashboardKnownNodes
{
    public function __construct(
        private readonly ClientInstancePortalService $instancePortal,
    ) {}

    /** @return list<string> */
    public function keys(): array
    {
        $keys = config('dashboard.monitored_node_keys', ['middleware']);
        $keys = is_array($keys) ? $keys : ['middleware'];

        $keys = array_merge($keys, $this->instancePortal->monitoredNodeKeys());

        return array_values(array_unique(array_map(static fn ($k) => (string) $k, $keys)));
    }

    public function exists(string $name): bool
    {
        return in_array($name, $this->keys(), true);
    }
}
