<?php

declare(strict_types=1);

namespace App\Middleware\Application\Presenters;

use Illuminate\Http\JsonResponse;

final class DeadLetterHttpPresenter
{
    /**
     * @param list<array<string, mixed>> $entries
     */
    public function list(array $entries): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $entries,
            'count'   => count($entries),
        ]);
    }

    public function resolved(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Dead letter #{$id} marked as resolved.",
        ]);
    }

    public function requeued(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => "Dead letter #{$id} requeued for processing.",
        ]);
    }

    public function notFound(string $error): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $error], 404);
    }
}
