<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\GetMiddlewareBusMetricsUseCase;
use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Dashboard\Application\UseCases\RefreshSystemNodeUseCase;
use App\Dashboard\Application\UseCases\SetNodeMiddlewareEventsUseCase;
use App\Dashboard\Domain\DashboardKnownNodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NodeStatusController
{
    public function __construct(
        private readonly GetSystemNodeStatusUseCase     $getNodeStatus,
        private readonly GetMiddlewareBusMetricsUseCase $getBusMetrics,
        private readonly RefreshSystemNodeUseCase       $refreshNode,
        private readonly SetNodeMiddlewareEventsUseCase $setMiddlewareEvents,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json($this->getNodeStatus->execute()->toArray());
    }

    public function busMetrics(): JsonResponse
    {
        return response()->json($this->getBusMetrics->execute()->toArray());
    }

    public function refresh(string $node): JsonResponse
    {
        abort_unless(DashboardKnownNodes::exists($node), 404);
        $this->refreshNode->execute($node);

        return response()->json($this->getNodeStatus->execute()->toArray());
    }

    public function patchMiddlewareEvents(Request $request, string $node): JsonResponse
    {
        abort_unless(DashboardKnownNodes::exists($node), 404);
        $data = $request->validate([
            'middleware_events_enabled' => ['required', 'boolean'],
        ]);
        $this->setMiddlewareEvents->execute($node, (bool) $data['middleware_events_enabled']);

        return response()->json($this->getNodeStatus->execute()->toArray());
    }
}
