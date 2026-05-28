<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\UseCases\GetBusStatusUseCase;
use App\Middleware\Application\UseCases\SearchEventByIdUseCase;
use Illuminate\Http\JsonResponse;

/**
 * Provides event search and bus status lookup.
 * Powers the EventSearchBar and BusStatusIndicator UI components.
 */
final class EventSearchController
{
    public function __construct(
        private readonly SearchEventByIdUseCase $searchEvent,
        private readonly GetBusStatusUseCase    $getBusStatus,
    ) {}

    /**
     * GET /api/middleware/events/{eventId}
     * Searches for a specific event by its UUID.
     */
    public function show(string $eventId): JsonResponse
    {
        $entry = $this->searchEvent->execute($eventId);

        if ($entry === null) {
            return response()->json([
                'success' => false,
                'message' => "Event '{$eventId}' not found in the bus tracking log.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $entry->toArray(),
        ]);
    }

    /**
     * GET /api/middleware/status
     * Returns the current bus operational status.
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success'    => true,
            'bus_status' => $this->getBusStatus->execute(),
        ]);
    }
}
