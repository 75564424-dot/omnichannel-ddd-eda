<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\Presenters\DeadLetterHttpPresenter;
use App\Middleware\Application\Support\MiddlewarePlatformAuthorizer;
use App\Middleware\Application\UseCases\GetDeadLetterQueueUseCase;
use App\Middleware\Application\UseCases\RequeueDeadLetterUseCase;
use App\Middleware\Domain\Repositories\DeadLetterRepositoryInterface;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Exposes and manages the dead-letter queue.
 * Supports the DeadLetterPanel UI component.
 */
final class DeadLetterController
{
    public function __construct(
        private readonly GetDeadLetterQueueUseCase $getDeadLetters,
        private readonly DeadLetterRepositoryInterface $deadLetterRepository,
        private readonly RequeueDeadLetterUseCase $requeueDeadLetter,
        private readonly MiddlewarePlatformAuthorizer $authorizer,
        private readonly DeadLetterHttpPresenter $presenter,
    ) {}

    /**
     * GET /api/middleware/dead-letters
     * Returns all unresolved dead-letter entries (synced from failed_jobs).
     */
    public function index(): JsonResponse
    {
        $entries = $this->getDeadLetters->execute();

        return $this->presenter->list(array_map(fn ($e) => $e->toArray(), $entries));
    }

    /**
     * PATCH /api/middleware/dead-letters/{id}/resolve
     * Marks a dead-letter entry as resolved (manual dismissal).
     */
    public function resolve(int $id): JsonResponse
    {
        $this->authorizer->authorizeResolveDeadLetter();

        $this->deadLetterRepository->markResolved($id);

        return $this->presenter->resolved($id);
    }

    /**
     * POST /api/middleware/dead-letters/{id}/requeue
     * Requeues a dead-letter entry for reprocessing.
     */
    public function requeue(int $id): JsonResponse
    {
        $this->authorizer->authorizeResolveDeadLetter();

        try {
            $this->requeueDeadLetter->execute($id);
        } catch (RuntimeException $e) {
            return $this->presenter->notFound($e->getMessage());
        }

        return $this->presenter->requeued($id);
    }
}
