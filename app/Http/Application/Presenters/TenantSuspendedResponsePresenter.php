<?php

declare(strict_types=1);

namespace App\Http\Application\Presenters;

use App\Shared\Api\Http\Responses\ProblemDetailsFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

final class TenantSuspendedResponsePresenter
{
    private const MESSAGE = 'El servicio asociado a esta empresa se encuentra temporalmente suspendido. Contacte al administrador para obtener más información.';

    public function respond(Request $request): Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            return ProblemDetailsFactory::make(
                title: 'Tenant Suspended',
                status: 403,
                detail: self::MESSAGE,
                type: 'tenant_suspended',
            );
        }

        return Inertia::render('Tenant/Suspended', [
            'message' => self::MESSAGE,
        ])->toResponse($request)->setStatusCode(503);
    }
}
