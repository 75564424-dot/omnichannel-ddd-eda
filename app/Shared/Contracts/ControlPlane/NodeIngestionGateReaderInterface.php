<?php

declare(strict_types=1);

namespace App\Shared\Contracts\ControlPlane;

/**
 * Port implemented by the observability/control plane so external modules can query
 * whether events from a logical node should be accepted — without referencing Dashboard types.
 */
interface NodeIngestionGateReaderInterface
{
    public function middlewareEventsEnabled(string $nodeKey): bool;
}
