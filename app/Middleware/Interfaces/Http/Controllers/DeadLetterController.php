<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\RequeueDeadLetterUseCase;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

/**
 * Exposes and manages the dead-letter queue.
 * Supports the DeadLetterPanel UI component.
 */
final class DeadLetterController
{
    public function __construct(
        private readonly GetDeadLetterQueueUseCase    $getDeadLetters,
        private readonly DeadLetterRepositoryInterface $deadLetterRepository,
        private readonly RequeueDeadLetterUseCase     $requeueDeadLetter,
    ) {}

    /**
     * GET /api/middleware/dead-letters
     * Returns all unresolved dead-letter entries (synced from failed_jobs).
     */
    public function index(): JsonResponse
    {
        $entries = $this->getDeadLetters->execute();

        return response()->json([
            'success' => true,
            'data'    => array_map(fn($e) => $e->toArray(), $entries),
            'count'   => count($entries),
        ]);
    }

    /**
     * PATCH /api/middleware/dead-letters/{id}/resolve
     * Marks a dead-letter entry as resolved (manual dismissal).
     */
    public function resolve(int $id): JsonResponse
    {
        Gate::authorize('platform.resolve-dead-letter');

        $this->deadLetterRepository->markResolved($id);

        return response()->json([
            'success' => true,
            'message' => "Dead letter #{$id} marked as resolved.",
        ]);
    }

    /**
     * POST /api/middleware/dead-letters/{id}/requeue
     * Requeues a dead-letter entry for reprocessing.
     */
    public function requeue(int $id): JsonResponse
    {
        Gate::authorize('platform.resolve-dead-letter');

        try {
            $this->requeueDeadLetter->execute($id);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Dead letter #{$id} requeued for processing.",
        ]);
    }
}
