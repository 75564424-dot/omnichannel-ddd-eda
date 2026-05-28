<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\UseCases\GetTopologySnapshotUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Exposes the system topology: producers → bus → consumers.
 * Used by the TopologyDiagram component in the control UI.
 */
final class TopologyController
{
    public function __construct(
        private readonly GetTopologySnapshotUseCase $getTopologySnapshot,
    ) {}

    /**
     * GET /api/middleware/topology
     */
    public function index(): JsonResponse
    {
        $topology = $this->getTopologySnapshot->execute();

        return response()->json([
            'success' => true,
            'data'    => $topology->toArray(),
        ]);
    }
}
