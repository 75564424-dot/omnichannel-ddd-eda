<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Security\Contracts\AuditLogWriterInterface;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records control-plane actions to audit_logs after successful responses.
 */
final class AuditControlPlaneMiddleware
{
    public function __construct(
        private readonly AuditLogWriterInterface $auditLogWriter,
    ) {}

    public function handle(Request $request, Closure $next, string $action, string $entityType): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return $response;
        }

        $principal = AuthenticatePlatformApi::principal($request);

        $this->auditLogWriter->record(
            action: $action,
            entityType: $entityType,
            entityId: $this->resolveEntityId($request),
            changes: [
                'method'      => $request->method(),
                'path'        => $request->path(),
                'input'       => $this->sanitizedInput($request),
                'actor_label' => $principal?->label,
            ],
            actorType: $principal?->actorType,
            actorId: $principal?->actorId,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return $response;
    }

    private function resolveEntityId(Request $request): string
    {
        foreach (['id', 'eventId', 'node'] as $key) {
            $value = $request->route($key);
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return 'n/a';
    }

    /**
     * @return array<string, mixed>
     */
    private function sanitizedInput(Request $request): array
    {
        $input = $request->except(['password', 'token', 'api_key']);

        if (isset($input['payload']) && is_array($input['payload'])) {
            $input['payload'] = ['_hash' => hash('sha256', json_encode($input['payload'], JSON_THROW_ON_ERROR))];
        }

        return $input;
    }
}
