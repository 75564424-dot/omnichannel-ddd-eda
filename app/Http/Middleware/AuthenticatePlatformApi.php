<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use App\Shared\Security\Contracts\PlatformApiAuthenticatorInterface;
use App\Shared\Security\PlatformApiPrincipal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticatePlatformApi
{
    public const PRINCIPAL_ATTRIBUTE = 'platform_api_principal';

    public function __construct(
        private readonly PlatformApiAuthenticatorInterface $authenticator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.api_auth_enabled', true)) {
            return $next($request);
        }

        $principal = $this->authenticator->authenticate($request);
        if ($principal === null) {
            if (config('platform_api.problem_details.enabled', true)) {
                return ProblemDetailsFactory::unauthorized(
                    'Provide Authorization Bearer token or X-API-Key header.',
                );
            }

            return response()->json([
                'success' => false,
                'error'   => 'Unauthenticated. Provide Authorization Bearer token or X-API-Key header.',
            ], 401);
        }

        $request->attributes->set(self::PRINCIPAL_ATTRIBUTE, $principal);

        return $next($request);
    }

    public static function principal(Request $request): ?PlatformApiPrincipal
    {
        $principal = $request->attributes->get(self::PRINCIPAL_ATTRIBUTE);

        return $principal instanceof PlatformApiPrincipal ? $principal : null;
    }
}
