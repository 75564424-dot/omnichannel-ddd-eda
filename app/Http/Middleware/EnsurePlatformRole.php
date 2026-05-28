<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Shared\Identity\Contracts\PlatformAuthorizationServiceInterface;
use App\Shared\Identity\Domain\PlatformRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to users with a given platform role (or higher admin).
 */
final class EnsurePlatformRole
{
    public function __construct(
        private readonly PlatformAuthorizationServiceInterface $authorization,
    ) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! config('platform_auth.web_auth_enabled', true)) {
            return $next($request);
        }

        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->guest(route('login'));
        }

        $userRole = $this->authorization->roleForUser($user);

        if ($userRole === PlatformRole::PlatformAdmin) {
            return $next($request);
        }

        foreach ($roles as $role) {
            if ($userRole->value === $role) {
                return $next($request);
            }
        }

        abort(403, 'Forbidden — insufficient platform role.');
    }
}
