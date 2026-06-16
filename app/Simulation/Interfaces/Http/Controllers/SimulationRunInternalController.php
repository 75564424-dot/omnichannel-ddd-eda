<?php

declare(strict_types=1);

namespace App\Simulation\Interfaces\Http\Controllers;

use App\Simulation\Application\Services\Progress\SimulationRunInternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SimulationRunInternalController
{
    public function __construct(
        private readonly SimulationRunInternalApiService $api,
    ) {}

    public function show(string $run): JsonResponse
    {
        $model = $this->api->findRun($run);

        return response()->json(['data' => $this->api->showPayload($model)]);
    }

    public function progress(Request $request, string $run): JsonResponse
    {
        $model = $this->api->findRun($run);

        return response()->json([
            'data' => $this->api->recordProgress(
                $model,
                (int) $request->input('progress_current', 0),
                (int) $request->input('planned_total', $model->planned_total),
            ),
        ]);
    }

    public function complete(Request $request, string $run): JsonResponse
    {
        $model = $this->api->findRun($run);
        $eventIds = $this->api->normalizeEventIds($request->input('event_ids', []));
        $published = (int) $request->input('published', count($eventIds));
        $queueMatches = (int) $request->input('queue_matches', 0);

        $model = $this->api->complete($model, $eventIds, $published, $queueMatches);

        return response()->json(['data' => ['status' => $model->status]]);
    }

    public function fail(Request $request, string $run): JsonResponse
    {
        $model = $this->api->findRun($run);
        $context = $request->input('context', []);
        if (! is_array($context)) {
            $context = [];
        }

        $this->api->fail($model, (string) $request->input('error_message', 'Simulation failed.'), $context);

        return response()->json(['data' => ['status' => $model->fresh()->status]]);
    }
}
