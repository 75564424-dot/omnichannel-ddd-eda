<?php

declare(strict_types=1);

namespace App\Dashboard\Application\DTOs;

use App\Dashboard\Domain\ReadModels\NodeStatusSnapshot;

final class NodeStatusDTO
{
    public function __construct(
        private readonly NodeStatusSnapshot $snapshot,
    ) {}

    public static function fromReadModel(NodeStatusSnapshot $snapshot): self
    {
        return new self($snapshot);
    }

    public function toArray(): array
    {
        $out = [];
        foreach ($this->snapshot->statusByNode as $key => $status) {
            $out[$key] = [
                'status'                    => $status->value(),
                'middleware_events_enabled' => $this->snapshot->middlewareEventsEnabledFor($key),
            ];
        }
        $out['last_updated'] = $this->snapshot->lastUpdated;

        return $out;
    }
}
