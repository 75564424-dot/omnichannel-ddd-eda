<?php

declare(strict_types=1);

namespace App\Middleware\Interfaces\Http\Controllers;

use App\Middleware\Application\Services\EventPublisherService;
use App\Middleware\Application\Support\MiddlewarePlatformAuthorizer;
use App\Middleware\Application\UseCases\GetEventQueueUseCase;
use App\Middleware\Domain\ValueObjects\CorrelationContext;
use App\Shared\Api\Application\Services\IdempotencyKeyStore;
use App\Shared\Api\Http\Responses\PaginationEnvelope;
use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * Exposes the FIFO event queue for monitoring and allows publishing events via API.
 */
final class EventQueueController
{
    public function __construct(
        private readonly GetEventQueueUseCase $getEventQueue,
        private readonly EventPublisherService $eventPublisher,
        private readonly IdempotencyKeyStore $idempotencyKeys,
        private readonly MiddlewarePlatformAuthorizer $authorizer,
    ) {}

    /**
     * GET /api/middleware/queue — supports ?limit= or ?page=&limit=
     */
    public function index(Request $request): JsonResponse
    {
        if ($request->query('page') !== null) {
            [$page, $limit] = PaginationEnvelope::resolvePageLimit(
                $request->query('page') !== null ? (int) $request->query('page') : null,
                $request->query('limit') !== null ? (int) $request->query('limit') : null,
            );
            $entries = $this->getEventQueue->executePaginated($page, $limit);

            return response()->json(
                PaginationEnvelope::wrap(
                    array_map(fn ($e) => $e->toArray(), $entries),
                    $page,
                    $limit,
                    $this->getEventQueue->countAll(),
                ),
            );
        }

        $limit   = (int) $request->query('limit', 50);
        $limit   = max(1, min($limit, 200));
        $entries = $this->getEventQueue->execute($limit);

        return response()
            ->json([
                'success' => true,
                'data'    => array_map(fn ($e) => $e->toArray(), $entries),
                'count'   => count($entries),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * POST /api/middleware/events/publish
     * Headers: X-Correlation-Id, Idempotency-Key (optional)
     */
    public function publish(Request $request): JsonResponse
    {
        $this->authorizer->authorizePublish();

        $idempotencyHeader = (string) config('platform_api.idempotency.header', 'Idempotency-Key');
        $idempotencyKey    = $request->header($idempotencyHeader);
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $cached = $this->idempotencyKeys->get($idempotencyKey);
            if ($cached !== null) {
                return response()->json($cached['body'], $cached['status']);
            }
        }

        $envelope = $request->only([
            'event_id',
            'event_type',
            'payload',
            'occurred_at',
            'origin',
            'correlation_id',
            'causation_id',
            'event_version',
            'schema_version',
            'aggregate_type',
            'aggregate_id',
            'metadata',
        ]);

        $correlation = CorrelationContext::fromHttp($envelope, $this->flattenHeaders($request));
        if ($correlation->correlationId !== null) {
            $envelope['correlation_id'] = $correlation->correlationId;
        }
        if ($correlation->causationId !== null) {
            $envelope['causation_id'] = $correlation->causationId;
        }

        try {
            $result = $this->eventPublisher->publish($envelope);
        } catch (InvalidArgumentException $e) {
            if (config('platform_api.problem_details.enabled', true)) {
                return ProblemDetailsFactory::validation($e->getMessage());
            }

            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        if ($result->idempotent) {
            $body = [
                'success' => true,
                'status'  => 'already_processed',
                'entry_id'=> $result->entryId,
                'message' => 'Event already published (idempotent).',
            ];
            $response = response()->json($body, 200);
        } else {
            $body = [
                'success'  => true,
                'entry_id' => $result->entryId,
                'message'  => 'Event published to bus.',
            ];
            $response = response()->json($body, 201);
        }

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $this->idempotencyKeys->remember($idempotencyKey, $response->getStatusCode(), $body);
        }

        return $response;
    }

    /**
     * @return array<string, string|null>
     */
    private function flattenHeaders(Request $request): array
    {
        $flat = [];
        foreach ($request->headers->all() as $key => $values) {
            $flat[strtolower((string) $key)] = is_array($values) ? ($values[0] ?? null) : $values;
        }

        return $flat;
    }
}
