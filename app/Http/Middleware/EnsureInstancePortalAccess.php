<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Application\Security\OperatorSessionTerminator;
use App\Models\User;
use App\Shared\Platform\Services\InstancePortalAccessGuard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Instance portal: tenant membership + role-scoped dashboard/middleware routes.
 */
final class EnsureInstancePortalAccess
{
    public function __construct(
        private readonly InstancePortalAccessGuard $accessGuard,
        private readonly OperatorSessionTerminator $sessionTerminator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return redirect()->guest(route('login'));
        }

        $decision = $this->accessGuard->evaluate($user, $request->path());

        if ($decision['allowed']) {
            return $next($request);
        }

        if ($decision['logout'] ?? false) {
            $this->sessionTerminator->terminate($request);
        }

        $redirect = redirect()->to($decision['redirect'] ?? route('login'));
        if (isset($decision['error'])) {
            $redirect->withErrors(['email' => $decision['error']]);
        }

        return $redirect;
    }
}
