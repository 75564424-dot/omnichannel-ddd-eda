<?php

declare(strict_types=1);

namespace App\Dashboard\Interfaces\Http\Controllers;

use App\Dashboard\Application\UseCases\GetRecentEventFeedUseCase;
use App\Shared\Api\Http\Responses\PaginationEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EventFeedController
{
    public function __construct(
        private readonly GetRecentEventFeedUseCase $getRecentFeed,
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->query('page') !== null) {
            [$page, $limit] = PaginationEnvelope::resolvePageLimit(
                $request->query('page') !== null ? (int) $request->query('page') : null,
                $request->query('limit') !== null ? (int) $request->query('limit') : null,
            );
            $entries = $this->getRecentFeed->executePaginated($page, $limit);

            return response()->json(
                PaginationEnvelope::wrap(
                    array_map(fn ($e) => $e->toArray(), $entries),
                    $page,
                    $limit,
                    $this->getRecentFeed->countAll(),
                ),
            );
        }

        $limit   = (int) ($request->query('limit', 50));
        $entries = $this->getRecentFeed->execute($limit);

        return response()->json([
            'success' => true,
            'data'    => array_map(fn ($e) => $e->toArray(), $entries),
            'count'   => count($entries),
        ]);
    }
}
