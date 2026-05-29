<?php

declare(strict_types=1);

namespace App\Middleware\Application\UseCases;

use App\Middleware\Application\DTOs\TopologyDTO;
use App\Middleware\Application\Services\Topology\TopologySnapshotAssembler;

final class GetTopologySnapshotUseCase
{
    public function __construct(
        private readonly TopologySnapshotAssembler $assembler,
    ) {}

    public function execute(): TopologyDTO
    {
        return $this->assembler->assemble();
    }
}
