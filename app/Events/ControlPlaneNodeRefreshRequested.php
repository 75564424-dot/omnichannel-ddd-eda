<?php

declare(strict_types=1);

namespace App\Events;

/**
 * Dispatched when an operator forces a node back online / re-enables bus ingestion.
 * Generic control-plane signal — not tied to a specific product or UI module name.
 */
final class ControlPlaneNodeRefreshRequested
{
    public function __construct(public readonly string $nodeKey) {}
}
