<?php

declare(strict_types=1);

namespace App\Integration\Application\Presenters;

use Illuminate\Http\JsonResponse;

final class IntegrationHttpPresenter
{
    /**
     * @param list<array<string, mixed>> $items
     */
    public function list(array $items): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $items,
            'count'   => count($items),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function show(array $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function notFound(string $error): JsonResponse
    {
        return response()->json(['success' => false, 'error' => $error], 404);
    }

    public function created(string $id): JsonResponse
    {
        return response()->json(['success' => true, 'id' => $id], 201);
    }

    public function updated(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Integration updated.']);
    }

    public function deleted(): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Integration deleted.']);
    }
}
