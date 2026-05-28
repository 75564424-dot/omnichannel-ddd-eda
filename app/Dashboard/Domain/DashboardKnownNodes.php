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
    /** @return list<string> */
    public static function keys(): array
    {
        $keys = config('dashboard.monitored_node_keys', ['middleware']);
        $keys = is_array($keys) ? $keys : ['middleware'];

        $keys = array_merge($keys, app(ClientInstancePortalService::class)->monitoredNodeKeys());

        return array_values(array_unique(array_map(static fn ($k) => (string) $k, $keys)));
    }

    public static function exists(string $name): bool
    {
        return in_array($name, self::keys(), true);
    }
}
