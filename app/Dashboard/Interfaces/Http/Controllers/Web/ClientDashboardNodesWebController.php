<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers\Web;

use App\Dashboard\Application\UseCases\GetSystemNodeStatusUseCase;
use App\Dashboard\Application\UseCases\RefreshSystemNodeUseCase;
use App\Dashboard\Application\UseCases\SetNodeMiddlewareEventsUseCase;
use App\Dashboard\Domain\DashboardKnownNodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

final class ClientDashboardNodesWebController
{
    public function __construct(
        private readonly GetSystemNodeStatusUseCase $getNodeStatus,
        private readonly RefreshSystemNodeUseCase $refreshNode,
        private readonly SetNodeMiddlewareEventsUseCase $setMiddlewareEvents,
    ) {}

    public function status(): JsonResponse
    {
        return response()->json($this->getNodeStatus->execute()->toArray());
    }

    public function refresh(string $node): JsonResponse
    {
        $nodeKey = $this->resolveNodeKey($node);
        abort_unless(DashboardKnownNodes::exists($nodeKey), 404);

        try {
            $this->refreshNode->execute($nodeKey);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return response()->json($this->getNodeStatus->execute()->toArray());
    }

    public function patchMiddlewareEvents(Request $request, string $node): JsonResponse
    {
        $nodeKey = $this->resolveNodeKey($node);
        abort_unless(DashboardKnownNodes::exists($nodeKey), 404);

        if (! $request->has('middleware_events_enabled')) {
            return response()->json(['message' => 'middleware_events_enabled es obligatorio.'], 422);
        }

        $enabled = $request->boolean('middleware_events_enabled');

        try {
            $this->setMiddlewareEvents->execute($nodeKey, $enabled);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return response()->json($this->getNodeStatus->execute()->toArray());
    }

    private function resolveNodeKey(string $node): string
    {
        return rawurldecode($node);
    }
}
