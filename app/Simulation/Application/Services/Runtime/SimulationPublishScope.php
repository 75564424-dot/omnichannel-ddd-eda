<?php

declare(strict_types=1);

namespace App\Simulation\Application\Services\Runtime;

/**
 * Request-scoped flag: while simulating, enqueue events as PENDING and drain after the publish loop.
 */
final class SimulationPublishScope
{
    private bool $deferProcessing = false;

    public function beginDeferring(): void
    {
        $this->deferProcessing = true;
    }

    public function endDeferring(): void
    {
        $this->deferProcessing = false;
    }

    public function shouldDeferProcessing(): bool
    {
        return $this->deferProcessing;
    }
}
