<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Application\Security\OperatorSessionTerminator;
use App\Models\User;
use App\Shared\Identity\Domain\PlatformRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures authenticated users belong to this deployment silo (control plane vs client).
 */
final class EnsureAuthenticatedInstanceBinding
{
    public function __construct(
        private readonly OperatorSessionTerminator $sessionTerminator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $next($request);
        }

        $role = PlatformRole::tryFromString((string) $user->getAttribute('platform_role'));
        $isControlPlane = (bool) config('platform.control_plane', false);

        if ($role !== null && $role->isSaasAdmin() && ! $isControlPlane) {
            return $this->forceLogout(
                $request,
                'El panel SaaS solo está disponible en la URL del control plane (puerto del registro central).',
            );
        }

        return $next($request);
    }

    private function forceLogout(Request $request, string $message): Response
    {
        $this->sessionTerminator->terminate($request);

        return redirect()
            ->route('login')
            ->withErrors(['email' => $message]);
    }
}
