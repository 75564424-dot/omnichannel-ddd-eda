<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Middleware\AuthenticatePlatformApi;
use App\Shared\Logging\StructuredLogContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures X-Correlation-ID on every API request and shares structured log context (Plan_Observabilidad Fase 1).
 */
final class CorrelationIdMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        StructuredLogContext::reset();

        $correlation = $request->header('X-Correlation-Id')
            ?? $request->header('X-Correlation-ID')
            ?? $request->input('correlation_id');

        if (! is_string($correlation) || ! Uuid::isValid($correlation)) {
            $correlation = Uuid::uuid4()->toString();
        }

        StructuredLogContext::setCorrelationId($correlation);

        $eventId = $request->input('event_id');
        if (is_string($eventId) && Uuid::isValid($eventId)) {
            StructuredLogContext::setEventUuid($eventId);
        }

        $principal = AuthenticatePlatformApi::principal($request);
        if ($principal !== null) {
            StructuredLogContext::setActorId($principal->actorId);
        }

        Log::shareContext(StructuredLogContext::toArray());

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Correlation-ID', $correlation);

        StructuredLogContext::reset();

        return $response;
    }

    public static function currentCorrelationId(): ?string
    {
        $ctx = StructuredLogContext::toArray();

        return $ctx['correlation_id'] ?? null;
    }
}
